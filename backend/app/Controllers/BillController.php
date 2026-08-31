<?php
/**
 * Bill Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Core\Pagination;
use App\Services\BillingService;
use App\Services\EmailService;
use App\Core\JWT;

class BillController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $page = Pagination::fromRequest();

        $month = $_GET['month'] ?? date('Y-m');
        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;

        $sql = "SELECT b.*, b.total as amount, h.unit as house_unit, h.unit, p.name as property_name, p.id as property_id, t.name as tenant_name, t.id as tenant_id
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON b.tenant_id = t.id
             WHERE b.owner_id = ? AND b.month = ?";
        $queryParams = [$ownerId, $month];

        $countSql = "SELECT COUNT(DISTINCT b.id) as total FROM bills b LEFT JOIN houses h ON b.house_id = h.id WHERE b.owner_id = ? AND b.month = ?";
        $countParams = [$ownerId, $month];

        if ($role === 'tenant') {
            $sql .= " AND t.id = ?";
            $queryParams[] = Router::getAuthTenantId();

            $countSql .= " AND b.tenant_id = ?";
            $countParams[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['bills' => [], 'meta' => Pagination::meta(0, $page['page'], $page['per_page'])]);
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            $sql .= " AND h.property_id IN ($placeholders)";
            $queryParams = array_merge($queryParams, $propertyIds);

            $countSql .= " AND h.property_id IN ($placeholders)";
            $countParams = array_merge($countParams, $propertyIds);
        }

        if ($propertyId > 0) {
            $sql .= " AND p.id = ?";
            $queryParams[] = $propertyId;

            $countSql .= " AND h.property_id = ?";
            $countParams[] = $propertyId;
        }

        $sql .= " ORDER BY p.name, h.unit LIMIT ?, ?";
        $queryParams[] = $page['offset'];
        $queryParams[] = $page['limit'];

        $bills = $db->fetchAll($sql, $queryParams);
        $billingService = new BillingService();

        // Calculate paid amount from bill item allocations. This keeps the
        // invoice tied to the snapshotted tenant instead of the current unit occupant.
        foreach ($bills as &$bill) {
            $paid = $billingService->getBillPaidTotal($bill);

            $bill['paid'] = $paid;
            $bill['balance'] = max(0.0, (float)$bill['total'] - $paid);
            if ($paid <= 0) {
                $bill['status'] = 'pending';
            } elseif ($paid >= (float)$bill['total']) {
                $bill['status'] = 'paid';
            } else {
                $bill['status'] = 'partial';
            }
        }
        unset($bill);

        $totalRow = $db->fetchOne($countSql, $countParams);
        $total = (int) ($totalRow['total'] ?? 0);

        Router::jsonResponse(['bills' => $bills, 'meta' => Pagination::meta($total, $page['page'], $page['per_page'])]);
    }

    /**
     * POST /api/bills/generate - Generate bills for occupied houses
     * Owners can generate for all properties, tenants can only generate for themselves
     */
    public function generate(array $params = []): void
    {
        AuthMiddleware::authenticate();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $currentMonth = date('Y-m');
        $month = $data['month'] ?? $currentMonth;
        $dueDate = $data['due_date'] ?? date('Y-m-05');
        $propertyId = isset($data['property_id']) ? (int) $data['property_id'] : 0;

        // Lock generation to current month only (no past or future months)
        if ($month !== $currentMonth) {
            Router::jsonResponse(['error' => 'Bills can only be generated for the current month (' . $currentMonth . '). Please use the current month.'], 400);
        }

        // Day-of-month gate (configurable). Set BILL_GENERATION_DAY in .env to enforce
        // generation only from a specific day of the month (e.g. 25). Leave unset or 0
        // to allow bill generation on any day (useful for development/Q&A and pre-billing).
        $genDay = (int)(getenv('BILL_GENERATION_DAY') ?: ($_ENV['BILL_GENERATION_DAY'] ?? 0));
        if ($genDay > 0 && (int) date('d') < $genDay) {
            Router::jsonResponse(['error' => "Bill generation is only allowed on or after day {$genDay} of the month. Please wait until the {$genDay}th."], 400);
        }

        // Build query based on role
        $sql = "SELECT h.id, h.rent, h.water_meter, h.elec_meter, h.unit, p.name as property_name, t.id as tenant_id, t.name as tenant_name, t.balance
                FROM houses h
                LEFT JOIN properties p ON h.property_id = p.id
                LEFT JOIN tenants t ON h.tenant_id = t.id
                WHERE h.owner_id = ? AND h.status = 'occupied' AND h.tenant_id IS NOT NULL";
        $queryParams = [$ownerId];

        // If tenant, only allow generating their own bill
        if ($role === 'tenant') {
            $tenantId = Router::getAuthTenantId();
            $sql .= " AND t.id = ?";
            $queryParams[] = $tenantId;
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) {
                Router::jsonResponse(['error' => 'No properties assigned'], 403);
            }
            $sql .= " AND h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $queryParams = array_merge($queryParams, $propertyIds);
        }

        if ($propertyId > 0 && $role !== 'tenant') {
            $sql .= " AND h.property_id = ?";
            $queryParams[] = $propertyId;
        }

        $sql .= " ORDER BY p.name, h.unit";
        $houses = $db->fetchAll($sql, $queryParams);

        $generated = 0;
        $summary = [];
        $newHouses = [];
        $billingService = new BillingService();

        foreach ($houses as $house) {
            $water = (float) ($data['water_charges'][$house['id']] ?? 0);
            $electricity = (float) ($data['elec_charges'][$house['id']] ?? 0);

            // Idempotent billing: if a bill already exists for this house + billing month,
            // DO NOT regenerate, recalculate, or alter it. Existing bills keep their line
            // items, paid amounts, allocations and status — so recorded payments are never
            // overwritten by a second run of "Generate Bills".
            $existingBill = $db->fetchOne(
                "SELECT id FROM bills WHERE owner_id = ? AND tenant_id = ? AND month = ? ORDER BY id LIMIT 1",
                [$ownerId, $house['tenant_id'], $month]
            );

            if ($existingBill) {
                $billId = (int) $existingBill['id'];
            } else {
                $tenant = $db->fetchOne(
                    "SELECT deposit FROM tenants WHERE id = ? AND owner_id = ?",
                    [$house['tenant_id'], $ownerId]
                );
                $depositAmount = (float) ($tenant['deposit'] ?? 0);
                $chargeItems = [
                    ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => (float)$house['rent']],
                ];
                if ($depositAmount > 0) {
                    $chargeItems[] = ['type' => 'Deposit', 'description' => 'Security Deposit', 'amount' => $depositAmount];
                }
                if ($water > 0) {
                    $chargeItems[] = ['type' => 'Water', 'description' => 'Water Charges', 'amount' => $water];
                }
                if ($electricity > 0) {
                    $chargeItems[] = ['type' => 'Electricity', 'description' => 'Electricity Charges', 'amount' => $electricity];
                }
                $billId = $billingService->createBillWithItems(
                    $ownerId,
                    $house['id'],
                    $house['tenant_id'],
                    $month,
                    $dueDate,
                    $chargeItems
                );
                $generated++;
                $newHouses[] = $house;
            }

            $billItems = $billingService->getBillItems($billId);
            $totalBill = array_sum(array_column($billItems, 'amount'));
            $paidBill = array_sum(array_column($billItems, 'paid'));

            foreach ($billItems as $item) {
                $summary[] = [
                    'tenant_name' => $house['tenant_name'] ?? 'Vacant',
                    'unit' => $house['unit'] ?? '',
                    'property' => $house['property_name'] ?? '',
                    'type' => $item['description'] ?? $item['type'],
                    'expected' => (float)$item['amount'],
                    'paid' => (float)$item['paid'],
                    'arrears' => max(0.0, (float)$item['amount'] - (float)$item['paid']),
                    'status' => $item['status'],
                ];
            }
        }

        // If there are no occupied units to bill, report that clearly.
        if (empty($houses)) {
            Router::jsonResponse([
                'message' => 'No occupied units found for billing.',
                'count' => 0,
                'month' => $month,
                'summary' => [],
                'emails_sent' => 0,
                'emails_failed' => 0,
            ]);
            return;
        }

        // Bills are idempotent per house + month: if every unit already has a bill for
        // this month, nothing new was created. Report that clearly and do NOT re-send
        // invoice emails, so an owner can never double-bill or spam tenants by clicking
        // "Generate Bills" more than once in a month.
        if ($generated === 0) {
            Router::jsonResponse([
                'message' => "Bills for {$month} already exist. No new bills were created and no emails were re-sent.",
                'count' => 0,
                'month' => $month,
                'summary' => $summary,
                'emails_sent' => 0,
                'emails_failed' => 0,
                'already_existed' => true,
            ]);
            return;
        }

        // Send bill notification emails ONLY to tenants whose bills were newly created
        // in this run, so a repeated "Generate Bills" never sends duplicate emails.
        $emailSent = 0;
        $emailFailed = 0;
        $emailService = new EmailService();

        // Property manager / owner name for a more personal, informative email.
        $ownerName = '';
        try {
            $ownerRow = $db->fetchOne("SELECT name FROM owners WHERE id = ?", [$ownerId]);
            $ownerName = $ownerRow['name'] ?? '';
        } catch (\Throwable $e) {
            $ownerName = '';
        }

        foreach ($newHouses as $house) {
            if (!empty($house['tenant_name']) && !empty($house['tenant_id'])) {
                $tenant = $db->fetchOne(
                    "SELECT id, name, email, next_of_kin_email, next_of_kin_name, house_id, property_id FROM tenants WHERE id = ? AND owner_id = ?",
                    [$house['tenant_id'], $ownerId]
                );
                
                if ($tenant && !empty($tenant['email'])) {
                    // Calculate total bill amount for this tenant
                    $tenantBills = $db->fetchAll(
                        "SELECT id, type, total FROM bills WHERE owner_id = ? AND tenant_id = ? AND month = ?",
                        [$ownerId, $house['tenant_id'], $month]
                    );

                    if (empty($tenantBills)) {
                        error_log('No bills found after generation for house ' . $house['id'] . ' month ' . $month);
                        $emailFailed++;
                        continue;
                    }
                    
                    $totalBill = array_sum(array_column($tenantBills, 'total'));
                    
                    // Generate invoice token
                    $jwt = new JWT();
                    $invoiceToken = $jwt->encode([
                        'owner_id' => $ownerId,
                        'actor_id' => $tenant['id'],
                        'role' => 'tenant',
                        'tenant_id' => $tenant['id']
                    ]);
                    
                    $appUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? 'http://localhost/RentalFlow');
                    $invoiceUrl = $appUrl . "/api/bills/{$tenantBills[0]['id']}/invoice?token={$invoiceToken}";
                    
                    $variables = [
                        'tenant' => $tenant['name'],
                        'recipient_name' => $tenant['name'],
                        'recipient_note' => 'This is the monthly invoice for your tenancy at ' . ($house['property_name'] ?? 'our property') . ', unit ' . ($house['unit'] ?? '') . '.',
                        'amount' => number_format($totalBill, 2),
                        'month' => date('F Y', strtotime($month . '-01')),
                        'property' => $house['property_name'] ?? '',
                        'house' => $house['unit'] ?? '',
                        'balance' => number_format($totalBill, 2),
                        'date' => date('Y-m-d'),
                        'invoice_url' => $invoiceUrl,
                        'owner_name' => $ownerName,
                        'payment_instructions' => 'Please arrange full payment by the due date. You can pay via M-Pesa or through the channel provided by the property office, and keep your payment reference for your records.',
                    ];
                    
                    try {
                        $sent = $emailService->sendTemplate('Billing Notification', $ownerId, $tenant['email'], $tenant['name'], $variables);
                        if ($sent) $emailSent++; else $emailFailed++;
                    } catch (\Exception $e) {
                        error_log('Failed to send bill email to ' . $tenant['email'] . ': ' . $e->getMessage());
                        $emailFailed++;
                    }
                    
                    // Also send to next of kin
                    if (!empty($tenant['next_of_kin_email'])) {
                        try {
                            $nokVariables = array_merge($variables, [
                                'recipient_name' => $tenant['next_of_kin_name'] ?? 'Next of Kin',
                                'recipient_note' => 'You have been registered as the next of kin for tenant ' . $tenant['name'] . ' at ' . ($house['property_name'] ?? 'our property') . ', unit ' . ($house['unit'] ?? '') . '. This is a courtesy copy of their invoice for your information.',
                            ]);
                            $nokSent = $emailService->sendTemplate('Billing Notification', $ownerId, $tenant['next_of_kin_email'], $tenant['next_of_kin_name'] ?? 'Next of Kin', $nokVariables);
                            if ($nokSent) $emailSent++;
                        } catch (\Exception $e) {
                            error_log('Failed to send bill email to next of kin: ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        Router::jsonResponse([
            'message' => "Generated {$generated} bills for {$month}. Emails sent to {$emailSent} recipients.",
            'count'   => $generated,
            'month'   => $month,
            'summary' => $summary,
            'emails_sent' => $emailSent,
            'emails_failed' => $emailFailed,
        ]);
    }

    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $billId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $billingService = new BillingService();

        $bill = $db->fetchOne(
            "SELECT b.*, h.unit, h.property_id, p.name as property_name, t.name as tenant_name, t.phone as tenant_phone
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON b.tenant_id = t.id
             WHERE b.id = ? AND b.owner_id = ?",
            [$billId, $ownerId]
        );

        if (!$bill) {
            \App\Core\AccessPolicy::assertResource(false, false, 'bill', $billId);
        }

        // Role-specific scope: tenants only their own bills, caretakers only assigned.
        $scopeAllowed = true;
        if ($role === 'tenant') {
            $scopeAllowed = ((int) $bill['tenant_id'] === (int) Router::getAuthTenantId());
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            $scopeAllowed = in_array((int) ($bill['property_id'] ?? 0), $propertyIds, true);
        }
        \App\Core\AccessPolicy::assertResource(true, $scopeAllowed, 'bill', $billId);

        // Ensure tenant name/phone are present for single invoices
        if (empty($bill['tenant_name'])) {
            $tenantRow = null;
            if (!empty($bill['tenant_id'])) {
                $tenantRow = $db->fetchOne("SELECT id, name, phone FROM tenants WHERE id = ? AND owner_id = ?", [(int)$bill['tenant_id'], $ownerId]);
            }
            if (empty($tenantRow) && !empty($bill['house_id'])) {
                $tenantRow = $db->fetchOne("SELECT t.id, t.name, t.phone FROM tenants t JOIN houses h ON h.tenant_id = t.id WHERE h.id = ? AND h.owner_id = ?", [(int)$bill['house_id'], $ownerId]);
            }
            if (!empty($tenantRow)) {
                $bill['tenant_name'] = $tenantRow['name'] ?? $bill['tenant_name'];
                $bill['tenant_phone'] = $tenantRow['phone'] ?? $bill['tenant_phone'];
            }
        }

        $billingService->ensureLegacyBillItems($bill);
        $bill['items'] = $billingService->getBillItems($billId);
        $bill['allocations'] = $billingService->getBillAllocations($billId);

        if (!empty($bill['items'])) {
            $payments = $db->fetchAll(
                "SELECT DISTINCT p.*, COALESCE(SUM(pa.amount), 0) as allocated_amount
                 FROM payments p
                 JOIN payment_allocations pa ON pa.payment_id = p.id
                 WHERE pa.bill_item_id IN (SELECT id FROM bill_items WHERE bill_id = ?)
                 AND p.owner_id = ?
                 GROUP BY p.id
                 ORDER BY p.created_at DESC",
                [$billId, $ownerId]
            );
        } else {
            $payments = $db->fetchAll(
                "SELECT * FROM payments WHERE tenant_id = ? AND owner_id = ? AND month = ? ORDER BY created_at DESC",
                [(int)$bill['tenant_id'], $ownerId, $bill['month']]
            );
        }

        $bill['payments'] = $payments;
        Router::jsonResponse(['bill' => $bill]);
    }

/**
     * PUT /api/bills/{id} - Edit a bill's line items to standardize/adjust invoiced amounts.
     * Owner or caretaker scoped. Keeps the bill in sync with payments:
     *   - existing items are updated in place (their payment allocations stay intact)
     *   - new items are added
     *   - items that already have confirmed payments allocated cannot be removed
     *   - each item's paid amount is recomputed from payment_allocations
     *   - the bill total/status and the tenant credit/balance are recalculated
     */
    public function update(array $params): void
    {
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $billId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $bill = $db->fetchOne(
            "SELECT b.*, h.property_id
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             WHERE b.id = ? AND b.owner_id = ?",
            [$billId, $ownerId]
        );
        if (!$bill) {
            Router::jsonResponse(['error' => 'Bill not found'], 404);
        }

        // Caretakers can only edit bills within their assigned properties
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!in_array((int)$bill['property_id'], $propertyIds, true)) {
                Router::jsonResponse(['error' => 'You do not have access to this bill'], 403);
            }
        }

        $data = Router::getRequestBody();
        $items = $data['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            Router::jsonResponse(['error' => 'At least one bill item is required'], 400);
        }

        $billingService = new BillingService();

        // Current paid amount per item, from the allocations source of truth
        $paidByItem = [];
        $allocRows = $db->fetchAll(
            "SELECT pa.bill_item_id AS item_id, COALESCE(SUM(pa.amount), 0) AS paid
             FROM payment_allocations pa
             JOIN payments p ON p.id = pa.payment_id
             WHERE pa.bill_item_id IN (SELECT id FROM bill_items WHERE bill_id = ?)
               AND p.status IN ('confirmed','completed','paid')
             GROUP BY pa.bill_item_id",
            [$billId]
        );
        foreach ($allocRows as $row) {
            $paidByItem[(int)$row['item_id']] = (float)$row['paid'];
        }

        $db->beginTransaction();
        try {
            $existingItems = $billingService->getBillItems($billId);
            $existingById = [];
            foreach ($existingItems as $it) {
                $existingById[(int)$it['id']] = $it;
            }

            $requestedIds = [];
            foreach ($items as $item) {
                $type = trim((string)($item['type'] ?? 'Other'));
                $description = trim((string)($item['description'] ?? ''));
                $amount = max(0.0, (float)($item['amount'] ?? 0));
                $itemId = (int)($item['id'] ?? 0);

                if ($itemId > 0 && isset($existingById[$itemId])) {
                    $requestedIds[] = $itemId;
                    // Amount threads through; paid reflects what has actually been
                    // allocated to this line item from confirmed payments.
                    $paid = (float)($paidByItem[$itemId] ?? 0);
                    $status = $paid <= 0 ? 'pending' : ($paid >= $amount ? 'paid' : 'partial');
                    $db->update('bill_items', [
                        'type' => $type,
                        'description' => $description,
                        'amount' => $amount,
                        'paid' => $paid,
                        'status' => $status,
                    ], 'id = ?', [$itemId]);
                } else {
                    // New line item
                    $db->insert('bill_items', [
                        'bill_id' => $billId,
                        'type' => $type,
                        'description' => $description ?: null,
                        'amount' => $amount,
                        'paid' => 0.00,
                        'status' => $amount > 0 ? 'pending' : 'paid',
                    ]);
                }
            }
// Remove line items that are no longer present, as long as no confirmed
            // payments are allocated to them. payment_allocations cascade on delete,
            // so refusing when paid > 0 protects the accounting trail.
            foreach ($existingItems as $it) {
                $itemId = (int)$it['id'];
                if (in_array($itemId, $requestedIds, true)) {
                    continue;
                }
                $paid = (float)($paidByItem[$itemId] ?? 0);
                if ($paid > 0) {
                    $db->rollback();
                    Router::jsonResponse([
                        'error' => "Cannot remove the '" . $it['type'] . "' item because KES " . number_format($paid, 2) . " in payments have already been allocated to it. Adjust its amount instead.",
                    ], 400);
                }
                $db->delete('bill_items', 'id = ? AND bill_id = ?', [$itemId, $billId]);
            }

            $billingService->recalculateBill($billId);

            // Keep the tenant's standing balance/credit in sync with the edited bill
            if (!empty($bill['tenant_id'])) {
                $billingService->recalcTenantCreditAndBalance((int)$bill['tenant_id']);
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            error_log('Bill update failed: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'Could not update bill: ' . $e->getMessage()], 500);
        }

        $updatedBill = $billingService->getBillTotals($billId);
        Router::jsonResponse([
            'message' => 'Bill updated successfully',
            'bill_id' => $billId,
            'total' => $updatedBill['total'],
            'paid' => $updatedBill['paid'],
            'balance' => max(0.0, $updatedBill['total'] - $updatedBill['paid']),
        ]);
    }
    /**
     * GET /api/bills/{id}/invoice - Generate professional PDF invoice
     * Supports token via query param for new window opens
     * For tenants: automatically includes all bills for the month (rent, water, electricity)
     */
    public function invoice(array $params): void
    {
        // Try to get token from query param first (for new window opens)
        $tokenParam = $_GET['token'] ?? null;
        
        if (!$tokenParam) {
            // Fall back to Authorization header
            AuthMiddleware::authenticate();
        } else {
            // Validate token from query param
            $jwt = new \App\Core\JWT();
            $user = $jwt->decode($tokenParam);
            if (!$user) {
                http_response_code(401);
                echo 'Invalid or expired token';
                exit;
            }
            $_REQUEST['auth_user_id'] = (int) ($user['owner_id'] ?? $user['user_id'] ?? 0);
            $_REQUEST['auth_actor_id'] = (int) ($user['actor_id'] ?? $_REQUEST['auth_user_id']);
            $_REQUEST['auth_user_role'] = $user['role'] ?? 'owner';
        }

        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();
        $billingService = new BillingService();

        $billId = (int) ($params['id'] ?? 0);
        
        $bill = $db->fetchOne(
            "SELECT b.*, h.unit, p.name as property_name, t.name as tenant_name, t.phone as tenant_phone, t.email as tenant_email, t.id_number as tenant_id_number
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON b.tenant_id = t.id
             WHERE b.id = ? AND b.owner_id = ?",
            [$billId, $ownerId]
        );

        if (!$bill) {
            Router::jsonResponse(['error' => 'Bill not found'], 404);
        }

        // Tenant authorization: can only access their own bills. Allow access
        // when the bill's tenant_id matches OR when the house currently has
        // the tenant assigned (legacy bills may store tenant on the house).
        if ($role === 'tenant') {
            $tenantId = Router::getAuthTenantId();
            $billTenantId = isset($bill['tenant_id']) ? (int)$bill['tenant_id'] : 0;
            if ($billTenantId !== $tenantId) {
                // Check house current tenant (fallback for legacy bills)
                $houseTenant = $db->fetchOne("SELECT tenant_id FROM houses WHERE id = ?", [(int)$bill['house_id']]);
                $houseTenantId = $houseTenant['tenant_id'] ?? 0;
                if ((int)$houseTenantId !== $tenantId) {
                    Router::jsonResponse(['error' => 'Access denied'], 403);
                }
            }

            // For tenants: fetch ALL bills for this month to create consolidated invoice
            $allBills = $db->fetchAll(
                "SELECT b.*, h.unit, p.name as property_name, t.name as tenant_name, t.phone as tenant_phone
                 FROM bills b
                 LEFT JOIN houses h ON b.house_id = h.id
                 LEFT JOIN properties p ON h.property_id = p.id
                 LEFT JOIN tenants t ON b.tenant_id = t.id
                 WHERE b.owner_id = ? AND b.tenant_id = ? AND b.month = ?
                 ORDER BY b.id",
                [$ownerId, $tenantId, $bill['month']]
            );

            $items = [];
            $grandTotal = 0.0;
            foreach ($allBills as $monthlyBill) {
                $billingService->ensureLegacyBillItems($monthlyBill);
                $billItems = $billingService->getBillItems((int)$monthlyBill['id']);
                foreach ($billItems as $item) {
                    $items[] = array_merge($item, [
                        'bill_id' => $monthlyBill['id'],
                        'bill_month' => $monthlyBill['month'],
                        'tenant_name' => $monthlyBill['tenant_name'] ?? $bill['tenant_name'] ?? null,
                    ]);
                    $grandTotal += (float)$item['amount'];
                }
            }

            $paymentRows = $db->fetchAll(
                "SELECT DISTINCT p.*, COALESCE(SUM(pa.amount), 0) as allocated_amount
                 FROM payments p
                 JOIN payment_allocations pa ON pa.payment_id = p.id
                 JOIN bill_items bi ON pa.bill_item_id = bi.id
                 JOIN bills b ON bi.bill_id = b.id
                 WHERE b.owner_id = ? AND b.tenant_id = ? AND b.month = ? AND p.status IN ('confirmed','completed','paid')
                 GROUP BY p.id
                 ORDER BY p.created_at DESC",
                [$ownerId, $tenantId, $bill['month']]
            );

            $allPayments = $paymentRows;
            $totalPaid = array_sum(array_column($allPayments, 'allocated_amount'));
            $balance = max(0.0, $grandTotal - $totalPaid);
            $status = $totalPaid <= 0 ? 'PENDING' : ($totalPaid >= $grandTotal ? 'PAID' : 'PARTIAL');

            $bill['items'] = $items;
            $bill['total'] = $grandTotal;
            $this->generateInvoicePdf($bill, $items, $allPayments, $totalPaid, $balance, $status, true);
            return;
        }

        $billingService->ensureLegacyBillItems($bill);
        $items = $billingService->getBillItems($billId);
        $bill['items'] = $items;

        if (!empty($items)) {
            $payments = $db->fetchAll(
                "SELECT DISTINCT p.*, COALESCE(SUM(pa.amount), 0) as allocated_amount
                 FROM payments p
                 JOIN payment_allocations pa ON pa.payment_id = p.id
                 WHERE pa.bill_item_id IN (SELECT id FROM bill_items WHERE bill_id = ?)
                 AND p.status IN ('confirmed','completed','paid')
                 GROUP BY p.id
                 ORDER BY p.created_at DESC",
                [$billId]
            );
            $totalPaid = array_sum(array_column($payments, 'allocated_amount'));
        } else {
            $payments = $db->fetchAll(
                "SELECT * FROM payments WHERE tenant_id = ? AND owner_id = ? AND month = ? AND status IN ('confirmed','completed','paid') ORDER BY created_at DESC",
                [(int)$bill['tenant_id'], $ownerId, $bill['month']]
            );
            $totalPaid = array_sum(array_column($payments, 'amount'));
        }

        $balance = max(0.0, (float)$bill['total'] - $totalPaid);
        $status = $totalPaid <= 0 ? 'PENDING' : ($totalPaid >= (float)$bill['total'] ? 'PAID' : 'PARTIAL');

        // Generate professional invoice PDF
        $this->generateInvoicePdf($bill, $items, $payments, $totalPaid, $balance, $status, false);
    }

    /**
     * Generate professional PDF invoice using PHP output buffering
     * @param bool $isMonthlyConsolidated If true, shows all charges for the month (for tenants)
     */
    private function generateInvoicePdf(array $bill, array $items, array $payments, float $totalPaid, float $balance, string $status, bool $isMonthlyConsolidated = false): void
    {
        $invoiceNumber = 'INV-' . strtoupper(substr($bill['property_name'] ?? 'RF', 0, 2)) . '-' . date('Ymd') . '-' . str_pad($bill['id'], 4, '0', STR_PAD_LEFT);
        if ($isMonthlyConsolidated) {
            $invoiceNumber = 'INV-' . strtoupper(substr($bill['property_name'] ?? 'RF', 0, 2)) . '-' . date('Ymd') . '-MONTHLY';
        }
        $invoiceDate = date('F d, Y');
        $dueDate = $bill['due_date'] ? date('F d, Y', strtotime($bill['due_date'])) : date('F d, Y', strtotime('+30 days'));
        $appUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? 'http://localhost');
        $statusClass = strtolower($status);

        // Start output buffering
        ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo $invoiceNumber; ?></title>
    <style>
        @page { margin: 0; size: A4; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #2c3e50;
            background: #ffffff;
        }
        .invoice-container {
            max-width: 800px;
            margin: 10px auto;
            background: #fff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .invoice-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 18px 25px;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }
        .company-info h1 { font-size: 20px; font-weight: 700; margin-bottom: 2px; }
        .company-info p { font-size: 10px; opacity: 0.95; margin: 1px 0; }
        .invoice-title-box { text-align: right; }
        .invoice-title { font-size: 24px; font-weight: 700; letter-spacing: 2px; margin-bottom: 4px; }
        .invoice-meta {
            font-size: 10px;
            background: rgba(255,255,255,0.15);
            padding: 5px 10px;
            border-radius: 6px;
            display: inline-block;
        }
        .invoice-body { padding: 15px 25px; }
        .addresses {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 20px;
        }
        .address-box { flex: 1; }
        .address-box h3 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 2px solid #2563eb;
            display: inline-block;
        }
        .address-box p {
            font-size: 10px;
            line-height: 1.5;
            color: #475569;
        }
        .address-box strong { color: #1e293b; font-size: 11px; }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .items-table thead {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }
        .items-table th {
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tbody tr:hover { background: #f8fafc; }
        .text-right { text-align: right; }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin: 15px 0;
        }
        .totals-box {
            width: 280px;
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e2e8f0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 11px;
            border-bottom: 1px solid #e2e8f0;
        }
        .total-row:last-child { border-bottom: none; }
        .total-row.final {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            padding: 12px 0 5px;
            border-top: 2px solid #2563eb;
            margin-top: 5px;
        }
        .total-row.final .total-label { color: #2563eb; }
        .label { color: #64748b; font-weight: 500; }
        .value { font-weight: 600; color: #1e293b; }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .payments-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
        }
        .payments-section h3 {
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .no-payments {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-style: italic;
            font-size: 12px;
        }
        .invoice-footer {
            background: #f8fafc;
            padding: 25px 45px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #64748b;
        }
        .invoice-footer strong { color: #2563eb; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .invoice-container { box-shadow: none; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1>RentaFlow</h1>
                <p>Property Management Solutions</p>
                <p><?php echo htmlspecialchars($appUrl); ?></p>
            </div>
            <div class="invoice-title-box">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-meta">
                    <strong><?php echo $invoiceNumber; ?></strong>
                </div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="addresses">
                <div class="address-box">
                    <h3>From</h3>
                    <p>
                        <strong>RentaFlow Property Management</strong><br>
                        <?php echo htmlspecialchars($bill['property_name'] ?? 'Property'); ?><br>
                        Unit <?php echo htmlspecialchars($bill['unit'] ?? 'N/A'); ?><br>
                        <br>
                        <strong>Billed To:</strong><br>
                        <?php echo htmlspecialchars($bill['tenant_name'] ?? 'Tenant'); ?><br>
                        Phone: <?php echo htmlspecialchars($bill['tenant_phone'] ?? 'N/A'); ?><br>
                        <?php if (!empty($bill['tenant_id_number'])): ?>
                        ID: <?php echo htmlspecialchars($bill['tenant_id_number']); ?><br>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="address-box">
                    <h3>Invoice Details</h3>
                    <p>
                        <strong>Invoice Date:</strong> <?php echo $invoiceDate; ?><br>
                        <strong>Due Date:</strong> <?php echo $dueDate; ?><br>
                        <strong>Billing Month:</strong> <?php echo date('F Y', strtotime($bill['month'] . '-01')); ?><br>
                        <strong>Status:</strong> 
                        <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo $status; ?></span>
                    </p>
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Description</th>
                        <th>Category</th>
                        <th class="text-right">Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['description'] ?? $item['type'] ?? 'Charge'); ?></strong>
                                <?php if ($isMonthlyConsolidated && !empty($item['bill_month'])): ?>
                                <br><small style="color: #64748b;">Billing <?php echo htmlspecialchars(date('F Y', strtotime($item['bill_month'] . '-01'))); ?></small>
                                <?php endif; ?>
                                <?php if ($isMonthlyConsolidated && !empty($item['tenant_name'])): ?>
                                <br><small style="color: #64748b;">Tenant: <?php echo htmlspecialchars($item['tenant_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['type'] ?? 'Charge'); ?></td>
                            <td class="text-right">KES <?php echo number_format((float)$item['amount'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($bill['description'] ?? ($bill['type'] ?? 'Charge')); ?></strong></td>
                            <td><?php echo htmlspecialchars($bill['type'] ?? 'Charge'); ?></td>
                            <td class="text-right">KES <?php echo number_format((float)$bill['total'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="totals-section">
                <div class="totals-box">
                    <div class="total-row">
                        <span class="label">Subtotal</span>
                        <span class="value">KES <?php echo number_format((float)$bill['total'], 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span class="label">Amount Paid</span>
                        <span class="value" style="color: #059669;">- KES <?php echo number_format($totalPaid, 2); ?></span>
                    </div>
                    <div class="total-row final">
                        <span class="total-label">Balance Due</span>
                        <span class="total-label">KES <?php echo number_format($balance, 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="payments-section">
                <h3>Payment History</h3>
                <?php if (!empty($payments)): ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Receipt #</th>
                            <th>Method</th>
                            <th class="text-right">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($payment['date'] ?? $payment['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($payment['receipt'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($payment['method'] ?? 'N/A')); ?></td>
                            <td class="text-right" style="color: #059669; font-weight: 600;">KES <?php echo number_format((float)($payment['allocated_amount'] ?? $payment['amount']), 2); ?></td>
                            <td><span class="status-badge status-paid">Confirmed</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-payments">No payments recorded for this month yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="invoice-footer">
            <p><strong>RentaFlow</strong> - Professional Property Management</p>
            <p style="margin-top: 5px;">Generated on <?php echo date('F d, Y'); ?> at <?php echo date('h:i A'); ?> | This is a computer-generated invoice</p>
            <p style="margin-top: 8px; font-size: 10px; color: #94a3b8;">For inquiries, contact property management</p>
        </div>
    </div>

    <div style="text-align: center; margin-top: 15px;">
        <button onclick="window.print()" style="padding: 12px 30px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(37,99,235,0.3);">
            Print / Save as PDF
        </button>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
<?php
        // Get the buffered content and send it
        $html = ob_get_clean();
        
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
}

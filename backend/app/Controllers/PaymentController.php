<?php
/**
 * Payment Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Services\EmailService;
use App\Core\Pagination;

class PaymentController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $page = Pagination::fromRequest();

        $sql = "SELECT p.*, t.name as tenant_name, h.unit as house_unit
             FROM payments p
             LEFT JOIN tenants t ON p.tenant_id = t.id
             LEFT JOIN houses h ON p.house_id = h.id
             WHERE p.owner_id = ?";
        $queryParams = [$ownerId];

        $countSql = "SELECT COUNT(*) as total FROM payments p LEFT JOIN houses h ON p.house_id = h.id WHERE p.owner_id = ?";
        $countParams = [$ownerId];

        if ($role === 'tenant') {
            $sql .= " AND p.tenant_id = ?";
            $queryParams[] = Router::getAuthTenantId();

            $countSql .= " AND p.tenant_id = ?";
            $countParams[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['payments' => [], 'meta' => Pagination::meta(0, $page['page'], $page['per_page'])]);
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            $sql .= " AND h.property_id IN ($placeholders)";
            $queryParams = array_merge($queryParams, $propertyIds);

            $countSql .= " AND h.property_id IN ($placeholders)";
            $countParams = array_merge($countParams, $propertyIds);
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT ?, ?";
        $queryParams[] = $page['offset'];
        $queryParams[] = $page['limit'];

        $payments = $db->fetchAll($sql, $queryParams);

        $totalRow = $db->fetchOne($countSql, $countParams);
        $total = (int) ($totalRow['total'] ?? 0);

        Router::jsonResponse(['payments' => $payments, 'meta' => Pagination::meta($total, $page['page'], $page['per_page'])]);
    }

    public function store(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        if (empty($data['amount'])) {
            Router::jsonResponse(['error' => 'Amount is required'], 400);
        }

        // Tenants can only record payments for themselves
        if ($role === 'tenant') {
            $tenantId = Router::getAuthTenantId();
            $tenant = $db->fetchOne(
                "SELECT id, house_id FROM tenants WHERE id = ? AND owner_id = ?",
                [$tenantId, $ownerId]
            );
            if (!$tenant) {
                Router::jsonResponse(['error' => 'Tenant not found'], 404);
            }
            $data['tenant_id'] = $tenantId;
            $data['house_id'] = $tenant['house_id'];
        } else {
            // Owners and caretakers must specify tenant_id
            if (empty($data['tenant_id'])) {
                Router::jsonResponse(['error' => 'Tenant ID is required'], 400);
            }
            // Verify tenant belongs to owner
            $tenant = $db->fetchOne(
                "SELECT id, house_id FROM tenants WHERE id = ? AND owner_id = ?",
                [$data['tenant_id'], $ownerId]
            );
            if (!$tenant) {
                Router::jsonResponse(['error' => 'Tenant not found'], 404);
            }
            $data['house_id'] = $tenant['house_id'];
        }

        $receipt = 'RCP-' . date('Y') . '-' . str_pad((time() % 10000), 4, '0', STR_PAD_LEFT);

        $isTenantSelfPay = ($role === 'tenant');
        $paymentId = $db->insert('payments', [
            'owner_id'    => $ownerId,
            'tenant_id'   => (int) $data['tenant_id'],
            'house_id'    => $tenant['house_id'],
            'month'       => $data['month'] ?? date('Y-m'),
            'amount'      => $data['amount'],
            'type'        => $data['type'] ?? 'Rent',
            'method'      => $data['method'] ?? 'M-Pesa',
            'date'        => $data['date'] ?? date('Y-m-d'),
            'status'      => $isTenantSelfPay ? 'confirmed' : 'completed',
            'receipt'     => $data['receipt'] ?? $receipt,
            'description' => $data['description'] ?? ($data['type'] ?? 'Rent') . ' Payment',
            'tenant_confirmed' => $isTenantSelfPay ? 1 : 0,
        ]);

        // Update tenant balance and carry over overpayment to next month
        if (($data['type'] ?? 'Rent') === 'Rent' && ($data['status'] ?? 'completed') === 'completed') {
            $current = $db->fetchOne("SELECT balance FROM tenants WHERE id = ?", [(int) $data['tenant_id']]);
            $currentBalance = $current ? (float)$current['balance'] : 0;
            $newBalance = $currentBalance - (float)$data['amount'];
            
            // Update tenant balance (can be negative = overpayment)
            $db->update('tenants', ['balance' => $newBalance], 'id = ?', [(int) $data['tenant_id']]);
            
            // If overpaid (negative balance), apply to next month's bill
            if ($newBalance < 0) {
                $currentMonth = $data['month'] ?? date('Y-m');
                $nextMonth = date('Y-m', strtotime($currentMonth . ' +1 month'));
                
                // Find next month's rent bill
                $nextBill = $db->fetchOne(
                    "SELECT id, total FROM bills WHERE house_id = ? AND month = ? AND type = 'Rent'",
                    [$tenant['house_id'], $nextMonth]
                );
                
                if ($nextBill) {
                    // Apply overpayment to next month (reduce the bill total)
                    $overpayment = abs($newBalance);
                    $newTotal = max(0, (float)$nextBill['total'] - $overpayment);
                    $db->update('bills', ['total' => $newTotal], 'id = ?', [$nextBill['id']]);
                    
                    // Reset tenant balance to 0 since overpayment was applied
                    $db->update('tenants', ['balance' => 0], 'id = ?', [(int) $data['tenant_id']]);
                }
            }
        }

        // Update bill status
        if ($tenant['house_id'] && !empty($data['month'])) {
            $houseId = $tenant['house_id'];
            $paidTotal = $db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE house_id = ? AND owner_id = ? AND status = 'completed' AND month = ?",
                [$houseId, $ownerId, $data['month']]
            );
            $bill = $db->fetchOne(
                "SELECT total FROM bills WHERE house_id = ? AND month = ?",
                [$houseId, $data['month']]
            );
            if ($bill) {
                $newStatus = ($paidTotal['total'] >= $bill['total']) ? 'paid' : 'partial';
                $db->update('bills', ['status' => $newStatus], 'house_id = ? AND month = ?', [$houseId, $data['month']]);
            }
        }

        $payment = $db->fetchOne("SELECT * FROM payments WHERE id = ?", [$paymentId]);
        
        // Send payment confirmation email to tenant and next of kin when owner records
        $emailSent = false;
        $nokEmailSent = false;
        if (!$isTenantSelfPay) {
            try {
                $tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ?", [(int) $data['tenant_id']]);
                if ($tenant && !empty($tenant['email'])) {
                    $emailService = new EmailService();
                    
                    // Find the bill ID for this payment to generate invoice link
                    $bill = $db->fetchOne(
                        "SELECT b.id FROM bills b WHERE b.house_id = ? AND b.month = ? AND b.owner_id = ? LIMIT 1",
                        [$tenant['house_id'], $payment['month'], $ownerId]
                    );
                    
                    // Generate JWT token for invoice access
                    $jwt = new \App\Core\JWT();
                    $invoiceToken = $jwt->encode([
                        'owner_id' => $ownerId,
                        'actor_id' => $tenant['id'],
                        'role' => 'tenant',
                        'tenant_id' => $tenant['id']
                    ]);
                    
                    // Add invoice URL to payment data
                    $payment['invoice_url'] = $bill 
                        ? (getenv('APP_URL') ?: $_ENV['APP_URL'] ?? 'http://localhost') . "/api/bills/{$bill['id']}/invoice?token={$invoiceToken}"
                        : null;
                    
                    // Include arrears/rent info in payment data for email
                    $house = $db->fetchOne("SELECT rent FROM houses WHERE id = ?", [$tenant['house_id']]);
                    $payment['monthly_rent'] = $house ? $house['rent'] : 0;
                    $payment['balance_after'] = $newBalance ?? 0;
                    
                    $emailSent = $emailService->sendPaymentConfirmation($ownerId, $tenant, $payment);
                    
                    // Send notification to next of kin if they have an email
                    if (!empty($tenant['next_of_kin_email'])) {
                        try {
                            $emailService->sendTemplate(
                                'Payment Receipt - ' . $tenant['name'],
                                $ownerId,
                                $tenant['next_of_kin_email'],
                                $tenant['next_of_kin_name'] ?? 'Next of Kin',
                                [
                                    'tenant_name' => $tenant['name'],
                                    'amount' => number_format((float)$payment['amount'], 2),
                                    'balance' => number_format($newBalance ?? 0, 2),
                                    'property' => '',
                                    'unit' => '',
                                    'receipt' => $payment['receipt'],
                                    'date' => $payment['date'],
                                ]
                            );
                            $nokEmailSent = true;
                        } catch (\Exception $nokErr) {
                            error_log('Failed to send next of kin payment email: ' . $nokErr->getMessage());
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log('Failed to send payment confirmation email: ' . $e->getMessage());
            }
        }
        
        $emailMsg = $emailSent ? ' & confirmation email sent' : '';
        $nokMsg = $nokEmailSent ? ' & next of kin notified' : '';
        Router::jsonResponse(['message' => "Payment recorded{$emailMsg}{$nokMsg}", 'payment' => $payment, 'email_sent' => $emailSent, 'nok_email_sent' => $nokEmailSent], 201);
    }

    public function show(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $paymentId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $payment = $db->fetchOne(
            "SELECT p.*, t.name as tenant_name, h.unit as house_unit
             FROM payments p
             LEFT JOIN tenants t ON p.tenant_id = t.id
             LEFT JOIN houses h ON p.house_id = h.id
             WHERE p.id = ? AND p.owner_id = ?",
            [$paymentId, $ownerId]
        );

        if (!$payment) {
            Router::jsonResponse(['error' => 'Payment not found'], 404);
        }
        Router::jsonResponse(['payment' => $payment]);
    }

    /**
     * GET /payments/tenant-finance/{id} - Return tenant financial info for payment form
     */
    public function tenantFinance(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $tenantId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $tenant = $db->fetchOne(
            "SELECT t.id, t.name, t.balance, h.rent, h.unit, p.name as property_name
             FROM tenants t
             LEFT JOIN houses h ON t.house_id = h.id
             LEFT JOIN properties p ON t.property_id = p.id
             WHERE t.id = ? AND t.owner_id = ?",
            [$tenantId, $ownerId]
        );

        if (!$tenant) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }

        // Calculate arrears (positive balance = owed)
        $balance = (float) ($tenant['balance'] ?? 0);
        $rent = (float) ($tenant['rent'] ?? 0);
        $arrears = max(0, $balance);
        $overpaid = $balance < 0 ? abs($balance) : 0;

        Router::jsonResponse([
            'tenant' => $tenant,
            'monthly_rent' => $rent,
            'balance' => $balance,
            'arrears' => $arrears,
            'overpaid' => $overpaid,
            'suggested_payment' => $balance > 0 ? $balance : $rent,
        ]);
    }

    /**
     * PUT /api/payments/{id}/confirm - Tenant confirms they received/reviewed a payment record
      */
    public function confirm(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $paymentId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        // Only tenants can confirm payments
        if ($role !== 'tenant') {
            Router::jsonResponse(['error' => 'Only tenants can confirm payments'], 403);
        }

        $payment = $db->fetchOne(
            "SELECT * FROM payments WHERE id = ? AND owner_id = ?",
            [$paymentId, $ownerId]
        );
        if (!$payment) {
            Router::jsonResponse(['error' => 'Payment not found'], 404);
        }

        // Verify this is the tenant's payment
        $tenantId = Router::getAuthTenantId();
        if ((int)$payment['tenant_id'] !== $tenantId) {
            Router::jsonResponse(['error' => 'This payment does not belong to you'], 403);
        }

        if ((int)$payment['tenant_confirmed'] === 1) {
            Router::jsonResponse(['error' => 'Payment already confirmed'], 400);
        }

        $db->update('payments', [
            'tenant_confirmed' => 1,
            'confirmed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$paymentId]);

        Router::jsonResponse(['message' => 'Payment confirmed successfully']);
    }
}

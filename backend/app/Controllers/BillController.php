<?php
/**
 * Bill Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

class BillController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $month = $_GET['month'] ?? date('Y-m');
        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;

        $sql = "SELECT b.*, b.total as amount, h.unit as house_unit, h.unit, p.name as property_name, p.id as property_id, t.name as tenant_name, t.id as tenant_id
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON b.tenant_id = t.id
             WHERE b.owner_id = ? AND b.month = ?";
        $queryParams = [$ownerId, $month];

        if ($role === 'tenant') {
            $sql .= " AND t.id = ?";
            $queryParams[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['bills' => []]);
            $sql .= " AND h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $queryParams = array_merge($queryParams, $propertyIds);
        }

        if ($propertyId > 0) {
            $sql .= " AND p.id = ?";
            $queryParams[] = $propertyId;
        }

        $sql .= " ORDER BY p.name, h.unit";
        $bills = $db->fetchAll($sql, $queryParams);

        // Calculate actual paid amount and balance from payments (scoped to the bill month)
        foreach ($bills as &$bill) {
            $paidRow = $db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE house_id = ? AND owner_id = ? AND month = ? AND status IN ('confirmed','completed','paid')",
                [$bill['house_id'], $ownerId, $month]
            );
            $paid = (float)($paidRow['total'] ?? 0);
            $bill['paid'] = $paid;
            $bill['balance'] = (float)$bill['total'] - $paid;
            if ($paid <= 0) $bill['status'] = 'pending';
            elseif ($paid >= (float)$bill['total']) $bill['status'] = 'paid';
            else $bill['status'] = 'partial';
        }

        Router::jsonResponse(['bills' => $bills]);
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

        $month = $data['month'] ?? date('Y-m');
        $dueDate = $data['due_date'] ?? date('Y-m-05');
        $propertyId = isset($data['property_id']) ? (int) $data['property_id'] : 0;

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
        foreach ($houses as $house) {
            $water = $data['water_charges'][$house['id']] ?? 0;
            $electricity = $data['elec_charges'][$house['id']] ?? 0;
            
            // Create separate bills for each charge type
            $chargeTypes = [
                ['type' => 'Rent', 'amount' => (float)$house['rent'], 'label' => 'Monthly Rent'],
                ['type' => 'Water', 'amount' => (float)$water, 'label' => 'Water Charges'],
                ['type' => 'Electricity', 'amount' => (float)$electricity, 'label' => 'Electricity Charges'],
            ];
            
            foreach ($chargeTypes as $charge) {
                if ($charge['amount'] <= 0) continue; // Skip zero amounts
                
                $existing = $db->fetchOne(
                    "SELECT id FROM bills WHERE house_id = ? AND month = ? AND type = ?",
                    [$house['id'], $month, $charge['type']]
                );
                
                if (!$existing) {
                    $db->insert('bills', [
                        'owner_id'    => $ownerId,
                        'house_id'    => $house['id'],
                        'tenant_id'   => $house['tenant_id'],
                        'month'       => $month,
                        'type'        => $charge['type'],
                        'total'       => $charge['amount'],
                        'rent'        => $charge['type'] === 'Rent' ? $charge['amount'] : 0,
                        'water'       => $charge['type'] === 'Water' ? $charge['amount'] : 0,
                        'electricity' => $charge['type'] === 'Electricity' ? $charge['amount'] : 0,
                        'status'      => 'pending',
                        'due_date'    => $dueDate,
                    ]);
                    $generated++;
                }
                
                // Payment summary for this specific charge type
                $paidRow = $db->fetchOne(
                    "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE house_id = ? AND owner_id = ? AND status IN ('confirmed','completed','paid') AND month = ? AND type = ?",
                    [$house['id'], $ownerId, $month, $charge['type']]
                );
                $paid = (float)($paidRow['total'] ?? 0);
                $expected = $charge['amount'];
                $arrears = max(0, $expected - $paid);
                $status = $paid <= 0 ? 'pending' : ($paid >= $expected ? 'paid' : 'partial');

                $summary[] = [
                    'tenant_name' => $house['tenant_name'] ?? 'Vacant',
                    'unit' => $house['unit'] ?? '',
                    'property' => $house['property_name'] ?? '',
                    'type' => $charge['label'],
                    'expected' => $expected,
                    'paid' => $paid,
                    'arrears' => $arrears,
                    'status' => $status,
                ];
            }
        }

        Router::jsonResponse([
            'message' => "Generated {$generated} bills for {$month}",
            'count'   => $generated,
            'month'   => $month,
            'summary' => $summary,
        ]);
    }

    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $billId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $bill = $db->fetchOne(
            "SELECT b.*, h.unit, p.name as property_name, t.name as tenant_name, t.phone as tenant_phone
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

        // Get payments against this bill
        $payments = $db->fetchAll(
            "SELECT * FROM payments WHERE house_id = ? AND owner_id = ? AND month = ? ORDER BY created_at DESC",
            [$bill['house_id'], $ownerId, $bill['month']]
        );
        $bill['payments'] = $payments;

        Router::jsonResponse(['bill' => $bill]);
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

        // Tenant authorization: can only access their own bills
        if ($role === 'tenant') {
            $tenantId = Router::getAuthTenantId();
            if ((int)$bill['tenant_id'] !== $tenantId) {
                Router::jsonResponse(['error' => 'Access denied'], 403);
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
            
            // Calculate total across all bills for the month
            $totalRent = 0;
            $totalWater = 0;
            $totalElectricity = 0;
            $grandTotal = 0;
            $allPayments = [];
            
            foreach ($allBills as $monthlyBill) {
                $totalRent += (float)($monthlyBill['rent'] ?? 0);
                $totalWater += (float)($monthlyBill['water'] ?? 0);
                $totalElectricity += (float)($monthlyBill['electricity'] ?? 0);
                $grandTotal += (float)($monthlyBill['total'] ?? 0);
                
                // Get payments for this bill
                $billPayments = $db->fetchAll(
                    "SELECT * FROM payments WHERE house_id = ? AND owner_id = ? AND month = ? AND status IN ('confirmed','completed','paid') ORDER BY created_at DESC",
                    [$monthlyBill['house_id'], $ownerId, $bill['month']]
                );
                $allPayments = array_merge($allPayments, $billPayments);
            }
            
            // Update bill with consolidated amounts
            $bill['rent'] = $totalRent;
            $bill['water'] = $totalWater;
            $bill['electricity'] = $totalElectricity;
            $bill['total'] = $grandTotal;
            
            // Calculate total paid
            $totalPaid = array_sum(array_column($allPayments, 'amount'));
            $balance = $grandTotal - $totalPaid;
            $status = $totalPaid <= 0 ? 'PENDING' : ($totalPaid >= $grandTotal ? 'PAID' : 'PARTIAL');
            
            // Generate consolidated invoice
            $this->generateInvoicePdf($bill, $allPayments, $totalPaid, $balance, $status, true);
            return;
        }

        // Calculate payments for owner/caretaker view (single bill)
        $payments = $db->fetchAll(
            "SELECT * FROM payments WHERE house_id = ? AND owner_id = ? AND month = ? AND status IN ('confirmed','completed','paid') ORDER BY created_at DESC",
            [$bill['house_id'], $ownerId, $bill['month']]
        );

        $totalPaid = array_sum(array_column($payments, 'amount'));
        $balance = (float)$bill['total'] - $totalPaid;
        $status = $totalPaid <= 0 ? 'PENDING' : ($totalPaid >= (float)$bill['total'] ? 'PAID' : 'PARTIAL');

        // Generate professional invoice PDF
        $this->generateInvoicePdf($bill, $payments, $totalPaid, $balance, $status, false);
    }

    /**
     * Generate professional PDF invoice using PHP output buffering
     * @param bool $isMonthlyConsolidated If true, shows all charges for the month (for tenants)
     */
    private function generateInvoicePdf(array $bill, array $payments, float $totalPaid, float $balance, string $status, bool $isMonthlyConsolidated = false): void
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
                <h1>RentFlow</h1>
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
                        <strong>RentFlow Property Management</strong><br>
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
                        <th style="width: 45%;">Description</th>
                        <th class="text-right">Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Monthly Rent</strong><br><small style="color: #64748b;">Unit <?php echo htmlspecialchars($bill['unit'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($bill['property_name'] ?? 'Property'); ?></small></td>
                        <td class="text-right">KES <?php echo number_format((float)$bill['rent'], 2); ?></td>
                    </tr>
                    <?php if ((float)$bill['water'] > 0): ?>
                    <tr>
                        <td><strong>Water Charges</strong><br><small style="color: #64748b;">Monthly water usage</small></td>
                        <td class="text-right">KES <?php echo number_format((float)$bill['water'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ((float)$bill['electricity'] > 0): ?>
                    <tr>
                        <td><strong>Electricity Charges</strong><br><small style="color: #64748b;">Monthly electricity usage</small></td>
                        <td class="text-right">KES <?php echo number_format((float)$bill['electricity'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($isMonthlyConsolidated): ?>
                    <tr style="background: #f0f9ff;">
                        <td><strong>Total for Month</strong></td>
                        <td class="text-right"><strong>KES <?php echo number_format((float)$bill['total'], 2); ?></strong></td>
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
                            <td class="text-right" style="color: #059669; font-weight: 600;">KES <?php echo number_format((float)$payment['amount'], 2); ?></td>
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
            <p><strong>RentFlow</strong> - Professional Property Management</p>
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
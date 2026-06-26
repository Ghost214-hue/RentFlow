<?php
/**
 * Payment Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Services\EmailService;

class PaymentController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $sql = "SELECT p.*, t.name as tenant_name, h.unit as house_unit
             FROM payments p
             LEFT JOIN tenants t ON p.tenant_id = t.id
             LEFT JOIN houses h ON p.house_id = h.id
             WHERE p.owner_id = ?";
        $queryParams = [$ownerId];

        if ($role === 'tenant') {
            $sql .= " AND p.tenant_id = ?";
            $queryParams[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['payments' => []]);
            $sql .= " AND h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $queryParams = array_merge($queryParams, $propertyIds);
        }

        $sql .= " ORDER BY p.created_at DESC";
        $payments = $db->fetchAll($sql, $queryParams);

        Router::jsonResponse(['payments' => $payments]);
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

        $paymentId = $db->insert('payments', [
            'owner_id'    => $ownerId,
            'tenant_id'   => (int) $data['tenant_id'],
            'house_id'    => $tenant['house_id'],
            'amount'      => $data['amount'],
            'type'        => $data['type'] ?? 'Rent',
            'method'      => $data['method'] ?? 'M-Pesa',
            'date'        => $data['date'] ?? date('Y-m-d'),
            'status'      => $data['status'] ?? 'completed',
            'receipt'     => $data['receipt'] ?? $receipt,
            'description' => $data['description'] ?? ($data['type'] ?? 'Rent') . ' Payment',
        ]);

        // Update tenant balance (reduce balance by payment amount)
        if (($data['type'] ?? 'Rent') === 'Rent' && ($data['status'] ?? 'completed') === 'completed') {
            $current = $db->fetchOne("SELECT balance FROM tenants WHERE id = ?", [(int) $data['tenant_id']]);
            if ($current) {
                $newBalance = max(0, (float)$current['balance'] - (float)$data['amount']);
                $db->update('tenants', ['balance' => $newBalance], 'id = ?', [(int) $data['tenant_id']]);
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
        
        // Send payment confirmation email
        $emailSent = false;
        try {
            $tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ?", [(int) $data['tenant_id']]);
            if ($tenant && $tenant['email']) {
                $emailService = new EmailService();
                $emailSent = $emailService->sendPaymentConfirmation($ownerId, $tenant, $payment);
            }
        } catch (\Exception $e) {
            error_log('Failed to send payment confirmation email: ' . $e->getMessage());
        }
        
        $emailMsg = $emailSent ? '& confirmation email sent' : '& confirmation email notification sent';
        Router::jsonResponse(['message' => "Payment recorded {$emailMsg}", 'payment' => $payment, 'email_sent' => $emailSent], 201);
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
}

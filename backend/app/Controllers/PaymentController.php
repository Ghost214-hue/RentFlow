<?php
/**
 * Payment Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class PaymentController
{
    public function index(): void
    {
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        $payments = $db->fetchAll(
            "SELECT p.*, t.name as tenant_name, h.unit as house_unit
             FROM payments p
             LEFT JOIN tenants t ON p.tenant_id = t.id
             LEFT JOIN houses h ON p.house_id = h.id
             WHERE p.owner_id = ?
             ORDER BY p.created_at DESC",
            [$ownerId]
        );

        Router::jsonResponse(['payments' => $payments]);
    }

    public function store(): void
    {
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        if (empty($data['tenant_id']) || empty($data['amount'])) {
            Router::jsonResponse(['error' => 'Tenant ID and amount are required'], 400);
        }

        // Verify tenant belongs to owner
        $tenant = $db->fetchOne(
            "SELECT id, house_id FROM tenants WHERE id = ? AND owner_id = ?",
            [$data['tenant_id'], $ownerId]
        );
        if (!$tenant) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
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
        Router::jsonResponse(['message' => 'Payment recorded', 'payment' => $payment], 201);
    }

    public function show(array $params): void
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
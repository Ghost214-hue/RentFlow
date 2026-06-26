<?php
/**
 * Bill Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class BillController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $month = $_GET['month'] ?? date('Y-m');

        $sql = "SELECT b.*, b.total as amount, 0 as paid, b.total as balance, h.unit as house_unit, h.unit, p.name as property_name, t.name as tenant_name
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON h.tenant_id = t.id
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

        $sql .= " ORDER BY p.name, h.unit";
        $bills = $db->fetchAll($sql, $queryParams);

        Router::jsonResponse(['bills' => $bills]);
    }

    /**
     * POST /api/bills/generate - Generate bills for all occupied houses
     */
    public function generate(array $params = []): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $month = $data['month'] ?? date('Y-m');
        $dueDate = $data['due_date'] ?? date('Y-m-05');

        // Get all occupied houses for this owner
        $houses = $db->fetchAll(
            "SELECT h.id, h.rent, h.water_meter, h.elec_meter, t.id as tenant_id
             FROM houses h
             LEFT JOIN tenants t ON h.tenant_id = t.id
             WHERE h.owner_id = ? AND h.status = 'occupied'",
            [$ownerId]
        );

        $generated = 0;
        foreach ($houses as $house) {
            // Check if bill already exists for this month
            $existing = $db->fetchOne(
                "SELECT id FROM bills WHERE house_id = ? AND month = ?",
                [$house['id'], $month]
            );
            if ($existing) continue;

            $water = $data['water_charges'][$house['id']] ?? 0;
            $electricity = $data['elec_charges'][$house['id']] ?? 0;
            $total = $house['rent'] + $water + $electricity;

            $db->insert('bills', [
                'owner_id'    => $ownerId,
                'house_id'    => $house['id'],
                'month'       => $month,
                'rent'        => $house['rent'],
                'water'       => $water,
                'electricity' => $electricity,
                'total'       => $total,
                'status'      => 'pending',
                'due_date'    => $dueDate,
            ]);
            $generated++;
        }

        Router::jsonResponse([
            'message' => "Generated {$generated} bills for {$month}",
            'count'   => $generated,
            'month'   => $month,
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
             LEFT JOIN tenants t ON h.tenant_id = t.id
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
}

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
            $bill['balance'] = max(0, (float)$bill['total'] - $paid);
            if ($paid <= 0) $bill['status'] = 'pending';
            elseif ($paid >= (float)$bill['total']) $bill['status'] = 'paid';
            else $bill['status'] = 'partial';
        }

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
        $propertyId = isset($data['property_id']) ? (int) $data['property_id'] : 0;

        // Get all occupied houses with an active tenant for this owner
        $sql = "SELECT h.id, h.rent, h.water_meter, h.elec_meter, h.unit, p.name as property_name, t.id as tenant_id, t.name as tenant_name, t.balance
                FROM houses h
                LEFT JOIN properties p ON h.property_id = p.id
                LEFT JOIN tenants t ON h.tenant_id = t.id
                WHERE h.owner_id = ? AND h.status = 'occupied' AND h.tenant_id IS NOT NULL";
        $queryParams = [$ownerId];

        if ($propertyId > 0) {
            $sql .= " AND h.property_id = ?";
            $queryParams[] = $propertyId;
        }

        $sql .= " ORDER BY p.name, h.unit";
        $houses = $db->fetchAll($sql, $queryParams);

        $generated = 0;
        $summary = [];
        foreach ($houses as $house) {
            // Create bill if missing
            $existing = $db->fetchOne(
                "SELECT id FROM bills WHERE house_id = ? AND month = ?",
                [$house['id'], $month]
            );
            if (!$existing) {
                $water = $data['water_charges'][$house['id']] ?? 0;
                $electricity = $data['elec_charges'][$house['id']] ?? 0;
                $total = $house['rent'] + $water + $electricity;
                $db->insert('bills', [
                    'owner_id'    => $ownerId,
                    'house_id'    => $house['id'],
                    'tenant_id'   => $house['tenant_id'],
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

            // Payment summary for this house/month (filter by month if column exists)
            $paidRow = $db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE house_id = ? AND owner_id = ? AND status IN ('confirmed','completed','paid') AND month = ?",
                [$house['id'], $ownerId, $month]
            );
            $paid = (float)($paidRow['total'] ?? 0);
            $expected = (float)($house['rent'] ?? 0);
            $arrears = max(0, $expected - $paid);
            $status = $paid <= 0 ? 'pending' : ($paid >= $expected ? 'paid' : 'partial');

            $summary[] = [
                'tenant_name' => $house['tenant_name'] ?? 'Vacant',
                'unit' => $house['unit'] ?? '',
                'property' => $house['property_name'] ?? '',
                'expected' => $expected,
                'paid' => $paid,
                'arrears' => $arrears,
                'status' => $status,
            ];
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
}

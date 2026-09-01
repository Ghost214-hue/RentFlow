<?php
/**
 * Dashboard Controller
 * Returns aggregated stats scoped to the authenticated owner
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class DashboardController
{
    /**
     * GET /api/dashboard
     */
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        if ($role === 'tenant') {
            $tenantId = Router::getAuthTenantId();
            $tenant = $db->fetchOne(
                "SELECT t.*, p.name as property_name, h.unit as house_unit
                 FROM tenants t
                 LEFT JOIN properties p ON t.property_id = p.id
                 LEFT JOIN houses h ON t.house_id = h.id
                 WHERE t.id = ? AND t.owner_id = ?",
                [$tenantId, $ownerId]
            );
            $paid = $db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE owner_id = ? AND tenant_id = ? AND status = 'completed'",
                [$ownerId, $tenantId]
            );
            $payments = $db->fetchAll(
                "SELECT * FROM payments WHERE owner_id = ? AND tenant_id = ? ORDER BY created_at DESC LIMIT 5",
                [$ownerId, $tenantId]
            );
            $complaints = $db->fetchAll(
                "SELECT * FROM complaints WHERE owner_id = ? AND tenant_id = ? ORDER BY created_at DESC LIMIT 5",
                [$ownerId, $tenantId]
            );
            Router::jsonResponse([
                'tenant' => $tenant,
                'paid' => (float) ($paid['total'] ?? 0),
                'recentPayments' => $payments,
                'activeComplaints' => $complaints,
            ]);
        }

        $propertyFilter = '';
        $propertyParams = [];
        $failClosed = false;
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if ($propertyIds) {
                $propertyFilter = " AND property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
                $propertyParams = $propertyIds;
            } else {
                // Fail closed: an unassigned caretaker must never see the
                // owner's whole portfolio as a fallback.
                $failClosed = true;
            }
        }

        if ($failClosed) {
            Router::jsonResponse([
                'properties' => ['total' => 0, 'total_units' => 0, 'occupied' => 0],
                'houses' => ['total' => 0, 'occupied' => 0, 'vacant' => 0],
                'revenue' => 0.0,
                'outstanding' => 0.0,
                'tenants' => 0,
                'recentPayments' => [],
                'activeComplaints' => [],
                'maintenance' => ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'total_cost' => 0.0],
            ]);
        }

        // Properties count
        $props = $db->fetchOne(
            "SELECT COUNT(*) as total, COALESCE(SUM(units), 0) as total_units, COALESCE(SUM(occupied), 0) as total_occupied FROM properties WHERE owner_id = ?" . ($propertyFilter ? " AND id IN (" . implode(',', array_fill(0, count($propertyParams), '?')) . ")" : ""),
            $propertyFilter ? array_merge([$ownerId], $propertyParams) : [$ownerId]
        );

        // Houses stats
        $houses = $db->fetchOne(
            "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied FROM houses WHERE owner_id = ?" . $propertyFilter,
            $propertyFilter ? array_merge([$ownerId], $propertyParams) : [$ownerId]
        );

        // Revenue
        $revenue = $db->fetchOne(
            "SELECT COALESCE(SUM(payments.amount), 0) as total FROM payments LEFT JOIN houses ON payments.house_id = houses.id WHERE payments.owner_id = ? AND payments.status = 'completed' AND payments.type = 'Rent' AND MONTH(payments.date) = MONTH(CURRENT_DATE()) AND YEAR(payments.date) = YEAR(CURRENT_DATE())" . ($propertyFilter ? " AND houses.property_id IN (" . implode(',', array_fill(0, count($propertyParams), '?')) . ")" : ""),
            $propertyFilter ? array_merge([$ownerId], $propertyParams) : [$ownerId]
        );

        // Outstanding rent (bills not fully paid)
        $outstanding = $db->fetchOne(
            "SELECT COALESCE(SUM(bills.total), 0) as total FROM bills LEFT JOIN houses ON bills.house_id = houses.id WHERE bills.owner_id = ? AND bills.status IN ('pending', 'partial', 'overdue')" . ($propertyFilter ? " AND houses.property_id IN (" . implode(',', array_fill(0, count($propertyParams), '?')) . ")" : ""),
            $propertyFilter ? array_merge([$ownerId], $propertyParams) : [$ownerId]
        );

        // Recent payments
        $recentPayments = $db->fetchAll(
            "SELECT p.*, t.name as tenant_name, h.unit
             FROM payments p
             LEFT JOIN tenants t ON p.tenant_id = t.id
             LEFT JOIN houses h ON p.house_id = h.id
             WHERE p.owner_id = ?" . ($propertyFilter ? " AND h.property_id IN (" . implode(',', array_fill(0, count($propertyParams), '?')) . ")" : "") . "
             ORDER BY p.created_at DESC LIMIT 5",
            $propertyFilter ? array_merge([$ownerId], $propertyParams) : [$ownerId]
        );

        // Active complaints
        $activeComplaints = $db->fetchAll(
            "SELECT c.*, t.name as tenant_name, h.unit
             FROM complaints c
             LEFT JOIN tenants t ON c.tenant_id = t.id
             LEFT JOIN houses h ON c.house_id = h.id
             WHERE c.owner_id = ? AND c.status != 'resolved'" . ($propertyFilter ? " AND h.property_id IN (" . implode(',', array_fill(0, count($propertyParams), '?')) . ")" : "") . "
             ORDER BY c.created_at DESC LIMIT 5",
            $propertyFilter ? array_merge([$ownerId], $propertyParams) : [$ownerId]
        );

        // Tenant count
        $tenants = $db->fetchOne(
            "SELECT COUNT(*) as total FROM tenants WHERE owner_id = ?" . $propertyFilter,
            $propertyFilter ? array_merge([$ownerId], $propertyParams) : [$ownerId]
        );

        // Maintenance stats
        $maintenanceWhere = "WHERE m.owner_id = ?";
        $maintenanceParams = [$ownerId];
        if ($propertyFilter) {
            $maintenanceWhere .= " AND (m.property_id IN (" . implode(',', array_fill(0, count($propertyParams), '?')) . ") OR m.property_id IS NULL)";
            $maintenanceParams = array_merge($maintenanceParams, $propertyParams);
        }
        $maintenanceStats = $db->fetchOne(
            "SELECT COUNT(*) as total, SUM(CASE WHEN m.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN m.status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(m.cost) as total_cost
             FROM maintenance_records m $maintenanceWhere",
            $maintenanceParams
        );

        Router::jsonResponse([
            'properties' => [
                'total'       => (int) ($props['total'] ?? 0),
                'total_units' => (int) ($props['total_units'] ?? 0),
                'occupied'    => (int) ($props['total_occupied'] ?? 0),
            ],
            'houses' => [
                'total'    => (int) ($houses['total'] ?? 0),
                'occupied' => (int) ($houses['occupied'] ?? 0),
                'vacant'   => (int) (($houses['total'] ?? 0) - ($houses['occupied'] ?? 0)),
            ],
            'revenue'      => (float) ($revenue['total'] ?? 0),
            'outstanding'  => (float) ($outstanding['total'] ?? 0),
            'tenants'      => (int) ($tenants['total'] ?? 0),
            'recentPayments' => $recentPayments,
            'activeComplaints' => $activeComplaints,
            'maintenance' => [
                'total'      => (int) ($maintenanceStats['total'] ?? 0),
                'pending'    => (int) ($maintenanceStats['pending'] ?? 0),
                'in_progress' => (int) ($maintenanceStats['in_progress'] ?? 0),
                'total_cost' => (float) ($maintenanceStats['total_cost'] ?? 0),
            ],
        ]);
    }
}

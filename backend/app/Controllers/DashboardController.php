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
    public function index(): void
    {
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        // Properties count
        $props = $db->fetchOne(
            "SELECT COUNT(*) as total, COALESCE(SUM(units), 0) as total_units, COALESCE(SUM(occupied), 0) as total_occupied FROM properties WHERE owner_id = ?",
            [$ownerId]
        );

        // Houses stats
        $houses = $db->fetchOne(
            "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied FROM houses WHERE owner_id = ?",
            [$ownerId]
        );

        // Revenue
        $revenue = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE owner_id = ? AND status = 'completed' AND type = 'Rent' AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())",
            [$ownerId]
        );

        // Outstanding rent (bills not fully paid)
        $outstanding = $db->fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total FROM bills WHERE owner_id = ? AND status IN ('pending', 'partial', 'overdue')",
            [$ownerId]
        );

        // Recent payments
        $recentPayments = $db->fetchAll(
            "SELECT p.*, t.name as tenant_name, h.unit
             FROM payments p
             LEFT JOIN tenants t ON p.tenant_id = t.id
             LEFT JOIN houses h ON p.house_id = h.id
             WHERE p.owner_id = ?
             ORDER BY p.created_at DESC LIMIT 5",
            [$ownerId]
        );

        // Active complaints
        $activeComplaints = $db->fetchAll(
            "SELECT c.*, t.name as tenant_name, h.unit
             FROM complaints c
             LEFT JOIN tenants t ON c.tenant_id = t.id
             LEFT JOIN houses h ON c.house_id = h.id
             WHERE c.owner_id = ? AND c.status != 'resolved'
             ORDER BY c.created_at DESC LIMIT 5",
            [$ownerId]
        );

        // Tenant count
        $tenants = $db->fetchOne(
            "SELECT COUNT(*) as total FROM tenants WHERE owner_id = ?",
            [$ownerId]
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
        ]);
    }
}
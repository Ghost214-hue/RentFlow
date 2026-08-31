<?php
/**
 * Tenancy & Vacancy Report Controller
 * Provides occupancy analytics, tenancy status, and vacancy summaries.
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\Pagination;

class TenancyVacancyReportController
{
    public function index(array $params = []): void
    {
        // Tenants must never access owner/caretaker analytics.
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        if (!$ownerId) {
            Router::jsonResponse(['error' => 'Authentication required'], 401);
        }

        $propertyId     = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
        $unitId         = isset($_GET['unit_id']) ? (int) $_GET['unit_id'] : 0;
        $vacancyStatus  = $_GET['vacancy_status'] ?? '';
        $occupancyStatus = $_GET['occupancy_status'] ?? '';
        $tenantId       = isset($_GET['tenant_id']) ? (int) $_GET['tenant_id'] : 0;
        $dateFrom       = $_GET['date_from'] ?? '';
        $dateTo         = $_GET['date_to'] ?? '';
        $propertyType   = $_GET['property_type'] ?? '';

        $page = Pagination::fromRequest();

        $sql = "SELECT h.id, h.unit, h.type as unit_type, h.status as occupancy_status,
                       h.rent, h.created_at as unit_created,
                       p.id as property_id, p.name as property_name, p.type as property_type,
                       t.id as tenant_id, t.name as tenant_name, t.phone as tenant_phone,
                       t.email as tenant_email, t.status as tenant_status,
                       t.lease_start, t.lease_end, t.deposit, t.balance
                FROM houses h
                JOIN properties p ON h.property_id = p.id
                LEFT JOIN tenants t ON h.tenant_id = t.id
                WHERE h.owner_id = ?";
        $queryParams = [$ownerId];

        $propertyIds = [];
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) {
                Router::jsonResponse([
                    'data' => [],
                    'meta' => Pagination::meta(0, $page['page'], $page['per_page']),
                    'summary' => $this->emptySummary(),
                ]);
            }
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            $sql .= " AND h.property_id IN ($placeholders)";
            $queryParams = array_merge($queryParams, $propertyIds);
        }

        if ($propertyId > 0) {
            $sql .= " AND p.id = ?";
            $queryParams[] = $propertyId;
        }
        if ($unitId > 0) {
            $sql .= " AND h.id = ?";
            $queryParams[] = $unitId;
        }
        if ($vacancyStatus === 'occupied') {
            $sql .= " AND h.status = 'occupied'";
        } elseif ($vacancyStatus === 'vacant') {
            $sql .= " AND h.status = 'vacant'";
        }
        if ($occupancyStatus === 'active') {
            $sql .= " AND t.status = 'active'";
        } elseif ($occupancyStatus === 'terminated') {
            $sql .= " AND t.status = 'terminated'";
        }
        if ($tenantId > 0) {
            $sql .= " AND t.id = ?";
            $queryParams[] = $tenantId;
        }
        if (!empty($propertyType)) {
            $sql .= " AND p.type = ?";
            $queryParams[] = $propertyType;
        }
        if (!empty($dateFrom)) {
            $sql .= " AND h.created_at >= ?";
            $queryParams[] = $dateFrom . ' 00:00:00';
        }
        if (!empty($dateTo)) {
            $sql .= " AND h.created_at <= ?";
            $queryParams[] = $dateTo . ' 23:59:59';
        }

        $sql .= " ORDER BY p.name, h.unit LIMIT ?, ?";
        $queryParams[] = $page['offset'];
        $queryParams[] = $page['limit'];

        try {
            $houses = $db->fetchAll($sql, $queryParams);
        } catch (\Throwable $e) {
            Router::jsonResponse(['error' => 'Failed to load report data', 'details' => $e->getMessage()], 500);
        }

        try {
            $countSql = "SELECT COUNT(*) as total
                         FROM houses h
                         JOIN properties p ON h.property_id = p.id
                         LEFT JOIN tenants t ON h.tenant_id = t.id
                         WHERE h.owner_id = ?";
            $countParams = [$ownerId];
            if ($role === 'caretaker' && $propertyIds) {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $countSql .= " AND h.property_id IN ($placeholders)";
                $countParams = array_merge($countParams, $propertyIds);
            }
            if ($propertyId > 0) {
                $countSql .= " AND p.id = ?";
                $countParams[] = $propertyId;
            }
            if ($unitId > 0) {
                $countSql .= " AND h.id = ?";
                $countParams[] = $unitId;
            }
            if ($vacancyStatus === 'occupied') {
                $countSql .= " AND h.status = 'occupied'";
            } elseif ($vacancyStatus === 'vacant') {
                $countSql .= " AND h.status = 'vacant'";
            }
            if ($occupancyStatus === 'active') {
                $countSql .= " AND t.status = 'active'";
            } elseif ($occupancyStatus === 'terminated') {
                $countSql .= " AND t.status = 'terminated'";
            }
            if ($tenantId > 0) {
                $countSql .= " AND t.id = ?";
                $countParams[] = $tenantId;
            }
            if (!empty($propertyType)) {
                $countSql .= " AND p.type = ?";
                $countParams[] = $propertyType;
            }
            if (!empty($dateFrom)) {
                $countSql .= " AND h.created_at >= ?";
                $countParams[] = $dateFrom . ' 00:00:00';
            }
            if (!empty($dateTo)) {
                $countSql .= " AND h.created_at <= ?";
                $countParams[] = $dateTo . ' 23:59:59';
            }

            $totalRow = $db->fetchOne($countSql, $countParams);
            $total = (int) ($totalRow['total'] ?? 0);
        } catch (\Throwable $e) {
            Router::jsonResponse(['error' => 'Failed to load report count', 'details' => $e->getMessage()], 500);
        }

        try {
            $summary = $this->computeSummary($db, $ownerId, $role, $propertyId, $propertyType);
        } catch (\Throwable $e) {
            Router::jsonResponse(['error' => 'Failed to compute summary', 'details' => $e->getMessage()], 500);
        }

        Router::jsonResponse([
            'data'    => $houses,
            'meta'    => Pagination::meta($total, $page['page'], $page['per_page']),
            'summary' => $summary,
        ]);
    }

    public function summary(array $params = []): void
    {
        // Tenants must never access owner/caretaker analytics.
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        if (!$ownerId) {
            Router::jsonResponse(['error' => 'Authentication required'], 401);
        }

        $summary = $this->computeSummary($db, $ownerId, $role);
        Router::jsonResponse(['summary' => $summary]);
    }

    private function computeSummary(Database $db, int $ownerId, string $role, int $propertyId = 0, string $propertyType = ''): array
    {
        $baseWhere = "h.owner_id = ?";
        $baseParams = [$ownerId];

        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if ($propertyIds) {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $baseWhere .= " AND h.property_id IN ($placeholders)";
                $baseParams = array_merge($baseParams, $propertyIds);
            }
        }
        if ($propertyId > 0) {
            $baseWhere .= " AND p.id = ?";
            $baseParams[] = $propertyId;
        }
        if (!empty($propertyType)) {
            $baseWhere .= " AND p.type = ?";
            $baseParams[] = $propertyType;
        }

        $totalUnits = $db->fetchOne(
            "SELECT COUNT(*) as total FROM houses h JOIN properties p ON h.property_id = p.id WHERE $baseWhere",
            $baseParams
        );
        $occupiedUnits = $db->fetchOne(
            "SELECT COUNT(*) as total FROM houses h JOIN properties p ON h.property_id = p.id WHERE $baseWhere AND h.status = 'occupied'",
            $baseParams
        );
        $vacantUnits = $db->fetchOne(
            "SELECT COUNT(*) as total FROM houses h JOIN properties p ON h.property_id = p.id WHERE $baseWhere AND h.status = 'vacant'",
            $baseParams
        );

        $activeTenants = $db->fetchOne(
            "SELECT COUNT(DISTINCT t.id) as total FROM houses h
             JOIN properties p ON h.property_id = p.id
             JOIN tenants t ON h.tenant_id = t.id
             WHERE $baseWhere AND t.status = 'active'",
            $baseParams
        );
        $terminatedTenants = $db->fetchOne(
            "SELECT COUNT(DISTINCT t.id) as total FROM houses h
             JOIN properties p ON h.property_id = p.id
             JOIN tenants t ON h.tenant_id = t.id
             WHERE $baseWhere AND t.status = 'terminated'",
            $baseParams
        );

        $total = (int) ($totalUnits['total'] ?? 0);
        $occupied = (int) ($occupiedUnits['total'] ?? 0);
        $vacant = (int) ($vacantUnits['total'] ?? 0);

        return [
            'total_units'        => $total,
            'occupied_units'     => $occupied,
            'vacant_units'       => $vacant,
            'occupancy_rate'     => $total > 0 ? round(($occupied / $total) * 100, 1) : 0,
            'vacancy_rate'       => $total > 0 ? round(($vacant / $total) * 100, 1) : 0,
            'active_tenants'     => (int) ($activeTenants['total'] ?? 0),
            'terminated_tenants' => (int) ($terminatedTenants['total'] ?? 0),
        ];
    }

    private function emptySummary(): array
    {
        return [
            'total_units'        => 0,
            'occupied_units'     => 0,
            'vacant_units'       => 0,
            'occupancy_rate'     => 0,
            'vacancy_rate'       => 0,
            'active_tenants'     => 0,
            'terminated_tenants' => 0,
        ];
    }
}
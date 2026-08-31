<?php
/**
 * Complaints Report Controller
 * Provides complaint statistics, resolution status, and response time analytics.
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\Pagination;

class ComplaintsReportController
{
    /**
     * GET /api/reports/complaints
     */
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

        // Filters
        $propertyId       = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
        $tenantId         = isset($_GET['tenant_id']) ? (int) $_GET['tenant_id'] : 0;
        $complaintCategory = $_GET['complaint_category'] ?? '';
        $complaintStatus  = $_GET['complaint_status'] ?? ''; // 'open', 'in-progress', 'resolved'
        $priority         = $_GET['priority'] ?? ''; // 'low', 'medium', 'high'
        $assignedStaff    = $_GET['assigned_staff'] ?? '';
        $dateFrom         = $_GET['date_from'] ?? '';
        $dateTo           = $_GET['date_to'] ?? '';

        $page = Pagination::fromRequest();

        $sql = "SELECT c.id, c.title, c.category, c.priority, c.status, c.date, c.description,
                       c.created_at, c.updated_at,
                       h.unit, h.id as house_id,
                       p.id as property_id, p.name as property_name,
                       t.id as tenant_id, t.name as tenant_name, t.phone as tenant_phone, t.email as tenant_email
                FROM complaints c
                LEFT JOIN houses h ON c.house_id = h.id
                LEFT JOIN properties p ON h.property_id = p.id
                LEFT JOIN tenants t ON c.tenant_id = t.id
                WHERE c.owner_id = ?";
        $queryParams = [$ownerId];

        // Role-based scoping
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) {
                Router::jsonResponse([
                    'data' => [],
                    'meta' => Pagination::meta(0, $page['page'], $page['per_page']),
                    'summary' => $this->emptyComplaintsSummary()
                ]);
            }
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            $sql .= " AND h.property_id IN ($placeholders)";
            $queryParams = array_merge($queryParams, $propertyIds);
        } elseif ($role === 'tenant') {
            $sql .= " AND c.tenant_id = ?";
            $queryParams[] = Router::getAuthTenantId();
        }

        // Apply filters
        if ($propertyId > 0) {
            $sql .= " AND p.id = ?";
            $queryParams[] = $propertyId;
        }
        if ($tenantId > 0) {
            $sql .= " AND t.id = ?";
            $queryParams[] = $tenantId;
        }
        if (!empty($complaintCategory)) {
            $sql .= " AND c.category = ?";
            $queryParams[] = $complaintCategory;
        }
        if (!empty($complaintStatus)) {
            $sql .= " AND c.status = ?";
            $queryParams[] = $complaintStatus;
        }
        if (!empty($priority)) {
            $sql .= " AND c.priority = ?";
            $queryParams[] = $priority;
        }
        if (!empty($dateFrom)) {
            $sql .= " AND c.date >= ?";
            $queryParams[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $sql .= " AND c.date <= ?";
            $queryParams[] = $dateTo;
        }

        try {
            // Build a simplified count query that matches the data filters
            $countSql = "SELECT COUNT(*) as total
                         FROM complaints c
                         LEFT JOIN houses h ON c.house_id = h.id
                         LEFT JOIN properties p ON h.property_id = p.id
                         LEFT JOIN tenants t ON c.tenant_id = t.id
                         WHERE c.owner_id = ?";
            $countParams = [$ownerId];

            if ($role === 'caretaker') {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $countSql .= " AND h.property_id IN ($placeholders)";
                $countParams = array_merge($countParams, $propertyIds);
            } elseif ($role === 'tenant') {
                $countSql .= " AND c.tenant_id = ?";
                $countParams[] = Router::getAuthTenantId();
            }
            if ($propertyId > 0) {
                $countSql .= " AND p.id = ?";
                $countParams[] = $propertyId;
            }
            if ($tenantId > 0) {
                $countSql .= " AND t.id = ?";
                $countParams[] = $tenantId;
            }
            if (!empty($complaintCategory)) {
                $countSql .= " AND c.category = ?";
                $countParams[] = $complaintCategory;
            }
            if (!empty($complaintStatus)) {
                $countSql .= " AND c.status = ?";
                $countParams[] = $complaintStatus;
            }
            if (!empty($priority)) {
                $countSql .= " AND c.priority = ?";
                $countParams[] = $priority;
            }
            if (!empty($dateFrom)) {
                $countSql .= " AND c.date >= ?";
                $countParams[] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $countSql .= " AND c.date <= ?";
                $countParams[] = $dateTo;
            }

            $totalRow = $db->fetchOne($countSql, $countParams);
            $total = (int) ($totalRow['total'] ?? 0);

            $sql .= " ORDER BY c.created_at DESC LIMIT ?, ?";
            $queryParams[] = $page['offset'];
            $queryParams[] = $page['limit'];

            $complaints = $db->fetchAll($sql, $queryParams);
        } catch (\Throwable $e) {
            error_log('ComplaintsReport error: ' . $e->getMessage() . ' SQL: ' . ($countSql ?? $sql));
            Router::jsonResponse(['error' => 'Failed to load complaints report data', 'details' => $e->getMessage()], 500);
        }

        try {
            $summary = $this->computeComplaintsSummary($db, $ownerId, $role, $propertyId);
        } catch (\Throwable $e) {
            Router::jsonResponse(['error' => 'Failed to compute complaints summary', 'details' => $e->getMessage()], 500);
        }

        Router::jsonResponse([
            'data'    => $complaints,
            'meta'    => Pagination::meta($total, $page['page'], $page['per_page']),
            'summary' => $summary,
        ]);
    }

    /**
     * GET /api/reports/complaints/summary
     */
    public function summary(array $params = []): void
    {
        // Tenants must never access owner/caretaker analytics.
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
        $summary = $this->computeComplaintsSummary($db, $ownerId, $role, $propertyId);
        Router::jsonResponse(['summary' => $summary]);
    }

    private function computeComplaintsSummary(Database $db, int $ownerId, string $role, int $propertyId = 0): array
    {
        $baseWhere = "c.owner_id = ?";
        $baseParams = [$ownerId];

        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if ($propertyIds) {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $baseWhere .= " AND h.property_id IN ($placeholders)";
                $baseParams = array_merge($baseParams, $propertyIds);
            }
        } elseif ($role === 'tenant') {
            $baseWhere .= " AND c.tenant_id = ?";
            $baseParams[] = Router::getAuthTenantId();
        }
        if ($propertyId > 0) {
            $baseWhere .= " AND p.id = ?";
            $baseParams[] = $propertyId;
        }

        // Total complaints
        $totalComplaints = $db->fetchOne(
            "SELECT COUNT(*) as count FROM complaints c
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere",
            $baseParams
        );

        // By status
        $byStatus = $db->fetchAll(
            "SELECT c.status, COUNT(*) as count
             FROM complaints c
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere
             GROUP BY c.status",
            $baseParams
        );

        // By priority
        $byPriority = $db->fetchAll(
            "SELECT c.priority, COUNT(*) as count
             FROM complaints c
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere
             GROUP BY c.priority",
            $baseParams
        );

        // By category
        $byCategory = $db->fetchAll(
            "SELECT c.category, COUNT(*) as count
             FROM complaints c
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere
             GROUP BY c.category ORDER BY count DESC",
            $baseParams
        );

        // Resolved vs unresolved
        $resolved = $db->fetchOne(
            "SELECT COUNT(*) as count FROM complaints c
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere AND c.status = 'resolved'",
            $baseParams
        );
        $open = $db->fetchOne(
            "SELECT COUNT(*) as count FROM complaints c
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere AND c.status IN ('open', 'in-progress')",
            $baseParams
        );

        // High priority unresolved
        $highPriorityOpen = $db->fetchOne(
            "SELECT COUNT(*) as count FROM complaints c
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere AND c.priority = 'high' AND c.status IN ('open', 'in-progress')",
            $baseParams
        );

        $total = (int) ($totalComplaints['count'] ?? 0);
        $resolvedCount = (int) ($resolved['count'] ?? 0);
        $openCount = (int) ($open['count'] ?? 0);

        return [
            'total_complaints'       => $total,
            'resolved'               => $resolvedCount,
            'open'                   => $openCount,
            'resolution_rate'        => $total > 0 ? round(($resolvedCount / $total) * 100, 1) : 0,
            'high_priority_open'     => (int) ($highPriorityOpen['count'] ?? 0),
            'by_status'              => $byStatus,
            'by_priority'            => $byPriority,
            'by_category'            => $byCategory,
        ];
    }

    private function emptyComplaintsSummary(): array
    {
        return [
            'total_complaints'   => 0,
            'resolved'           => 0,
            'open'               => 0,
            'resolution_rate'    => 0,
            'high_priority_open' => 0,
            'by_status'          => [],
            'by_priority'        => [],
            'by_category'        => [],
        ];
    }
}
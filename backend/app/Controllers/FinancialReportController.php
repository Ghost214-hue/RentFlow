<?php
/**
 * Financial Report Controller
 * Provides financial summaries, rent collected, outstanding balances, expenses, income analysis.
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\Pagination;

class FinancialReportController
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

        $dateFrom      = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo        = $_GET['date_to'] ?? date('Y-m-t');
        $propertyId    = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
        $paymentStatus = $_GET['payment_status'] ?? '';
        $paymentMethod = $_GET['payment_method'] ?? '';
        $incomeType    = $_GET['income_type'] ?? '';
        $expenseType   = $_GET['expense_type'] ?? '';
        $tenantId      = isset($_GET['tenant_id']) ? (int) $_GET['tenant_id'] : 0;

        $page = Pagination::fromRequest();

        $sql = "SELECT pay.id, pay.amount, pay.type, pay.method, pay.date, pay.status, pay.receipt, pay.month,
                       pay.created_at,
                       h.unit, p.name as property_name, p.id as property_id,
                       t.id as tenant_id, t.name as tenant_name, t.phone as tenant_phone
                FROM payments pay
                LEFT JOIN houses h ON pay.house_id = h.id
                LEFT JOIN properties p ON h.property_id = p.id
                LEFT JOIN tenants t ON pay.tenant_id = t.id
                WHERE pay.owner_id = ? AND pay.date >= ? AND pay.date <= ?";
        $queryParams = [$ownerId, $dateFrom, $dateTo];

        try {
            if ($role === 'caretaker') {
                $propertyIds = Router::getCaretakerPropertyIds($db);
                if (!$propertyIds) {
                    Router::jsonResponse([
                        'data' => [],
                        'meta' => Pagination::meta(0, $page['page'], $page['per_page']),
                        'summary' => $this->emptyFinancialSummary(),
                    ]);
                }
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $sql .= " AND h.property_id IN ($placeholders)";
                $queryParams = array_merge($queryParams, $propertyIds);
            } elseif ($role === 'tenant') {
                $sql .= " AND pay.tenant_id = ?";
                $queryParams[] = Router::getAuthTenantId();
            }

            if ($propertyId > 0) {
                $sql .= " AND p.id = ?";
                $queryParams[] = $propertyId;
            }
            if (!empty($paymentStatus)) {
                $sql .= " AND pay.status = ?";
                $queryParams[] = $paymentStatus;
            }
            if (!empty($paymentMethod)) {
                $sql .= " AND pay.method = ?";
                $queryParams[] = $paymentMethod;
            }
            if (!empty($incomeType)) {
                $sql .= " AND pay.type = ?";
                $queryParams[] = $incomeType;
            }
            if ($tenantId > 0) {
                $sql .= " AND t.id = ?";
                $queryParams[] = $tenantId;
            }

            $sql .= " ORDER BY pay.date DESC, pay.created_at DESC LIMIT ?, ?";
            $queryParams[] = $page['offset'];
            $queryParams[] = $page['limit'];

            $payments = $db->fetchAll($sql, $queryParams);
        } catch (\Throwable $e) {
            Router::jsonResponse(['error' => 'Failed to load financial data', 'details' => $e->getMessage()], 500);
        }

        try {
            $summary = $this->computeFinancialSummary($db, $ownerId, $role, $dateFrom, $dateTo, $propertyId);
        } catch (\Throwable $e) {
            Router::jsonResponse(['error' => 'Failed to compute financial summary', 'details' => $e->getMessage()], 500);
        }

        Router::jsonResponse([
            'data'    => $payments,
            'meta'    => Pagination::meta(count($payments), $page['page'], $page['per_page']),
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

        $dateFrom   = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo     = $_GET['date_to'] ?? date('Y-m-t');
        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;

        $summary = $this->computeFinancialSummary($db, $ownerId, $role, $dateFrom, $dateTo, $propertyId);
        Router::jsonResponse(['summary' => $summary]);
    }

    private function computeFinancialSummary(Database $db, int $ownerId, string $role, string $dateFrom, string $dateTo, int $propertyId = 0): array
    {
        $baseWhere = "pay.owner_id = ? AND pay.date >= ? AND pay.date <= ?";
        $baseParams = [$ownerId, $dateFrom, $dateTo];

        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if ($propertyIds) {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $baseWhere .= " AND h.property_id IN ($placeholders)";
                $baseParams = array_merge($baseParams, $propertyIds);
            }
        } elseif ($role === 'tenant') {
            $baseWhere .= " AND pay.tenant_id = ?";
            $baseParams[] = Router::getAuthTenantId();
        }
        if ($propertyId > 0) {
            $baseWhere .= " AND p.id = ?";
            $baseParams[] = $propertyId;
        }

        $totalRevenue = $db->fetchOne(
            "SELECT COALESCE(SUM(pay.amount), 0) as total FROM payments pay
             LEFT JOIN houses h ON pay.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere",
            $baseParams
        );

        $collected = $db->fetchOne(
            "SELECT COALESCE(SUM(pay.amount), 0) as total FROM payments pay
             LEFT JOIN houses h ON pay.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere AND pay.status IN ('completed','confirmed','paid')",
            $baseParams
        );

        $pending = $db->fetchOne(
            "SELECT COALESCE(SUM(pay.amount), 0) as total FROM payments pay
             LEFT JOIN houses h ON pay.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere AND pay.status = 'pending'",
            $baseParams
        );

        $failed = $db->fetchOne(
            "SELECT COALESCE(SUM(pay.amount), 0) as total FROM payments pay
             LEFT JOIN houses h ON pay.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere AND pay.status = 'failed'",
            $baseParams
        );

        $incomeByType = $db->fetchAll(
            "SELECT pay.type, COALESCE(SUM(pay.amount), 0) as total, COUNT(*) as count
             FROM payments pay
             LEFT JOIN houses h ON pay.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere AND pay.status IN ('completed','confirmed','paid')
             GROUP BY pay.type ORDER BY total DESC",
            $baseParams
        );

        $methodBreakdown = $db->fetchAll(
            "SELECT pay.method, COALESCE(SUM(pay.amount), 0) as total, COUNT(*) as count
             FROM payments pay
             LEFT JOIN houses h ON pay.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE $baseWhere
             GROUP BY pay.method ORDER BY total DESC",
            $baseParams
        );

        $monthlyTrend = $db->fetchAll(
            "SELECT pay.month, COALESCE(SUM(pay.amount), 0) as total, COUNT(*) as count
             FROM payments pay
             LEFT JOIN houses h ON pay.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE pay.owner_id = ? AND pay.status IN ('completed','confirmed','paid')
             AND pay.month >= DATE_FORMAT(DATE_SUB(CURRENT_DATE(), INTERVAL 5 MONTH), '%Y-%m')
             GROUP BY pay.month ORDER BY pay.month ASC",
            [$ownerId]
        );

        $rev = (float) ($totalRevenue['total'] ?? 0);
        $col = (float) ($collected['total'] ?? 0);
        $pen = (float) ($pending['total'] ?? 0);
        $fai = (float) ($failed['total'] ?? 0);

        return [
            'total_revenue'     => $rev,
            'total_collected'   => $col,
            'total_pending'     => $pen,
            'total_failed'      => $fai,
            'outstanding'       => $rev - $col,
            'collection_rate'   => $rev > 0 ? round(($col / $rev) * 100, 1) : 0,
            'income_by_type'    => $incomeByType,
            'method_breakdown'  => $methodBreakdown,
            'monthly_trend'     => $monthlyTrend,
        ];
    }

    private function emptyFinancialSummary(): array
    {
        return [
            'total_revenue'    => 0,
            'total_collected'  => 0,
            'total_pending'    => 0,
            'total_failed'     => 0,
            'outstanding'      => 0,
            'collection_rate'  => 0,
            'income_by_type'   => [],
            'method_breakdown' => [],
            'monthly_trend'    => [],
        ];
    }
}
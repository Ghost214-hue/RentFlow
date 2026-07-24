<?php
/**
 * Bills Report Controller
 * Provides bill summaries, paid/pending/overdue bills, and billing statistics.
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\Pagination;

class BillsReportController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        if (!$ownerId) {
            Router::jsonResponse(['error' => 'Authentication required'], 401);
        }

        $propertyId    = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
        $tenantId      = isset($_GET['tenant_id']) ? (int) $_GET['tenant_id'] : 0;
        $billType      = $_GET['bill_type'] ?? '';
        $billStatus    = $_GET['bill_status'] ?? '';
        $dueDateFrom   = $_GET['due_date_from'] ?? '';
        $dueDateTo     = $_GET['due_date_to'] ?? '';
        $paymentStatus = $_GET['payment_status'] ?? '';
        $dateFrom      = $_GET['date_from'] ?? '';
        $dateTo        = $_GET['date_to'] ?? '';

        $page = Pagination::fromRequest();

        $sql = "SELECT b.id, b.month, b.type, b.total, b.rent, b.water, b.electricity,
                       b.status as bill_status, b.due_date, b.created_at,
                       h.unit, h.id as house_id,
                       p.id as property_id, p.name as property_name,
                       t.id as tenant_id, t.name as tenant_name, t.phone as tenant_phone
                FROM bills b
                LEFT JOIN houses h ON b.house_id = h.id
                LEFT JOIN properties p ON h.property_id = p.id
                LEFT JOIN tenants t ON COALESCE(b.tenant_id, h.tenant_id) = t.id
                WHERE b.owner_id = ?";
        $queryParams = [$ownerId];

        try {
            if ($role === 'caretaker') {
                $propertyIds = Router::getCaretakerPropertyIds($db);
                if (!$propertyIds) {
                    Router::jsonResponse([
                        'data' => [],
                        'meta' => Pagination::meta(0, $page['page'], $page['per_page']),
                        'summary' => $this->emptyBillsSummary(),
                    ]);
                }
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $sql .= " AND h.property_id IN ($placeholders)";
                $queryParams = array_merge($queryParams, $propertyIds);
            } elseif ($role === 'tenant') {
                $sql .= " AND t.id = ?";
                $queryParams[] = Router::getAuthTenantId();
            }

            if ($propertyId > 0) {
                $sql .= " AND p.id = ?";
                $queryParams[] = $propertyId;
            }
            if ($tenantId > 0) {
                $sql .= " AND t.id = ?";
                $queryParams[] = $tenantId;
            }
            if (!empty($billType)) {
                $sql .= " AND b.type = ?";
                $queryParams[] = $billType;
            }
            if (!empty($billStatus)) {
                $sql .= " AND b.status = ?";
                $queryParams[] = $billStatus;
            }
            if (!empty($dueDateFrom)) {
                $sql .= " AND b.due_date >= ?";
                $queryParams[] = $dueDateFrom;
            }
            if (!empty($dueDateTo)) {
                $sql .= " AND b.due_date <= ?";
                $queryParams[] = $dueDateTo;
            }
            if (!empty($dateFrom)) {
                $sql .= " AND b.created_at >= ?";
                $queryParams[] = $dateFrom . ' 00:00:00';
            }
            if (!empty($dateTo)) {
                $sql .= " AND b.created_at <= ?";
                $queryParams[] = $dateTo . ' 23:59:59';
            }

            $sql .= " ORDER BY b.created_at DESC, b.month DESC LIMIT ?, ?";
            $queryParams[] = $page['offset'];
            $queryParams[] = $page['limit'];

            $bills = $db->fetchAll($sql, $queryParams);
        } catch (\Throwable $e) {
            Router::jsonResponse(['error' => 'Failed to load bills report data', 'details' => $e->getMessage()], 500);
        }

        try {
            foreach ($bills as &$bill) {
                $paidRow = $db->fetchOne(
                    "SELECT COALESCE(SUM(amount), 0) as total FROM payments
                     WHERE house_id = ? AND owner_id = ? AND month = ? AND status IN ('confirmed','completed','paid')",
                    [$bill['house_id'], $ownerId, $bill['month']]
                );
                $paid = (float) ($paidRow['total'] ?? 0);
                $bill['paid'] = $paid;
                $bill['balance'] = (float) $bill['total'] - $paid;
                if ($paid <= 0) $bill['payment_status'] = 'pending';
                elseif ($paid >= (float) $bill['total']) $bill['payment_status'] = 'paid';
                else $bill['payment_status'] = 'partial';
            }

            $summary = $this->computeBillsSummary($db, $ownerId, $role, $propertyId);
        } catch (\Throwable $e) {
            Router::jsonResponse(['error' => 'Failed to compute bills report summary', 'details' => $e->getMessage()], 500);
        }

        Router::jsonResponse([
            'data'    => $bills,
            'meta'    => Pagination::meta(count($bills), $page['page'], $page['per_page']),
            'summary' => $summary,
        ]);
    }

    public function summary(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        if (!$ownerId) {
            Router::jsonResponse(['error' => 'Authentication required'], 401);
        }

        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
        $summary = $this->computeBillsSummary($db, $ownerId, $role, $propertyId);
        Router::jsonResponse(['summary' => $summary]);
    }

    private function computeBillsSummary(Database $db, int $ownerId, string $role, int $propertyId = 0): array
    {
        $baseWhere = "b.owner_id = ?";
        $baseParams = [$ownerId];

        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if ($propertyIds) {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $baseWhere .= " AND h.property_id IN ($placeholders)";
                $baseParams = array_merge($baseParams, $propertyIds);
            }
        } elseif ($role === 'tenant') {
            $baseWhere .= " AND t.id = ?";
            $baseParams[] = Router::getAuthTenantId();
        }
        if ($propertyId > 0) {
            $baseWhere .= " AND p.id = ?";
            $baseParams[] = $propertyId;
        }

        $totalBills = $db->fetchOne(
            "SELECT COALESCE(SUM(b.total), 0) as total, COUNT(*) as count
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON COALESCE(b.tenant_id, h.tenant_id) = t.id
             WHERE $baseWhere",
            $baseParams
        );

        $byStatus = $db->fetchAll(
            "SELECT b.status, COALESCE(SUM(b.total), 0) as total, COUNT(*) as count
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON COALESCE(b.tenant_id, h.tenant_id) = t.id
             WHERE $baseWhere
             GROUP BY b.status",
            $baseParams
        );

        $byType = $db->fetchAll(
            "SELECT b.type, COALESCE(SUM(b.total), 0) as total, COUNT(*) as count
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON COALESCE(b.tenant_id, h.tenant_id) = t.id
             WHERE $baseWhere
             GROUP BY b.type",
            $baseParams
        );

        $overdue = $db->fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(b.total), 0) as total
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON COALESCE(b.tenant_id, h.tenant_id) = t.id
             WHERE $baseWhere AND b.due_date < CURRENT_DATE() AND b.status != 'paid'",
            $baseParams
        );

        $currentMonth = date('Y-m');
        $currentMonthBills = $db->fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(b.total), 0) as total
             FROM bills b
             LEFT JOIN houses h ON b.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             LEFT JOIN tenants t ON COALESCE(b.tenant_id, h.tenant_id) = t.id
             WHERE $baseWhere AND b.month = ?",
            array_merge($baseParams, [$currentMonth])
        );

        $statusMap = [];
        foreach ($byStatus as $s) {
            $statusMap[$s['status']] = [
                'total' => (float) $s['total'],
                'count' => (int) $s['count'],
            ];
        }

        return [
            'total_bills'          => (int) ($totalBills['count'] ?? 0),
            'total_amount'         => (float) ($totalBills['total'] ?? 0),
            'paid'                 => $statusMap['paid'] ?? ['total' => 0, 'count' => 0],
            'partial'              => $statusMap['partial'] ?? ['total' => 0, 'count' => 0],
            'pending'              => $statusMap['pending'] ?? ['total' => 0, 'count' => 0],
            'overdue'              => $statusMap['overdue'] ?? ['total' => 0, 'count' => 0],
            'overdue_count'        => (int) ($overdue['count'] ?? 0),
            'overdue_amount'       => (float) ($overdue['total'] ?? 0),
            'current_month_count'  => (int) ($currentMonthBills['count'] ?? 0),
            'current_month_total'  => (float) ($currentMonthBills['total'] ?? 0),
            'by_type'              => $byType,
            'by_status'            => $byStatus,
        ];
    }

    private function emptyBillsSummary(): array
    {
        return [
            'total_bills'         => 0,
            'total_amount'        => 0,
            'paid'                => ['total' => 0, 'count' => 0],
            'partial'             => ['total' => 0, 'count' => 0],
            'pending'             => ['total' => 0, 'count' => 0],
            'overdue'             => ['total' => 0, 'count' => 0],
            'overdue_count'       => 0,
            'overdue_amount'      => 0,
            'current_month_count' => 0,
            'current_month_total' => 0,
            'by_type'             => [],
            'by_status'           => [],
        ];
    }
}
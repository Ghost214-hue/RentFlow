<?php
/**
 * Email Log Controller - Track email delivery status
 * Accessible by owners and caretakers
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class EmailLogController
{
    public function index(array $params = []): void
    {
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $sql = "SELECT el.*, t.name as tenant_name, t.email as tenant_email, h.unit as house_unit, p.name as property_name FROM email_logs el LEFT JOIN tenants t ON t.email = el.to_email AND t.owner_id = el.owner_id LEFT JOIN houses h ON h.id = t.house_id LEFT JOIN properties p ON p.id = h.property_id WHERE el.owner_id = ?";
        $queryParams = [$ownerId];

        if ($role === "caretaker") {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!empty($propertyIds)) {
                $sql .= " AND (p.id IN (" . implode(",", array_fill(0, count($propertyIds), "?")) . ") OR el.to_email NOT IN (SELECT email FROM tenants WHERE owner_id = ?))";
                $queryParams = array_merge($queryParams, $propertyIds, [$ownerId]);
            } else {
                Router::jsonResponse(["logs" => [], "stats" => $this->emptyStats()]);
                return;
            }
        }

        $status = $_GET["status"] ?? "";
        $validStatuses = ["sent", "failed", "pending"];
        if ($status && in_array($status, $validStatuses, true)) {
            $sql .= " AND el.status = ?";
            $queryParams[] = $status;
        }

        $search = trim((string) ($_GET["search"] ?? ""));
        if ($search !== "") {
            $sql .= " AND (el.to_email LIKE ? OR el.to_name LIKE ? OR el.subject LIKE ?)";
            $searchTerm = "%{$search}%";
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }

        $dateFrom = trim((string) ($_GET["date_from"] ?? ""));
        $dateTo = trim((string) ($_GET["date_to"] ?? ""));
        if ($dateFrom) {
            $sql .= " AND el.sent_at >= ?";
            $queryParams[] = $dateFrom . " 00:00:00";
        }
        if ($dateTo) {
            $sql .= " AND el.sent_at <= ?";
            $queryParams[] = $dateTo . " 23:59:59";
        }

        $emailType = $_GET["email_type"] ?? "";
        if ($emailType) {
            switch ($emailType) {
                case "welcome":
                    $sql .= " AND (el.subject LIKE '%welcome%' OR el.subject LIKE '%setup%' OR el.subject LIKE '%password%')";
                    break;
                case "rent_reminder":
                    $sql .= " AND (el.subject LIKE '%rent%reminder%' OR el.subject LIKE '%payment%reminder%')";
                    break;
                case "bill":
                    $sql .= " AND (el.subject LIKE '%bill%' OR el.subject LIKE '%invoice%')";
                    break;
                case "receipt":
                    $sql .= " AND (el.subject LIKE '%receipt%' OR el.subject LIKE '%payment%received%')";
                    break;
                case "complaint":
                    $sql .= " AND (el.subject LIKE '%complaint%' OR el.subject LIKE '%notice%')";
                    break;
                case "maintenance":
                    $sql .= " AND (el.subject LIKE '%maintenance%')";
                    break;
                case "vacate":
                    $sql .= " AND (el.subject LIKE '%vacate%' OR el.subject LIKE '%termination%')";
                    break;
            }
        }

        $sort = $_GET["sort"] ?? "sent_at";
        $order = strtoupper($_GET["order"] ?? "DESC");
        $order = $order === "ASC" ? "ASC" : "DESC";
        $allowedSorts = ["sent_at", "to_email", "subject", "status", "created_at"];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = "sent_at";
        }
        $sql .= " ORDER BY el.{$sort} {$order}";

        $page = max(1, (int) ($_GET["page"] ?? 1));
        $perPage = min(100, max(10, (int) ($_GET["per_page"] ?? 25)));
        $offset = ($page - 1) * $perPage;

        $countSql = preg_replace("/^SELECT el\\.\*,.*FROM/", "SELECT COUNT(*) as total FROM", $sql);
        $countSql = preg_replace("/ORDER BY.*$/", "", $countSql);
        $totalResult = $db->fetchOne($countSql, $queryParams);
        $total = (int) ($totalResult["total"] ?? 0);

        $sql .= " LIMIT ? OFFSET ?";
        $queryParams[] = $perPage;
        $queryParams[] = $offset;

        $logs = $db->fetchAll($sql, $queryParams);
        $stats = $this->getStats($ownerId, $role, $db);

        Router::jsonResponse([
            "logs" => $logs,
            "stats" => $stats,
            "pagination" => [
                "page" => $page,
                "per_page" => $perPage,
                "total" => $total,
                "total_pages" => (int) ceil($total / $perPage),
            ]
        ]);
    }

    public function stats(array $params = []): void
    {
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $stats = $this->getStats($ownerId, $role, $db);
        Router::jsonResponse(["stats" => $stats]);
    }

    private function getStats(int $ownerId, string $role, Database $db): array
    {
        $sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent, SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending FROM email_logs WHERE owner_id = ?";
        $params = [$ownerId];

        if ($role === "caretaker") {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!empty($propertyIds)) {
                $sql .= " AND (to_email IN (SELECT email FROM tenants WHERE owner_id = ? AND property_id IN (" . implode(",", array_fill(0, count($propertyIds), "?")) . ") OR to_email NOT IN (SELECT email FROM tenants WHERE owner_id = ?))";
                $params = array_merge($params, [$ownerId], $propertyIds, [$ownerId]);
            } else {
                return $this->emptyStats();
            }
        }

        $result = $db->fetchOne($sql, $params);
        return [
            "total" => (int) ($result["total"] ?? 0),
            "sent" => (int) ($result["sent"] ?? 0),
            "failed" => (int) ($result["failed"] ?? 0),
            "pending" => (int) ($result["pending"] ?? 0),
            "success_rate" => $result["total"] > 0 ? round((($result["sent"] ?? 0) / $result["total"]) * 100, 1) : 0,
        ];
    }

    public function show(array $params = []): void
    {
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $id = (int) ($params['id'] ?? 0);
        if (!$id) {
            Router::jsonResponse(['error' => 'Invalid email log ID'], 400);
            return;
        }

        $sql = "SELECT el.*, t.name as tenant_name, h.unit as house_unit, p.name as property_name FROM email_logs el LEFT JOIN tenants t ON t.email = el.to_email AND t.owner_id = el.owner_id LEFT JOIN houses h ON h.id = t.house_id LEFT JOIN properties p ON p.id = h.property_id WHERE el.id = ? AND el.owner_id = ?";
        $queryParams = [$id, $ownerId];

        if ($role === "caretaker") {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!empty($propertyIds)) {
                $sql .= " AND (p.id IN (" . implode(",", array_fill(0, count($propertyIds), "?")) . ") OR el.to_email NOT IN (SELECT email FROM tenants WHERE owner_id = ?))";
                $queryParams = array_merge($queryParams, $propertyIds, [$ownerId]);
            } else {
                Router::jsonResponse(['error' => 'Unauthorized'], 403);
                return;
            }
        }

        $log = $db->fetchOne($sql, $queryParams);
        if (!$log) {
            Router::jsonResponse(['error' => 'Email log not found'], 404);
            return;
        }

        Router::jsonResponse(['log' => $log]);
    }

    private function emptyStats(): array
    {
        return [
            "total" => 0,
            "sent" => 0,
            "failed" => 0,
            "pending" => 0,
            "success_rate" => 0,
        ];
    }
}

<?php
/**
 * Maintenance Controller - Track maintenance records and costs per tenant/property
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class MaintenanceController
{
    public function index(array $params = []): void
    {
        try {
            $ownerId = Router::getAuthUserId();
            $role = Router::getAuthRole();
            $db = Database::getInstance();

            $status = $_GET['status'] ?? '';
            $propertyId = $_GET['property_id'] ?? '';

            $sql = "SELECT SQL_CALC_FOUND_ROWS m.*, 
                           t.name as tenant_name, t.phone as tenant_phone,
                           h.unit, p.name as property_name
                    FROM maintenance_records m
                    LEFT JOIN tenants t ON m.tenant_id = t.id
                    LEFT JOIN houses h ON m.house_id = h.id
                    LEFT JOIN properties p ON m.property_id = p.id
                    WHERE m.owner_id = ?";
            $params = [$ownerId];

            if ($role === 'caretaker') {
                $propertyIds = Router::getCaretakerPropertyIds($db);
                if ($propertyIds) {
                    $sql .= " AND (m.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ") OR m.property_id IS NULL)";
                    $params = array_merge($params, $propertyIds);
                } else {
                    $sql .= " AND 1=0";
                }
            } elseif ($role === 'tenant') {
                $actorId = Router::getAuthTenantId();
                if (!empty($actorId)) {
                    $sql .= " AND (m.tenant_id = ? OR m.recipient_ids LIKE CONCAT('%', ?, '%'))";
                    $params[] = $actorId;
                    $params[] = (string)$actorId;
                } else {
                    $sql .= " AND 1=0";
                }
            }

            if ($status && in_array($status, ['pending', 'in-progress', 'completed', 'cancelled'])) {
                $sql .= " AND m.status = ?";
                $params[] = $status;
            }

            if ($propertyId) {
                $sql .= " AND m.property_id = ?";
                $params[] = (int)$propertyId;
            }

            $sql .= " ORDER BY m.created_at DESC";

            $records = $db->fetchAll($sql, $params);

            Router::jsonResponse(['maintenance' => $records]);
        } catch (\Exception $e) {
            error_log('Maintenance index error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            Router::jsonResponse(['error' => 'Failed to load maintenance records', 'details' => $e->getMessage()], 500);
        }
    }

    public function store(array $params = []): void
    {
        try {
            $ownerId = Router::getAuthUserId();
            $role = Router::getAuthRole();
            $actorId = Router::getAuthActorId();
            $data = Router::getRequestBody();
            $db = Database::getInstance();

            if (empty($data['title'])) {
                Router::jsonResponse(['error' => 'Title is required'], 400);
            }

            // Determine recipient configuration
            $tenantId = !empty($data['tenant_id']) ? (int)$data['tenant_id'] : null;
            $houseId = null;
            $propertyId = !empty($data['property_id']) ? (int)$data['property_id'] : null;
            $recipientType = $data['recipient_type'] ?? 'individual';
            $recipientIds = null;

            if ($role === 'tenant') {
                $tenantActorId = Router::getAuthTenantId();
                $tenant = $db->fetchOne("SELECT id, house_id, property_id FROM tenants WHERE id = ? AND owner_id = ?", [$tenantActorId, $ownerId]);
                if ($tenant) {
                    $tenantId = $tenant['id'];
                    $houseId = $tenant['house_id'];
                    $propertyId = $tenant['property_id'];
                }
                $recipientType = 'individual';
            } elseif ($tenantId) {
                // Validate tenant belongs to owner
                $tenant = $db->fetchOne("SELECT id, house_id, property_id FROM tenants WHERE id = ? AND owner_id = ?", [$tenantId, $ownerId]);
                if ($tenant) {
                    $houseId = $tenant['house_id'];
                    if (!$propertyId) $propertyId = $tenant['property_id'];
                }
                $recipientIds = json_encode([$tenantId]);
            } elseif ($propertyId) {
                // Validate property belongs to owner
                $property = $db->fetchOne("SELECT id FROM properties WHERE id = ? AND owner_id = ?", [$propertyId, $ownerId]);
                if (!$property && $role !== 'caretaker') {
                    Router::jsonResponse(['error' => 'Property not found'], 404);
                }
                if ($role === 'caretaker') {
                    $caretakerPropertyIds = Router::getCaretakerPropertyIds($db);
                    if (!in_array($propertyId, $caretakerPropertyIds)) {
                        Router::jsonResponse(['error' => 'Not authorized for this property'], 403);
                    }
                }
                // Get all tenants in this property
                $tenants = $db->fetchAll("SELECT id, house_id FROM tenants WHERE property_id = ? AND owner_id = ? AND status = 'active'", [$propertyId, $ownerId]);
                $recipientIds = json_encode(array_column($tenants, 'id'));
                if (!empty($tenants[0])) {
                    $tenantId = $tenants[0]['id'];
                    $houseId = $tenants[0]['house_id'];
                }
                $recipientType = 'property';
            } else {
                // All tenants
                $tenants = $db->fetchAll("SELECT id, house_id, property_id FROM tenants WHERE owner_id = ? AND status = 'active'", [$ownerId]);
                $recipientIds = json_encode(array_column($tenants, 'id'));
                if (!empty($tenants[0])) {
                    $tenantId = $tenants[0]['id'];
                    $houseId = $tenants[0]['house_id'];
                    if (!$propertyId) $propertyId = $tenants[0]['property_id'];
                }
                $recipientType = 'all';
            }

            $insertData = [
                'owner_id'       => $ownerId,
                'property_id'    => $propertyId,
                'house_id'       => $houseId,
                'tenant_id'      => $tenantId,
                'title'          => $data['title'],
                'description'    => $data['description'] ?? '',
                'category'       => $data['category'] ?? 'General',
                'priority'       => $data['priority'] ?? 'medium',
                'status'         => 'pending',
                'cost'           => !empty($data['cost']) ? (float)$data['cost'] : 0.00,
                'cost_notes'     => $data['cost_notes'] ?? '',
                'vendor_name'    => $data['vendor_name'] ?? '',
                'vendor_phone'   => $data['vendor_phone'] ?? '',
                'assigned_to'    => $data['assigned_to'] ?? '',
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'notes'          => $data['notes'] ?? '',
                'recipient_type' => $recipientType,
                'recipient_ids'  => $recipientIds,
            ];

            $recordId = $db->insert('maintenance_records', $insertData);
            $record = $db->fetchOne("SELECT * FROM maintenance_records WHERE id = ?", [$recordId]);

            Router::jsonResponse(['message' => 'Maintenance record created', 'maintenance' => $record], 201);
        } catch (\Exception $e) {
            error_log('Maintenance store error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            Router::jsonResponse(['error' => 'Failed to create maintenance record', 'details' => $e->getMessage()], 500);
        }
    }

    public function show(array $params): void
    {
        try {
            $ownerId = Router::getAuthUserId();
            $role = Router::getAuthRole();
            $recordId = (int) ($params['id'] ?? 0);
            $db = Database::getInstance();

            $sql = "SELECT m.*, t.name as tenant_name, t.phone as tenant_phone, 
                           h.unit, p.name as property_name
                    FROM maintenance_records m
                    LEFT JOIN tenants t ON m.tenant_id = t.id
                    LEFT JOIN houses h ON m.house_id = h.id
                    LEFT JOIN properties p ON m.property_id = p.id
                    WHERE m.id = ? AND m.owner_id = ?";
            $queryParams = [$recordId, $ownerId];

            if ($role === 'caretaker') {
                $propertyIds = Router::getCaretakerPropertyIds($db);
                if ($propertyIds) {
                    $sql .= " AND (m.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ") OR m.property_id IS NULL)";
                    $queryParams = array_merge($queryParams, $propertyIds);
                }
            } elseif ($role === 'tenant') {
                $actorId = Router::getAuthTenantId();
                $sql .= " AND (m.tenant_id = ? OR m.recipient_ids LIKE CONCAT('%', ?, '%'))";
                $queryParams[] = $actorId;
                $queryParams[] = (string)$actorId;
            }

            $record = $db->fetchOne($sql, $queryParams);
            if (!$record) {
                Router::jsonResponse(['error' => 'Maintenance record not found'], 404);
            }

            Router::jsonResponse(['maintenance' => $record]);
        } catch (\Exception $e) {
            error_log('Maintenance show error: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'Failed to load record', 'details' => $e->getMessage()], 500);
        }
    }

    public function update(array $params): void
    {
        try {
            $ownerId = Router::getAuthUserId();
            $role = Router::getAuthRole();
            $recordId = (int) ($params['id'] ?? 0);
            $data = Router::getRequestBody();
            $db = Database::getInstance();

            $existing = $db->fetchOne("SELECT * FROM maintenance_records WHERE id = ? AND owner_id = ?", [$recordId, $ownerId]);
            if (!$existing) {
                Router::jsonResponse(['error' => 'Maintenance record not found'], 404);
            }

            $updateData = [];
            if (isset($data['status'])) $updateData['status'] = $data['status'];
            if (isset($data['cost'])) $updateData['cost'] = (float)$data['cost'];
            if (isset($data['cost_notes'])) $updateData['cost_notes'] = $data['cost_notes'];
            if (isset($data['vendor_name'])) $updateData['vendor_name'] = $data['vendor_name'];
            if (isset($data['vendor_phone'])) $updateData['vendor_phone'] = $data['vendor_phone'];
            if (isset($data['assigned_to'])) $updateData['assigned_to'] = $data['assigned_to'];
            if (isset($data['scheduled_date'])) $updateData['scheduled_date'] = $data['scheduled_date'];
            if (isset($data['description'])) $updateData['description'] = $data['description'];
            if (isset($data['priority'])) $updateData['priority'] = $data['priority'];
            if (isset($data['notes'])) $updateData['notes'] = $data['notes'];
            if (isset($data['title'])) $updateData['title'] = $data['title'];
            if (isset($data['category'])) $updateData['category'] = $data['category'];

            // Auto-set completed_date when status changes to completed
            if (isset($data['status']) && $data['status'] === 'completed' && empty($existing['completed_date'])) {
                $updateData['completed_date'] = date('Y-m-d');
            }

            if (!empty($updateData)) {
                $db->update('maintenance_records', $updateData, 'id = ?', [$recordId]);
            }

            $record = $db->fetchOne("SELECT * FROM maintenance_records WHERE id = ?", [$recordId]);

            Router::jsonResponse(['message' => 'Maintenance record updated', 'maintenance' => $record]);
        } catch (\Exception $e) {
            error_log('Maintenance update error: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'Failed to update record', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy(array $params): void
    {
        try {
            Router::requireOwner();
            $ownerId = Router::getAuthUserId();
            $recordId = (int) ($params['id'] ?? 0);
            $db = Database::getInstance();

            $existing = $db->fetchOne("SELECT id FROM maintenance_records WHERE id = ? AND owner_id = ?", [$recordId, $ownerId]);
            if (!$existing) {
                Router::jsonResponse(['error' => 'Maintenance record not found'], 404);
            }

            $db->delete('maintenance_records', 'id = ?', [$recordId]);
            Router::jsonResponse(['message' => 'Maintenance record deleted']);
        } catch (\Exception $e) {
            error_log('Maintenance destroy error: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'Failed to delete record', 'details' => $e->getMessage()], 500);
        }
    }

    public function stats(array $params = []): void
    {
        try {
            $ownerId = Router::getAuthUserId();
            $role = Router::getAuthRole();
            $db = Database::getInstance();

            $where = "WHERE m.owner_id = ?";
            $queryParams = [$ownerId];

            if ($role === 'caretaker') {
                $propertyIds = Router::getCaretakerPropertyIds($db);
                if ($propertyIds) {
                    $where .= " AND (m.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ") OR m.property_id IS NULL)";
                    $queryParams = array_merge($queryParams, $propertyIds);
                } else {
                    $where .= " AND 1=0";
                }
            }

            // Total records count
            $total = $db->fetchOne("SELECT COUNT(*) as total FROM maintenance_records m $where", $queryParams);

            // Cost stats
            $costStats = $db->fetchOne("SELECT SUM(cost) as total_cost, AVG(cost) as avg_cost FROM maintenance_records m $where", $queryParams);

            // Status breakdown
            $pending = $db->fetchOne("SELECT COUNT(*) as count FROM maintenance_records m $where AND m.status = 'pending'", $queryParams);
            $inProgress = $db->fetchOne("SELECT COUNT(*) as count FROM maintenance_records m $where AND m.status = 'in-progress'", $queryParams);
            $completed = $db->fetchOne("SELECT COUNT(*) as count FROM maintenance_records m $where AND m.status = 'completed'", $queryParams);

            // This month costs
            $monthStart = date('Y-m-01');
            $monthCost = $db->fetchOne("SELECT SUM(cost) as monthly_cost FROM maintenance_records m $where AND m.created_at >= ?", array_merge($queryParams, [$monthStart . ' 00:00:00']));

            Router::jsonResponse([
                'stats' => [
                    'total' => (int)($total['total'] ?? 0),
                    'total_cost' => (float)($costStats['total_cost'] ?? 0),
                    'avg_cost' => (float)($costStats['avg_cost'] ?? 0),
                    'pending' => (int)($pending['count'] ?? 0),
                    'in_progress' => (int)($inProgress['count'] ?? 0),
                    'completed' => (int)($completed['count'] ?? 0),
                    'monthly_cost' => (float)($monthCost['monthly_cost'] ?? 0),
                ]
            ]);
        } catch (\Exception $e) {
            error_log('Maintenance stats error: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'Failed to load stats', 'details' => $e->getMessage()], 500);
        }
    }
}
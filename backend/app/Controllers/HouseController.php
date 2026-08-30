<?php
/**
 * House Controller - Owner-scoped CRUD
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\IdEncoder;
use App\Core\Router;
use App\Core\Pagination;

class HouseController
{
    /**
     * GET /api/houses
     */
    public function index(array $params = []): void
    {
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : null;

        $page = Pagination::fromRequest();

        $sql = "SELECT h.*, p.name as property_name, t.name as tenant_name 
                FROM houses h 
                LEFT JOIN properties p ON h.property_id = p.id 
                LEFT JOIN tenants t ON h.tenant_id = t.id 
                WHERE h.owner_id = ?";
        $queryParams = [$ownerId];

        $countSql = "SELECT COUNT(*) as total FROM houses h WHERE h.owner_id = ?";
        $countParams = [$ownerId];

        if ($propertyId) {
            $sql .= " AND h.property_id = ?";
            $queryParams[] = $propertyId;
            $countSql .= " AND h.property_id = ?";
            $countParams[] = $propertyId;
        }

        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) {
                Router::jsonResponse(['houses' => [], 'meta' => Pagination::meta(0, $page['page'], $page['per_page'])]);
                return;
            }
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            $sql .= " AND h.property_id IN ($placeholders)";
            $queryParams = array_merge($queryParams, $propertyIds);

            $countSql .= " AND h.property_id IN ($placeholders)";
            $countParams = array_merge($countParams, $propertyIds);
        }

        // Order houses chronologically/naturally by unit within each property,
        // e.g. A1, A2 ... A10 then B1, B2 ... B10 then C1, C2 ... C10
        $sql .= " ORDER BY p.name ASC,
                  CAST(REGEXP_REPLACE(h.unit, '[0-9]+$', '') AS CHAR) ASC,
                  CAST(REGEXP_REPLACE(h.unit, '^[^0-9]*', '') AS UNSIGNED) ASC
                  LIMIT ?, ?";
        $queryParams[] = $page['offset'];
        $queryParams[] = $page['limit'];

        $houses = $db->fetchAll($sql, $queryParams);
        $houses = array_map(function ($house) {
            if (isset($house['id'])) {
                $house['encoded_id'] = IdEncoder::encode((int) $house['id']);
            }
            return $house;
        }, $houses);

        $totalRow = $db->fetchOne($countSql, $countParams);
        $total = (int) ($totalRow['total'] ?? 0);

        Router::jsonResponse(['houses' => $houses, 'meta' => Pagination::meta($total, $page['page'], $page['per_page'])]);
    }

    /**
     * GET /api/houses/available
     * Get houses that are not occupied by a tenant
     */
    public function available(array $params = []): void
    {
        Router::requireOwnerOrCaretaker();
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        $propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : null;

        $sql = "SELECT h.id, h.property_id, h.unit, h.type, h.rent, p.name as property_name
                FROM houses h
                LEFT JOIN properties p ON h.property_id = p.id
                WHERE h.owner_id = ?
                  AND h.status = 'vacant'
                  AND h.tenant_id IS NULL";
        $queryParams = [$ownerId];

        if ($propertyId) {
            $sql .= " AND h.property_id = ?";
            $queryParams[] = $propertyId;
        }

        if (Router::getAuthRole() === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) {
                Router::jsonResponse(['houses' => []]);
                return;
            }
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            $sql .= " AND h.property_id IN ($placeholders)";
            $queryParams = array_merge($queryParams, $propertyIds);
        }

        $sql .= " ORDER BY p.name ASC, h.unit ASC";
        $houses = $db->fetchAll($sql, $queryParams);
        $houses = array_map(function ($house) {
            if (isset($house['id'])) {
                $house['encoded_id'] = IdEncoder::encode((int) $house['id']);
            }
            return $house;
        }, $houses);

        Router::jsonResponse(['houses' => $houses]);
    }

    /**
     * POST /api/houses
     */
    public function store(array $params = []): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        if (empty($data['property_id']) || empty($data['unit'])) {
            Router::jsonResponse(['error' => 'Property ID and unit number are required'], 400);
        }

        // Verify property belongs to owner
        $property = $db->fetchOne(
            "SELECT id, units FROM properties WHERE id = ? AND owner_id = ?",
            [$data['property_id'], $ownerId]
        );
        if (!$property) {
            Router::jsonResponse(['error' => 'Property not found'], 404);
        }

        $houseId = $db->insert('houses', [
            'owner_id'    => $ownerId,
            'property_id' => (int) $data['property_id'],
            'unit'        => $data['unit'],
            'type'        => $data['type'] ?? '1 Bedroom',
            'status'      => $data['status'] ?? 'vacant',
            'rent'        => $data['rent'] ?? 0,
            'water_meter' => $data['water_meter'] ?? null,
            'elec_meter'  => $data['elec_meter'] ?? null,
        ]);

        // Update property unit count
        $count = $db->fetchOne(
            "SELECT COUNT(*) as total FROM houses WHERE property_id = ?",
            [$data['property_id']]
        );
        $db->update('properties', ['units' => $count['total']], 'id = ?', [(int) $data['property_id']]);

        $house = $db->fetchOne("SELECT * FROM houses WHERE id = ?", [$houseId]);
        Router::jsonResponse(['message' => 'House created', 'house' => $house], 201);
    }

    /**
     * PUT /api/houses/{id}
     */
    public function update(array $params): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $houseId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $existing = $db->fetchOne(
            "SELECT id FROM houses WHERE id = ? AND owner_id = ?",
            [$houseId, $ownerId]
        );
        if (!$existing) {
            Router::jsonResponse(['error' => 'House not found'], 404);
        }

        $updateData = [];
        foreach (['unit', 'type', 'status', 'rent', 'water_meter', 'elec_meter', 'tenant_id'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $db->update('houses', $updateData, 'id = ?', [$houseId]);
        }

        $house = $db->fetchOne("SELECT * FROM houses WHERE id = ?", [$houseId]);
        Router::jsonResponse(['message' => 'House updated', 'house' => $house]);
    }

    /**
     * GET /api/houses/{id}
     */
    public function show(array $params): void
    {
        try {
            Router::requireOwnerOrCaretaker();
            $ownerId = Router::getAuthUserId();
            $role = Router::getAuthRole();
            $houseId = (int) ($params['id'] ?? 0);
            $db = Database::getInstance();

            $house = $db->fetchOne(
                "SELECT h.*, p.name as property_name
                 FROM houses h
                 LEFT JOIN properties p ON h.property_id = p.id
                 WHERE h.id = ? AND h.owner_id = ?",
                [$houseId, $ownerId]
            );

            if (!$house) {
                Router::jsonResponse(['error' => 'House not found'], 404);
            }

            // Current tenant
            $currentTenant = null;
            if (!empty($house['tenant_id'])) {
                $currentTenant = $db->fetchOne(
                    "SELECT id, name, email, phone, lease_start, lease_end, status
                     FROM tenants
                     WHERE id = ? AND owner_id = ?",
                    [$house['tenant_id'], $ownerId]
                );                if ($currentTenant && isset($currentTenant['id'])) {
                    $currentTenant['encoded_id'] = IdEncoder::encode((int) $currentTenant['id']);
                }            }

            // Past tenants who lived in this house
            $pastTenants = [];
            try {
                $pastTenants = $db->fetchAll(
                    "SELECT t.name, t.email, t.phone, t.lease_start, t.lease_end, t.status, p.name as property_name, h.unit
                     FROM tenants t
                     LEFT JOIN houses h ON t.house_id = h.id
                     LEFT JOIN properties p ON t.property_id = p.id
                     WHERE t.owner_id = ? AND t.house_id = ?
                     ORDER BY t.lease_start DESC",
                    [$ownerId, $houseId]
                );
            } catch (\Throwable $e) {
                error_log('Failed to load house past tenants: ' . $e->getMessage());
            }

            // Revenue from payments for this house
            $revenue = [];
            try {
                $revenue = $db->fetchAll(
                    "SELECT amount, date, method, status FROM payments WHERE house_id = ? AND owner_id = ? ORDER BY date DESC",
                    [$houseId, $ownerId]
                );
            } catch (\Throwable $e) {
                error_log('Failed to load house payments: ' . $e->getMessage());
            }

            // All maintenance records for this house
            $maintenance = [];
            try {
                $maintenance = $db->fetchAll(
                    "SELECT m.*, t.name as tenant_name
                     FROM maintenance_records m
                     LEFT JOIN tenants t ON m.tenant_id = t.id
                     WHERE m.house_id = ? AND m.owner_id = ?
                     ORDER BY m.created_at DESC",
                    [$houseId, $ownerId]
                );
            } catch (\Throwable $e) {
                error_log('Failed to load house maintenance: ' . $e->getMessage());
            }

            // Total maintenance cost for this house
            $damageCost = ['total' => 0];
            try {
                $damageCost = $db->fetchOne(
                    "SELECT SUM(cost) as total FROM maintenance_records WHERE house_id = ? AND owner_id = ? AND category = 'Damage'",
                    [$houseId, $ownerId]
                );
            } catch (\Throwable $e) {
                error_log('Failed to load house damage cost: ' . $e->getMessage());
            }

            Router::jsonResponse([
                'house' => $house,
                'current_tenant' => $currentTenant,
                'past_tenants' => $pastTenants,
                'payments' => $revenue,
                'maintenance' => $maintenance,
                'damage_cost' => (float)($damageCost['total'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            error_log('House show error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            Router::jsonResponse(['error' => 'Failed to load house details', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/houses/{id}
      */
    public function destroy(array $params): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $houseId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $existing = $db->fetchOne(
            "SELECT id, property_id FROM houses WHERE id = ? AND owner_id = ?",
            [$houseId, $ownerId]
        );
        if (!$existing) {
            Router::jsonResponse(['error' => 'House not found'], 404);
        }

        $db->delete('houses', 'id = ?', [$houseId]);

        // Update property unit count
        $count = $db->fetchOne(
            "SELECT COUNT(*) as total FROM houses WHERE property_id = ?",
            [$existing['property_id']]
        );
        $db->update('properties', ['units' => $count['total']], 'id = ?', [$existing['property_id']]);

        Router::jsonResponse(['message' => 'House deleted']);
    }
}
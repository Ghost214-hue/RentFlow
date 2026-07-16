<?php
/**
 * House Controller - Owner-scoped CRUD
 */
namespace App\Controllers;

use App\Core\Database;
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

        $sql .= " ORDER BY h.created_at DESC LIMIT ?, ?";
        $queryParams[] = $page['offset'];
        $queryParams[] = $page['limit'];

        $houses = $db->fetchAll($sql, $queryParams);

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

        $sql .= " ORDER BY p.name ASC, h.unit ASC";
        $houses = $db->fetchAll($sql, $queryParams);

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
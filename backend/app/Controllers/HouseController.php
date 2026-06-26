<?php
/**
 * House Controller - Owner-scoped CRUD
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class HouseController
{
    /**
     * GET /api/houses
     */
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';
        $propertyId = (int) ($_GET['property_id'] ?? 0);

        $sql = "SELECT h.*, p.name as property_name, t.name as tenant_name, t.phone as tenant_phone
                FROM houses h
                LEFT JOIN properties p ON h.property_id = p.id
                LEFT JOIN tenants t ON h.tenant_id = t.id
                WHERE h.owner_id = ?";
        $params = [$ownerId];

        if ($status && in_array($status, ['occupied', 'vacant'])) {
            $sql .= " AND h.status = ?";
            $params[] = $status;
        }

        if ($propertyId > 0) {
            $sql .= " AND h.property_id = ?";
            $params[] = $propertyId;
        }

        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['houses' => []]);
            $sql .= " AND h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $params = array_merge($params, $propertyIds);
        } elseif ($role === 'tenant') {
            $sql .= " AND h.id = (SELECT house_id FROM tenants WHERE id = ? AND owner_id = ?)";
            $params[] = Router::getAuthTenantId();
            $params[] = $ownerId;
        }

        $sql .= " ORDER BY p.name, h.unit";

        $houses = $db->fetchAll($sql, $params);
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

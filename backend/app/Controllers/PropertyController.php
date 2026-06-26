<?php
/**
 * Property Controller - Owner-scoped CRUD
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class PropertyController
{
    /**
     * GET /api/properties
     */
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $sql = "SELECT p.*, 
                    (SELECT COUNT(*) FROM houses WHERE property_id = p.id) as unit_count,
                    (SELECT COUNT(*) FROM houses WHERE property_id = p.id AND status = 'occupied') as occupied_count
             FROM properties p 
             WHERE p.owner_id = ?";
        $queryParams = [$ownerId];

        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['properties' => []]);
            $sql .= " AND p.id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $queryParams = array_merge($queryParams, $propertyIds);
        } elseif ($role === 'tenant') {
            $sql .= " AND p.id = (SELECT property_id FROM tenants WHERE id = ? AND owner_id = ?)";
            $queryParams[] = Router::getAuthTenantId();
            $queryParams[] = $ownerId;
        }

        $sql .= " ORDER BY p.created_at DESC";
        $properties = $db->fetchAll($sql, $queryParams);

        Router::jsonResponse(['properties' => $properties]);
    }

    /**
     * GET /api/properties/{id}
     */
    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $propertyId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $sql = "SELECT * FROM properties WHERE id = ? AND owner_id = ?";
        $queryParams = [$propertyId, $ownerId];
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['error' => 'Property not found'], 404);
            $sql .= " AND id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $queryParams = array_merge($queryParams, $propertyIds);
        } elseif ($role === 'tenant') {
            $sql .= " AND id = (SELECT property_id FROM tenants WHERE id = ? AND owner_id = ?)";
            $queryParams[] = Router::getAuthTenantId();
            $queryParams[] = $ownerId;
        }

        $property = $db->fetchOne($sql, $queryParams);

        if (!$property) {
            Router::jsonResponse(['error' => 'Property not found'], 404);
        }

        // Get houses for this property
        $houses = $db->fetchAll(
            "SELECT h.*, t.name as tenant_name 
             FROM houses h 
             LEFT JOIN tenants t ON h.tenant_id = t.id 
             WHERE h.property_id = ? AND h.owner_id = ?",
            [$propertyId, $ownerId]
        );

        $property['houses'] = $houses;
        Router::jsonResponse(['property' => $property]);
    }

    /**
     * POST /api/properties
     */
    public function store(array $params = []): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $required = ['name', 'address'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Router::jsonResponse(['error' => "Field '{$field}' is required"], 400);
            }
        }

        $propertyId = $db->insert('properties', [
            'owner_id' => $ownerId,
            'name'     => $data['name'],
            'address'  => $data['address'],
            'type'     => $data['type'] ?? 'Apartment Block',
            'units'    => (int) ($data['units'] ?? 0),
            'image'    => $data['image'] ?? null,
            'rent'     => $data['rent'] ?? 0,
        ]);

        $property = $db->fetchOne(
            "SELECT * FROM properties WHERE id = ?",
            [$propertyId]
        );

        Router::jsonResponse(['message' => 'Property created', 'property' => $property], 201);
    }

    /**
     * PUT /api/properties/{id}
     */
    public function update(array $params): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $propertyId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        // Verify ownership
        $existing = $db->fetchOne(
            "SELECT id FROM properties WHERE id = ? AND owner_id = ?",
            [$propertyId, $ownerId]
        );
        if (!$existing) {
            Router::jsonResponse(['error' => 'Property not found'], 404);
        }

        $updateData = [];
        foreach (['name', 'address', 'type', 'units', 'image', 'rent'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $db->update('properties', $updateData, 'id = ?', [$propertyId]);
        }

        $property = $db->fetchOne("SELECT * FROM properties WHERE id = ?", [$propertyId]);
        Router::jsonResponse(['message' => 'Property updated', 'property' => $property]);
    }

    /**
     * DELETE /api/properties/{id}
     */
    public function destroy(array $params): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $propertyId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $existing = $db->fetchOne(
            "SELECT id FROM properties WHERE id = ? AND owner_id = ?",
            [$propertyId, $ownerId]
        );
        if (!$existing) {
            Router::jsonResponse(['error' => 'Property not found'], 404);
        }

        $db->delete('properties', 'id = ?', [$propertyId]);
        Router::jsonResponse(['message' => 'Property deleted']);
    }
}

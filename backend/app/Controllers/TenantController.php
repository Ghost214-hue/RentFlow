<?php
/**
 * Tenant Controller - Owner-scoped CRUD with onboarding
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class TenantController
{
    /**
     * GET /api/tenants
     */
    public function index(): void
    {
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        $tenants = $db->fetchAll(
            "SELECT t.*, p.name as property_name, h.unit as house_unit, h.rent
             FROM tenants t
             LEFT JOIN properties p ON t.property_id = p.id
             LEFT JOIN houses h ON t.house_id = h.id
             WHERE t.owner_id = ?
             ORDER BY t.name ASC",
            [$ownerId]
        );

        Router::jsonResponse(['tenants' => $tenants]);
    }

    /**
     * GET /api/tenants/{id}
     */
    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $tenantId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $tenant = $db->fetchOne(
            "SELECT t.*, p.name as property_name, h.unit as house_unit
             FROM tenants t
             LEFT JOIN properties p ON t.property_id = p.id
             LEFT JOIN houses h ON t.house_id = h.id
             WHERE t.id = ? AND t.owner_id = ?",
            [$tenantId, $ownerId]
        );

        if (!$tenant) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }

        // Get payment history
        $payments = $db->fetchAll(
            "SELECT * FROM payments WHERE tenant_id = ? AND owner_id = ? ORDER BY created_at DESC",
            [$tenantId, $ownerId]
        );

        // Get complaints
        $complaints = $db->fetchAll(
            "SELECT * FROM complaints WHERE tenant_id = ? AND owner_id = ? ORDER BY created_at DESC",
            [$tenantId, $ownerId]
        );

        $tenant['payments'] = $payments;
        $tenant['complaints'] = $complaints;
        Router::jsonResponse(['tenant' => $tenant]);
    }

    /**
     * POST /api/tenants - Complete tenant onboarding
     */
    public function store(): void
    {
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        if (empty($data['name'])) {
            Router::jsonResponse(['error' => 'Tenant name is required'], 400);
        }

        // If house_id provided, verify it belongs to owner
        if (!empty($data['house_id'])) {
            $house = $db->fetchOne(
                "SELECT id, property_id, status FROM houses WHERE id = ? AND owner_id = ?",
                [$data['house_id'], $ownerId]
            );
            if (!$house) {
                Router::jsonResponse(['error' => 'House not found'], 404);
            }
            if ($house['status'] === 'occupied') {
                Router::jsonResponse(['error' => 'House is already occupied'], 400);
            }
        }

        // If house_id provided, auto-set property_id from the house
        if (!empty($data['house_id'])) {
            $data['property_id'] = $house['property_id'] ?? null;
        }

        $tenantId = $db->insert('tenants', [
            'owner_id'          => $ownerId,
            'property_id'       => !empty($data['property_id']) ? (int) $data['property_id'] : null,
            'house_id'          => !empty($data['house_id']) ? (int) $data['house_id'] : null,
            'name'              => $data['name'],
            'email'             => $data['email'] ?? null,
            'phone'             => $data['phone'] ?? null,
            'id_number'         => $data['id_number'] ?? null,
            'id_type'           => $data['id_type'] ?? 'National ID',
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'lease_start'       => $data['lease_start'] ?? null,
            'lease_end'         => $data['lease_end'] ?? null,
            'deposit'           => $data['deposit'] ?? 0,
            'balance'           => $data['balance'] ?? 0,
            'water_balance'     => $data['water_balance'] ?? 0,
            'elec_balance'      => $data['elec_balance'] ?? 0,
            'documents'         => $data['documents'] ?? null,
        ]);

        // If house assigned, mark as occupied and link tenant
        if (!empty($data['house_id'])) {
            $db->update('houses', [
                'status'    => 'occupied',
                'tenant_id' => $tenantId,
            ], 'id = ?', [(int) $data['house_id']]);

            // Update property occupied count
            $propertyId = $house['property_id'] ?? null;
            if ($propertyId) {
                $occupied = $db->fetchOne(
                    "SELECT COUNT(*) as total FROM houses WHERE property_id = ? AND status = 'occupied'",
                    [$propertyId]
                );
                $db->update('properties', ['occupied' => $occupied['total']], 'id = ?', [$propertyId]);
            }
        }

        // Generate initial bill for the month
        if (!empty($data['house_id']) && !empty($data['rent'])) {
            $month = date('Y-m');
            $existing = $db->fetchOne(
                "SELECT id FROM bills WHERE house_id = ? AND month = ?",
                [(int) $data['house_id'], $month]
            );
            if (!$existing) {
                $db->insert('bills', [
                    'owner_id' => $ownerId,
                    'house_id' => (int) $data['house_id'],
                    'month'    => $month,
                    'rent'     => $data['rent'],
                    'total'    => $data['rent'],
                    'status'   => 'pending',
                    'due_date' => date('Y-m-05'),
                ]);
            }
        }

        $tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ?", [$tenantId]);
        Router::jsonResponse(['message' => 'Tenant registered', 'tenant' => $tenant], 201);
    }

    /**
     * PUT /api/tenants/{id}
     */
    public function update(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $tenantId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $existing = $db->fetchOne(
            "SELECT id FROM tenants WHERE id = ? AND owner_id = ?",
            [$tenantId, $ownerId]
        );
        if (!$existing) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }

        $updateData = [];
        $allowed = ['name', 'email', 'phone', 'id_number', 'id_type', 'emergency_contact',
                     'lease_start', 'lease_end', 'deposit', 'balance', 'water_balance', 'elec_balance'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $db->update('tenants', $updateData, 'id = ?', [$tenantId]);
        }

        $tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ?", [$tenantId]);
        Router::jsonResponse(['message' => 'Tenant updated', 'tenant' => $tenant]);
    }

    /**
     * DELETE /api/tenants/{id}
     */
    public function destroy(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $tenantId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        // Get tenant info before deleting
        $tenant = $db->fetchOne(
            "SELECT id, house_id FROM tenants WHERE id = ? AND owner_id = ?",
            [$tenantId, $ownerId]
        );
        if (!$tenant) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }

        // Free up the house
        if ($tenant['house_id']) {
            $db->update('houses', ['status' => 'vacant', 'tenant_id' => null], 'id = ?', [$tenant['house_id']]);
        }

        $db->delete('tenants', 'id = ?', [$tenantId]);
        Router::jsonResponse(['message' => 'Tenant deleted']);
    }
}
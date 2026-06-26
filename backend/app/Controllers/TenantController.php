<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Services\EmailService;

class TenantController
{
   
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $sql = "SELECT t.*, p.name as property_name, h.unit as house_unit, h.rent
             FROM tenants t
             LEFT JOIN properties p ON t.property_id = p.id
             LEFT JOIN houses h ON t.house_id = h.id
             WHERE t.owner_id = ?";
        $queryParams = [$ownerId];

        if ($role === 'tenant') {
            $sql .= " AND t.id = ?";
            $queryParams[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) {
                Router::jsonResponse(['tenants' => []]);
            }
            $sql .= " AND t.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $queryParams = array_merge($queryParams, $propertyIds);
        }

        $sql .= " ORDER BY t.name ASC";
        $tenants = $db->fetchAll($sql, $queryParams);

        Router::jsonResponse(['tenants' => $tenants]);
    }

   
    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $tenantId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $sql = "SELECT t.*, p.name as property_name, h.unit as house_unit
             FROM tenants t
             LEFT JOIN properties p ON t.property_id = p.id
             LEFT JOIN houses h ON t.house_id = h.id
             WHERE t.id = ? AND t.owner_id = ?";
        $queryParams = [$tenantId, $ownerId];
        if ($role === 'tenant') {
            $sql .= " AND t.id = ?";
            $queryParams[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['error' => 'Tenant not found'], 404);
            $sql .= " AND t.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $queryParams = array_merge($queryParams, $propertyIds);
        }
        $tenant = $db->fetchOne($sql, $queryParams);

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
    public function store(array $params = []): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $data['name'] = trim((string) ($data['name'] ?? ''));
        $data['email'] = trim((string) ($data['email'] ?? ''));
        $data['phone'] = trim((string) ($data['phone'] ?? ''));
        $data['id_number'] = trim((string) ($data['id_number'] ?? ''));

        if ($data['name'] === '') {
            Router::jsonResponse(['error' => 'Tenant name is required'], 400);
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Router::jsonResponse(['error' => 'Invalid email format'], 400);
        }
        if ($data['phone'] === '') {
            Router::jsonResponse(['error' => 'Tenant phone is required'], 400);
        }
        if ($data['email'] === '') {
            Router::jsonResponse(['error' => 'Tenant email is required for login credentials'], 400);
        }
        if ($data['id_number'] === '') {
            Router::jsonResponse(['error' => 'Tenant national ID is required for default credentials'], 400);
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

        try {
            $db->beginTransaction();

            $tenantId = $db->insert('tenants', [
                'owner_id'          => $ownerId,
                'property_id'       => !empty($data['property_id']) ? (int) $data['property_id'] : null,
                'house_id'          => !empty($data['house_id']) ? (int) $data['house_id'] : null,
                'name'              => $data['name'],
                'email'             => $data['email'] !== '' ? $data['email'] : null,
                'password'          => password_hash($data['password'] ?? $data['id_number'], PASSWORD_BCRYPT),
                'phone'             => $data['phone'],
                'id_number'         => $data['id_number'] !== '' ? $data['id_number'] : null,
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

            // If house assigned, atomically claim it while it is still vacant.
            if (!empty($data['house_id'])) {
                $updated = $db->update('houses', [
                    'status'    => 'occupied',
                    'tenant_id' => $tenantId,
                ], 'id = ? AND owner_id = ? AND status = ?', [(int) $data['house_id'], $ownerId, 'vacant']);

                if ($updated < 1) {
                    throw new \RuntimeException('House is already occupied');
                }

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
            $db->commit();
            
            // Send welcome email to tenant
            $emailSent = false;
            try {
                $property = $db->fetchOne("SELECT name FROM properties WHERE id = ?", [$tenant['property_id']]);
                $house = $db->fetchOne("SELECT unit FROM houses WHERE id = ?", [$tenant['house_id']]);
                $emailService = new EmailService();
                $emailSent = $emailService->sendTenantWelcome(
                    $ownerId,
                    $tenant,
                    $property['name'] ?? 'N/A',
                    $house['unit'] ?? 'N/A'
                );
            } catch (\Throwable $e) {
                error_log('Failed to send tenant welcome email: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollback();
            Router::jsonResponse(['error' => $e->getMessage()], 400);
        }

        $emailMsg = $emailSent ? '& welcome email sent' : '& but welcome email could not be sent';
        Router::jsonResponse(['message' => "Tenant registered {$emailMsg}", 'tenant' => $tenant, 'email_sent' => $emailSent], 201);
    }

    /**
     * PUT /api/tenants/{id}
     */
    public function update(array $params): void
    {
        Router::requireOwner();
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
                $updateData[$field] = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
            }
        }

        if (isset($updateData['name']) && $updateData['name'] === '') {
            Router::jsonResponse(['error' => 'Tenant name is required'], 400);
        }
        if (!empty($updateData['email']) && !filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
            Router::jsonResponse(['error' => 'Invalid email format'], 400);
        }

        if (!empty($updateData)) {
            $db->update('tenants', $updateData, 'id = ? AND owner_id = ?', [$tenantId, $ownerId]);
        }

        $tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ? AND owner_id = ?", [$tenantId, $ownerId]);
        Router::jsonResponse(['message' => 'Tenant updated', 'tenant' => $tenant]);
    }

    /**
     * DELETE /api/tenants/{id}
     */
    public function destroy(array $params): void
    {
        Router::requireOwner();
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

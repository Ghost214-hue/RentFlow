<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\Pagination;
use App\Services\EmailService;

class TenantController
{
    
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();
           $page = Pagination::fromRequest();

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

        $sqlCount = "SELECT COUNT(*) as total FROM tenants t WHERE t.owner_id = ?";
        $countParams = [$ownerId];

        if ($role === 'tenant') {
            $sql .= " AND t.id = ?";
            $queryParams[] = Router::getAuthTenantId();
            $sqlCount .= " AND t.id = ?";
            $countParams[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) {
                Router::jsonResponse(['tenants' => [], 'meta' => Pagination::meta(0, $page['page'], $page['per_page'])]);
            }
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            $sql .= " AND t.property_id IN ($placeholders)";
            $queryParams = array_merge($queryParams, $propertyIds);

            $sqlCount .= " AND t.property_id IN ($placeholders)";
            $countParams = array_merge($countParams, $propertyIds);
        }

        $sql .= " ORDER BY t.name ASC LIMIT ?, ?";
        $queryParams[] = $page['offset'];
        $queryParams[] = $page['limit'];

        $tenants = $db->fetchAll($sql, $queryParams);

        $totalRow = $db->fetchOne($sqlCount, $countParams);
        $total = (int) ($totalRow['total'] ?? 0);

        Router::jsonResponse(['tenants' => $tenants, 'meta' => Pagination::meta($total, $page['page'], $page['per_page'])]);
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
        $tenant['documents'] = json_decode($tenant['documents'] ?? '[]', true) ?: [];
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
        $data['next_of_kin_name'] = trim((string) ($data['next_of_kin_name'] ?? ''));
        $data['next_of_kin_phone'] = trim((string) ($data['next_of_kin_phone'] ?? ''));
        $data['next_of_kin_email'] = trim((string) ($data['next_of_kin_email'] ?? ''));

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
                'owner_id'             => $ownerId,
                'property_id'          => !empty($data['property_id']) ? (int) $data['property_id'] : null,
                'house_id'             => !empty($data['house_id']) ? (int) $data['house_id'] : null,
                'name'                 => $data['name'],
                'email'                => $data['email'] !== '' ? $data['email'] : null,
                'password'             => password_hash($data['password'] ?? $data['id_number'], PASSWORD_BCRYPT),
                'phone'                => $data['phone'],
                'id_number'            => $data['id_number'] !== '' ? $data['id_number'] : null,
                'id_type'              => $data['id_type'] ?? 'National ID',
                'next_of_kin_name'     => $data['next_of_kin_name'] !== '' ? $data['next_of_kin_name'] : null,
                'next_of_kin_phone'    => $data['next_of_kin_phone'] !== '' ? $data['next_of_kin_phone'] : null,
                'next_of_kin_email'    => $data['next_of_kin_email'] !== '' ? $data['next_of_kin_email'] : null,
                'lease_start'          => $data['lease_start'] ?? null,
                'lease_end'            => $data['lease_end'] ?? null,
                'deposit'              => $data['deposit'] ?? 0,
                'balance'              => $data['balance'] ?? 0,
                'water_balance'        => $data['water_balance'] ?? 0,
                'elec_balance'         => $data['elec_balance'] ?? 0,
                'documents'            => $data['documents'] ?? null,
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
            try { $db->rollback(); } catch (\Throwable $t) { /* no active transaction */ }
            Router::jsonResponse(['error' => 'Failed to register tenant'], 400);
        }

        $emailMsg = $emailSent ? '& welcome email sent' : '& but welcome email could not be sent';
        Router::jsonResponse(['message' => "Tenant registered {$emailMsg}", 'tenant' => $tenant, 'email_sent' => $emailSent], 201);
    }

    /**
     * PUT /api/tenants/{id}
      */
     public function update(array $params): void
     {
         $role = Router::getAuthRole();
         $actorId = Router::getAuthUserId();
         $tenantId = (int) ($params['id'] ?? 0);
         $data = Router::getRequestBody();
         $db = Database::getInstance();
 
         // Verify tenant exists and get owner_id
         $existing = $db->fetchOne(
             "SELECT id, owner_id FROM tenants WHERE id = ?",
             [$tenantId]
         );
         
         if (!$existing) {
             Router::jsonResponse(['error' => 'Tenant not found'], 404);
         }
         
         $ownerId = $existing['owner_id'];
         
         // Check authorization: owner can update any of their tenants, tenant can update themselves
         if ($role === 'owner') {
             // Owner is updating - verify tenant belongs to them
             if ($existing['owner_id'] != $actorId) {
                 Router::jsonResponse(['error' => 'Not authorized'], 403);
             }
         } elseif ($role === 'tenant') {
             // Tenant is updating - verify it's their own record
             $authTenantId = Router::getAuthTenantId();
             if ($tenantId !== $authTenantId) {
                 Router::jsonResponse(['error' => 'Only owners can perform this action'], 403);
             }
         } else {
             Router::jsonResponse(['error' => 'Not authorized'], 403);
         }
 
         $updateData = [];
         
         // Define allowed fields based on role
         if ($role === 'owner') {
             // Owners can update all fields
             $allowed = ['name', 'email', 'phone', 'id_number', 'id_type', 'next_of_kin_name', 'next_of_kin_phone', 'next_of_kin_email',
                      'lease_start', 'lease_end', 'deposit', 'balance', 'water_balance', 'elec_balance'];
         } else {
             // Tenants can only update phone and email
             $allowed = ['phone', 'email'];
         }
         
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
     * POST /api/tenants/request-termination
     * Tenant-initiated: tenant requests to terminate their tenancy
     */
    public function requestTermination(): void
    {
        $actorId = Router::getAuthActorId();
        $role = Router::getAuthRole();
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        // Only tenants can use this endpoint
        if ($role !== 'tenant') {
            Router::jsonResponse(['error' => 'Only tenants can request termination'], 403);
        }

        $tenantId = Router::getAuthTenantId();
        if (!$tenantId) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }

        // Get tenant details with property/house
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

        // Check if tenant is currently in a house
        if (empty($tenant['house_id'])) {
            Router::jsonResponse(['error' => 'You are not currently occupying a house'], 400);
        }

        // Check if already has a pending termination
        $pendingExists = $db->fetchOne(
            "SELECT id FROM tenancy_terminations WHERE tenant_id = ? AND status = 'pending'",
            [$tenantId]
        );
        if ($pendingExists) {
            Router::jsonResponse(['error' => 'You already have a pending termination request'], 400);
        }

        $reason = trim((string) ($data['reason'] ?? ''));
        $effectiveDate = trim((string) ($data['effective_date'] ?? date('Y-m-d', strtotime('+30 days'))));

        try {
            $db->beginTransaction();

            // Update tenant status to pending_termination
            $db->update('tenants', ['status' => 'pending_termination'], 'id = ? AND owner_id = ?', [$tenantId, $ownerId]);

            // Create termination request record
            $db->insert('tenancy_terminations', [
                'owner_id' => $ownerId,
                'tenant_id' => $tenantId,
                'property_id' => $tenant['property_id'],
                'house_id' => $tenant['house_id'],
                'initiated_by' => 'tenant',
                'initiated_by_user_id' => $tenantId,
                'reason' => $reason,
                'effective_date' => $effectiveDate,
                'status' => 'pending',
            ]);

            $db->commit();

            // Send email notification to owner
            $emailSent = false;
            try {
                $owner = $db->fetchOne("SELECT name, email FROM owners WHERE id = ?", [$ownerId]);
                if ($owner && !empty($owner['email'])) {
                    $emailService = new EmailService();
                    $emailSent = $emailService->sendTemplate('Termination Request', $ownerId, $owner['email'], $owner['name'], [
                        'owner_name' => $owner['name'],
                        'tenant_name' => $tenant['name'],
                        'property' => $tenant['property_name'] ?? 'N/A',
                        'house' => $tenant['house_unit'] ?? 'N/A',
                        'date' => $effectiveDate,
                        'reason' => $reason ?: 'Not provided',
                    ]);
                }
            } catch (\Throwable $e) {
                error_log('Failed to send termination request email: ' . $e->getMessage());
            }

            Router::jsonResponse([
                'message' => 'Termination request submitted. The property owner has been notified.',
                'email_sent' => $emailSent,
                'status' => 'pending'
            ]);

        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollback();
            Router::jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/tenants/{id}/terminate
     * Owner/Caretaker-initiated: directly terminate a tenant's tenancy
     */
    public function terminate(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $tenantId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        // Only owner or caretaker can directly terminate
        if ($role !== 'owner' && $role !== 'caretaker') {
            Router::jsonResponse(['error' => 'Not authorized'], 403);
        }

        // Get tenant with house and property
        $tenant = $db->fetchOne(
            "SELECT t.*, p.name as property_name, h.unit as house_unit, o.name as owner_name
             FROM tenants t
             LEFT JOIN properties p ON t.property_id = p.id
             LEFT JOIN houses h ON t.house_id = h.id
             LEFT JOIN owners o ON t.owner_id = o.id
             WHERE t.id = ? AND t.owner_id = ?",
            [$tenantId, $ownerId]
        );

        if (!$tenant) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }

        // If caretaker, check they manage this property
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!in_array($tenant['property_id'], $propertyIds)) {
                Router::jsonResponse(['error' => 'Not authorized to terminate this tenant'], 403);
            }
        }

        $reason = trim((string) ($data['reason'] ?? ''));
        $effectiveDate = trim((string) ($data['effective_date'] ?? date('Y-m-d')));

        try {
            $db->beginTransaction();

            // Release the house if occupied
            if (!empty($tenant['house_id'])) {
                $db->update('houses', [
                    'status' => 'vacant',
                    'tenant_id' => null,
                ], 'id = ? AND owner_id = ?', [$tenant['house_id'], $ownerId]);
            }

            // Update tenant record
            $db->update('tenants', [
                'house_id' => null,
                'property_id' => null,
                'lease_end' => $effectiveDate,
                'status' => 'terminated',
            ], 'id = ? AND owner_id = ?', [$tenantId, $ownerId]);

            // Update property occupied count
            if ($tenant['property_id']) {
                $occupied = $db->fetchOne(
                    "SELECT COUNT(*) as total FROM houses WHERE property_id = ? AND status = 'occupied'",
                    [$tenant['property_id']]
                );
                $db->update('properties', ['occupied' => $occupied['total']], 'id = ?', [$tenant['property_id']]);
            }

            // Create or update termination record
            $existingTermination = $db->fetchOne(
                "SELECT id FROM tenancy_terminations WHERE tenant_id = ? AND status = 'pending'",
                [$tenantId]
            );

            if ($existingTermination) {
                $db->update('tenancy_terminations', [
                    'status' => 'completed',
                    'reason' => $reason,
                    'effective_date' => $effectiveDate,
                ], 'id = ?', [$existingTermination['id']]);
            } else {
                $db->insert('tenancy_terminations', [
                    'owner_id' => $ownerId,
                    'tenant_id' => $tenantId,
                    'property_id' => $tenant['property_id'],
                    'house_id' => $tenant['house_id'],
                    'initiated_by' => $role,
                    'initiated_by_user_id' => $role === 'owner' ? $ownerId : Router::getAuthActorId(),
                    'reason' => $reason,
                    'effective_date' => $effectiveDate,
                    'status' => 'completed',
                ]);
            }

            $db->commit();

            // Send termination notice email to tenant
            $emailSent = false;
            try {
                if (!empty($tenant['email'])) {
                    $emailService = new EmailService();
                    $emailSent = $emailService->sendTemplate('Termination Notice', $ownerId, $tenant['email'], $tenant['name'], [
                        'tenant_name' => $tenant['name'],
                        'property' => $tenant['property_name'] ?? 'N/A',
                        'house' => $tenant['house_unit'] ?? 'N/A',
                        'date' => $effectiveDate,
                        'reason' => $reason ?: 'Not provided',
                        'owner_name' => $tenant['owner_name'] ?? 'Property Manager',
                    ]);
                }
            } catch (\Throwable $e) {
                error_log('Failed to send termination notice email: ' . $e->getMessage());
            }

            Router::jsonResponse([
                'message' => 'Tenancy terminated successfully' . ($emailSent ? ' & notice email sent' : ''),
                'email_sent' => $emailSent,
                'status' => 'terminated'
            ]);

        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollback();
            Router::jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/tenancy-terminations
     * List termination records for filtering/audit
     */
    public function listTerminations(): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $sql = "SELECT tt.*, t.name as tenant_name, t.email as tenant_email, 
                       p.name as property_name, h.unit as house_unit
                FROM tenancy_terminations tt
                LEFT JOIN tenants t ON tt.tenant_id = t.id
                LEFT JOIN properties p ON tt.property_id = p.id
                LEFT JOIN houses h ON tt.house_id = h.id
                WHERE tt.owner_id = ?";
        $params = [$ownerId];

        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (empty($propertyIds)) {
                Router::jsonResponse(['terminations' => []]);
            }
            $sql .= " AND tt.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $params = array_merge($params, $propertyIds);
        }

        $sql .= " ORDER BY tt.created_at DESC";
        $terminations = $db->fetchAll($sql, $params);

        Router::jsonResponse(['terminations' => $terminations]);
    }

    /**
     * POST /api/tenants/{id}/vacate - Legacy: Move tenant out (vacate house)
     * Clears house occupancy and updates related records
     */
    public function vacate(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $tenantId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        // Get tenant with house and property details
        $tenant = $db->fetchOne(
            "SELECT t.id, t.name, t.house_id, t.property_id, h.unit as house_unit, h.rent, p.name as property_name
             FROM tenants t
             LEFT JOIN houses h ON t.house_id = h.id
             LEFT JOIN properties p ON t.property_id = p.id
             WHERE t.id = ? AND t.owner_id = ?",
            [$tenantId, $ownerId]
        );
        if (!$tenant) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }

        // Only owner or the tenant themselves can vacate
        if ($role !== 'owner') {
            if ($role !== 'tenant' || Router::getAuthTenantId() !== $tenantId) {
                Router::jsonResponse(['error' => 'Not authorized'], 403);
            }
        }

        if (empty($tenant['house_id'])) {
            Router::jsonResponse(['error' => 'Tenant is not currently occupying a house'], 400);
        }

        $data = Router::getRequestBody();
        $reason = trim((string) ($data['reason'] ?? ''));
        $effectiveDate = trim((string) ($data['effective_date'] ?? date('Y-m-d')));

        try {
            $db->beginTransaction();

            // Release the house
            $db->update('houses', [
                'status' => 'vacant',
                'tenant_id' => null,
            ], 'id = ? AND owner_id = ?', [$tenant['house_id'], $ownerId]);

            // Clear tenant house and property references and mark as terminated
            $db->update('tenants', [
                'house_id' => null,
                'property_id' => null,
                'lease_end' => $effectiveDate,
                'status' => 'terminated',
            ], 'id = ? AND owner_id = ?', [$tenantId, $ownerId]);

            // Update property occupied count
            if ($tenant['property_id']) {
                $occupied = $db->fetchOne(
                    "SELECT COUNT(*) as total FROM houses WHERE property_id = ? AND status = 'occupied'",
                    [$tenant['property_id']]
                );
                $db->update('properties', ['occupied' => $occupied['total']], 'id = ?', [$tenant['property_id']]);
            }

            // Create audit record if not existing
            $existingTermination = $db->fetchOne(
                "SELECT id FROM tenancy_terminations WHERE tenant_id = ? AND status = 'pending'",
                [$tenantId]
            );
            if (!$existingTermination) {
                $db->insert('tenancy_terminations', [
                    'owner_id' => $ownerId,
                    'tenant_id' => $tenantId,
                    'property_id' => $tenant['property_id'],
                    'house_id' => $tenant['house_id'],
                    'initiated_by' => $role,
                    'initiated_by_user_id' => $role === 'owner' ? $ownerId : Router::getAuthActorId(),
                    'reason' => $reason,
                    'effective_date' => $effectiveDate,
                    'status' => 'completed',
                ]);
            } else {
                $db->update('tenancy_terminations', [
                    'status' => 'completed',
                    'effective_date' => $effectiveDate,
                    'reason' => $reason ?: $existingTermination['reason'],
                ], 'id = ?', [$existingTermination['id']]);
            }

            $db->commit();

            // Send vacate confirmation email
            $emailSent = false;
            try {
                $emailService = new EmailService();
                $emailSent = $emailService->sendTenantVacate(
                    $ownerId,
                    $tenantId,
                    $tenant['property_name'] ?? 'N/A',
                    $tenant['house_unit'] ?? 'N/A'
                );
            } catch (\Throwable $e) {
                error_log('Failed to send vacate email: ' . $e->getMessage());
            }

            Router::jsonResponse([
                'message' => 'Tenant vacated successfully' . ($emailSent ? ' & confirmation email sent' : ''),
                'email_sent' => $emailSent
            ]);
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollback();
            Router::jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/tenants/property-contact - Owner & caretaker emergency contact for tenant's property
     */
    public function propertyContact(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        if ($role !== 'tenant') {
            Router::jsonResponse(['error' => 'Only tenants can access this'], 403);
        }

        $tenantId = Router::getAuthTenantId();
        if (!$tenantId) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }

        $tenant = $db->fetchOne(
            "SELECT property_id FROM tenants WHERE id = ? AND owner_id = ?",
            [$tenantId, $ownerId]
        );

        if (!$tenant || !$tenant['property_id']) {
            Router::jsonResponse(['contacts' => null], 200);
        }

        $propertyId = (int) $tenant['property_id'];

        // Get owner contact info
        $owner = $db->fetchOne(
            "SELECT name, email, phone FROM owners WHERE id = ?",
            [$ownerId]
        );

        // Get caretaker assigned to this property
        $caretaker = $db->fetchOne(
            "SELECT name, email, phone FROM caretakers 
             WHERE owner_id = ? AND assigned_properties LIKE ?",
            [$ownerId, '%' . $propertyId . '%']
        );

        Router::jsonResponse([
            'contacts' => [
                'owner' => $owner ? [
                    'name'  => $owner['name'],
                    'email' => $owner['email'],
                    'phone' => $owner['phone'] ?? '',
                ] : null,
                'caretaker' => $caretaker ? [
                    'name'  => $caretaker['name'],
                    'email' => $caretaker['email'],
                    'phone' => $caretaker['phone'] ?? '',
                ] : null,
            ]
        ]);
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
<?php
/**
 * Complaint Controller - Two-way communication
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Services\EmailService;

class ComplaintController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';
        $direction = $_GET['direction'] ?? 'all'; // 'received', 'sent', 'all'

        $sql = "SELECT c.*, t.name as tenant_name, t.phone as tenant_phone, t.email as tenant_email,
                       h.unit, p.name as property_name,
                       o.name as owner_name, ct.name as caretaker_name
                FROM complaints c
                LEFT JOIN tenants t ON c.tenant_id = t.id
                LEFT JOIN houses h ON c.house_id = h.id
                LEFT JOIN properties p ON h.property_id = p.id
                LEFT JOIN owners o ON c.owner_id = o.id
                LEFT JOIN caretakers ct ON c.owner_id = ct.owner_id
                WHERE c.owner_id = ?";
        $params = [$ownerId];

        if ($role === 'tenant') {
            try {
                $actorId = Router::getAuthTenantId();
            } catch (\Throwable $e) {
                Router::jsonResponse(['complaints' => []]);
            }
            if (!empty($actorId)) {
                $sql .= " AND (c.tenant_id = ? OR c.recipient_ids LIKE CONCAT('%', ?, '%'))";
                $params[] = $actorId;
                $params[] = (string)$actorId;
            } else {
                $sql .= " AND 0=1";
            }
        } elseif ($role === 'caretaker') {
            // Caretakers see complaints for their assigned properties
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) {
                Router::jsonResponse(['complaints' => []]);
            }
            $sql .= " AND (h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ") OR c.sender_role = 'caretaker')";
            $params = array_merge($params, $propertyIds);
        } else {
            // Owner sees all, with optional direction filter
            if ($direction === 'sent') {
                $sql .= " AND c.sender_role IN ('owner', 'caretaker')";
            } elseif ($direction === 'received') {
                $sql .= " AND c.sender_role = 'tenant'";
            }
        }

        if ($status && in_array($status, ['open', 'in-progress', 'resolved'])) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $complaints = $db->fetchAll($sql, $params);
        // Deduplicate by complaint id to avoid LEFT JOIN artifacts
        $seen = [];
        $complaints = array_values(array_filter($complaints, function($c) use (&$seen) {
            $id = (int)($c['id'] ?? 0);
            if ($id === 0) return false;
            if (isset($seen[$id])) return false;
            $seen[$id] = true;
            return true;
        }));
        
        // For tenants, mark which ones are unread
        if ($role === 'tenant') {
            $actorId = Router::getAuthTenantId();
            foreach ($complaints as &$c) {
                $readBy = json_decode($c['read_by'] ?? '[]', true);
                $c['is_read'] = in_array($actorId, $readBy);
                $c['is_unread'] = !$c['is_read'];
            }
        }

        Router::jsonResponse(['complaints' => $complaints]);
    }

    public function store(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        if (empty($data['title'])) {
            Router::jsonResponse(['error' => 'Title is required'], 400);
        }
        if (empty($data['description'])) {
            Router::jsonResponse(['error' => 'Description is required'], 400);
        }

        // Determine recipient configuration
        $recipientType = $data['recipient_type'] ?? 'individual';
        $recipientIds = null;
        $targetTenantId = null;
        $targetHouseId = null;
        $targetPropertyId = null;

        if ($role === 'tenant') {
            // Tenant must be sending to owner/caretaker
            $tenantId = Router::getAuthTenantId();
            $tenant = $db->fetchOne("SELECT id, house_id, property_id FROM tenants WHERE id = ? AND owner_id = ?", [$tenantId, $ownerId]);
            if (!$tenant) {
                Router::jsonResponse(['error' => 'Tenant not found'], 404);
            }
            $targetTenantId = $tenantId;
            $targetHouseId = $tenant['house_id'];
            $targetPropertyId = $tenant['property_id'];
            $senderRole = 'tenant';
            $recipientType = 'individual';
            $recipientIds = json_encode([$ownerId]); // owner receives this
        } else {
            // Owner or caretaker sending notice/complaint
            $senderRole = $role;
            $recipientType = $data['recipient_type'] ?? 'individual';
            
            if ($recipientType === 'individual') {
                if (empty($data['tenant_id'])) {
                    Router::jsonResponse(['error' => 'Tenant ID is required when sending to an individual tenant'], 400);
                }
                $tenantId = (int)$data['tenant_id'];
                $tenant = $db->fetchOne("SELECT id, house_id, property_id FROM tenants WHERE id = ? AND owner_id = ?", [$tenantId, $ownerId]);
                if (!$tenant) {
                    Router::jsonResponse(['error' => 'Tenant not found or does not belong to you'], 404);
                }
                // Caretaker authorization check
                if ($role === 'caretaker') {
                    $propertyIds = Router::getCaretakerPropertyIds($db);
                    if (!in_array($tenant['property_id'], $propertyIds)) {
                        Router::jsonResponse(['error' => 'Not authorized to contact this tenant'], 403);
                    }
                }
                $targetTenantId = $tenantId;
                $targetHouseId = $tenant['house_id'];
                $targetPropertyId = $tenant['property_id'];
                $recipientIds = json_encode([$tenantId]);
            } elseif ($recipientType === 'property') {
                if (empty($data['property_id'])) {
                    Router::jsonResponse(['error' => 'Property ID is required for property-wide notices'], 400);
                }
                $propertyId = (int)$data['property_id'];
                $property = $db->fetchOne("SELECT id FROM properties WHERE id = ? AND owner_id = ?", [$propertyId, $ownerId]);
                if (!$property) {
                    Router::jsonResponse(['error' => 'Property not found'], 404);
                }
                if ($role === 'caretaker') {
                    $propertyIds = Router::getCaretakerPropertyIds($db);
                    if (!in_array($propertyId, $propertyIds)) {
                        Router::jsonResponse(['error' => 'Not authorized for this property'], 403);
                    }
                }
                $targetPropertyId = $propertyId;
                // Get all tenant IDs in this property
                $tenants = $db->fetchAll("SELECT id FROM tenants WHERE property_id = ? AND owner_id = ? AND status = 'active'", [$propertyId, $ownerId]);
                $recipientIds = json_encode(array_column($tenants, 'id'));
            } elseif ($recipientType === 'all') {
                // All managed tenants
                if ($role === 'caretaker') {
                    $propertyIds = Router::getCaretakerPropertyIds($db);
                    if (!$propertyIds) {
                        Router::jsonResponse(['error' => 'No assigned properties'], 400);
                    }
                    $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                    $tenants = $db->fetchAll("SELECT id, property_id, house_id FROM tenants WHERE property_id IN ($placeholders) AND owner_id = ? AND status = 'active'", array_merge($propertyIds, [$ownerId]));
                } else {
                    $tenants = $db->fetchAll("SELECT id, property_id, house_id FROM tenants WHERE owner_id = ? AND status = 'active'", [$ownerId]);
                }
                if (!$tenants) {
                    Router::jsonResponse(['error' => 'No active tenants found'], 400);
                }
                $recipientIds = json_encode(array_column($tenants, 'id'));
                // Use first tenant's house/property for the complaint record
                if ($tenants[0]) {
                    $targetHouseId = $tenants[0]['house_id'];
                    $targetPropertyId = $tenants[0]['property_id'];
                }
            }
        }

        $timeline = json_encode([
            ['date' => date('Y-m-d H:i:s'), 'status' => ucfirst($senderRole) . ' Sent', 'note' => ucfirst($senderRole) . ' created this ' . ($recipientType === 'all' ? 'announcement' : ($recipientType === 'property' ? 'property notice' : 'message'))]
        ]);

        $complaintId = $db->insert('complaints', [
            'owner_id'       => $ownerId,
            'tenant_id'      => $targetTenantId,
            'house_id'       => $targetHouseId,
            'property_id'    => $targetPropertyId,
            'title'          => $data['title'],
            'category'       => $data['category'] ?? 'Notice',
            'priority'       => $data['priority'] ?? 'medium',
            'status'         => 'open',
            'date'           => date('Y-m-d'),
            'description'    => $data['description'],
            'sender_role'    => $senderRole,
            'recipient_type' => $recipientType,
            'recipient_ids'  => $recipientIds,
            'timeline'       => $timeline,
            'comments'       => '[]',
        ]);

        $complaint = $db->fetchOne("SELECT * FROM complaints WHERE id = ?", [$complaintId]);

        // Send email notifications to recipient tenants
        $emailSent = false;
        try {
            $recipientTenantIds = json_decode($recipientIds, true) ?: [];
            $actorName = '';
            if ($role === 'owner') {
                $owner = $db->fetchOne("SELECT name FROM owners WHERE id = ?", [$ownerId]);
                $actorName = $owner['name'] ?? 'Property Owner';
            } else {
                $caretaker = $db->fetchOne("SELECT name FROM caretakers WHERE id = ?", [$ownerId]);
                $actorName = $caretaker['name'] ?? 'Property Manager';
            }

            foreach ($recipientTenantIds as $tid) {
                $tenant = $db->fetchOne("SELECT name, email FROM tenants WHERE id = ?", [$tid]);
                if ($tenant && !empty($tenant['email'])) {
                    $emailService = new EmailService();
                    $emailSent = $emailService->sendTemplate('Management Notice', $ownerId, $tenant['email'], $tenant['name'], [
                        'tenant_name' => $tenant['name'],
                        'property' => $targetPropertyId ? ($db->fetchOne("SELECT name FROM properties WHERE id = ?", [$targetPropertyId])['name'] ?? 'N/A') : 'N/A',
                        'house' => $targetHouseId ? ($db->fetchOne("SELECT unit FROM houses WHERE id = ?", [$targetHouseId])['unit'] ?? 'N/A') : 'N/A',
                        'date' => date('Y-m-d'),
                        'category' => $data['category'] ?? 'Notice',
                        'priority' => $data['priority'] ?? 'medium',
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'sender_name' => $actorName,
                    ]) || $emailSent;
                }
            }
        } catch (\Exception $e) {
            error_log('Failed to send notice email: ' . $e->getMessage());
        }

        $msg = $role === 'tenant' ? 'Complaint submitted' : 'Notice sent';
        Router::jsonResponse(['message' => $msg, 'complaint' => $complaint, 'email_sent' => $emailSent], 201);
    }

    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $complaintId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $sql = "SELECT c.*, t.name as tenant_name, t.phone as tenant_phone, h.unit, p.name as property_name,
                       o.name as owner_name
                FROM complaints c
                LEFT JOIN tenants t ON c.tenant_id = t.id
                LEFT JOIN houses h ON c.house_id = h.id
                LEFT JOIN properties p ON h.property_id = p.id
                LEFT JOIN owners o ON c.owner_id = o.id
                WHERE c.id = ? AND c.owner_id = ?";
        $queryParams = [$complaintId, $ownerId];

        if ($role === 'tenant') {
            $actorId = Router::getAuthTenantId();
            $sql .= " AND (c.tenant_id = ? OR c.recipient_ids LIKE CONCAT('%', ?, '%'))";
            $queryParams[] = $actorId;
            $queryParams[] = (string)$actorId;
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            $sql .= " AND (h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ") OR c.sender_role = 'caretaker')";
            $queryParams = array_merge($queryParams, $propertyIds);
        }

        $complaint = $db->fetchOne($sql, $queryParams);
        if (!$complaint) {
            Router::jsonResponse(['error' => 'Complaint not found'], 404);
        }

        // Mark as read if tenant
        if ($role === 'tenant') {
            $actorId = Router::getAuthTenantId();
            $readBy = json_decode($complaint['read_by'] ?? '[]', true);
            if (!in_array($actorId, $readBy)) {
                $readBy[] = $actorId;
                $db->update('complaints', [
                    'read_by' => json_encode($readBy),
                    'read_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$complaintId]);
            }
        }

        $complaint['timeline'] = json_decode($complaint['timeline'] ?? '[]', true);
        $complaint['comments'] = json_decode($complaint['comments'] ?? '[]', true);

        Router::jsonResponse(['complaint' => $complaint]);
    }

    public function update(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $complaintId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $existing = $db->fetchOne("SELECT * FROM complaints WHERE id = ? AND owner_id = ?", [$complaintId, $ownerId]);
        if (!$existing) {
            Router::jsonResponse(['error' => 'Complaint not found'], 404);
        }

        // Authorization check for caretaker
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            $allowed = $db->fetchOne(
                "SELECT c.id FROM complaints c LEFT JOIN houses h ON c.house_id = h.id WHERE c.id = ? AND (h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ") OR c.sender_role = 'caretaker')",
                array_merge([$complaintId], $propertyIds)
            );
            if (!$allowed) {
                Router::jsonResponse(['error' => 'Not authorized'], 403);
            }
        }

        $updateData = [];
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
            $timeline = json_decode($existing['timeline'] ?? '[]', true);
            $timeline[] = [
                'date'   => date('Y-m-d H:i:s'),
                'status' => ucfirst(str_replace('-', ' ', $data['status'])),
                'note'   => $data['note'] ?? 'Status updated',
            ];
            $updateData['timeline'] = json_encode($timeline);
        }

        if (isset($data['comments'])) {
            $comments = json_decode($existing['comments'] ?? '[]', true);
            $commentText = $data['comments'];
            $commentUser = $data['comment_user'] ?? ($role === 'tenant' ? 'Tenant' : ($role === 'caretaker' ? 'Caretaker' : 'Owner'));
            $comments[] = [
                'user' => $commentUser,
                'role' => $role,
                'date' => date('Y-m-d H:i:s'),
                'text' => $commentText,
            ];
            $updateData['comments'] = json_encode($comments);
        }

        if (!empty($updateData)) {
            $db->update('complaints', $updateData, 'id = ?', [$complaintId]);
        }

        $complaint = $db->fetchOne("SELECT * FROM complaints WHERE id = ?", [$complaintId]);
        $complaint['timeline'] = json_decode($complaint['timeline'] ?? '[]', true);
        $complaint['comments'] = json_decode($complaint['comments'] ?? '[]', true);

        // Send reply email
        $replyEmailSent = false;
        if (!empty($data['comments'])) {
            try {
                $recipientIds = json_decode($complaint['recipient_ids'] ?? $complaint['tenant_id'], true);
                $recipientTenantIds = is_array($recipientIds) ? $recipientIds : [$recipientIds];
                $actorName = $role === 'owner' ? ($db->fetchOne("SELECT name FROM owners WHERE id = ?", [$ownerId])['name'] ?? 'Owner') : ($db->fetchOne("SELECT name FROM caretakers WHERE id = ?", [$ownerId])['name'] ?? 'Manager');

                foreach ($recipientTenantIds as $tid) {
                    $tenant = $db->fetchOne("SELECT name, email FROM tenants WHERE id = ?", [$tid]);
                    if ($tenant && !empty($tenant['email'])) {
                        $emailService = new EmailService();
                        $replyEmailSent = $emailService->sendComplaintUpdate($ownerId, $tenant, $complaint, $data['comments']);
                    }
                }
            } catch (\Exception $e) {
                error_log('Failed to send complaint reply email: ' . $e->getMessage());
            }
        }

        Router::jsonResponse(['message' => 'Complaint updated', 'complaint' => $complaint, 'email_sent' => $replyEmailSent]);
    }

    public function destroy(array $params): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $complaintId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $existing = $db->fetchOne("SELECT id FROM complaints WHERE id = ? AND owner_id = ?", [$complaintId, $ownerId]);
        if (!$existing) {
            Router::jsonResponse(['error' => 'Complaint not found'], 404);
        }

        $db->delete('complaints', 'id = ?', [$complaintId]);
        Router::jsonResponse(['message' => 'Complaint deleted']);
    }
}
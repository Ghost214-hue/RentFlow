<?php
/**
 * Complaint Controller
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

        $sql = "SELECT c.*, t.name as tenant_name, t.phone as tenant_phone, h.unit, p.name as property_name
                FROM complaints c
                LEFT JOIN tenants t ON c.tenant_id = t.id
                LEFT JOIN houses h ON c.house_id = h.id
                LEFT JOIN properties p ON h.property_id = p.id
                WHERE c.owner_id = ?";
        $params = [$ownerId];

        if ($role === 'tenant') {
            $sql .= " AND c.tenant_id = ?";
            $params[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['complaints' => []]);
            $sql .= " AND h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $params = array_merge($params, $propertyIds);
        }

        if ($status && in_array($status, ['open', 'in-progress', 'resolved'])) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $complaints = $db->fetchAll($sql, $params);
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

        if ($role === 'tenant') {
            $data['tenant_id'] = Router::getAuthTenantId();
        } elseif (empty($data['tenant_id'])) {
            Router::jsonResponse(['error' => 'Tenant ID is required'], 400);
        }

        // Verify tenant belongs to owner
        $tenant = $db->fetchOne(
            "SELECT id, house_id FROM tenants WHERE id = ? AND owner_id = ?",
            [$data['tenant_id'], $ownerId]
        );
        if (!$tenant) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
        }
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            $allowed = $db->fetchOne(
                "SELECT h.id FROM houses h WHERE h.id = ? AND h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")",
                array_merge([$tenant['house_id']], $propertyIds)
            );
            if (!$allowed) {
                Router::jsonResponse(['error' => 'Tenant is outside your assigned properties'], 403);
            }
        }

        $timeline = json_encode([
            ['date' => date('Y-m-d'), 'status' => 'Submitted', 'note' => 'Complaint logged']
        ]);

        $complaintId = $db->insert('complaints', [
            'owner_id'    => $ownerId,
            'tenant_id'   => (int) $data['tenant_id'],
            'house_id'    => $tenant['house_id'],
            'title'       => $data['title'],
            'category'    => $data['category'] ?? 'Other',
            'priority'    => $data['priority'] ?? 'medium',
            'status'      => 'open',
            'date'        => $data['date'] ?? date('Y-m-d'),
            'description' => $data['description'] ?? '',
            'timeline'    => $timeline,
            'comments'    => '[]',
        ]);

        $complaint = $db->fetchOne("SELECT * FROM complaints WHERE id = ?", [$complaintId]);
        
        // Send complaint confirmation email to tenant
        $emailSent = false;
        try {
            $tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ?", [(int) $data['tenant_id']]);
            if ($tenant && $tenant['email']) {
                $emailService = new EmailService();
                $emailSent = $emailService->sendComplaintUpdate($ownerId, $tenant, $complaint);
            }
        } catch (\Exception $e) {
            error_log('Failed to send complaint confirmation email: ' . $e->getMessage());
        }
        
        $emailMsg = $emailSent ? '& notification email sent' : '& notification email sent';
        Router::jsonResponse(['message' => "Complaint submitted {$emailMsg}", 'complaint' => $complaint, 'email_sent' => $emailSent], 201);
    }

    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $complaintId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $sql = "SELECT c.*, t.name as tenant_name, t.phone as tenant_phone, h.unit, p.name as property_name
             FROM complaints c
             LEFT JOIN tenants t ON c.tenant_id = t.id
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE c.id = ? AND c.owner_id = ?";
        $queryParams = [$complaintId, $ownerId];
        if ($role === 'tenant') {
            $sql .= " AND c.tenant_id = ?";
            $queryParams[] = Router::getAuthTenantId();
        } elseif ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            if (!$propertyIds) Router::jsonResponse(['error' => 'Complaint not found'], 404);
            $sql .= " AND h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")";
            $queryParams = array_merge($queryParams, $propertyIds);
        }
        $complaint = $db->fetchOne($sql, $queryParams);

        if (!$complaint) {
            Router::jsonResponse(['error' => 'Complaint not found'], 404);
        }

        // Decode JSON fields
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

        if ($role === 'tenant') {
            Router::jsonResponse(['error' => 'Tenants can submit complaints but cannot reply or update them'], 403);
        }

        $existing = $db->fetchOne("SELECT * FROM complaints WHERE id = ? AND owner_id = ?", [$complaintId, $ownerId]);
        if (!$existing) {
            Router::jsonResponse(['error' => 'Complaint not found'], 404);
        }
        if ($role === 'caretaker') {
            $propertyIds = Router::getCaretakerPropertyIds($db);
            $allowed = $db->fetchOne(
                "SELECT c.id FROM complaints c LEFT JOIN houses h ON c.house_id = h.id WHERE c.id = ? AND h.property_id IN (" . implode(',', array_fill(0, count($propertyIds), '?')) . ")",
                array_merge([$complaintId], $propertyIds)
            );
            if (!$allowed) {
                Router::jsonResponse(['error' => 'Complaint is outside your assigned properties'], 403);
            }
        }

        $updateData = [];
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];

            // Add timeline entry
            $timeline = json_decode($existing['timeline'] ?? '[]', true);
            $timeline[] = [
                'date'   => date('Y-m-d'),
                'status' => ucfirst(str_replace('-', ' ', $data['status'])),
                'note'   => $data['note'] ?? 'Status updated',
            ];
            $updateData['timeline'] = json_encode($timeline);
        }

        if (isset($data['comments'])) {
            $comments = json_decode($existing['comments'] ?? '[]', true);
            $comments[] = [
                'user' => $data['comment_user'] ?? ucfirst($role),
                'role' => $role,
                'date' => date('Y-m-d'),
                'text' => $data['comments'],
            ];
            $updateData['comments'] = json_encode($comments);
        }

        if (!empty($updateData)) {
            $db->update('complaints', $updateData, 'id = ?', [$complaintId]);
        }

        $complaint = $db->fetchOne("SELECT * FROM complaints WHERE id = ?", [$complaintId]);
        $complaint['timeline'] = json_decode($complaint['timeline'] ?? '[]', true);
        $complaint['comments'] = json_decode($complaint['comments'] ?? '[]', true);
        
        // Send complaint update email to tenant if there's a reply
        $replyEmailSent = false;
        if (!empty($data['comments'])) {
            try {
                $tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ?", [$complaint['tenant_id']]);
                if ($tenant && $tenant['email']) {
                    $emailService = new EmailService();
                    $replyEmailSent = $emailService->sendComplaintUpdate($ownerId, $tenant, $complaint, $data['comments']);
                }
            } catch (\Exception $e) {
                error_log('Failed to send complaint update email: ' . $e->getMessage());
            }
        }

        $msg = 'Complaint updated';
        if ($replyEmailSent) {
            $msg .= ' & reply email sent';
        }
        Router::jsonResponse(['message' => $msg, 'complaint' => $complaint, 'email_sent' => $replyEmailSent]);
    }
}

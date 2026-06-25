<?php
/**
 * Complaint Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class ComplaintController
{
    public function index(): void
    {
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';

        $sql = "SELECT c.*, t.name as tenant_name, t.phone as tenant_phone, h.unit, p.name as property_name
                FROM complaints c
                LEFT JOIN tenants t ON c.tenant_id = t.id
                LEFT JOIN houses h ON c.house_id = h.id
                LEFT JOIN properties p ON h.property_id = p.id
                WHERE c.owner_id = ?";
        $params = [$ownerId];

        if ($status && in_array($status, ['open', 'in-progress', 'resolved'])) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $complaints = $db->fetchAll($sql, $params);
        Router::jsonResponse(['complaints' => $complaints]);
    }

    public function store(): void
    {
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        if (empty($data['title']) || empty($data['tenant_id'])) {
            Router::jsonResponse(['error' => 'Title and tenant ID are required'], 400);
        }

        // Verify tenant belongs to owner
        $tenant = $db->fetchOne(
            "SELECT id, house_id FROM tenants WHERE id = ? AND owner_id = ?",
            [$data['tenant_id'], $ownerId]
        );
        if (!$tenant) {
            Router::jsonResponse(['error' => 'Tenant not found'], 404);
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
        Router::jsonResponse(['message' => 'Complaint submitted', 'complaint' => $complaint], 201);
    }

    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $complaintId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $complaint = $db->fetchOne(
            "SELECT c.*, t.name as tenant_name, t.phone as tenant_phone, h.unit, p.name as property_name
             FROM complaints c
             LEFT JOIN tenants t ON c.tenant_id = t.id
             LEFT JOIN houses h ON c.house_id = h.id
             LEFT JOIN properties p ON h.property_id = p.id
             WHERE c.id = ? AND c.owner_id = ?",
            [$complaintId, $ownerId]
        );

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
        $complaintId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $existing = $db->fetchOne(
            "SELECT * FROM complaints WHERE id = ? AND owner_id = ?",
            [$complaintId, $ownerId]
        );
        if (!$existing) {
            Router::jsonResponse(['error' => 'Complaint not found'], 404);
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
                'user' => $data['comment_user'] ?? 'System',
                'role' => $data['comment_role'] ?? 'owner',
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

        Router::jsonResponse(['message' => 'Complaint updated', 'complaint' => $complaint]);
    }
}
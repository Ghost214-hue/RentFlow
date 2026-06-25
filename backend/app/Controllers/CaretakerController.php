<?php
/**
 * Caretaker Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class CaretakerController
{
    public function index(): void
    {
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        $caretakers = $db->fetchAll(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM properties WHERE FIND_IN_SET(id, c.assigned_properties)) as property_count
             FROM caretakers c 
             WHERE c.owner_id = ? 
             ORDER BY c.name ASC",
            [$ownerId]
        );

        Router::jsonResponse(['caretakers' => $caretakers]);
    }

    public function store(): void
    {
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            Router::jsonResponse(['error' => 'Name, email, and password are required'], 400);
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $caretakerId = $db->insert('caretakers', [
            'owner_id'             => $ownerId,
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? null,
            'password'             => $hashedPassword,
            'avatar'               => 'CT',
            'assigned_properties'  => $data['assigned_properties'] ?? null,
        ]);

        $caretaker = $db->fetchOne("SELECT id, name, email, phone, avatar, assigned_properties FROM caretakers WHERE id = ?", [$caretakerId]);
        Router::jsonResponse(['message' => 'Caretaker added', 'caretaker' => $caretaker], 201);
    }

    public function update(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $caretakerId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $existing = $db->fetchOne("SELECT id FROM caretakers WHERE id = ? AND owner_id = ?", [$caretakerId, $ownerId]);
        if (!$existing) {
            Router::jsonResponse(['error' => 'Caretaker not found'], 404);
        }

        $updateData = [];
        foreach (['name', 'email', 'phone', 'assigned_properties'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (!empty($updateData)) {
            $db->update('caretakers', $updateData, 'id = ?', [$caretakerId]);
        }

        $caretaker = $db->fetchOne("SELECT id, name, email, phone, avatar, assigned_properties FROM caretakers WHERE id = ?", [$caretakerId]);
        Router::jsonResponse(['message' => 'Caretaker updated', 'caretaker' => $caretaker]);
    }

    public function destroy(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $caretakerId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $existing = $db->fetchOne("SELECT id FROM caretakers WHERE id = ? AND owner_id = ?", [$caretakerId, $ownerId]);
        if (!$existing) {
            Router::jsonResponse(['error' => 'Caretaker not found'], 404);
        }

        $db->delete('caretakers', 'id = ?', [$caretakerId]);
        Router::jsonResponse(['message' => 'Caretaker removed']);
    }
}
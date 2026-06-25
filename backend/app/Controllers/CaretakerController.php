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
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        $caretakers = $db->fetchAll(
            "SELECT c.id, c.owner_id, c.name, c.email, c.phone, c.id_number, c.avatar, c.assigned_properties, c.created_at, c.updated_at,
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
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $data['name'] = trim((string) ($data['name'] ?? ''));
        $data['email'] = trim((string) ($data['email'] ?? ''));
        $data['id_number'] = trim((string) ($data['id_number'] ?? ''));

        if ($data['name'] === '' || $data['email'] === '' || $data['id_number'] === '') {
            Router::jsonResponse(['error' => 'Name, email, and national ID are required'], 400);
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Router::jsonResponse(['error' => 'Invalid email format'], 400);
        }

        $existing = $db->fetchOne("SELECT id FROM caretakers WHERE email = ?", [$data['email']]);
        if ($existing) {
            Router::jsonResponse(['error' => 'A caretaker with this email already exists'], 409);
        }

        $propertyIds = array_values(array_filter(array_map('intval', explode(',', (string) ($data['assigned_properties'] ?? '')))));
        foreach ($propertyIds as $propertyId) {
            $property = $db->fetchOne("SELECT id FROM properties WHERE id = ? AND owner_id = ?", [$propertyId, $ownerId]);
            if (!$property) {
                Router::jsonResponse(['error' => 'One or more assigned properties were not found'], 400);
            }
        }

        $hashedPassword = password_hash($data['password'] ?? $data['id_number'], PASSWORD_BCRYPT);

        $caretakerId = $db->insert('caretakers', [
            'owner_id'             => $ownerId,
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? null,
            'id_number'            => $data['id_number'],
            'password'             => $hashedPassword,
            'avatar'               => 'CT',
            'assigned_properties'  => $propertyIds ? implode(',', $propertyIds) : null,
        ]);

        $caretaker = $db->fetchOne("SELECT id, name, email, phone, id_number, avatar, assigned_properties FROM caretakers WHERE id = ?", [$caretakerId]);
        Router::jsonResponse(['message' => 'Caretaker added', 'caretaker' => $caretaker], 201);
    }

    public function update(array $params): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $caretakerId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $existing = $db->fetchOne("SELECT id FROM caretakers WHERE id = ? AND owner_id = ?", [$caretakerId, $ownerId]);
        if (!$existing) {
            Router::jsonResponse(['error' => 'Caretaker not found'], 404);
        }

        $updateData = [];
        foreach (['name', 'email', 'phone', 'id_number', 'assigned_properties'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        if (!empty($updateData['email']) && !filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
            Router::jsonResponse(['error' => 'Invalid email format'], 400);
        }
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } elseif (!empty($data['id_number']) && !empty($data['reset_password_to_id'])) {
            $updateData['password'] = password_hash($data['id_number'], PASSWORD_BCRYPT);
        }

        if (!empty($updateData)) {
            $db->update('caretakers', $updateData, 'id = ?', [$caretakerId]);
        }

        $caretaker = $db->fetchOne("SELECT id, name, email, phone, id_number, avatar, assigned_properties FROM caretakers WHERE id = ?", [$caretakerId]);
        Router::jsonResponse(['message' => 'Caretaker updated', 'caretaker' => $caretaker]);
    }

    public function destroy(array $params): void
    {
        Router::requireOwner();
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

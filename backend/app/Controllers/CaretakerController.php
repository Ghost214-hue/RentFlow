<?php
/**
 * Caretaker Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\Pagination;
use App\Services\EmailService;

class CaretakerController
{
    public function index(array $params = []): void
    {
        try {
            Router::requireOwner();
            $ownerId = Router::getAuthUserId();
            $db = Database::getInstance();
            $page = Pagination::fromRequest();

            // Get total
            $totalRow = $db->fetchOne("SELECT COUNT(*) as total FROM caretakers c WHERE c.owner_id = ?", [$ownerId]);
            $total = (int) ($totalRow['total'] ?? 0);

            $caretakers = $db->fetchAll(
                "SELECT c.id, c.owner_id, c.name, c.email, c.phone, c.id_number, c.avatar, c.assigned_properties, c.created_at, c.updated_at
             FROM caretakers c 
             WHERE c.owner_id = ? 
             ORDER BY c.name ASC LIMIT ?, ?",
                [$ownerId, $page['offset'], $page['limit']]
            );

            $propertyIds = [];
            foreach ($caretakers as $caretaker) {
                $ids = array_filter(array_map('intval', explode(',', (string) $caretaker['assigned_properties'])));
                $propertyIds = array_merge($propertyIds, $ids);
            }
            $propertyIds = array_unique(array_filter($propertyIds));

            $propertyMap = [];
            if (!empty($propertyIds)) {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $props = $db->fetchAll(
                    "SELECT id, name FROM properties WHERE owner_id = ? AND id IN ($placeholders)",
                    array_merge([$ownerId], $propertyIds)
                );
                foreach ($props as $prop) {
                    $propertyMap[(int) $prop['id']] = $prop['name'];
                }
            }

            foreach ($caretakers as &$caretaker) {
                $ids = array_filter(array_map('intval', explode(',', (string) $caretaker['assigned_properties'])));
                $names = [];
                foreach ($ids as $id) {
                    if (isset($propertyMap[$id])) {
                        $names[] = $propertyMap[$id];
                    }
                }
                $caretaker['assigned_property_names'] = $names;
                $caretaker['property_count'] = count($names);
            }
            unset($caretaker);

            Router::jsonResponse(['caretakers' => $caretakers, 'meta' => Pagination::meta($total, $page['page'], $page['per_page'])]);
        } catch (\Throwable $e) {
            error_log('CaretakerController@index error: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'Caretaker list failed'], 500);
        }
    }

    public function store(array $params = []): void
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

        $existing = $db->fetchOne(
            "SELECT id FROM caretakers WHERE owner_id = ? AND email = ?",
            [$ownerId, $data['email']]
        );
        if ($existing) {
            Router::jsonResponse(['error' => 'A caretaker with this email already exists for your account'], 409);
        }

        $propertyIds = array_values(array_filter(array_map('intval', explode(',', (string) ($data['assigned_properties'] ?? '')))));
        
        // Validate at least one property is assigned
        if (empty($propertyIds)) {
            Router::jsonResponse(['error' => 'At least one property must be assigned to the caretaker'], 400);
        }
        
        // Enforce one-caretaker-per-property rule
        foreach ($propertyIds as $propertyId) {
            $property = $db->fetchOne("SELECT id, name FROM properties WHERE id = ? AND owner_id = ?", [$propertyId, $ownerId]);
            if (!$property) {
                Router::jsonResponse(['error' => 'One or more assigned properties were not found'], 400);
            }
            
            // Check if another caretaker already manages this property
            $existingCaretaker = $db->fetchOne(
                "SELECT id, name FROM caretakers WHERE owner_id = ? AND assigned_properties LIKE ? AND id != ?",
                [$ownerId, '%' . $propertyId . '%', 0]
            );
            if ($existingCaretaker) {
                Router::jsonResponse([
                    'error' => "Property '{$property['name']}' is already assigned to caretaker {$existingCaretaker['name']}. Each property can have only one caretaker."
                ], 409);
            }
        }

        $hashedPassword = password_hash($data['password'] ?? $data['id_number'], PASSWORD_BCRYPT);

        $caretakerId = $db->insert('caretakers', [
            'owner_id'             => $ownerId,
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? null,
            'password'             => $hashedPassword,
            'avatar'               => 'CT',
            'assigned_properties'  => $propertyIds ? implode(',', $propertyIds) : null,
        ]);
        
        // Fetch created caretaker (include id_number for welcome email)
        $caretaker = $db->fetchOne("SELECT id, name, email, phone, id_number, avatar, assigned_properties FROM caretakers WHERE id = ?", [$caretakerId]);
        
        // Send welcome email to caretaker (non-blocking, fire-and-forget)
        $emailSent = false;
        try {
            $emailService = new EmailService();
            $emailSent = $emailService->sendCaretakerWelcome($ownerId, $caretaker);
            if (!$emailSent) {
                error_log("Caretaker welcome email not sent for {$caretaker['email']} - check email_logs table or templates");
            }
        } catch (\Throwable $e) {
            // Never let email failure break the API response
            error_log('Caretaker welcome email error: ' . $e->getMessage());
        }
        
        $emailMsg = $emailSent ? '& welcome email sent' : '& welcome email notification sent';
        Router::jsonResponse(['message' => "Caretaker added {$emailMsg}", 'caretaker' => $caretaker, 'email_sent' => $emailSent], 201);
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

        // Enforce one-caretaker-per-property on update too
        if (isset($updateData['assigned_properties'])) {
            $propertyIds = array_values(array_filter(array_map('intval', explode(',', (string) $updateData['assigned_properties']))));
            
            // Validate at least one property is assigned
            if (empty($propertyIds)) {
                Router::jsonResponse(['error' => 'At least one property must be assigned to the caretaker'], 400);
            }
            foreach ($propertyIds as $propertyId) {
                $property = $db->fetchOne("SELECT id, name FROM properties WHERE id = ? AND owner_id = ?", [$propertyId, $ownerId]);
                if (!$property) {
                    Router::jsonResponse(['error' => 'One or more assigned properties were not found'], 400);
                }
                
                $otherCaretaker = $db->fetchOne(
                    "SELECT id, name FROM caretakers WHERE owner_id = ? AND assigned_properties LIKE ? AND id != ?",
                    [$ownerId, '%' . $propertyId . '%', $caretakerId]
                );
                if ($otherCaretaker) {
                    Router::jsonResponse([
                        'error' => "Property '{$property['name']}' is already assigned to caretaker {$otherCaretaker['name']}. Each property can have only one caretaker."
                    ], 409);
                }
            }
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
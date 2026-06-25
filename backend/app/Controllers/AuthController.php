<?php
/**
 * Authentication Controller
 * Handles owner registration, login, and token management
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\JWT;
use App\Core\Router;

class AuthController
{
    /**
     * POST /api/auth/register
     * Register a new owner account
     */
    public function register(): void
    {
        $data = Router::getRequestBody();

        // Validate required fields
        $required = ['name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Router::jsonResponse(['error' => "Field '{$field}' is required"], 400);
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Router::jsonResponse(['error' => 'Invalid email format'], 400);
        }

        // Validate password strength
        if (strlen($data['password']) < 6) {
            Router::jsonResponse(['error' => 'Password must be at least 6 characters'], 400);
        }

        $db = Database::getInstance();

        // Check if email already exists
        $existing = $db->fetchOne(
            "SELECT id FROM owners WHERE email = ?",
            [$data['email']]
        );

        if ($existing) {
            Router::jsonResponse(['error' => 'An account with this email already exists'], 409);
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // Generate avatar initials
        $nameParts = explode(' ', trim($data['name']));
        $initials = '';
        foreach ($nameParts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper($part[0]);
            }
        }
        $initials = substr($initials, 0, 2);

        // Insert owner
        $ownerId = $db->insert('owners', [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $hashedPassword,
            'phone'    => $data['phone'] ?? null,
            'avatar'   => $initials ?: 'OW',
        ]);

        // Generate JWT token
        $token = JWT::encode([
            'owner_id' => $ownerId,
            'email'    => $data['email'],
            'role'     => 'owner',
            'name'     => $data['name'],
        ]);

        // Generate onboarding templates for this owner
        $this->createDefaultTemplates($db, $ownerId);

        Router::jsonResponse([
            'message' => 'Registration successful',
            'token'   => $token,
            'user'    => [
                'id'     => $ownerId,
                'name'   => $data['name'],
                'email'  => $data['email'],
                'phone'  => $data['phone'] ?? '',
                'avatar' => $initials ?: 'OW',
                'role'   => 'owner',
            ],
        ], 201);
    }

    /**
     * POST /api/auth/login
     * Authenticate owner and return JWT token
     */
    public function login(): void
    {
        $data = Router::getRequestBody();

        if (empty($data['email']) || empty($data['password'])) {
            Router::jsonResponse(['error' => 'Email and password are required'], 400);
        }

        $db = Database::getInstance();

        $owner = $db->fetchOne(
            "SELECT id, name, email, password, phone, avatar FROM owners WHERE email = ?",
            [$data['email']]
        );

        if ($owner && password_verify($data['password'], $owner['password'])) {
            // Update last login
            $db->update(
                'owners',
                ['last_login' => date('Y-m-d H:i:s')],
                'id = ?',
                [$owner['id']]
            );

            // Generate token
            $token = JWT::encode([
                'owner_id' => (int) $owner['id'],
                'actor_id' => (int) $owner['id'],
                'email'    => $owner['email'],
                'role'     => 'owner',
                'name'     => $owner['name'],
            ]);

            Router::jsonResponse([
                'message' => 'Login successful',
                'token'   => $token,
                'user'    => [
                    'id'        => (int) $owner['id'],
                    'name'      => $owner['name'],
                    'email'     => $owner['email'],
                    'phone'     => $owner['phone'] ?? '',
                    'avatar'    => $owner['avatar'] ?? 'OW',
                    'role'      => 'owner',
                    'lastLogin' => date('Y-m-d H:i:s'),
                ],
            ]);
        }

        $caretaker = $db->fetchOne(
            "SELECT id, owner_id, name, email, password, phone, avatar, assigned_properties FROM caretakers WHERE email = ?",
            [$data['email']]
        );
        if ($caretaker && password_verify($data['password'], $caretaker['password'])) {
            $token = JWT::encode([
                'owner_id'  => (int) $caretaker['owner_id'],
                'actor_id'  => (int) $caretaker['id'],
                'email'     => $caretaker['email'],
                'role'      => 'caretaker',
                'name'      => $caretaker['name'],
            ]);
            Router::jsonResponse([
                'message' => 'Login successful',
                'token'   => $token,
                'user'    => [
                    'id' => (int) $caretaker['id'],
                    'owner_id' => (int) $caretaker['owner_id'],
                    'name' => $caretaker['name'],
                    'email' => $caretaker['email'],
                    'phone' => $caretaker['phone'] ?? '',
                    'avatar' => $caretaker['avatar'] ?? 'CT',
                    'role' => 'caretaker',
                    'assigned_properties' => $caretaker['assigned_properties'] ?? '',
                ],
            ]);
        }

        $tenant = $db->fetchOne(
            "SELECT id, owner_id, name, email, password, phone FROM tenants WHERE email = ?",
            [$data['email']]
        );
        if ($tenant && !empty($tenant['password']) && password_verify($data['password'], $tenant['password'])) {
            $token = JWT::encode([
                'owner_id' => (int) $tenant['owner_id'],
                'actor_id' => (int) $tenant['id'],
                'tenant_id' => (int) $tenant['id'],
                'email'    => $tenant['email'],
                'role'     => 'tenant',
                'name'     => $tenant['name'],
            ]);
            Router::jsonResponse([
                'message' => 'Login successful',
                'token'   => $token,
                'user'    => [
                    'id' => (int) $tenant['id'],
                    'owner_id' => (int) $tenant['owner_id'],
                    'name' => $tenant['name'],
                    'email' => $tenant['email'],
                    'phone' => $tenant['phone'] ?? '',
                    'avatar' => 'TN',
                    'role' => 'tenant',
                ],
            ]);
        }

        Router::jsonResponse(['error' => 'Invalid email or password'], 401);
    }

    /**
     * GET /api/auth/me
     * Get current authenticated user profile
     */
    public function me(): void
    {
        $db = Database::getInstance();
        $role = Router::getAuthRole();
        $actorId = Router::getAuthActorId();
        $ownerId = Router::getAuthUserId();

        if ($role === 'caretaker') {
            $caretaker = $db->fetchOne(
                "SELECT id, owner_id, name, email, phone, avatar, assigned_properties, created_at FROM caretakers WHERE id = ? AND owner_id = ?",
                [$actorId, $ownerId]
            );
            if (!$caretaker) {
                Router::jsonResponse(['error' => 'User not found'], 404);
            }
            $caretaker['id'] = (int) $caretaker['id'];
            $caretaker['role'] = 'caretaker';
            Router::jsonResponse(['user' => $caretaker]);
        }

        if ($role === 'tenant') {
            $tenant = $db->fetchOne(
                "SELECT id, owner_id, name, email, phone, id_number, id_type, house_id, property_id, created_at FROM tenants WHERE id = ? AND owner_id = ?",
                [$actorId, $ownerId]
            );
            if (!$tenant) {
                Router::jsonResponse(['error' => 'User not found'], 404);
            }
            $tenant['id'] = (int) $tenant['id'];
            $tenant['role'] = 'tenant';
            Router::jsonResponse(['user' => $tenant]);
        }

        $owner = $db->fetchOne(
            "SELECT id, name, email, phone, avatar, last_login, created_at FROM owners WHERE id = ?",
            [$ownerId]
        );

        if (!$owner) {
            Router::jsonResponse(['error' => 'User not found'], 404);
        }

        $owner['id'] = (int) $owner['id'];

        Router::jsonResponse(['user' => $owner]);
    }

    /**
     * POST /api/auth/logout
     * Logout (client-side token removal - server just acknowledges)
     */
    public function logout(): void
    {
        Router::jsonResponse(['message' => 'Logged out successfully']);
    }

    /**
     * Create default communication templates for a new owner
     */
    private function createDefaultTemplates(Database $db, int $ownerId): void
    {
        $templates = [
            [
                'name'    => 'Rent Reminder',
                'type'    => 'email',
                'subject' => 'Rent Reminder - {{month}}',
                'body'    => 'Dear {{tenant}},\n\nThis is a friendly reminder that your rent of KES {{amount}} for {{month}} is due on {{dueDate}}.\n\nThank you,\n{{owner}}'
            ],
            [
                'name'    => 'Rent Confirmation',
                'type'    => 'whatsapp',
                'subject' => '',
                'body'    => 'Hi {{tenant}},\n\nYour payment of KES {{amount}} for {{month}} has been received. Receipt: {{receipt}}'
            ],
            [
                'name'    => 'Maintenance Notice',
                'type'    => 'email',
                'subject' => 'Scheduled Maintenance - {{property}}',
                'body'    => 'Dear Residents,\n\nPlease be informed that maintenance will be conducted on {{date}} from {{time}}: {{details}}\n\nThank you for your patience.'
            ],
            [
                'name'    => 'Complaint Update',
                'type'    => 'email',
                'subject' => 'Update on Your Complaint #{{id}}',
                'body'    => 'Dear {{tenant}},\n\nYour complaint regarding {{issue}} has been updated to status: {{status}}.\n\n{{message}}'
            ],
            [
                'name'    => 'Lease Renewal',
                'type'    => 'email',
                'subject' => 'Lease Renewal Notice',
                'body'    => 'Dear {{tenant}},\n\nYour lease for {{house}} expires on {{date}}. Please contact us to discuss renewal options.\n\nBest regards,\n{{owner}}'
            ],
        ];

        foreach ($templates as $template) {
            $db->insert('templates', array_merge($template, ['owner_id' => $ownerId]));
        }
    }
}

<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\JWT;
use App\Core\Router;
use App\Services\EmailService;

class AuthController
{
    public function register(): void
    {
        try {
            $data = Router::getRequestBody();
            $required = ['name', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Router::jsonResponse(['error' => "Field '{$field}' is required"], 400);
                }
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Router::jsonResponse(['error' => 'Invalid email format'], 400);
            }
            if (strlen($data['password']) < 6) {
                Router::jsonResponse(['error' => 'Password must be at least 6 characters'], 400);
            }
            $db = Database::getInstance();
            $existing = $db->fetchOne("SELECT id FROM owners WHERE email = ?", [$data['email']]);
            if ($existing) {
                Router::jsonResponse(['error' => 'An account with this email already exists'], 409);
            }
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            $nameParts = explode(' ', trim($data['name']));
            $initials = '';
            foreach ($nameParts as $part) {
                if (!empty($part)) $initials .= strtoupper($part[0]);
            }
            $initials = substr($initials, 0, 2);
            $ownerId = $db->insert('owners', [
                'name' => $data['name'], 'email' => $data['email'],
                'password' => $hashedPassword, 'phone' => $data['phone'] ?? null,
                'avatar' => $initials ?: 'OW',
            ]);
            $token = JWT::encode([
                'owner_id' => $ownerId, 'email' => $data['email'],
                'role' => 'owner', 'name' => $data['name'],
            ]);
            try { $this->createDefaultTemplates($db, $ownerId); } catch (\Exception $e) { error_log('Failed to create default templates: ' . $e->getMessage()); }
            Router::jsonResponse([
                'message' => 'Registration successful', 'token' => $token,
                'user' => ['id' => $ownerId, 'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? '', 'avatar' => $initials ?: 'OW', 'role' => 'owner'],
            ], 201);
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            Router::jsonResponse(['error' => 'Registration failed. Please try again.'], 500);
        }
    }

    public function login(array $params = []): void
    {
        try {
            $data = Router::getRequestBody();
            if (empty($data['email']) || empty($data['password'])) {
                Router::jsonResponse(['error' => 'Email and password are required'], 400);
            }
            $db = Database::getInstance();
            $owner = $db->fetchOne("SELECT id, name, email, password, phone, avatar FROM owners WHERE email = ?", [$data['email']]);
            if ($owner && password_verify($data['password'], $owner['password'])) {
                $db->update('owners', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$owner['id']]);
                $token = JWT::encode(['owner_id' => (int)$owner['id'], 'actor_id' => (int)$owner['id'], 'email' => $owner['email'], 'role' => 'owner', 'name' => $owner['name']]);
                Router::jsonResponse(['message' => 'Login successful', 'token' => $token, 'user' => ['id' => (int)$owner['id'], 'name' => $owner['name'], 'email' => $owner['email'], 'phone' => $owner['phone'] ?? '', 'avatar' => $owner['avatar'] ?? 'OW', 'role' => 'owner', 'lastLogin' => date('Y-m-d H:i:s')]]);
            }
            $caretaker = $db->fetchOne("SELECT id, owner_id, name, email, password, phone, avatar, assigned_properties FROM caretakers WHERE email = ?", [$data['email']]);
            if ($caretaker && password_verify($data['password'], $caretaker['password'])) {
                $token = JWT::encode(['owner_id' => (int)$caretaker['owner_id'], 'actor_id' => (int)$caretaker['id'], 'email' => $caretaker['email'], 'role' => 'caretaker', 'name' => $caretaker['name']]);
                Router::jsonResponse(['message' => 'Login successful', 'token' => $token, 'user' => ['id' => (int)$caretaker['id'], 'owner_id' => (int)$caretaker['owner_id'], 'name' => $caretaker['name'], 'email' => $caretaker['email'], 'phone' => $caretaker['phone'] ?? '', 'avatar' => $caretaker['avatar'] ?? 'CT', 'role' => 'caretaker', 'assigned_properties' => $caretaker['assigned_properties'] ?? '']]);
            }
            $tenant = $db->fetchOne("SELECT id, owner_id, name, email, password, phone, status FROM tenants WHERE email = ?", [$data['email']]);
            if ($tenant && !empty($tenant['password']) && password_verify($data['password'], $tenant['password'])) {
                if (($tenant['status'] ?? 'active') === 'terminated') {
                    Router::jsonResponse(['error' => 'Your tenancy has been terminated. Please contact the property owner for assistance.'], 403);
                }
                $token = JWT::encode(['owner_id' => (int)$tenant['owner_id'], 'actor_id' => (int)$tenant['id'], 'tenant_id' => (int)$tenant['id'], 'email' => $tenant['email'], 'role' => 'tenant', 'name' => $tenant['name']]);
                Router::jsonResponse(['message' => 'Login successful', 'token' => $token, 'user' => ['id' => (int)$tenant['id'], 'owner_id' => (int)$tenant['owner_id'], 'name' => $tenant['name'], 'email' => $tenant['email'], 'phone' => $tenant['phone'] ?? '', 'avatar' => 'TN', 'role' => 'tenant']]);
            }
            Router::jsonResponse(['error' => 'Invalid email or password'], 401);
        } catch (\Throwable $e) {
            error_log('Login error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            Router::jsonResponse(['error' => 'Login failed', 'detail' => $e->getMessage()], 500);
        }
    }

    public function me(array $params = []): void
    {
        $db = Database::getInstance();
        $role = Router::getAuthRole();
        $actorId = Router::getAuthActorId();
        $ownerId = Router::getAuthUserId();
        if ($role === 'caretaker') {
            $caretaker = $db->fetchOne("SELECT id, owner_id, name, email, phone, avatar, assigned_properties, created_at FROM caretakers WHERE id = ? AND owner_id = ?", [$actorId, $ownerId]);
            if (!$caretaker) { Router::jsonResponse(['error' => 'User not found'], 404); }
            $caretaker['id'] = (int)$caretaker['id']; $caretaker['role'] = 'caretaker';
            Router::jsonResponse(['user' => $caretaker]);
        }
        if ($role === 'tenant') {
            $tenant = $db->fetchOne("SELECT id, owner_id, name, email, phone, id_number, id_type, house_id, property_id, created_at FROM tenants WHERE id = ? AND owner_id = ?", [$actorId, $ownerId]);
            if (!$tenant) { Router::jsonResponse(['error' => 'User not found'], 404); }
            $tenant['id'] = (int)$tenant['id']; $tenant['role'] = 'tenant';
            Router::jsonResponse(['user' => $tenant]);
        }
        $owner = $db->fetchOne("SELECT id, name, email, phone, avatar, last_login, created_at FROM owners WHERE id = ?", [$ownerId]);
        if (!$owner) { Router::jsonResponse(['error' => 'User not found'], 404); }
        $owner['id'] = (int)$owner['id'];
        Router::jsonResponse(['user' => $owner]);
    }

    public function logout(array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        setcookie('rf_token', '', time() - 42000, '/');
        Router::jsonResponse(['message' => 'Logged out successfully']);
    }

    /**
     * POST /api/auth/forgot-password
     * Uses MySQL NOW() to avoid PHP/DB timezone mismatch
     */
    public function forgotPassword(): void
    {
        try {
            $data = Router::getRequestBody();
            $email = trim((string)($data['email'] ?? ''));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Router::jsonResponse(['error' => 'Please enter a valid email address'], 400);
            }
            $db = Database::getInstance();
            
            // Get DB current time to stay in sync with MySQL's NOW()
            $dbTimeResult = $db->fetchOne("SELECT NOW() as db_now");
            $dbNow = $dbTimeResult['db_now'] ?? date('Y-m-d H:i:s');
            
            $user = null;
            $userType = null;
            $owner = $db->fetchOne("SELECT id, name, email FROM owners WHERE email = ?", [$email]);
            if ($owner) { $user = $owner; $userType = 'owner'; }
            if (!$user) {
                $tenant = $db->fetchOne("SELECT id, name, email, status FROM tenants WHERE email = ?", [$email]);
                if ($tenant && $tenant['status'] !== 'terminated') { $user = $tenant; $userType = 'tenant'; }
            }
            if (!$user) {
                $caretaker = $db->fetchOne("SELECT id, name, email FROM caretakers WHERE email = ?", [$email]);
                if ($caretaker) { $user = $caretaker; $userType = 'caretaker'; }
            }
            if (!$user) {
                Router::jsonResponse(['error' => 'No account found with that email address. Please check the email or create a new account.', 'recommend_signup' => true], 404);
            }
            
            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // Use DATE_ADD with MySQL NOW() to stay in sync with DB timezone
            $db->update('password_reset_tokens', ['used' => 1], 'email = ? AND used = 0', [$email]);
            $db->insert('password_reset_tokens', [
                'owner_id' => $userType === 'owner' ? (int)$user['id'] : null,
                'tenant_id' => $userType === 'tenant' ? (int)$user['id'] : null,
                'email' => $email, 'code' => $code,
                'expires_at' => date('Y-m-d H:i:s', strtotime($dbNow) + 15 * 60),
                'used' => 0, 'attempts' => 0,
            ]);
            
            $emailSent = false;
            try {
                $emailService = new EmailService();
                $emailSent = $emailService->sendTemplate('Password Reset', 1, $email, $user['name'], [
                    'code' => $code, 'expires' => '15 minutes', 'name' => $user['name'],
                ]);
            } catch (\Throwable $e) { error_log('Failed to send password reset email: ' . $e->getMessage()); }
            
            Router::jsonResponse([
                'message' => $emailSent ? 'A verification code has been sent to your email. Please check your inbox (and spam folder).' : 'Failed to send email. Please try again or contact support.',
                'email_sent' => $emailSent
            ]);
        } catch (\Throwable $e) {
            error_log('Forgot password error: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function verifyResetCode(): void
    {
        try {
            $data = Router::getRequestBody();
            $email = trim((string)($data['email'] ?? ''));
            $code = trim((string)($data['code'] ?? ''));
            if (empty($email) || empty($code)) {
                Router::jsonResponse(['error' => 'Email and code are required'], 400);
            }
            $db = Database::getInstance();
            $token = $db->fetchOne(
                "SELECT * FROM password_reset_tokens WHERE email = ? AND used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1",
                [$email]
            );
            if (!$token) {
                Router::jsonResponse(['error' => 'Invalid or expired verification code'], 400);
            }
            if ($token['attempts'] >= 5) {
                Router::jsonResponse(['error' => 'Too many failed attempts. Please request a new code.'], 400);
            }
            if ($code !== $token['code']) {
                $db->update('password_reset_tokens', ['attempts' => $token['attempts'] + 1], 'id = ?', [$token['id']]);
                $remaining = 5 - ($token['attempts'] + 1);
                Router::jsonResponse(['error' => "Invalid code. {$remaining} attempts remaining."], 400);
            }
            Router::jsonResponse(['message' => 'Code verified successfully', 'valid' => true]);
        } catch (\Throwable $e) {
            error_log('Verify reset code error: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function resetPassword(): void
    {
        try {
            $data = Router::getRequestBody();
            $email = trim((string)($data['email'] ?? ''));
            $code = trim((string)($data['code'] ?? ''));
            $newPassword = (string)($data['password'] ?? '');
            if (empty($email) || empty($code) || empty($newPassword)) {
                Router::jsonResponse(['error' => 'Email, code, and new password are required'], 400);
            }
            if (strlen($newPassword) < 6) {
                Router::jsonResponse(['error' => 'Password must be at least 6 characters'], 400);
            }
            $db = Database::getInstance();
            $token = $db->fetchOne(
                "SELECT * FROM password_reset_tokens WHERE email = ? AND used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1",
                [$email]
            );
            if (!$token || $code !== $token['code']) {
                Router::jsonResponse(['error' => 'Invalid or expired verification code'], 400);
            }
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $updated = false;
            if ($token['owner_id']) {
                $updated = $db->update('owners', ['password' => $hashedPassword], 'id = ?', [$token['owner_id']]);
            } elseif ($token['tenant_id']) {
                $updated = $db->update('tenants', ['password' => $hashedPassword], 'id = ?', [$token['tenant_id']]);
            }
            if (!$updated) { Router::jsonResponse(['error' => 'User not found'], 404); }
            $db->update('password_reset_tokens', ['used' => 1], 'id = ?', [$token['id']]);
            Router::jsonResponse(['message' => 'Password reset successful. You can now login with your new password.', 'success' => true]);
        } catch (\Throwable $e) {
            error_log('Reset password error: ' . $e->getMessage());
            Router::jsonResponse(['error' => 'An error occurred. Please try again.'], 500);
        }
    }

    private function createDefaultTemplates(Database $db, int $ownerId): void
    {
        $tableExists = $db->fetchOne("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'templates'");
        if (!$tableExists || $tableExists['count'] == 0) return;
        $templates = [
            ['name' => 'Rent Reminder', 'type' => 'email', 'subject' => 'Rent Reminder - {{month}}', 'body' => 'Dear {{tenant}},\n\nThis is a friendly reminder that your rent of KES {{amount}} for {{month}} is due on {{dueDate}}.\n\nThank you,\n{{owner}}'],
            ['name' => 'Rent Confirmation', 'type' => 'whatsapp', 'subject' => '', 'body' => 'Hi {{tenant}},\n\nYour payment of KES {{amount}} for {{month}} has been received. Receipt: {{receipt}}'],
            ['name' => 'Maintenance Notice', 'type' => 'email', 'subject' => 'Scheduled Maintenance - {{property}}', 'body' => 'Dear Residents,\n\nPlease be informed that maintenance will be conducted on {{date}} from {{time}}: {{details}}\n\nThank you for your patience.'],
            ['name' => 'Complaint Update', 'type' => 'email', 'subject' => 'Update on Your Complaint #{{id}}', 'body' => 'Dear {{tenant}},\n\nYour complaint regarding {{issue}} has been updated to status: {{status}}.\n\n{{message}}'],
            ['name' => 'Lease Renewal', 'type' => 'email', 'subject' => 'Lease Renewal Notice', 'body' => 'Dear {{tenant}},\n\nYour lease for {{house}} expires on {{date}}. Please contact us to discuss renewal options.\n\nBest regards,\n{{owner}}'],
        ];
        foreach ($templates as $template) {
            try { $db->insert('templates', array_merge($template, ['owner_id' => $ownerId])); } catch (\Exception $e) { error_log('Failed to insert template: ' . $e->getMessage()); }
        }
    }
}
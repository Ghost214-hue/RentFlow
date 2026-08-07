<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Services\EmailService;

class SetupPasswordController
{
    /**
     * GET /api/auth/setup-password?token=...
     */
    public function validateToken(array $params = []): void
    {
        try {
            $token = trim((string) ($_GET['token'] ?? ''));

            if ($token === '') {
                Router::jsonResponse(['valid' => false, 'error' => 'Missing setup token.'], 400);
                return;
            }

            $db = Database::getInstance();

            $tokenRow = $db->fetchOne(
                'SELECT * FROM password_setup_tokens WHERE token = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1',
                [$token]
            );

            if (!$tokenRow) {
                Router::jsonResponse(['valid' => false, 'error' => 'Invalid or expired setup link.'], 400);
                return;
            }

            Router::jsonResponse([
                'valid' => true,
                'user_type' => $tokenRow['user_type'],
                'user_id' => (int) $tokenRow['user_id'],
            ]);
        } catch (\Throwable $e) {
            error_log('SetupPasswordController@validateToken error: ' . $e->getMessage());
            Router::jsonResponse(['valid' => false, 'error' => 'Unable to validate setup link.'], 500);
        }
    }

    /**
     * POST /api/auth/setup-password
     */
    public function setupPassword(array $params = []): void
    {
        try {
            $data = Router::getRequestBody();
            $token = trim((string) ($data['token'] ?? ''));
            $password = (string) ($data['new_password'] ?? '');

            if ($token === '' || $password === '') {
                Router::jsonResponse(['success' => false, 'error' => 'Token and new password are required.'], 400);
                return;
            }

            if (strlen($password) < 6) {
                Router::jsonResponse(['success' => false, 'error' => 'Password must be at least 6 characters.'], 400);
                return;
            }

            $db = Database::getInstance();

            $tokenRow = $db->fetchOne(
                'SELECT * FROM password_setup_tokens WHERE token = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1',
                [$token]
            );

            if (!$tokenRow) {
                Router::jsonResponse([
                    'success' => false,
                    'error' => 'Invalid or expired setup link.',
                    'code' => 'token_invalid',
                ], 400);
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $updated = false;

            $userType = $tokenRow['user_type'];
            $userId = (int) $tokenRow['user_id'];
            $ownerId = (int) $tokenRow['owner_id'];

            if ($userType === 'tenant') {
                $updated = (bool) $db->update('tenants', ['password' => $hashedPassword], 'id = ? AND owner_id = ?', [$userId, $ownerId]);
            } elseif ($userType === 'caretaker') {
                $updated = (bool) $db->update('caretakers', ['password' => $hashedPassword], 'id = ? AND owner_id = ?', [$userId, $ownerId]);
            }

            if (!$updated) {
                Router::jsonResponse(['success' => false, 'error' => 'User not found.'], 404);
                return;
            }

            $db->update('password_setup_tokens', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$tokenRow['id']]);

            Router::jsonResponse([
                'success' => true,
                'message' => 'Password set successfully. You can now log in.',
            ]);
        } catch (\Throwable $e) {
            error_log('SetupPasswordController@setupPassword error: ' . $e->getMessage());
            Router::jsonResponse(['success' => false, 'error' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * POST /api/auth/resend-setup-email
     */
    public function resendSetupEmail(array $params = []): void
    {
        try {
            $data = Router::getRequestBody();
            $email = trim((string) ($data['email'] ?? ''));
            $userType = trim((string) ($data['user_type'] ?? ''));

            if ($email === '' || !in_array($userType, ['tenant', 'caretaker'], true)) {
                Router::jsonResponse(['email_sent' => false, 'error' => 'Email and valid user type are required.'], 400);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Router::jsonResponse(['email_sent' => false, 'error' => 'Invalid email format.'], 400);
                return;
            }

            $db = Database::getInstance();

            $user = null;
            if ($userType === 'tenant') {
                $user = $db->fetchOne('SELECT id, name, email, owner_id FROM tenants WHERE email = ?', [$email]);
            } else {
                $user = $db->fetchOne('SELECT id, name, email, owner_id FROM caretakers WHERE email = ?', [$email]);
            }

            if (!$user) {
                Router::jsonResponse([
                    'email_sent' => false,
                    'error' => 'No account found with that email address.',
                    'recommend_signup' => true,
                ], 404);
                return;
            }

            $ownerId = (int) ($user['owner_id'] ?? 0);
            $userId = (int) $user['id'];

            if ($ownerId <= 0 || $userId <= 0) {
                Router::jsonResponse(['email_sent' => false, 'error' => 'Invalid user record.'], 400);
                return;
            }

            try {
                $db->update(
                    'password_setup_tokens',
                    ['used_at' => date('Y-m-d H:i:s')],
                    'user_type = ? AND user_id = ? AND owner_id = ? AND used_at IS NULL',
                    [$userType, $userId, $ownerId]
                );
            } catch (\Throwable $e) {
                error_log('Failed to invalidate existing setup tokens: ' . $e->getMessage());
            }

            $emailService = new EmailService();

            if ($userType === 'tenant') {
                $emailSent = $emailService->sendTenantWelcome($ownerId, $user, '', '');
            } else {
                $emailSent = $emailService->sendCaretakerWelcome($ownerId, $user);
            }

            if (!$emailSent) {
                Router::jsonResponse(['email_sent' => false, 'error' => 'Failed to send setup email. Please try again.'], 500);
                return;
            }

            Router::jsonResponse([
                'email_sent' => true,
                'message' => 'A new setup link has been sent to your email. Please check your inbox (and spam folder).',
            ]);
        } catch (\Throwable $e) {
            error_log('SetupPasswordController@resendSetupEmail error: ' . $e->getMessage());
            Router::jsonResponse(['email_sent' => false, 'error' => 'An error occurred. Please try again.'], 500);
        }
    }
}
<?php
/**
 * Authentication Middleware
 * Extracts owner_id from JWT token and attaches it to the request
 */
namespace App\Middleware;

use App\Core\JWT;
use App\Core\Router;

class AuthMiddleware
{
    /**
     * Run auth check. Call this before protected routes.
     */
    public static function authenticate(): void
    {
        $token = null;

        // Extract token from Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            Router::jsonResponse(['error' => 'Authentication required. No token provided.'], 401);
        }

        $payload = JWT::decode($token);
        if (!$payload) {
            Router::jsonResponse(['error' => 'Invalid or expired token.'], 401);
        }

        // Attach owner_id to the request for downstream use
        $_REQUEST['auth_user_id'] = (int) ($payload['owner_id'] ?? $payload['user_id'] ?? 0);
        $_REQUEST['auth_user_role'] = $payload['role'] ?? 'owner';

        if ($_REQUEST['auth_user_id'] <= 0) {
            Router::jsonResponse(['error' => 'Invalid token payload.'], 401);
        }
    }

    /**
     * Optional: check if user has a specific role
     */
    public static function requireRole(string $role): void
    {
        self::authenticate();
        if ($_REQUEST['auth_user_role'] !== $role) {
            Router::jsonResponse(['error' => 'Insufficient permissions.'], 403);
        }
    }
}
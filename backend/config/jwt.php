<?php
/**
 * JWT Configuration
 * Returns JWT settings - loads .env if available
 */

// Attempt to load .env if JWT_SECRET is not already present.
if (empty(getenv('JWT_SECRET')) && empty($_ENV['JWT_SECRET'])) {
    $envFiles = [__DIR__ . '/../../.env.production', __DIR__ . '/../../.env'];
    foreach ($envFiles as $envPath) {
        if (file_exists($envPath)) {
            if (!class_exists('\App\Core\Env')) {
                require_once __DIR__ . '/../app/Core/Env.php';
            }
            if (class_exists('\App\Core\Env')) {
                \App\Core\Env::load($envPath);
            }
            break;
        }
    }
}

// Get JWT secret from environment
$jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';

// Validate JWT secret strength
if (empty($jwtSecret)) {
    error_log('CRITICAL: JWT_SECRET not configured. Application will not function without a valid JWT_SECRET.');
    http_response_code(500);
    die('Application configuration error: JWT_SECRET not set.');
} elseif (strlen($jwtSecret) < 32) {
    error_log('WARNING: JWT_SECRET is shorter than 32 characters. For better security, use at least 32 characters.');
}

return [
    'secret_key'      => $jwtSecret,
    'algorithm'       => 'HS256',
    'expiry_seconds'  => 86400 * 7, // 7 days
    'issuer'          => 'rentaflow.app',
];

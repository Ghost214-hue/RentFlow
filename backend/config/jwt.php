<?php
/**
 * JWT Configuration
 * Returns JWT settings - loads .env if available
 */

// Attempt to load .env if JWT_SECRET is not already present.
if (empty(getenv('JWT_SECRET')) && empty($_ENV['JWT_SECRET'])) {
    if (!class_exists('\App\Core\Env')) {
        require_once __DIR__ . '/../app/Core/Env.php';
    }
    // Load correct .env for localhost vs production
    $isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true) ||
                   str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:');
    $envPath = $isLocalhost
        ? __DIR__ . '/../../.env'
        : (file_exists(__DIR__ . '/../../.env.production') ? __DIR__ . '/../../.env.production' : __DIR__ . '/../../.env');
    if (class_exists('\App\Core\Env')) {
        \App\Core\Env::load($envPath);
    }
}

// Get JWT secret from environment
$jwtSecret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';

error_log('[JWT CONFIG] Loaded JWT_SECRET - Length: ' . strlen($jwtSecret) . ' bytes, isLocalhost: ' . ($isLocalhost ? 'YES' : 'NO') . ', envPath: ' . $envPath);

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

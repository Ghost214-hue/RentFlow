<?php

// Ensure base path is available for all pages that include auth.php
require_once __DIR__ . '/base-path-fix.php';
$basePath = rtrim((string)($basePath ?? ''), '/');

// Determine if we're in production/HTTPS environment
$isProduction = ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'development') === 'production';
$isHttps = $isProduction || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

// Set session cookie path to / so sessions work across all pages in subdirectory
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Set cache-control headers to prevent stale content (must be before any output)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Get token from cookie (primary source)
$token = $_COOKIE['rf_token'] ?? null;

// If no cookie token, check session (fallback)
if (!$token && isset($_SESSION['rf_token'])) {
    $token = $_SESSION['rf_token'];
}

// Redirect to signin if no token found
if (!$token) {
    require_once __DIR__ . '/base-path-fix.php';
    $basePath = rtrim((string)($basePath ?? ''), '/');
    header('Location: ' . $basePath . '/signin');
    exit;
}

// Validate and decode token
require_once __DIR__ . '/../../backend/app/Core/Env.php';
$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true) ||
               str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:');
$envPath = $isLocalhost
    ? __DIR__ . '/../../.env'
    : (file_exists(__DIR__ . '/../../.env.production') ? __DIR__ . '/../../.env.production' : __DIR__ . '/../../.env');
\App\Core\Env::load($envPath);
require_once __DIR__ . '/../../backend/app/Core/JWT.php';

$jwt = new \App\Core\JWT();
$user = $jwt->decode($token);
// Debug logging when token decode fails to help diagnose local issues
if (!$user) {
    try {
        $parts = explode('.', $token);
        $partsCount = count($parts);
        $sample = substr($token, 0, 64);
        error_log("DEBUG: JWT decode failed. token_len=" . strlen($token) . " parts=" . $partsCount . " sample=" . $sample);
    } catch (\Throwable $e) {
        error_log('DEBUG: JWT decode failure and logging error: ' . $e->getMessage());
    }
}

// Redirect if token is invalid or expired
if (!$user) {
    error_log('[AUTH] Token validation failed - redirecting to signin');
    require_once __DIR__ . '/base-path-fix.php';
    $basePath = rtrim((string)($basePath ?? ''), '/');
    
    // Clear cookie with proper settings
    $cookieParams = session_get_cookie_params();
    setcookie('rf_token', '', time() - 42000, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
    $_SESSION = [];
    session_destroy();
    header('Location: ' . $basePath . '/signin');
    error_log('[AUTH] Redirect header sent to: ' . $basePath . '/signin');
    exit;
}

// Set session data for this page load
$_SESSION['rf_token'] = $token;
$_SESSION['rf_user'] = $user;

// Make user data available globally
$currentUser = $user;
$userRole = $user['role'] ?? 'owner';
$userId = $user['owner_id'] ?? $user['user_id'] ?? null;
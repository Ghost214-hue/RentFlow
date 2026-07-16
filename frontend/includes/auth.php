<?php

// Set session cookie path to / so sessions work across all pages in subdirectory
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
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
    // Calculate base path: find /RentaFlow/ in script name and use everything before it
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = '/RentaFlow'; // Hardcoded for this deployment
    header('Location: ' . $basePath . '/signin');
    exit;
}

// Validate and decode token
require_once __DIR__ . '/../../backend/app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../../backend/app/Core/JWT.php';

$jwt = new \App\Core\JWT();
$user = $jwt->decode($token);

// Redirect if token is invalid or expired
if (!$user) {
    setcookie('rf_token', '', time() - 42000, '/');
    $_SESSION = [];
    session_destroy();
    $basePath = '/RentaFlow'; // Hardcoded for this deployment
    header('Location: ' . $basePath . '/signin');
    exit;
}

// Set session data for this page load
$_SESSION['rf_token'] = $token;
$_SESSION['rf_user'] = $user;

// Make user data available globally
$currentUser = $user;
$userRole = $user['role'] ?? 'owner';
$userId = $user['owner_id'] ?? $user['user_id'] ?? null;

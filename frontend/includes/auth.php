<?php
/**
 * Shared authentication utility
 * Ensures consistent session and token validation across all pages
 */

if (session_status() === PHP_SESSION_NONE) {
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
    header('Location: /signin');
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
    header('Location: /signin');
    exit;
}

// Set session data for this page load
$_SESSION['rf_token'] = $token;
$_SESSION['rf_user'] = $user;

// Make user data available globally
$currentUser = $user;
$userRole = $user['role'] ?? 'owner';
$userId = $user['owner_id'] ?? $user['user_id'] ?? null;

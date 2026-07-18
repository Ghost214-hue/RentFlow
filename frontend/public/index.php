<?php
// For SPA fallback (when accessed via rewrite), use REQUEST_URI to get the correct base path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

// If we're in /frontend/public/index.php (SPA fallback), extract base from REQUEST_URI
if (strpos($basePath, '/frontend/public') !== false) {
    // e.g. /RentalFlow/dashboard -> basePath = /RentalFlow
    $basePath = preg_replace('#/[^/]+/?$#', '', $requestUri);
    $basePath = rtrim($basePath, '/');
}

// Extract page name from URL (e.g. /dashboard -> dashboard)
$pageName = trim(str_replace($basePath, '', $requestUri), '/');
if (empty($pageName)) $pageName = 'signin';

// Handle signin page
if ($pageName === 'signin' || $pageName === '') {
    require __DIR__ . '/signin.php';
    exit;
}

// Handle signup page
if ($pageName === 'signup') {
    require __DIR__ . '/signup.php';
    exit;
}

// Handle forgot-password page
if ($pageName === 'forgot-password') {
    require __DIR__ . '/forgot-password.php';
    exit;
}

// Handle terms-and-conditions page
if ($pageName === 'terms-and-conditions') {
    require __DIR__ . '/../pages/terms-and-conditions.php';
    exit;
}

// Check if this is a valid authenticated page
$validPages = ['dashboard', 'properties', 'houses', 'tenants', 'caretakers', 'bills', 'payments', 'complaints', 'communications', 'reports', 'tenant-dashboard', 'tenant-profile', 'settings'];
$pageFile = __DIR__ . '/../pages/' . $pageName . '.php';

if (in_array($pageName, $validPages) && file_exists($pageFile)) {
    require $pageFile;
} else {
    // Invalid page: redirect to signin
    header('Location: ' . $basePath . '/signin');
    exit;
}
<?php
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract page name from URL (e.g. /RentFlow/dashboard -> dashboard)
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
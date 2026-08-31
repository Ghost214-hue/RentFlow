<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

if (strpos($basePath, '/frontend/public') !== false) {
    $basePath = dirname(dirname($basePath));
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

// Handle setup-password page
if ($pageName === 'setup-password') {
    require __DIR__ . '/setup-password.php';
    exit;
}

// Handle terms-and-conditions page
if ($pageName === 'terms-and-conditions') {
    require __DIR__ . '/../pages/terms-and-conditions.php';
    exit;
}

// Check if this is a valid authenticated page
$validPages = ['dashboard', 'properties', 'houses', 'tenants', 'caretakers', 'bills', 'payments', 'complaints', 'communications', 'reports', 'tenant-dashboard', 'caretaker-dashboard', 'tenant-profile', 'settings', 'maintenance', 'documents', 'email-logs'];
$pageFile = __DIR__ . '/../pages/' . $pageName . '.php';

if (in_array($pageName, $validPages) && file_exists($pageFile)) {
    // Load shared auth (also sets $userRole from the JWT) and enforce role access.
    require_once __DIR__ . '/../includes/auth.php';

    // ---------- Role-based page access control ----------
    // Owner-only pages (management of staff).
    $ownerOnlyPages = ['caretakers'];

    // Owner + caretaker (admin) pages. Tenants are never allowed here.
    $ownerOrCaretakerPages = ['dashboard', 'properties', 'houses', 'tenants', 'communications', 'reports', 'maintenance', 'email-logs', 'settings'];

    // Tenant-only pages.
    $tenantOnlyPages = ['tenant-dashboard', 'tenant-profile'];

    // Shared pages (payments, bills, complaints, documents) are intentionally
    // available to owners, caretakers AND tenants, so they are NOT restricted here.

    $blocked = false;
    if (in_array($pageName, $ownerOnlyPages, true)) {
        $blocked = ($userRole !== 'owner');
    } elseif (in_array($pageName, $ownerOrCaretakerPages, true)) {
        $blocked = ($userRole !== 'owner' && $userRole !== 'caretaker');
    } elseif (in_array($pageName, $tenantOnlyPages, true)) {
        $blocked = ($userRole !== 'tenant');
    }

    if ($blocked) {
        // Redirect to the dashboard appropriate to the user's role.
        $target = ($userRole === 'tenant') ? 'tenant-dashboard'
                : (($userRole === 'caretaker') ? 'caretaker-dashboard' : 'dashboard');
        header('Location: ' . $basePath . '/' . $target);
        exit;
    }

    require $pageFile;
} else {
    // Invalid page: redirect to signin
    header('Location: ' . $basePath . '/signin');
    exit;
}
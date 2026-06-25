<?php
/**
 * PHP Dev Server Router
 * Routes /api/* to backend, everything else to frontend
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route API requests to backend
if (strpos($uri, '/api') === 0) {
    // Remove /api prefix and route to backend
    $_SERVER['REQUEST_URI'] = substr($uri, 4);
    if (empty($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = '/';
    }
    require __DIR__ . '/backend/public/index.php';
    return true;
}

// Static files - serve directly if they exist in frontend/public
$staticPath = __DIR__ . '/frontend/public' . $uri;
if ($uri !== '/' && file_exists($staticPath) && !is_dir($staticPath)) {
    return false; // Let PHP built-in server handle it
}

// Route to frontend
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/frontend/public/index.php';

// Handle SPA routes in frontend/public (signin, signup)
if (file_exists(__DIR__ . '/frontend/public' . $uri . '.php')) {
    require __DIR__ . '/frontend/public' . $uri . '.php';
    return true;
}

// Handle page routes in frontend/pages (dashboard, properties, houses, tenants, etc.)
$pageRoutes = ['/dashboard', '/properties', '/houses', '/tenants', '/payments', '/bills', '/complaints', '/communications', '/reports', '/settings', '/tenant-dashboard', '/caretaker-dashboard', '/tenant-profile', '/reports'];
foreach ($pageRoutes as $page) {
    if ($uri === $page) {
        $pageFile = __DIR__ . '/frontend/pages' . $page . '.php';
        if (file_exists($pageFile)) {
            require $pageFile;
            return true;
        }
    }
}

// Default to index.php (SPA)
require __DIR__ . '/frontend/public/index.php';
return true;
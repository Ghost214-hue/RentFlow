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
    // Serve the static file directly with correct MIME type
    $ext = pathinfo($staticPath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    $mimeType = $mimeTypes[$ext] ?? mime_content_type($staticPath);
    header('Content-Type: ' . $mimeType);
    readfile($staticPath);
    return true;
}

// Route standalone page files (houses.php, properties.php, etc.)
$pagePath = __DIR__ . '/frontend/pages' . $uri . '.php';
if ($uri !== '/' && file_exists($pagePath)) {
    require $pagePath;
    return true;
}

// Route to frontend SPA
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/frontend/public/index.php';

// All non-API, non-static routes go to the SPA (index.php)
require __DIR__ . '/frontend/public/index.php';
return true;

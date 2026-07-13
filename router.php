<?php
// Development router for PHP built-in server
// Usage: php -S localhost:8080 router.php

// Helper to serve static files with correct MIME type
function serve_static_file(string $path)
{
    if (!file_exists($path) || !is_file($path)) {
        return false;
    }
    
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'html' => 'text/html; charset=utf-8',
        'htm' => 'text/html; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'json' => 'application/json',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'map' => 'application/json'
    ];
    
    $mime = $mimes[$ext] ?? 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

// This router is invoked for ALL requests when using PHP built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API routes go to backend
if (str_starts_with($uri, '/api') || str_starts_with($uri, '/backend')) {
    require __DIR__ . '/backend/public/index.php';
    exit;
}

// Static files - serve directly from frontend/public
$staticFile = __DIR__ . '/frontend/public' . $uri;
if ($uri !== '/' && str_contains($uri, '.')) {
    // Has a file extension - check if it's a static file
    if (serve_static_file($staticFile)) {
        exit;
    }
}

// All other requests go to the frontend router (SPA)
require __DIR__ . '/frontend/public/index.php';
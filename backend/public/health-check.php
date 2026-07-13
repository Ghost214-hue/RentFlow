<?php

/**
 * Health Check Endpoint
 * Place this in backend/public/health-check.php
 * Access via: /api/health-check
 */

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'version' => '1.0.0',
    'environment' => $_ENV['APP_ENV'] ?? 'unknown',
    'uptime' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
    'checks' => []
];

$allHealthy = true;

// Check database
try {
    require_once __DIR__ . '/../config/database.php';
    // Attempt a simple query
    $db = new Database();
    $health['checks']['database'] = [
        'status' => 'connected',
        'timestamp' => date('c')
    ];
} catch (Exception $e) {
    $allHealthy = false;
    $health['checks']['database'] = [
        'status' => 'error',
        'message' => 'Database connection failed',
        'timestamp' => date('c')
    ];
}

// Check file system permissions
$uploadDir = __DIR__ . '/uploads';
if (is_writable($uploadDir)) {
    $health['checks']['uploads'] = [
        'status' => 'writable',
        'path' => $uploadDir,
        'timestamp' => date('c')
    ];
} else {
    $allHealthy = false;
    $health['checks']['uploads'] = [
        'status' => 'error',
        'message' => 'Upload directory not writable',
        'path' => $uploadDir,
        'timestamp' => date('c')
    ];
}

// Check logs directory
$logsDir = __DIR__ . '/../../logs';
if (is_writable($logsDir)) {
    $health['checks']['logs'] = [
        'status' => 'writable',
        'timestamp' => date('c')
    ];
} else {
    $allHealthy = false;
    $health['checks']['logs'] = [
        'status' => 'error',
        'message' => 'Logs directory not writable',
        'timestamp' => date('c')
    ];
}

// Check memory
if (function_exists('memory_get_usage')) {
    $health['checks']['memory'] = [
        'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        'limit_mb' => ini_get('memory_limit'),
        'timestamp' => date('c')
    ];
}

$health['status'] = $allHealthy ? 'healthy' : 'degraded';

http_response_code($allHealthy ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

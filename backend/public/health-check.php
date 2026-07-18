<?php

/**
 * Health Check Endpoint
 * Access via: https://yourdomain.com/api/health-check
 * 
 * SECURITY: This endpoint should be protected in production
 * Option 1: Restrict by IP in .htaccess
 * Option 2: Remove/disable in production
 */

// Only allow health check in non-production or from specific IPs
$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
$allowedIps = ['127.0.0.1', '::1', 'YOUR_ADMIN_IP_HERE']; // Add your admin IPs

if ($appEnv === 'production') {
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($clientIp, $allowedIps)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not found']);
        exit;
    }
}

header('Content-Type: application/json');

// Bootstrap environment
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database;

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'version' => '1.0.0',
    'environment' => $appEnv,
    'checks' => []
];

$allHealthy = true;

// Check database (basic check only)
try {
    $db = Database::getInstance();
    $db->fetchOne("SELECT 1");
    $health['checks']['database'] = [
        'status' => 'connected',
        'timestamp' => date('c')
    ];
} catch (Exception $e) {
    $allHealthy = false;
    $health['checks']['database'] = [
        'status' => 'error',
        'timestamp' => date('c')
    ];
}

$health['status'] = $allHealthy ? 'healthy' : 'degraded';

http_response_code($allHealthy ? 200 : 503);
echo json_encode($health, JSON_UNESCAPED_SLASHES);

<?php
/**
 * API Entry Point
 * Handles all /api/* requests
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load environment
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

use App\Core\Router;
use App\Core\Database;
use App\Middleware\AuthMiddleware;

// Initialize router
$router = new Router();

// ==================== AUTH ROUTES ====================
$router->post('/auth/login', ['App\Controllers\AuthController', 'login']);
$router->post('/auth/register', ['App\Controllers\AuthController', 'register']);
$router->get('/auth/me', ['App\Controllers\AuthController', 'me'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== DASHBOARD ROUTES ====================
$router->get('/dashboard', ['App\Controllers\DashboardController', 'index'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== PROPERTY ROUTES ====================
$router->get('/properties', ['App\Controllers\PropertyController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/properties/{id}', ['App\Controllers\PropertyController', 'show'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/properties', ['App\Controllers\PropertyController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/properties/{id}', ['App\Controllers\PropertyController', 'update'], [function() { AuthMiddleware::authenticate(); }]);
$router->delete('/properties/{id}', ['App\Controllers\PropertyController', 'destroy'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== HOUSE ROUTES ====================
$router->get('/houses', ['App\Controllers\HouseController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/houses', ['App\Controllers\HouseController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/houses/{id}', ['App\Controllers\HouseController', 'update'], [function() { AuthMiddleware::authenticate(); }]);
$router->delete('/houses/{id}', ['App\Controllers\HouseController', 'destroy'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== TENANT ROUTES ====================
$router->get('/tenants', ['App\Controllers\TenantController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/tenants/{id}', ['App\Controllers\TenantController', 'show'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/tenants', ['App\Controllers\TenantController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/tenants/{id}', ['App\Controllers\TenantController', 'update'], [function() { AuthMiddleware::authenticate(); }]);
$router->delete('/tenants/{id}', ['App\Controllers\TenantController', 'destroy'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== PAYMENT ROUTES ====================
$router->get('/payments', ['App\Controllers\PaymentController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/payments', ['App\Controllers\PaymentController', 'store'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== BILL ROUTES ====================
$router->get('/bills', ['App\Controllers\BillController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/bills/generate', ['App\Controllers\BillController', 'generate'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== COMPLAINT ROUTES ====================
$router->get('/complaints', ['App\Controllers\ComplaintController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/complaints/{id}', ['App\Controllers\ComplaintController', 'show'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/complaints', ['App\Controllers\ComplaintController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/complaints/{id}', ['App\Controllers\ComplaintController', 'update'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== COMMUNICATION ROUTES ====================
$router->get('/communications', ['App\Controllers\CommunicationController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/communications', ['App\Controllers\CommunicationController', 'store'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== CARETAKER ROUTES ====================
$router->get('/caretakers', ['App\Controllers\CaretakerController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/caretakers', ['App\Controllers\CaretakerController', 'store'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== REPORT ROUTES ====================
$router->get('/reports', ['App\Controllers\ReportController', 'index'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== UPLOAD ROUTES ====================
$router->post('/upload', function() {
    $ownerId = Router::getAuthUserId();
    if (!$ownerId) {
        Router::jsonResponse(['error' => 'Authentication required'], 401);
    }
    
    if (!isset($_FILES['file'])) {
        Router::jsonResponse(['error' => 'No file uploaded'], 400);
    }
    
    $file = $_FILES['file'];
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        Router::jsonResponse(['error' => 'Invalid file type. Allowed: PDF, JPG, PNG'], 400);
    }
    
    if ($file['size'] > $maxSize) {
        Router::jsonResponse(['error' => 'File too large. Max 10MB'], 400);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('doc_') . '_' . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/uploads/';
    
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        Router::jsonResponse(['error' => 'Failed to upload file'], 500);
    }
    
    Router::jsonResponse([
        'message' => 'File uploaded',
        'file' => [
            'name' => $file['name'],
            'url' => '/uploads/' . $filename,
            'size' => $file['size'],
            'type' => $file['type'],
        ]
    ]);
}, [function() { \App\Middleware\AuthMiddleware::authenticate(); }]);

// ==================== TEMPLATE ROUTES ====================
$router->get('/templates', function() {
    Router::jsonResponse([
        ['id' => 1, 'name' => 'Rent Reminder', 'subject' => 'Rent Payment Reminder', 'body' => 'Dear {tenant}, this is a reminder that your rent of {amount} is due on {due_date}.'],
        ['id' => 2, 'name' => 'Payment Receipt', 'subject' => 'Payment Received', 'body' => 'Dear {tenant}, we have received your payment of {amount}. Thank you!'],
        ['id' => 3, 'name' => 'Complaint Acknowledgment', 'subject' => 'Complaint Received', 'body' => 'Dear {tenant}, we have received your complaint regarding {issue}. We will address it shortly.'],
    ]);
});

// Dispatch
$router->dispatch();
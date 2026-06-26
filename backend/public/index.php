<?php
/**
 * API Entry Point
 * Handles all /api/* requests
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

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
App\Core\Env::load(__DIR__ . '/../../.env');

use App\Core\Router;
use App\Core\Database;
use App\Middleware\AuthMiddleware;

Router::sendBaseHeaders();

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Initialize router
$router = new Router();

// ==================== AUTH ROUTES ====================
$router->post('/auth/login', ['App\Controllers\AuthController', 'login']);
$router->post('/auth/register', ['App\Controllers\AuthController', 'register']);
$router->post('/auth/logout', ['App\Controllers\AuthController', 'logout']);
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
$router->put('/caretakers/{id}', ['App\Controllers\CaretakerController', 'update'], [function() { AuthMiddleware::authenticate(); }]);
$router->delete('/caretakers/{id}', ['App\Controllers\CaretakerController', 'destroy'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== DOCUMENT ROUTES ====================
$router->get('/documents', ['App\Controllers\DocumentController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/documents/{id}/pdf', ['App\Controllers\DocumentController', 'downloadPdf'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/documents/{id}', ['App\Controllers\DocumentController', 'show'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/documents', ['App\Controllers\DocumentController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/documents/{id}', ['App\Controllers\DocumentController', 'update'], [function() { AuthMiddleware::authenticate(); }]);
$router->delete('/documents/{id}', ['App\Controllers\DocumentController', 'destroy'], [function() { AuthMiddleware::authenticate(); }]);

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
    $allowedTypes = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];
    $maxSize = 10 * 1024 * 1024; // 10MB

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        Router::jsonResponse(['error' => 'Invalid upload'], 400);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $detectedType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';

    if (!isset($allowedTypes[$ext]) || $allowedTypes[$ext] !== $detectedType) {
        Router::jsonResponse(['error' => 'Invalid file type. Allowed: PDF, JPG, PNG'], 400);
    }
    
    if ($file['size'] > $maxSize) {
        Router::jsonResponse(['error' => 'File too large. Max 10MB'], 400);
    }
    
    $filename = 'doc_' . bin2hex(random_bytes(16)) . '.' . $ext;
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        Router::jsonResponse(['error' => 'Upload directory is not available'], 500);
    }
    
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        Router::jsonResponse(['error' => 'Failed to upload file'], 500);
    }

    chmod($uploadDir . $filename, 0644);
    
    Router::jsonResponse([
        'message' => 'File uploaded',
        'file' => [
            'name' => basename($file['name']),
            'url' => '/uploads/' . $filename,
            'size' => $file['size'],
            'type' => $detectedType,
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

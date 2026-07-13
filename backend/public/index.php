<?php
/**
 * API Entry Point
 * Handles all /api/* requests
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' data:; connect-src \'self\'; frame-ancestors \'none\';');

// HTTPS enforcement
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

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
$router->post('/auth/forgot-password', ['App\Controllers\AuthController', 'forgotPassword']);
$router->post('/auth/verify-reset-code', ['App\Controllers\AuthController', 'verifyResetCode']);
$router->post('/auth/reset-password', ['App\Controllers\AuthController', 'resetPassword']);

// ==================== DASHBOARD ROUTES ====================
$router->get('/dashboard', ['App\Controllers\DashboardController', 'index'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== PROPERTY ROUTES ====================
$router->get('/properties', ['App\Controllers\PropertyController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/properties/available', ['App\Controllers\PropertyController', 'availableForCaretaker'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/properties/{id}', ['App\Controllers\PropertyController', 'show'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/properties', ['App\Controllers\PropertyController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/properties/{id}', ['App\Controllers\PropertyController', 'update'], [function() { AuthMiddleware::authenticate(); }]);
$router->delete('/properties/{id}', ['App\Controllers\PropertyController', 'destroy'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== HOUSE ROUTES ====================
$router->get('/houses', ['App\Controllers\HouseController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/houses/available', ['App\Controllers\HouseController', 'available'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/houses', ['App\Controllers\HouseController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/houses/{id}', ['App\Controllers\HouseController', 'update'], [function() { AuthMiddleware::authenticate(); }]);
$router->delete('/houses/{id}', ['App\Controllers\HouseController', 'destroy'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== TENANT ROUTES ====================
$router->get('/tenants', ['App\Controllers\TenantController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/tenants/{id}', ['App\Controllers\TenantController', 'show'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/tenants', ['App\Controllers\TenantController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/tenants/{id}', ['App\Controllers\TenantController', 'update'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/tenants/{id}/vacate', ['App\Controllers\TenantController', 'vacate'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/tenants/{id}/terminate', ['App\Controllers\TenantController', 'terminate'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/tenants/request-termination', ['App\Controllers\TenantController', 'requestTermination'], [function() { AuthMiddleware::authenticate(); }]);
$router->get('/tenancy-terminations', ['App\Controllers\TenantController', 'listTerminations'], [function() { AuthMiddleware::authenticate(); }]);
$router->delete('/tenants/{id}', ['App\Controllers\TenantController', 'destroy'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== PAYMENT ROUTES ====================
$router->get('/payments', ['App\Controllers\PaymentController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/payments', ['App\Controllers\PaymentController', 'store'], [function() { AuthMiddleware::authenticate(); }]);
$router->put('/payments/{id}/confirm', ['App\Controllers\PaymentController', 'confirm'], [function() { AuthMiddleware::authenticate(); }]);

// ==================== BILL ROUTES ====================
$router->get('/bills', ['App\Controllers\BillController', 'index'], [function() { AuthMiddleware::authenticate(); }]);
$router->post('/bills/generate', ['App\Controllers\BillController', 'generate'], [function() { AuthMiddleware::authenticate(); }]);
// Invoice route handles auth internally to support token via query param
$router->get('/bills/{id}/invoice', ['App\Controllers\BillController', 'invoice']);

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

// PDF bill export
$router->get('/bills/{id}/pdf', function(array $params) {
    $token = $_GET['token'] ?? null;
    if (!$token) { http_response_code(401); echo 'Missing token'; exit; }
    $jwt = new \App\Core\JWT();
    $user = $jwt->decode($token);
    if (!$user) { http_response_code(401); echo 'Invalid token'; exit; }

    $ownerId = $user['sub'] ?? 0;
    $db = \App\Core\Database::getInstance();

    $billId = (int) ($params['id'] ?? 0);
    $bill = $db->fetchOne(
        "SELECT b.*, h.unit, p.name as property_name, t.name as tenant_name, t.phone as tenant_phone, t.email as tenant_email
         FROM bills b
         LEFT JOIN houses h ON b.house_id = h.id
         LEFT JOIN properties p ON h.property_id = p.id
         LEFT JOIN tenants t ON h.tenant_id = t.id
         WHERE b.id = ? AND b.owner_id = ?",
        [$billId, $ownerId]
    );

    if (!$bill) { http_response_code(404); echo 'Bill not found'; exit; }

    $payments = $db->fetchAll(
        "SELECT * FROM payments WHERE house_id = ? AND owner_id = ? AND month = ? ORDER BY created_at DESC",
        [$bill['house_id'], $ownerId, $bill['month']]
    );

    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Invoice</title><style>
      body{font-family: Arial, sans-serif; color:#111; padding:24px;}
      .box{max-width:800px; margin:0 auto; border:1px solid #e2e8f0; padding:32px; border-radius:8px;}
      h1{font-size:22px; margin-bottom:6px;}.muted{color:#666; font-size:12px; margin-bottom:18px;}
      table{width:100%; border-collapse:collapse; margin-top:14px;}
      th,td{text-align:left; padding:8px 6px; border-bottom:1px solid #e5e7eb; font-size:14px;}
      th{background:#f8fafc; font-weight:600;}
      .right{text-align:right;}.total{font-weight:700; font-size:16px; margin-top:6px;}
      .footer{margin-top:22px; font-size:12px; color:#666;}
      .btn-print{display:inline-block; margin-bottom:14px; padding:8px 12px; border:1px solid #ccc; border-radius:6px; background:#fff; cursor:pointer;}
    </style></head><body>
    <div class="box">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <div><h1>Invoice</h1><div class="muted">'.htmlspecialchars($bill['property_name'] ?? '').'</div></div>
        <div><button class="btn-print" onclick="window.print()">Print / Save as PDF</button></div>
      </div>
      <div class="muted">Month: '.htmlspecialchars($bill['month']).' &nbsp;|&nbsp; Unit: '.htmlspecialchars($bill['unit']).'</div>
      <table>
        <thead><tr><th>Tenant</th><th class="right">Amount</th><th class="right">Paid</th><th class="right">Balance</th><th>Status</th></tr></thead>
        <tbody>
          <tr>
            <td>'.htmlspecialchars($bill['tenant_name'] ?? 'N/A').'<br><span style="color:#666;font-size:12px;">'.htmlspecialchars($bill['tenant_phone'] ?? '').'</span></td>
            <td class="right">KES '.number_format((float)($bill['total'] ?? 0), 2).'</td>
            <td class="right">KES '.number_format((float)($bill['paid'] ?? 0), 2).'</td>
            <td class="right">KES '.number_format((float)$bill['balance'] ?? 0, 2).'</td>
            <td>'.htmlspecialchars($bill['status'] ?? 'pending').'</td>
          </tr>
        </tbody>
      </table>
      <div class="total right">Balance Due: KES '.number_format((float)$bill['balance'] ?? 0, 2).'</div>

      <h3 style="margin-top:22px; font-size:16px;">Payments</h3>
      <table>
        <thead><tr><th>Date</th><th>Receipt</th><th>Method</th><th class="right">Amount</th><th>Status</th></tr></thead>
        <tbody>';
    if ($payments) {
        foreach ($payments as $p) {
            echo '<tr><td>'.htmlspecialchars($p['date'] ?? '').'</td><td>'.htmlspecialchars($p['receipt'] ?? '').'</td><td>'.htmlspecialchars($p['method'] ?? '').'<td class="right">KES '.number_format((float)($p['amount'] ?? 0), 2).'</td><td>'.htmlspecialchars($p['status'] ?? '').'</td></tr>';
        }
    } else {
        echo '<tr><td colspan="5" style="color:#888;">No payments recorded</td></tr>';
    }
    echo '</tbody></table>
      <div class="footer">Generated by RentFlow &middot; '.date('Y-m-d H:i').'</div>
    </div>
    </body></html>';
});

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

    chmod($uploadDir . $filename, 0640);
    
    // Generate file hash for integrity verification
    $fileHash = hash_file('sha256', $uploadDir . $filename);
    
    Router::jsonResponse([
        'message' => 'File uploaded',
        'file' => [
            'name' => basename($file['name']),
            'url' => '/uploads/' . $filename,
            'size' => $file['size'],
            'type' => $detectedType,
            'hash' => $fileHash,
            'hash_algorithm' => 'sha256'
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

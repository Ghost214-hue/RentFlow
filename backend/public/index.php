<?php
/**
 * API Entry Point
 * Handles all /api/* requests
 */

// Intercept upload requests and serve them directly (bypass router)
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriBasename = basename($uriPath);
$isUpload = preg_match('#^/?uploads/doc_[a-f0-9]+\.[a-z0-9]+$#i', $uriPath);
if ($isUpload) {
    $uploadDir = __DIR__ . '/uploads/';
    $uploadFile = $uploadDir . $uriBasename;

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    if (file_exists($uploadFile) && is_file($uploadFile)) {
        $ext = strtolower(pathinfo($uriBasename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=86400');
        $data = @file_get_contents($uploadFile);
        if ($data !== false) {
            echo $data;
            exit;
        }
        http_response_code(500);
        echo 'Failed to read file';
        exit;
    }

    // If the exact filename doesn't match, try to find any file starting with doc_
    $matched = false;
    if (is_dir($uploadDir) && ($dh = opendir($uploadDir))) {
        $prefix = preg_replace('/^doc_[a-f0-9]+/i', '', $uriBasename);
        while (($file = readdir($dh)) !== false) {
            if (strpos($file, 'doc_') === 0 && substr($file, -strlen($prefix)) === $prefix) {
                $matched = $uploadDir . $file;
                break;
            }
        }
        closedir($dh);
    }

    if ($matched && file_exists($matched) && is_file($matched)) {
        $ext = strtolower(pathinfo($matched, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=86400');
        $data = @file_get_contents($matched);
        if ($data !== false) {
            echo $data;
            exit;
        }
        http_response_code(500);
        echo 'Failed to read file';
        exit;
    }

    http_response_code(404);
    echo 'File not found: ' . htmlspecialchars($uriBasename);
    exit;
}

// Debug: disabled in production
// if (strpos($uriPath, 'uploads') !== false) {
//     header('Content-Type: text/plain');
//     echo "URI: $uriPath\nBasename: $uriBasename\nMatch: " . ($isUpload ? 'yes' : 'no') . "\n";
//     exit;
// }

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com; img-src \'self\' data: https:; font-src \'self\' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; connect-src \'self\' https:; style-src-elem \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com https://fonts.googleapis.com; frame-ancestors \'none\';');

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

// Load environment - prefer .env for localhost, .env.production for production
$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true) ||
               str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:');
$envPath = $isLocalhost
    ? __DIR__ . '/../../.env'
    : (file_exists(__DIR__ . '/../../.env.production') ? __DIR__ . '/../../.env.production' : __DIR__ . '/../../.env');
\App\Core\Env::load($envPath);

use App\Core\Router;
use App\Middleware\AuthMiddleware;

Router::sendBaseHeaders();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();

// ==================== AUTH ROUTES ====================
$router->post('/auth/login', ['App\Controllers\AuthController', 'login']);
$router->post('/auth/register', ['App\Controllers\AuthController', 'register']);
$router->post('/auth/logout', ['App\Controllers\AuthController', 'logout']);
$router->get('/auth/me', ['App\Controllers\AuthController', 'me'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/auth/forgot-password', ['App\Controllers\AuthController', 'forgotPassword']);
$router->post('/auth/verify-reset-code', ['App\Controllers\AuthController', 'verifyResetCode']);
$router->post('/auth/reset-password', ['App\Controllers\AuthController', 'resetPassword']);
$router->get('/auth/setup-password', ['App\Controllers\SetupPasswordController', 'validateToken']);
$router->post('/auth/setup-password', ['App\Controllers\SetupPasswordController', 'setupPassword']);
$router->post('/auth/resend-setup-email', ['App\Controllers\SetupPasswordController', 'resendSetupEmail']);

// ==================== DASHBOARD ====================
$router->get('/dashboard', ['App\Controllers\DashboardController', 'index'], [fn() => AuthMiddleware::authenticate()]);

// ==================== PROPERTIES ====================
$router->get('/properties', ['App\Controllers\PropertyController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/properties/available', ['App\Controllers\PropertyController', 'availableForCaretaker'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/properties/{id}', ['App\Controllers\PropertyController', 'show'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/properties', ['App\Controllers\PropertyController', 'store'], [fn() => AuthMiddleware::authenticate()]);
$router->put('/properties/{id}', ['App\Controllers\PropertyController', 'update'], [fn() => AuthMiddleware::authenticate()]);
$router->delete('/properties/{id}', ['App\Controllers\PropertyController', 'destroy'], [fn() => AuthMiddleware::authenticate()]);

// ==================== HOUSES ====================
$router->get('/houses', ['App\Controllers\HouseController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/houses/available', ['App\Controllers\HouseController', 'available'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/houses', ['App\Controllers\HouseController', 'store'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/houses/{id}', ['App\Controllers\HouseController', 'show'], [fn() => AuthMiddleware::authenticate()]);
$router->put('/houses/{id}', ['App\Controllers\HouseController', 'update'], [fn() => AuthMiddleware::authenticate()]);
$router->delete('/houses/{id}', ['App\Controllers\HouseController', 'destroy'], [fn() => AuthMiddleware::authenticate()]);

// ==================== TENANTS ====================
$router->get('/tenants', ['App\Controllers\TenantController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/tenants/property-contact', ['App\Controllers\TenantController', 'propertyContact'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/tenants/{id}', ['App\Controllers\TenantController', 'show'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/tenants', ['App\Controllers\TenantController', 'store'], [fn() => AuthMiddleware::authenticate()]);
$router->put('/tenants/{id}', ['App\Controllers\TenantController', 'update'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/tenants/{id}/vacate', ['App\Controllers\TenantController', 'vacate'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/tenants/{id}/terminate', ['App\Controllers\TenantController', 'terminate'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/tenants/request-termination', ['App\Controllers\TenantController', 'requestTermination'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/tenants/consent-data-protection', ['App\Controllers\TenantController', 'consentDataProtection'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/tenancy-terminations', ['App\Controllers\TenantController', 'listTerminations'], [fn() => AuthMiddleware::authenticate()]);
$router->delete('/tenants/{id}', ['App\Controllers\TenantController', 'destroy'], [fn() => AuthMiddleware::authenticate()]);

// ==================== PAYMENTS ====================
$router->get('/payments', ['App\Controllers\PaymentController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/payments', ['App\Controllers\PaymentController', 'store'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/payments/tenant-finance/{id}', ['App\Controllers\PaymentController', 'tenantFinance'], [fn() => AuthMiddleware::authenticate()]);
$router->put('/payments/{id}/confirm', ['App\Controllers\PaymentController', 'confirm'], [fn() => AuthMiddleware::authenticate()]);

// ==================== BILLS ====================
$router->get('/bills', ['App\Controllers\BillController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/bills/generate', ['App\Controllers\BillController', 'generate'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/bills/{id}/invoice', ['App\Controllers\BillController', 'invoice']);

// ==================== COMPLAINTS ====================
$router->get('/complaints', ['App\Controllers\ComplaintController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/complaints/{id}', ['App\Controllers\ComplaintController', 'show'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/complaints', ['App\Controllers\ComplaintController', 'store'], [fn() => AuthMiddleware::authenticate()]);
$router->put('/complaints/{id}', ['App\Controllers\ComplaintController', 'update'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/complaints/{id}/approve', ['App\Controllers\ComplaintController', 'approve'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/complaints/{id}/reject', ['App\Controllers\ComplaintController', 'reject'], [fn() => AuthMiddleware::authenticate()]);

// ==================== COMMUNICATIONS ====================
$router->get('/communications', ['App\Controllers\CommunicationController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/communications', ['App\Controllers\CommunicationController', 'store'], [fn() => AuthMiddleware::authenticate()]);

// ==================== CARETAKERS ====================
$router->get('/caretakers', ['App\Controllers\CaretakerController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/caretakers', ['App\Controllers\CaretakerController', 'store'], [fn() => AuthMiddleware::authenticate()]);
$router->put('/caretakers/{id}', ['App\Controllers\CaretakerController', 'update'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/caretakers/{id}/resend-setup-link', ['App\Controllers\CaretakerController', 'resendSetupLink'], [fn() => AuthMiddleware::authenticate()]);
$router->delete('/caretakers/{id}', ['App\Controllers\CaretakerController', 'destroy'], [fn() => AuthMiddleware::authenticate()]);

// ==================== DOCUMENTS ====================
$router->get('/documents', ['App\Controllers\DocumentController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/documents/{id}/pdf', ['App\Controllers\DocumentController', 'downloadPdf'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/documents/{id}', ['App\Controllers\DocumentController', 'show'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/documents', ['App\Controllers\DocumentController', 'store'], [fn() => AuthMiddleware::authenticate()]);
$router->put('/documents/{id}', ['App\Controllers\DocumentController', 'update'], [fn() => AuthMiddleware::authenticate()]);
$router->delete('/documents/{id}', ['App\Controllers\DocumentController', 'destroy'], [fn() => AuthMiddleware::authenticate()]);

// ==================== MAINTENANCE ====================
$router->get('/maintenance', ['App\Controllers\MaintenanceController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/maintenance/stats', ['App\Controllers\MaintenanceController', 'stats'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/maintenance/{id}', ['App\Controllers\MaintenanceController', 'show'], [fn() => AuthMiddleware::authenticate()]);
$router->post('/maintenance', ['App\Controllers\MaintenanceController', 'store'], [fn() => AuthMiddleware::authenticate()]);
$router->put('/maintenance/{id}', ['App\Controllers\MaintenanceController', 'update'], [fn() => AuthMiddleware::authenticate()]);
$router->delete('/maintenance/{id}', ['App\Controllers\MaintenanceController', 'destroy'], [fn() => AuthMiddleware::authenticate()]);

// ==================== REPORTS ====================
$router->get('/reports', ['App\Controllers\ReportController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/reports/tenancy-vacancy', ['App\Controllers\TenancyVacancyReportController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/reports/tenancy-vacancy/summary', ['App\Controllers\TenancyVacancyReportController', 'summary'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/reports/financial', ['App\Controllers\FinancialReportController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/reports/financial/summary', ['App\Controllers\FinancialReportController', 'summary'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/reports/bills', ['App\Controllers\BillsReportController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/reports/bills/summary', ['App\Controllers\BillsReportController', 'summary'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/reports/complaints', ['App\Controllers\ComplaintsReportController', 'index'], [fn() => AuthMiddleware::authenticate()]);
$router->get('/reports/complaints/summary', ['App\Controllers\ComplaintsReportController', 'summary'], [fn() => AuthMiddleware::authenticate()]);

// ==================== UPLOAD ====================
$router->post('/upload', function() {
    $ownerId = \App\Core\Router::getAuthUserId();
    if (!$ownerId) {
        \App\Core\Router::jsonResponse(['error' => 'Authentication required'], 401);
    }
    if (!isset($_FILES['file'])) {
        \App\Core\Router::jsonResponse(['error' => 'No file uploaded'], 400);
    }
    $file = $_FILES['file'];
    $allowedTypes = ['pdf','jpg','jpeg','png'];
    $maxSize = 10 * 1024 * 1024;
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        \App\Core\Router::jsonResponse(['error' => 'Invalid upload'], 400);
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes, true)) {
        \App\Core\Router::jsonResponse(['error' => 'Invalid file type. Allowed: PDF, JPG, PNG'], 400);
    }
    $detectedType = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedType = $finfo->file($file['tmp_name']) ?: '';
    }
    if ($file['size'] > $maxSize) {
        \App\Core\Router::jsonResponse(['error' => 'File too large. Max 10MB'], 400);
    }
    $filename = 'doc_' . bin2hex(random_bytes(16)) . '.' . $ext;
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        \App\Core\Router::jsonResponse(['error' => 'Upload directory not available'], 500);
    }
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        \App\Core\Router::jsonResponse(['error' => 'Failed to upload file'], 500);
    }
    @chmod($uploadDir . $filename, 0666);
    $fileHash = hash_file('sha256', $uploadDir . $filename);
    $basePath = rtrim((string) ($_ENV['BASE_PATH'] ?? getenv('BASE_PATH') ?? ''), '/');
    $uploadUrl = ($basePath ? $basePath . '/' : '') . 'uploads/' . $filename;
    \App\Core\Router::jsonResponse([
        'message' => 'File uploaded',
        'file' => [
            'name' => basename($file['name']),
            'url' => $uploadUrl,
            'size' => $file['size'],
            'type' => $detectedType,
            'hash' => $fileHash,
        ]
    ]);
}, [fn() => \App\Middleware\AuthMiddleware::authenticate()]);

// ==================== TEMPLATES ====================
$router->get('/templates', function() {
    \App\Core\Router::jsonResponse([
        ['id' => 1, 'name' => 'Rent Reminder', 'subject' => 'Rent Payment Reminder', 'body' => 'Dear {tenant}, this is a reminder that your rent of {amount} is due on {due_date}.'],
        ['id' => 2, 'name' => 'Payment Receipt', 'subject' => 'Payment Received', 'body' => 'Dear {tenant}, we have received your payment of {amount}. Thank you!'],
        ['id' => 3, 'name' => 'Complaint Acknowledgment', 'subject' => 'Complaint Received', 'body' => 'Dear {tenant}, we have received your complaint regarding {issue}. We will address it shortly.'],
    ]);
});

$router->dispatch();

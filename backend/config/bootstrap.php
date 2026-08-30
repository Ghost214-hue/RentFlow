<?php


// Load environment variables FIRST using custom Env loader (supports all value types)
require_once __DIR__ . '/../app/Core/Env.php';
$envFiles = [dirname(__DIR__, 2) . '/.env.production', dirname(__DIR__, 2) . '/.env'];
foreach ($envFiles as $envPath) {
    if (file_exists($envPath)) {
        \App\Core\Env::load($envPath);
        break;
    }
}

// Detect environment
$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'development';
define('APP_ENV', $appEnv);

// Base paths
define('BASE_PATH', dirname(__DIR__, 2));
define('BACKEND_PATH', dirname(__DIR__));
define('LOGS_PATH', BASE_PATH . '/logs');

// Ensure logs directory exists
if (!file_exists(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

// Load environment-specific configuration
if (APP_ENV === 'production') {
    require_once __DIR__ . '/production.php';
} else {
    // Development configuration
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('error_log', LOGS_PATH . '/development.log');
}

// Database configuration
require_once __DIR__ . '/database.php';

// JWT configuration
require_once __DIR__ . '/jwt.php';

// Register autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BACKEND_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

// Application meta
const APP_NAME = 'RentaFlow';
const APP_VERSION = '1.0.0';

// API Routes
const API_PREFIX = '/api/v1';
const BACKEND_PREFIX = '/backend';

// Security
const SESSION_LIFETIME = 3600;
const REMEMBER_TOKEN_LIFETIME = 604800;
const PASSWORD_RESET_TIMEOUT = 3600;

// Rate Limiting
const RATE_LIMIT_ENABLED = true;
const RATE_LIMIT_REQUESTS = 100;
const RATE_LIMIT_WINDOW = 3600;

// File Upload
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_FILE_TYPES = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'csv'];
const UPLOAD_DIRECTORY = BACKEND_PATH . '/public/uploads';

// Ensure upload directory exists
if (!file_exists(UPLOAD_DIRECTORY)) {
    mkdir(UPLOAD_DIRECTORY, 0755, true);
}

return [
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'env' => APP_ENV,
        'debug' => APP_ENV !== 'production',
    ],
    'paths' => [
        'base' => BASE_PATH,
        'backend' => BACKEND_PATH,
        'logs' => LOGS_PATH,
        'uploads' => UPLOAD_DIRECTORY,
    ],
    'security' => [
        'sessionLifetime' => SESSION_LIFETIME,
        'rememberTokenLifetime' => REMEMBER_TOKEN_LIFETIME,
        'passwordResetTimeout' => PASSWORD_RESET_TIMEOUT,
        'rateLimitEnabled' => RATE_LIMIT_ENABLED,
        'rateLimitRequests' => RATE_LIMIT_REQUESTS,
        'rateLimitWindow' => RATE_LIMIT_WINDOW,
    ],
    'upload' => [
        'maxFileSize' => MAX_FILE_SIZE,
        'allowedTypes' => ALLOWED_FILE_TYPES,
        'directory' => UPLOAD_DIRECTORY,
    ],
];

<?php

/**
 * Production Error Handler Configuration
 * Place this in backend/config/production.php
 * Include it early in your application bootstrap
 */

// Disable error display in production
if (!defined('APP_ENV')) {
    define('APP_ENV', $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production');
}

if (APP_ENV === 'production') {
    error_reporting(~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Set error log location
$logPath = dirname(__DIR__) . '/logs/error.log';
if (!file_exists(dirname($logPath))) {
    mkdir(dirname($logPath), 0755, true);
}
ini_set('error_log', $logPath);

/**
 * Custom error handler
 */
function production_error_handler($errno, $errstr, $errfile, $errline)
{
    $timestamp = date('Y-m-d H:i:s');
    $error_message = "[$timestamp] [$errno] $errstr in $errfile:$errline\n";
    
    // Log the error
    error_log($error_message);
    
    // In production, don't expose error details
    if (APP_ENV === 'production') {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error'], JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    return true;
}

set_error_handler('production_error_handler');

/**
 * Exception handler
 */
function production_exception_handler($exception)
{
    $timestamp = date('Y-m-d H:i:s');
    $error_message = "[$timestamp] Exception: " . $exception->getMessage() . 
                     " in " . $exception->getFile() . ":" . $exception->getLine() . "\n";
    
    error_log($error_message);
    
    http_response_code(500);
    
    if (APP_ENV === 'production') {
        echo json_encode(['error' => 'Internal server error'], JSON_UNESCAPED_SLASHES);
    } else {
        echo $exception->getMessage();
    }
    
    exit;
}

set_exception_handler('production_exception_handler');

/**
 * Security headers
 */
if (php_sapi_name() !== 'cli') {
    // Remove server info
    header_remove('X-Powered-By');
    header_remove('Server');
    
    // Prevent caching sensitive pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    
    // HTTPS enforcement in production
    if (APP_ENV === 'production' && empty($_SERVER['HTTPS'])) {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $url", true, 301);
        exit;
    }
}

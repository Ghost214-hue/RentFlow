<?php
/**
 * Base Secure Controller with OWASP security measures
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\BotProtectionMiddleware;
use App\Middleware\SecurityAuditMiddleware;

class SecureController
{
    /**
     * Security headers for all responses
     */
    protected static function sendSecurityHeaders(): void
    {
        // Prevent XSS
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        
        // HTTPS enforcement (if using HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'");
        
        // Cache control for sensitive pages
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * CSRF Token generation and validation
     */
    protected static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }

    protected static function validateCsrfToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        // Token expires after 2 hours
        if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 7200) {
            unset($_SESSION['csrf_token']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Input sanitization helper
     */
    protected static function sanitizeInput($value): mixed
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeInput'], $value);
        }
        
        if (is_string($value)) {
            // Remove null bytes
            $value = str_replace(chr(0), '', $value);
            // Trim whitespace
            $value = trim($value);
            // Normalize line endings
            $value = preg_replace("/\r\n|\r/", "\n", $value);
        }
        
        return $value;
    }

    /**
     * Validate and sanitize request body
     */
    protected static function getSanitizedRequestBody(): array
    {
        $data = Router::getRequestBody();
        return self::sanitizeInput($data);
    }

    /**
     * Common validation rules
     */
    protected static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }

    protected static function validatePhone(string $phone): bool
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        // Check if it's a valid phone number (7-15 digits, may start with +)
        return preg_match('/^\+?[0-9]{7,15}$/', $phone);
    }

    /**
     * Check if request method matches expected
     */
    protected static function validateMethod(string $expected): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        if ($method !== $expected) {
            Router::jsonResponse(['error' => 'Method not allowed'], 405);
        }
    }

    /**
     * Apply rate limiting with automatic key generation
     */
    protected static function applyRateLimit(string $type, ?string $key = null): void
    {
        RateLimitMiddleware::check($type);
    }

    /**
     * Log a security event
     */
    protected static function logSecurity(string $action, ?int $userId = null, string $severity = 'info', array $details = []): void
    {
        SecurityAuditMiddleware::log($action, $userId, null, null, $severity, $details);
    }

    /**
     * Validate file upload security
     */
    protected static function validateFileUpload(array $file, array $allowedTypes = [], int $maxSize = 5242880): array
    {
        $errors = [];
        
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum limit of ' . ($maxSize / 1024 / 1024) . 'MB';
        }
        
        // Check file type using MIME type and extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = array_column($allowedTypes, 'ext');
        
        if (!empty($allowedTypes) && !in_array($extension, $allowedExtensions)) {
            $errors[] = 'File type not allowed';
        }
        
        // Check for PHP files in uploads
        if ($extension === 'php' || $mimeType === 'application/x-php') {
            $errors[] = 'PHP files are not allowed';
        }
        
        // Check for executable files
        $executableExtensions = ['exe', 'bat', 'cmd', 'sh', 'ps1', 'vbs', 'js'];
        if (in_array($extension, $executableExtensions)) {
            $errors[] = 'Executable files are not allowed';
        }
        
        return $errors;
    }

    /**
     * Secure file upload handler
     */
    protected static function handleFileUpload(array $file, string $uploadDir, array $allowedTypes = []): ?string
    {
        $errors = self::validateFileUpload($file, $allowedTypes);
        
        if (!empty($errors)) {
            return null;
        }
        
        // Generate secure random filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $uploadDir . '/' . $newFilename;
        
        // Ensure upload directory exists and is secure
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0750, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Set secure permissions
            chmod($targetPath, 0640);
            return $newFilename;
        }
        
        return null;
    }
}
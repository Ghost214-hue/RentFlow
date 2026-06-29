<?php
/**
 * Email Test Script
 * Run this to test email sending and check configuration
 */

// Register autoloader manually
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Debug: check if EmailService file exists
$serviceFile = __DIR__ . '/app/Services/EmailService.php';
if (!file_exists($serviceFile)) {
    die("ERROR: EmailService.php not found at: $serviceFile\n");
}

// Load environment
App\Core\Env::load(__DIR__ . '/../.env');

// Manually include EmailService if autoloader fails
if (!class_exists('App\Services\EmailService')) {
    require_once $serviceFile;
}

$emailService = new \App\Services\EmailService();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Email Configuration Test ===\n\n";

// Check if .env is loaded
echo "Environment Variables:\n";
echo "MAIL_USE_SMTP: " . ($_ENV['MAIL_USE_SMTP'] ?? 'NOT SET') . "\n";
echo "MAIL_HOST: " . ($_ENV['MAIL_HOST'] ?? 'NOT SET') . "\n";
echo "MAIL_PORT: " . ($_ENV['MAIL_PORT'] ?? 'NOT SET') . "\n";
echo "MAIL_USERNAME: " . ($_ENV['MAIL_USERNAME'] ?? 'NOT SET') . "\n";
echo "MAIL_PASSWORD: " . ($_ENV['MAIL_PASSWORD'] ? '***SET***' : 'NOT SET') . "\n";
echo "MAIL_FROM_EMAIL: " . ($_ENV['MAIL_FROM_EMAIL'] ?? 'NOT SET') . "\n";
echo "MAIL_ENCRYPTION: " . ($_ENV['MAIL_ENCRYPTION'] ?? 'NOT SET') . "\n\n";

// Check if required extensions are loaded
echo "PHP Extensions:\n";
echo "openssl: " . (extension_loaded('openssl') ? 'LOADED' : 'MISSING') . "\n";
echo "sockets: " . (extension_loaded('sockets') ? 'LOADED' : 'MISSING') . "\n\n";

// Try sending a test email
echo "Attempting to send test email...\n";

try {
    $sent = $emailService->send(
        $_ENV['MAIL_USERNAME'], // Send to yourself
        'Test Recipient',
        'RentFlow Email Test',
        "Hello!\n\nThis is a test email from RentFlow.\n\nIf you receive this, email is working!\n\nSent at: " . date('Y-m-d H:i:s')
    );
    
    echo "\nResult: " . ($sent ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    echo "Check /var/log/apache2/error.log for detailed SMTP logs\n";
    echo "Or run: tail -f backend/logs/email.log\n";
    
} catch (\Exception $e) {
    echo "\n✗ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Common Gmail SMTP Issues ===\n";
echo "1. Use an App Password (not your regular password) if 2FA is enabled\n";
echo "2. Enable 'Less secure app access' in Google Account settings\n";
echo "3. Make sure MAIL_FROM_EMAIL matches your Gmail address\n";
echo "4. Check port 587 is not blocked by firewall\n";
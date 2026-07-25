<?php
/**
 * Minimal email test - no bootstrap, no database
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";

// Manually load env
$envPath = __DIR__ . '/../.env.production';
echo "ENV PATH: $envPath\n";
echo "EXISTS: " . (file_exists($envPath) ? 'YES' : 'NO') . "\n";
echo "READABLE: " . (is_readable($envPath) ? 'YES' : 'NO') . "\n";

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

echo "\nMAIL_PASSWORD from getenv: " . (getenv('MAIL_PASSWORD') ? 'SET (' . strlen(getenv('MAIL_PASSWORD')) . ' chars)' : 'NOT SET') . "\n";
echo "MAIL_PASSWORD from \$_ENV: " . (isset($_ENV['MAIL_PASSWORD']) ? 'SET (' . strlen($_ENV['MAIL_PASSWORD']) . ' chars)' : 'NOT SET') . "\n";

// Now manually include EmailService
echo "\nLoading EmailService...\n";
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Services/EmailQueueService.php';
require_once __DIR__ . '/app/Services/NotificationRecipientService.php';
require_once __DIR__ . '/app/Services/EmailService.php';

echo "Creating EmailService...\n";
try {
    $emailService = new \App\Services\EmailService();
    echo "EmailService created successfully\n";
    
    echo "\nSending test email...\n";
    $sent = $emailService->send('karenjuduncan750@gmail.com', 'Test User', 'Minimal Test', 'This is a minimal test from the web server');
    echo "Result: " . ($sent ? 'SUCCESS' : 'FAILED') . "\n";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Done ===\n";
echo "</pre>";
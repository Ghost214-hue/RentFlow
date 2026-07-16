<?php
/**
 * Manual email queue processor
 * Run this to process pending emails immediately:
 * php backend/process_queue.php
 */

// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/../app/Core/Env.php';
use App\Core\Env;
use App\Services\EmailQueueService;

Env::load(__DIR__ . '/../.env');

echo "=== Processing Email Queue ===\n\n";

try {
    $mailUseSmtp = filter_var($_ENV['MAIL_USE_SMTP'] ?? getenv('MAIL_USE_SMTP'), FILTER_VALIDATE_BOOLEAN);
    $mailUsername = $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME');
    $mailPassword = $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD');

    if (!$mailUseSmtp) {
        if (!empty($mailUsername) && !empty($mailPassword)) {
            echo "WARNING: MAIL_USE_SMTP=false but SMTP credentials are configured. Set MAIL_USE_SMTP=true in .env to use SMTP instead of PHP mail().\n\n";
        } else {
            echo "WARNING: MAIL_USE_SMTP=false and SMTP is not configured. PHP mail() will be used and may fail on this host.\n\n";
        }
    }

    $queueService = new EmailQueueService();
    
    // Get pending count first
    $stats = $queueService->getStats();
    echo "Pending emails: {$stats['pending']}\n\n";
    
    if ($stats['pending'] == 0) {
        echo "No pending emails to process.\n";
        exit(0);
    }
    
    // Process emails (batch size 10)
    echo "Processing emails...\n";
    $result = $queueService->process(10);
    
    echo "\nResults:\n";
    echo "  ✓ Processed: {$result['processed']}\n";
    echo "  ✗ Failed: {$result['failed']}\n";
    echo "  Total: {$result['total']}\n";
    
    // Show updated stats
    $stats = $queueService->getStats();
    echo "\nQueue Stats (last 24h):\n";
    echo "  Pending: {$stats['pending']}\n";
    echo "  Processing: {$stats['processing']}\n";
    echo "  Sent: {$stats['sent']}\n";
    echo "  Failed: {$stats['failed']}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nMake sure:\n";
    echo "1. MySQL is running\n";
    echo "2. email_queue table exists\n";
    echo "3. SMTP is configured in .env\n";
    exit(1);
}

echo "\n=== Done ===\n";
exit(0);
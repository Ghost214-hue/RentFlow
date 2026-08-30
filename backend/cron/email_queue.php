<?php


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

Env::load();

$checkMode = in_array('--check', $argv ?? [], true);
if ($checkMode) {
    try {
        $queueService = new EmailQueueService();
        $batchSize = (int) $queueService->getSetting('batch_size', '10');
        $enabled = $queueService->getSetting('enabled', '1') === '1' ? 'yes' : 'no';
        $stats = $queueService->getStats();

        echo "Cron check passed: email queue processor loaded successfully.\n";
        echo "  Batch size: {$batchSize}\n";
        echo "  Queue enabled: {$enabled}\n";
        echo "  Pending: {$stats['pending']}\n";
        echo "  Processing: {$stats['processing']}\n";
        echo "  Sent: {$stats['sent']}\n";
        echo "  Failed: {$stats['failed']}\n";
        exit(0);
    } catch (\Throwable $e) {
        echo "Cron check failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Prevent concurrent processing
$lockFile = __DIR__ . '/../logs/email_queue.lock';
if (!is_dir(dirname($lockFile))) {
    mkdir(dirname($lockFile), 0755, true);
}
if (file_exists($lockFile)) {
    $lockTime = filemtime($lockFile);
    if (time() - $lockTime < 120) { // 2 minute timeout
        echo "Another instance is running. Exiting.\n";
        exit(0);
    }
}

touch($lockFile);

try {
    $queueService = new EmailQueueService();
    
    // Get batch size from settings or use default
    $batchSize = (int) $queueService->getSetting('batch_size', '10');
    
    // Process pending emails
    $result = $queueService->process($batchSize);
    
    echo "Email Queue Processing:\n";
    echo "  Processed: {$result['processed']}\n";
    echo "  Failed: {$result['failed']}\n";
    echo "  Total: {$result['total']}\n";
    
    // If there are failed emails, try to retry them
    if ($result['failed'] > 0) {
        $retryResult = $queueService->retryFailed($batchSize);
        echo "\nRetry Failed:\n";
        echo "  Retried: {$retryResult['retried']}\n";
        echo "  Failed: {$retryResult['failed']}\n";
    }
    
    // Get stats
    $stats = $queueService->getStats();
    echo "\nQueue Stats (last 24h):\n";
    echo "  Pending: {$stats['pending']}\n";
    echo "  Processing: {$stats['processing']}\n";
    echo "  Sent: {$stats['sent']}\n";
    echo "  Failed: {$stats['failed']}\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Email queue processor error: " . $e->getMessage());
} finally {
    @unlink($lockFile);
}

echo "\nDone.\n";
exit(0);

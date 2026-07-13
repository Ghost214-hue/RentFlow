<?php
/**
 * Send Pending Payment Confirmations
 * 
 * Immediately sends all pending payment confirmation emails from the queue.
 * Run this after a payment is recorded to ensure instant delivery.
 * 
 * Usage: php backend/send_pending_emails.php
 */

// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/app/Core/Env.php';
use App\Core\Env;
use App\Services\EmailService;

Env::load(__DIR__ . '/../.env');

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

echo "=== Sending Pending Payment Confirmations ===\n\n";

try {
    $db = App\Core\Database::getInstance();
    $emailService = new EmailService();
    
    // Get all pending payment confirmation emails
    $pendingEmails = $db->fetchAll(
        "SELECT * FROM email_queue 
         WHERE template_name = 'Payment Confirmation' 
         AND status = 'pending'
         AND attempts < max_attempts
         ORDER BY created_at ASC"
    );
    
    echo "Found " . count($pendingEmails) . " pending payment confirmation emails\n\n";
    
    if (empty($pendingEmails)) {
        echo "No pending payment confirmations to send.\n";
        exit(0);
    }
    
    $sent = 0;
    $failed = 0;
    
    foreach ($pendingEmails as $email) {
        echo "Sending to: {$email['recipient_email']}... ";
        
        // Send immediately
        $result = $emailService->send(
            $email['recipient_email'],
            $email['recipient_name'] ?? '',
            $email['subject'],
            $email['body']
        );
        
        if ($result) {
            // Update status to sent
            $db->update('email_queue', [
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$email['id']]);
            
            echo "✓ SENT\n";
            $sent++;
        } else {
            // Mark as failed
            $db->update('email_queue', [
                'status' => 'failed',
                'error_message' => 'SMTP delivery failed - check credentials'
            ], 'id = ?', [$email['id']]);
            
            echo "✗ FAILED (check SMTP credentials in .env)\n";
            $failed++;
        }
    }
    
    echo "\n=== Results ===\n";
    echo "Successfully sent: $sent\n";
    echo "Failed: $failed\n";
    echo "Total processed: " . count($pendingEmails) . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Send pending emails error: " . $e->getMessage());
    exit(1);
}

echo "\n=== Done ===\n";
exit(0);
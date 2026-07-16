<?php
/**
 * Email Test Script
 * Run: php backend/cron/test_email.php
 * 
 * This script tests:
 * 1. Database connection
 * 2. Email template availability
 * 3. SMTP configuration
 * 4. Actual email sending
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
use App\Core\Database;
use App\Services\EmailService;

Env::load(__DIR__ . '/../../.env');

echo "=== RentaFlow Email Test ===\n\n";

// Test 1: Database Connection
echo "[1] Testing Database Connection... ";
try {
    $db = Database::getInstance();
    $db->fetchAll("SELECT id FROM owners LIMIT 1");
    echo "✓ OK\n";
} catch (\Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Email Service Initialization
echo "[2] Testing Email Service... ";
try {
    $emailService = new EmailService();
    echo "✓ OK\n";
} catch (\Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check Email Templates
echo "[3] Checking Email Templates... ";
$templateNames = ['Rent Reminder', 'Password Reset', 'Tenant Welcome', 'Payment Confirmation'];
$foundTemplates = [];
foreach ($templateNames as $name) {
    $template = $emailService->getTemplate($name, 1);
    if ($template) {
        $foundTemplates[] = $name;
        echo "✓ Found '{$name}'\n";
    } else {
        echo "! Missing '{$name}' (will use default)\n";
    }
}

// Test 4: SMTP Configuration
echo "\n[4] Checking SMTP Configuration... ";
$smtpHost = Env::get('MAIL_HOST', '');
$smtpPort = Env::get('MAIL_PORT', '');
$smtpUser = Env::get('MAIL_USERNAME', '');
$smtpPass = Env::get('MAIL_PASSWORD', '');

if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
    echo "✗ INCOMPLETE\n";
    echo "    MAIL_HOST: " . (empty($smtpHost) ? 'MISSING' : 'Set') . "\n";
    echo "    MAIL_USERNAME: " . (empty($smtpUser) ? 'MISSING' : 'Set') . "\n";
    echo "    MAIL_PASSWORD: " . (empty($smtpPass) ? 'MISSING' : 'Set') . "\n";
    exit(1);
}
echo "✓ Configured\n";
echo "    Host: {$smtpHost}:{$smtpPort}\n";
echo "    User: {$smtpUser}\n";

// Test 5: Send Test Email
echo "\n[5] Sending Test Email... ";
echo "\n    Enter recipient email: ";
$recipientEmail = trim(fgets(STDIN));
if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    echo "✗ Invalid email address\n";
    exit(1);
}

$testSubject = 'RentaFlow - Email Test';
$testBody = "Dear User,\n\nThis is a test email from RentaFlow to verify email functionality.\n\nIf you received this, your email configuration is working correctly!\n\nTime: " . date('Y-m-d H:i:s') . "\n\nBest regards,\nRentaFlow Team";

try {
    $sent = $emailService->send(
        $recipientEmail,
        'Test Recipient',
        $testSubject,
        $testBody
    );
    
    if ($sent) {
        echo "✓ SENT SUCCESSFULLY\n";
        echo "    Check inbox: {$recipientEmail}\n";
        echo "    Subject: {$testSubject}\n";
    } else {
        echo "✗ FAILED TO SEND\n";
        echo "    Check logs/error.log for SMTP errors\n";
        echo "    Common issues:\n";
        echo "    - Wrong email/password\n";
        echo "    - Gmail: Use App Password (not regular password)\n";
        echo "    - Firewall blocking port {$smtpPort}\n";
    }
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: Check for overdue bills
echo "\n[6] Checking for Overdue Bills... ";
$overdueCount = $db->fetchOne("
    SELECT COUNT(*) as total 
    FROM bills b
    JOIN houses h ON h.id = b.house_id
    JOIN tenants t ON t.id = h.tenant_id
    WHERE b.status != 'paid'
      AND b.due_date <= CURDATE()
      AND h.status = 'occupied'
      AND t.email IS NOT NULL
      AND TRIM(t.email) != ''
");

if ($overdueCount && $overdueCount['total'] > 0) {
    echo "✓ Found {$overdueCount['total']} overdue bill(s)\n";
    echo "    Run 'php backend/cron/rent_reminders.php' to send reminders\n";
} else {
    echo "! No overdue bills found\n";
    echo "    To test: Update a bill's due_date to past date:\n";
    echo "    UPDATE bills SET due_date = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE status != 'paid';\n";
}

// Summary
echo "\n=== Test Complete ===\n";
echo "Next steps:\n";
echo "1. Check your email at: {$recipientEmail}\n";
echo "2. If not received, check: backend/logs/error.log\n";
echo "3. To send actual reminders: php backend/cron/rent_reminders.php\n";
echo "4. To automate: Add to crontab\n";
echo "   0 8 * * * cd " . dirname(__DIR__) . " && php backend/cron/rent_reminders.php\n";

exit(0);
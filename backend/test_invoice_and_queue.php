<?php
/**
 * Comprehensive test script for:
 * 1. Invoice generation
 * 2. Payment confirmation emails with invoice URLs
 * 3. Email queue system
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
use App\Core\Database;
use App\Core\JWT;
use App\Services\EmailService;
use App\Services\EmailQueueService;
use App\Services\NotificationRecipientService;
use App\Controllers\BillController;

Env::load(__DIR__ . '/../../.env');

echo "=== RentFlow Invoice & Email Queue System Test ===\n\n";

$db = Database::getInstance();
$errors = [];
$warnings = [];

// Test 1: Check database connection
echo "TEST 1: Database Connection\n";
echo "---------------------------\n";
try {
    $result = $db->fetchOne("SELECT 1 as test");
    if ($result && $result['test'] == 1) {
        echo "✓ Database connection successful\n\n";
    } else {
        $errors[] = "Database connection test failed";
        echo "✗ Database connection failed\n\n";
    }
} catch (Exception $e) {
    $errors[] = "Database error: " . $e->getMessage();
    echo "✗ Database error: " . $e->getMessage() . "\n\n";
}

// Test 2: Check email queue table exists
echo "TEST 2: Email Queue Table\n";
echo "-------------------------\n";
try {
    $result = $db->fetchOne("SHOW TABLES LIKE 'email_queue'");
    if ($result) {
        echo "✓ Email queue table exists\n";
        
        // Check table structure
        $columns = $db->fetchAll("DESCRIBE email_queue");
        $columnNames = array_column($columns, 'Field');
        $requiredColumns = ['id', 'owner_id', 'template_name', 'recipient_email', 'subject', 'body', 'status', 'priority', 'attempts'];
        
        foreach ($requiredColumns as $col) {
            if (!in_array($col, $columnNames)) {
                $errors[] = "Missing column: $col";
                echo "✗ Missing column: $col\n";
            }
        }
        if (!in_array('status', $columnNames)) {
            echo "✓ Email queue table structure OK\n\n";
        }
    } else {
        $warnings[] = "Email queue table not found - run migration first";
        echo "⚠ Email queue table not found\n";
        echo "  Run: php backend/database/migrate.php\n\n";
    }
} catch (Exception $e) {
    $warnings[] = "Could not check email_queue table: " . $e->getMessage();
    echo "⚠ Could not check table: " . $e->getMessage() . "\n\n";
}

// Test 3: Check email templates have invoice placeholders
echo "TEST 3: Email Templates - Invoice Placeholders\n";
echo "-----------------------------------------------\n";
try {
    $template = $db->fetchOne(
        "SELECT subject, body FROM email_templates WHERE name = 'Payment Confirmation' AND owner_id = 1"
    );
    
    if ($template) {
        $hasInvoiceUrl = strpos($template['body'], '{{invoice_url}}') !== false;
        $hasInvoiceSection = strpos($template['body'], '{{invoice_section}}') !== false;
        
        if ($hasInvoiceUrl && $hasInvoiceSection) {
            echo "✓ Payment Confirmation template has invoice placeholders\n\n";
        } else {
            $warnings[] = "Template missing invoice placeholders";
            echo "⚠ Template missing invoice placeholders\n";
            echo "  Has {{invoice_url}}: " . ($hasInvoiceUrl ? 'Yes' : 'No') . "\n";
            echo "  Has {{invoice_section}}: " . ($hasInvoiceSection ? 'Yes' : 'No') . "\n";
            echo "  Run: php backend/database/seeders/seed_email_templates.php\n\n";
        }
    } else {
        $warnings[] = "Payment Confirmation template not found";
        echo "⚠ Payment Confirmation template not found\n\n";
    }
} catch (Exception $e) {
    $warnings[] = "Could not check templates: " . $e->getMessage();
    echo "⚠ Could not check templates: " . $e->getMessage() . "\n\n";
}

// Test 4: Test EmailQueueService
echo "TEST 4: Email Queue Service\n";
echo "----------------------------\n";
try {
    $queueService = new EmailQueueService();
    $stats = $queueService->getStats();
    
    echo "✓ EmailQueueService initialized\n";
    echo "  Queue stats (last 24h):\n";
    echo "    Pending: {$stats['pending']}\n";
    echo "    Processing: {$stats['processing']}\n";
    echo "    Sent: {$stats['sent']}\n";
    echo "    Failed: {$stats['failed']}\n";
    echo "    Total: {$stats['total']}\n\n";
} catch (Exception $e) {
    $errors[] = "EmailQueueService error: " . $e->getMessage();
    echo "✗ EmailQueueService error: " . $e->getMessage() . "\n\n";
}

// Test 5: Test queueing an email
echo "TEST 5: Queue Test Email\n";
echo "------------------------\n";
try {
    $queueService = new EmailQueueService();
    $ownerId = 1; // System owner
    
    $queued = $queueService->queue(
        $ownerId,
        'Test Queue',
        'test@example.com',
        'Test User',
        'Test Subject',
        'Test Body',
        10, // High priority
        new DateTime()
    );
    
    if ($queued) {
        echo "✓ Email queued successfully\n";
        echo "  Check email_queue table for pending email\n\n";
    } else {
        $errors[] = "Failed to queue test email";
        echo "✗ Failed to queue test email\n\n";
    }
} catch (Exception $e) {
    $errors[] = "Queue test error: " . $e->getMessage();
    echo "✗ Queue test error: " . $e->getMessage() . "\n\n";
}

// Test 6: Check tenants with next of kin
echo "TEST 6: Tenants with Next of Kin\n";
echo "---------------------------------\n";
try {
    $tenants = $db->fetchAll("
        SELECT t.id, t.name, t.email, t.next_of_kin_name, t.next_of_kin_email 
        FROM tenants t
        WHERE t.owner_id = 1
          AND t.next_of_kin_email IS NOT NULL 
          AND TRIM(t.next_of_kin_email) != ''
        LIMIT 5
    ");
    
    if (empty($tenants)) {
        echo "⚠ No tenants with Next of Kin found\n";
        echo "  Create a tenant with next_of_kin_email to test invoice emails\n\n";
    } else {
        echo "✓ Found " . count($tenants) . " tenant(s) with Next of Kin:\n";
        foreach ($tenants as $t) {
            echo "  - {$t['name']} ({$t['email']})\n";
            echo "    Next of Kin: {$t['next_of_kin_name']} ({$t['next_of_kin_email']})\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    $warnings[] = "Could not check tenants: " . $e->getMessage();
    echo "⚠ Could not check tenants: " . $e->getMessage() . "\n\n";
}

// Test 7: Check bills with payments
echo "TEST 7: Bills and Payments\n";
echo "---------------------------\n";
try {
    $bills = $db->fetchAll("
        SELECT b.id, b.month, b.total, b.status, t.name as tenant_name, t.email
        FROM bills b
        LEFT JOIN tenants t ON b.tenant_id = t.id
        WHERE b.owner_id = 1
        LIMIT 5
    ");
    
    if (empty($bills)) {
        echo "⚠ No bills found\n";
        echo "  Generate bills first to test invoice generation\n\n";
    } else {
        echo "✓ Found " . count($bills) . " bill(s):\n";
        foreach ($bills as $bill) {
            echo "  - Bill #{$bill['id']}: {$bill['month']} - KES {$bill['total']} ({$bill['status']})\n";
            echo "    Tenant: {$bill['tenant_name']} ({$bill['email']})\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    $warnings[] = "Could not check bills: " . $e->getMessage();
    echo "⚠ Could not check bills: " . $e->getMessage() . "\n\n";
}

// Test 8: Test JWT token generation for invoices
echo "TEST 8: JWT Token Generation\n";
echo "-----------------------------\n";
try {
    $jwt = new JWT();
    $testPayload = [
        'owner_id' => 1,
        'actor_id' => 1,
        'role' => 'tenant',
        'tenant_id' => 1
    ];
    
    $token = $jwt->encode($testPayload);
    $decoded = $jwt->decode($token);
    
    if ($decoded && $decoded['owner_id'] == 1 && $decoded['role'] == 'tenant') {
        echo "✓ JWT token generation works\n";
        echo "  Token: " . substr($token, 0, 50) . "...\n\n";
    } else {
        $errors[] = "JWT token decode failed";
        echo "✗ JWT token decode failed\n\n";
    }
} catch (Exception $e) {
    $errors[] = "JWT error: " . $e->getMessage();
    echo "✗ JWT error: " . $e->getMessage() . "\n\n";
}

// Test 9: Check if invoice endpoint is accessible
echo "TEST 9: Invoice Endpoint Check\n";
echo "--------------------------------\n";
try {
    $billController = new BillController();
    
    // Check if invoice method exists
    if (method_exists($billController, 'invoice')) {
        echo "✓ BillController::invoice() method exists\n";
    } else {
        $errors[] = "BillController::invoice() method not found";
        echo "✗ BillController::invoice() method not found\n";
    }
    
    // Check if queue method exists
    if (method_exists($queueService, 'process')) {
        echo "✓ EmailQueueService::process() method exists\n";
    } else {
        $errors[] = "EmailQueueService::process() method not found";
        echo "✗ EmailQueueService::process() method not found\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    $errors[] = "Endpoint check error: " . $e->getMessage();
    echo "✗ Endpoint check error: " . $e->getMessage() . "\n\n";
}

// Test 10: Check .env configuration
echo "TEST 10: Configuration Check\n";
echo "-----------------------------\n";
$smtpConfigured = !empty(getenv('MAIL_USERNAME')) && !empty(getenv('MAIL_PASSWORD'));
$queueEnabled = filter_var(getenv('MAIL_QUEUE_ENABLED') ?: $_ENV['MAIL_QUEUE_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN);

echo "SMTP Configured: " . ($smtpConfigured ? '✓ Yes' : '✗ No') . "\n";
echo "Queue Enabled: " . ($queueEnabled ? '✓ Yes' : '✗ No') . "\n";
echo "App URL: " . (getenv('APP_URL') ?: $_ENV['APP_URL'] ?? 'Not set') . "\n\n";

if (!$smtpConfigured) {
    $warnings[] = "SMTP not configured - emails won't send";
    echo "⚠ SMTP not configured in .env\n\n";
}

// Summary
echo "=== TEST SUMMARY ===\n";
echo "-------------------\n";

if (empty($errors)) {
    echo "✓ All critical tests passed!\n\n";
} else {
    echo "✗ Errors found:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠ Warnings:\n";
    foreach ($warnings as $warning) {
        echo "  - $warning\n";
    }
    echo "\n";
}

// Manual testing instructions
echo "=== MANUAL TESTING STEPS ===\n";
echo "----------------------------\n";
echo "1. Update email templates:\n";
echo "   php backend/database/seeders/seed_email_templates.php\n\n";
echo "2. Create email queue table:\n";
echo "   php backend/database/migrate.php\n\n";
echo "3. Setup cron job for queue processing:\n";
echo "   crontab -e\n";
echo "   Add: * * * * * php /opt/lampp/htdocs/RentFlow/backend/cron/email_queue.php\n\n";
echo "4. Test invoice generation:\n";
echo "   - Go to Bills page\n";
echo "   - Click 'Invoice' button on any bill\n";
echo "   - Verify invoice opens in new tab with print dialog\n\n";
echo "5. Test payment confirmation:\n";
echo "   - Record a payment for a tenant\n";
echo "   - Check email inbox for payment confirmation\n";
echo "   - Verify email contains invoice download link\n";
echo "   - Verify email sent to next of kin (if configured)\n\n";
echo "6. Test email queue:\n";
echo "   - Record multiple payments\n";
echo "   - Run: php backend/cron/email_queue.php\n";
echo "   - Check logs for 'Processed' messages\n\n";

$hasErrors = !empty($errors);
exit($hasErrors ? 1 : 0);
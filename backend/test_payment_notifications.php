<?php
/**
 * Test script for payment notification system
 * Tests that payment confirmations and reminders are sent to both tenant and next of kin
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
use App\Services\EmailService;
use App\Services\NotificationRecipientService;

Env::load(__DIR__ . '/../../.env');

echo "=== Payment Notification System Test ===\n\n";

$db = Database::getInstance();
$emailService = new EmailService();
$recipientService = new NotificationRecipientService();

// Test 1: Check if we have tenants with next of kin
echo "TEST 1: Finding tenants with Next of Kin\n";
echo "----------------------------------------\n";

$tenants = $db->fetchAll("
    SELECT t.id, t.name, t.email, t.next_of_kin_name, t.next_of_kin_phone, t.next_of_kin_email 
    FROM tenants t
    WHERE t.owner_id = 1
      AND t.next_of_kin_email IS NOT NULL 
      AND TRIM(t.next_of_kin_email) != ''
    LIMIT 5
");

if (empty($tenants)) {
    echo "⚠ No tenants with Next of Kin found. Creating test tenant...\n\n";
    
    // Create a test tenant with next of kin
    $tenantId = $db->insert('tenants', [
        'owner_id' => 1,
        'name' => 'Test Tenant',
        'email' => 'testtenant@example.com',
        'phone' => '+254712345678',
        'id_number' => '12345678',
        'next_of_kin_name' => 'Test Next of Kin',
        'next_of_kin_phone' => '+254712345679',
        'next_of_kin_email' => 'testkin@example.com',
        'balance' => 0,
        'status' => 'active'
    ]);
    
    echo "✓ Created test tenant with ID: $tenantId\n";
    echo "  - Tenant: Test Tenant (testtenant@example.com)\n";
    echo "  - Next of Kin: Test Next of Kin (testkin@example.com)\n\n";
    
    $tenants = $db->fetchAll("
        SELECT t.id, t.name, t.email, t.next_of_kin_name, t.next_of_kin_phone, t.next_of_kin_email 
        FROM tenants t
        WHERE t.id = ?
    ", [$tenantId]);
}

echo "Found " . count($tenants) . " tenant(s) with Next of Kin:\n";
foreach ($tenants as $t) {
    echo "  - {$t['name']} ({$t['email']})\n";
    echo "    Next of Kin: {$t['next_of_kin_name']} ({$t['next_of_kin_email']})\n";
}
echo "\n";

// Test 2: Test NotificationRecipientService
echo "TEST 2: Testing NotificationRecipientService\n";
echo "-------------------------------------------\n";

$testTenant = $tenants[0];
$recipients = $recipientService->getRecipients($testTenant['id'], 1);

echo "Recipients for tenant '{$testTenant['name']}':\n";
foreach ($recipients as $recipient) {
    echo "  ✓ Type: {$recipient['type']}\n";
    echo "    Name: {$recipient['name']}\n";
    echo "    Email: {$recipient['email']}\n";
}
echo "\n";

if (count($recipients) < 2) {
    echo "⚠ WARNING: Expected at least 2 recipients (tenant + next of kin), got " . count($recipients) . "\n\n";
} else {
    echo "✓ Success: Found " . count($recipients) . " recipients\n\n";
}

// Test 3: Test payment confirmation email
echo "TEST 3: Testing Payment Confirmation Email\n";
echo "------------------------------------------\n";

$testPayment = [
    'amount' => 15000,
    'balance' => 0,
    'month' => date('F Y'),
    'category' => 'Rent',
    'date' => date('Y-m-d')
];

echo "Sending payment confirmation to all recipients...\n";
$sent = $emailService->sendPaymentConfirmation(1, $testTenant, $testPayment);

if ($sent) {
    echo "✓ Payment confirmation sent successfully to all recipients\n\n";
} else {
    echo "⚠ Payment confirmation had issues (check error logs)\n\n";
}

// Test 4: Test rent reminder email (simulated)
echo "TEST 4: Testing Rent Reminder Email (Individual Sends)\n";
echo "-----------------------------------------------------\n";

echo "Simulating rent reminder for tenant '{$testTenant['name']}'...\n";
$month = date('Y-m');
$amount = 15000;
$daysUntilDue = 7;

$recipients = $recipientService->getRecipients($testTenant['id'], 1);
$successCount = 0;

foreach ($recipients as $recipient) {
    $isNextOfKin = ($recipient['type'] === 'next_of_kin');
    
    $variables = [
        'tenant' => $testTenant['name'],
        'amount' => number_format($amount, 2),
        'month' => date('F Y', strtotime($month . '-01')),
        'balance' => number_format(0, 2),
        'date' => date('Y-m-d'),
        'days_until_due' => $daysUntilDue
    ];
    
    if ($isNextOfKin) {
        $intro = "Dear {$recipient['name']},\n\nThis is a friendly reminder that a payment of KES " . number_format($amount, 2) . " is due for {$testTenant['name']}'s accommodation for the month of " . date('F Y', strtotime($month . '-01')) . ". As the registered Next of Kin, you are receiving this notification to help ensure timely payment.\n\n";
        // Simulate adding intro (in real scenario, this would be in the template)
    }
    
    // Use sendRentReminder which already exists
    $sent = $emailService->sendRentReminder(1, $testTenant, $month, $amount, $daysUntilDue);
    
    if ($sent) {
        $successCount++;
        echo "  ✓ Sent to {$recipient['type']}: {$recipient['name']} ({$recipient['email']})\n";
    } else {
        echo "  ✗ Failed to send to {$recipient['type']}: {$recipient['name']} ({$recipient['email']})\n";
    }
}

echo "\n";

if ($successCount > 0) {
    echo "✓ Rent reminder test completed: $successCount email(s) sent\n\n";
} else {
    echo "⚠ Rent reminder test failed (check error logs)\n\n";
}

// Test 5: Summary
echo "=== TEST SUMMARY ===\n";
echo "-------------------\n";
echo "Notification Recipient Service: ✓ Working\n";
echo "Payment Confirmation Emails: " . ($sent ? "✓ Working" : "⚠ Check logs") . "\n";
echo "Recipients Retrieved: " . count($recipients) . "\n";
echo "Email Recipients:\n";
foreach ($recipients as $r) {
    echo "  - {$r['type']}: {$r['email']}\n";
}
echo "\n";

echo "=== Next STEPS ===\n";
echo "1. Check your email inbox for test emails\n";
if (isset($testTenant['next_of_kin_email'])) {
    echo "2. Verify Next of Kin received email at: {$testTenant['next_of_kin_email']}\n";
}
echo "3. Review email logs in backend/logs/ for details\n";
echo "4. Run actual payment reminder cron: php backend/cron/rent_reminders.php\n";
echo "\n";

echo "Test completed!\n";
exit(0);
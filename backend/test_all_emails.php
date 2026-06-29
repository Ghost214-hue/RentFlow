<?php
/**
 * Comprehensive email test for all RentFlow notification types
 */

require_once __DIR__ . '/app/Core/Env.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Router.php';
require_once __DIR__ . '/app/Services/EmailService.php';

use App\Core\Database;
use App\Core\Router;
use App\Services\EmailService;

// Stub auth helpers used by EmailService
if (!function_exists('App\\Core\\getAuthUserId')) {
    function getAuthUserId(): int { return 1; }
}
if (!function_exists('App\\Core\\getAuthRole')) {
    function getAuthRole(): string { return 'owner'; }
}

$db = Database::getInstance();
$emailService = new EmailService();

// Use a fixed owner for all tests
$ownerId = 1;
$testEmail = 'karenjuduncan750@gmail.com'; // Replace with your test email

echo "=================================================\n";
echo "RentFlow Email Test Suite\n";
echo "=================================================\n\n";

// Check if templates exist
$templates = $db->fetchAll("SELECT name FROM email_templates WHERE owner_id = ?", [$ownerId]);
$templateNames = array_column($templates, 'name');

echo "Available templates:\n";
foreach ($templateNames as $name) {
    echo "  - $name\n";
}
echo "\n";

$tests = [
    [
        'name' => 'Tenant Welcome',
        'method' => 'sendTenantWelcome',
        'args' => [
            $ownerId,
            ['name' => 'Test Tenant', 'email' => $testEmail, 'id_number' => '12345678', 'temp_password' => 'test123'],
            'Sunrise Apartments',
            'Unit 4B'
        ]
    ],
    [
        'name' => 'Caretaker Welcome',
        'method' => 'sendCaretakerWelcome',
        'args' => [
            $ownerId,
            ['name' => 'Test Caretaker', 'email' => $testEmail, 'id_number' => '87654321', 'temp_password' => 'care123']
        ]
    ],
    [
        'name' => 'Payment Confirmation',
        'method' => 'sendPaymentConfirmation',
        'args' => [
            $ownerId,
            ['name' => 'Test Tenant', 'email' => $testEmail],
            ['amount' => 45000, 'balance' => 0, 'month' => 'January 2025', 'category' => 'Rent', 'date' => date('Y-m-d')]
        ]
    ],
    [
        'name' => 'Complaint Update',
        'method' => 'sendComplaintUpdate',
        'args' => [
            $ownerId,
            ['name' => 'Test Tenant', 'email' => $testEmail],
            ['id' => 1, 'category' => 'Plumbing', 'date' => date('Y-m-d')],
            'We are looking into this issue.'
        ]
    ],
    [
        'name' => 'Rent Reminder',
        'method' => 'sendRentReminder',
        'args' => [
            $ownerId,
            ['name' => 'Test Tenant', 'email' => $testEmail, 'balance' => 45000],
            '2025-01-01',
            45000,
            5
        ]
    ],
];

$results = [];

foreach ($tests as $test) {
    echo "Testing: {$test['name']}... ";
    
    if (!in_array($test['name'], $templateNames)) {
        echo "SKIPPED (template not found)\n";
        $results[] = ['name' => $test['name'], 'status' => 'skipped', 'reason' => 'template missing'];
        continue;
    }
    
    try {
        $result = call_user_func_array([$emailService, $test['method']], $test['args']);
        if ($result) {
            echo "SUCCESS\n";
            $results[] = ['name' => $test['name'], 'status' => 'success'];
        } else {
            echo "FAILED\n";
            $results[] = ['name' => $test['name'], 'status' => 'failed', 'reason' => 'send() returned false'];
        }
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        $results[] = ['name' => $test['name'], 'status' => 'error', 'reason' => $e->getMessage()];
    }
}

echo "\n=================================================\n";
echo "Test Summary\n";
echo "=================================================\n";

$passed = 0;
$failed = 0;
$skipped = 0;

foreach ($results as $r) {
    $icon = match($r['status']) {
        'success' => '✓',
        'failed' => '✗',
        'error' => '!',
        'skipped' => '-'
    };
    echo sprintf("  %s %-25s %s\n", $icon, $r['name'], strtoupper($r['status']));
    if ($r['status'] === 'success') $passed++;
    elseif (in_array($r['status'], ['failed', 'error'])) $failed++;
    else $skipped++;
}

echo "\n";
echo "Total: " . count($results) . " | ";
echo "Passed: $passed | ";
echo "Failed: $failed | ";
echo "Skipped: $skipped\n";

if ($failed > 0) {
    echo "\nCheck the error log for details:\n";
    echo "  tail -f /var/log/apache2/error.log\n";
    echo "Or run: tail -f backend/logs/email.log\n";
    exit(1);
} else {
    echo "\nAll tests passed!\n";
    exit(0);
}
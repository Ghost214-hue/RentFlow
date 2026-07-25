<?php
/**
 * Diagnose email sending - tests the EXACT same code as the API endpoint
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";

echo "1. Loading bootstrap...\n";
require_once __DIR__ . '/config/bootstrap.php';

echo "2. Checking if EmailService SMTP credentials load...\n";
$emailService = new \App\Services\EmailService();

echo "3. Attempting direct send (same as forgotPassword does now)...\n";
$sent = $emailService->send('karenjuduncan750@gmail.com', 'Test User', 'Diagnostic Test', 'This is a direct send test from EmailService');
echo "Result: " . ($sent ? 'SUCCESS' : 'FAILED') . "\n";

echo "\n4. Attempting sendTemplate for 'Password Reset' on owner_id=9...\n";
$sent2 = $emailService->sendTemplate('Password Reset', 9, 'karenjuduncan750@gmail.com', 'Test User', ['code' => '123456', 'expires' => '15 minutes', 'name' => 'Test User']);
echo "Result: " . ($sent2 ? 'SUCCESS' : 'FAILED') . "\n";

echo "\n5. Checking email_templates table directly...\n";
use App\Core\Database;
$db = Database::getInstance();
$templates = $db->fetchAll("SELECT * FROM email_templates WHERE owner_id = 9 AND name = 'Password Reset' AND type = 'email'");
if (empty($templates)) {
    echo "No Password Reset template for owner_id=9\n";
    $templates = $db->fetchAll("SELECT * FROM email_templates WHERE owner_id = 1 AND name = 'Password Reset' AND type = 'email'");
    if (!empty($templates)) {
        echo "Found for owner_id=1 instead:\n";
        print_r($templates[0]);
    } else {
        echo "Template not found anywhere!\n";
    }
} else {
    echo "Template found:\n";
    print_r($templates[0]);
}

echo "\n=== Done ===\n";
echo "</pre>";
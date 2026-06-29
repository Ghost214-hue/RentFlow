<?php
/**
 * Seed Password Reset email template
 */
require_once __DIR__ . '/../../app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../../app/Core/Database.php';

$db = \App\Core\Database::getInstance();

$existing = $db->fetchOne("SELECT id FROM email_templates WHERE name = 'Password Reset'");
if (!$existing) {
    $db->insert('email_templates', [
        'owner_id' => 1,
        'name' => 'Password Reset',
        'type' => 'email',
        'subject' => 'Password Reset Code - RentFlow',
        'body' => "Dear {{name}},\n\nWe received a request to reset your password for your RentFlow account.\n\nYour verification code is: {{code}}\n\nThis code will expire in {{expires}}.\n\nIf you did not request a password reset, please ignore this email.\n\nTo reset your password:\n1. Enter the verification code above\n2. Create a new secure password\n\nBest regards,\nRentFlow Team",
        'created_at' => date('Y-m-d H:i:s')
    ]);
    echo "Password Reset template created.\n";
} else {
    echo "Password Reset template already exists.\n";
}
<?php
/**
 * Quick seeder: insert email templates only (no migrations)
 */

require_once __DIR__ . '/../../app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance();

// Create email_templates table if it doesn't exist
$db->query("CREATE TABLE IF NOT EXISTS email_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'email',
    subject VARCHAR(500) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_owner_id (owner_id),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Check if templates already exist
$existing = $db->fetchOne("SELECT COUNT(*) as count FROM email_templates WHERE owner_id = 1");
if ($existing && $existing['count'] > 0) {
    echo "Email templates already exist. Skipping.\n";
    exit;
}

$templates = [
    [
        'owner_id' => 1,
        'name' => 'Tenant Welcome',
        'type' => 'email',
        'subject' => 'Welcome to {{property}} - Your New Home',
        'body' => "Dear {{tenant}},\n\nWelcome to {{property}}! We're excited to have you as our new tenant.\n\nHere are your details:\n- Property: {{property}}\n- Unit/House: {{house}}\n- Email: {{email}}\n- Password: {{password}}\n\nYou can login to your tenant portal at: {{link}}\n\nPlease keep your login credentials secure. For security reasons, we recommend changing your password after your first login.\n\nIf you have any questions, feel free to reach out.\n\nWelcome home!\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Caretaker Welcome',
        'type' => 'email',
        'subject' => 'Welcome to RentFlow - Caretaker Account',
        'body' => "Dear {{tenant}},\n\nWelcome to RentFlow! You have been added as a caretaker.\n\nHere are your login details:\n- Email: {{email}}\n- Password: {{password}}\n\nYou can login to your caretaker portal at: {{link}}\n\nYour responsibilities include managing assigned properties and handling maintenance requests.\n\nIf you have any questions, please contact the property owner.\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Payment Confirmation',
        'type' => 'email',
        'subject' => 'Payment Confirmation - KES {{amount}}',
        'body' => "Hi {{tenant}},\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES {{amount}}\n- Category: {{category}}\n- Date: {{date}}\n- Current Balance: KES {{balance}}\n\nThank you for your payment!\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Complaint Update',
        'type' => 'email',
        'subject' => 'Update on Your Complaint #{{id}}',
        'body' => "Dear {{tenant}},\n\nThis is to inform you that your complaint regarding {{category}} has been received.\n\nComplaint ID: #{{id}}\nDate: {{date}}\n\nWe will review your complaint and get back to you shortly.\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Complaint Reply',
        'type' => 'email',
        'subject' => 'Reply to Your Complaint #{{id}}',
        'body' => "Dear {{tenant}},\n\nWe have updated your complaint #{{id}} regarding {{category}}.\n\nPlease login to your portal to view the response and any updates.\n\nPortal: {{link}}\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Rent Reminder',
        'type' => 'email',
        'subject' => 'Rent Reminder - {{month}}',
        'body' => "Dear {{tenant}},\n\nThis is a friendly reminder that your rent for {{month}} is due soon.\n\nAmount Due: KES {{amount}}\nDue Date: 5th of the month\nCurrent Balance: KES {{balance}}\n\nPlease make your payment before the due date to avoid late fees.\n\nYou can make payments through:\n- M-Pesa\n- Bank Transfer\n- Cash at the office\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Rent Reminder Final',
        'type' => 'email',
        'subject' => 'URGENT: Rent Due Tomorrow - {{month}}',
        'body' => "Dear {{tenant}},\n\nThis is an urgent reminder that your rent for {{month}} is due TOMORROW.\n\nAmount Due: KES {{amount}}\nDue Date: 5th of the month\nCurrent Balance: KES {{balance}}\n\nPlease make your payment immediately to avoid late fees and penalties.\n\nIf you have already made the payment, please disregard this message.\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Lease Renewal',
        'type' => 'email',
        'subject' => 'Lease Renewal Notice',
        'body' => "Dear {{tenant}},\n\nYour lease for {{house}} is expiring soon.\n\nWe would like to discuss renewal options with you. Please contact us to schedule a meeting or visit our office.\n\nWe value you as a tenant and look forward to continuing our relationship.\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
];

foreach ($templates as $template) {
    $db->insert('email_templates', $template);
    echo "Inserted template: {$template['name']}\n";
}

echo "\nEmail templates seeded successfully!\n";
echo "Total templates: " . count($templates) . "\n";
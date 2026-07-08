<?php
/**
 * Seed email templates
 */
require_once __DIR__ . '/../migrate.php';

$db = Database::getInstance();

// Check if templates already exist
$existing = $db->fetchOne("SELECT COUNT(*) as count FROM email_templates WHERE owner_id = 1");
if ($existing && $existing['count'] > 0) {
    echo "Email templates already seeded.\n";
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
        'body' => "Hi {{tenant}},\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES {{amount}}\n- Category: {{category}}\n- Date: {{date}}\n- Current Balance: KES {{balance}}\n\n{{invoice_section}}\n\nOr download your invoice directly:\n{{invoice_url}}\n\nThank you for your payment!\n\nBest regards,\nProperty Management",
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
        'body' => "Dear {{tenant}},\n\nThis is a friendly reminder that your rent for {{month}} is due soon.\n\nAmount Due: KES {{amount}}\nDue Date: 5th of the month\nCurrent Balance: KES {{balance}}\n\nPlease make your payment before the due date to avoid late fees.\n\nPayment Instructions:\n{{payment_instructions}}\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Rent Reminder Final',
        'type' => 'email',
        'subject' => 'URGENT: Rent Due Tomorrow - {{month}}',
        'body' => "Dear {{tenant}},\n\nThis is an urgent reminder that your rent for {{month}} is due TOMORROW.\n\nAmount Due: KES {{amount}}\nDue Date: 5th of the month\nCurrent Balance: KES {{balance}}\n\nPlease make your payment immediately to avoid late fees and penalties.\n\nPayment Instructions:\n{{payment_instructions}}\n\nIf you have already made the payment, please disregard this message.\n\nBest regards,\nProperty Management",
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
    [
        'owner_id' => 1,
        'name' => 'Tenant Vacate',
        'type' => 'email',
        'subject' => 'Tenancy Termination Confirmation - {{property}}',
        'body' => "Dear {{tenant}},\n\nThis is to confirm that your tenancy has been successfully terminated.\n\nVacate Details:\n- Property: {{property}}\n- Unit/House: {{house}}\n- Termination Date: {{date}}\n- National ID: {{national_id}}\n\nYour house has been marked as vacant and is now available for new tenants. We thank you for having been part of our community.\n\nIf you have any final questions or need assistance with the move-out process, please contact us.\n\nWe wish you all the best in your next home.\n\nBest regards,\nProperty Management",
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'owner_id' => 1,
        'name' => 'Password Reset',
        'type' => 'email',
        'subject' => 'Password Reset Code - RentFlow',
        'body' => "Dear {{name}},\n\nWe received a request to reset your password for your RentFlow account.\n\nYour verification code is: {{code}}\n\nThis code will expire in {{expires}}.\n\nIf you did not request a password reset, please ignore this email and your password will remain unchanged.\n\nTo reset your password:\n1. Enter the verification code above\n2. Create a new secure password\n\nBest regards,\nRentFlow Team",
        'created_at' => date('Y-m-d H:i:s')
    ],
];

    $inserted = 0;
    $updated = 0;
    foreach ($templates as $template) {
        $exists = $db->fetchOne("SELECT id FROM email_templates WHERE owner_id = ? AND name = ?", [$template['owner_id'], $template['name']]);
        if (!$exists) {
            $db->insert('email_templates', $template);
            $inserted++;
        } else {
            // Update existing template to ensure latest version
            $db->query(
                "UPDATE email_templates SET subject = ?, body = ?, updated_at = NOW() WHERE owner_id = ? AND name = ?",
                [$template['subject'], $template['body'], $template['owner_id'], $template['name']]
            );
            $updated++;
        }
    }
    
    echo "Email templates updated: $updated updated, $inserted new.\n";

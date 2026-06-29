<?php
/**
 * Seed Management Notice email template
 */
require_once __DIR__ . '/../../app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../../app/Core/Database.php';

$db = \App\Core\Database::getInstance();

$existing = $db->fetchOne("SELECT id FROM email_templates WHERE name = 'Management Notice'");
if ($existing) {
    $db->update('email_templates', [
        'subject' => '{{title}} - RentFlow',
        'body' => "Dear {{tenant_name}},\n\nYou have received an important notice from {{sender_name}}.\n\nProperty: {{property}}\nUnit: {{house}}\nDate: {{date}}\n\nCategory: {{category}}\n\nSubject: {{title}}\n\nMessage:\n{{description}}\n\nPlease log in to your RentFlow account to view full details, track updates, and respond if needed.\n\nBest regards,\n{{sender_name}}",
    ], 'id = ?', [$existing['id']]);
    echo "Template 'Management Notice' updated.\n";
} else {
    $db->insert('email_templates', [
        'owner_id' => 1,
        'name' => 'Management Notice',
        'type' => 'email',
        'subject' => '{{title}} - RentFlow',
        'body' => "Dear {{tenant_name}},\n\nYou have received an important notice from {{sender_name}}.\n\nProperty: {{property}}\nUnit: {{house}}\nDate: {{date}}\n\nCategory: {{category}}\n\nSubject: {{title}}\n\nMessage:\n{{description}}\n\nPlease log in to your RentFlow account to view full details, track updates, and respond if needed.\n\nBest regards,\n{{sender_name}}",
        'created_at' => date('Y-m-d H:i:s')
    ]);
    echo "Template 'Management Notice' created.\n";
}

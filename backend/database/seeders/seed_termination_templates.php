<?php
/**
 * Seed tenancy termination email templates
 */
require_once __DIR__ . '/../../app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../../app/Core/Database.php';

$db = \App\Core\Database::getInstance();

$templates = [
    [
        'name' => 'Termination Request',
        'subject' => 'Tenancy Termination Request - {{property}} {{house}}',
        'body' => "Dear {{owner_name}},\n\nTenant {{tenant_name}} has requested to terminate their tenancy.\n\nDetails:\n- Tenant: {{tenant_name}}\n- Property: {{property}}\n- Unit: {{house}}\n- Termination Date: {{date}}\n- Reason: {{reason}}\n\nPlease log in to review and approve this request.\n\nBest regards,\nRentFlow"
    ],
    [
        'name' => 'Termination Notice',
        'subject' => 'Tenancy Termination Notice - {{property}} {{house}}',
        'body' => "Dear {{tenant_name}},\n\nThis is to inform you that your tenancy has been terminated.\n\nDetails:\n- Property: {{property}}\n- Unit: {{house}}\n- Termination Date: {{date}}\n- Reason: {{reason}}\n\nPlease ensure the property is vacated by the termination date. Contact your property manager for any handover instructions.\n\nBest regards,\n{{owner_name}}\nRentFlow"
    ]
];

$existing = $db->fetchOne("SELECT id FROM email_templates WHERE name = 'Termination Request'");
if (!$existing) {
    $db->insert('email_templates', [
        'owner_id' => 1,
        'name' => $templates[0]['name'],
        'type' => 'email',
        'subject' => $templates[0]['subject'],
        'body' => $templates[0]['body'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    echo "Template 'Termination Request' created.\n";
} else {
    echo "Template 'Termination Request' already exists.\n";
}

$existing2 = $db->fetchOne("SELECT id FROM email_templates WHERE name = 'Termination Notice'");
if (!$existing2) {
    $db->insert('email_templates', [
        'owner_id' => 1,
        'name' => $templates[1]['name'],
        'type' => 'email',
        'subject' => $templates[1]['subject'],
        'body' => $templates[1]['body'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    echo "Template 'Termination Notice' created.\n";
} else {
    echo "Template 'Termination Notice' already exists.\n";
}
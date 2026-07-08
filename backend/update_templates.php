<?php
/**
 * Quick script to update email templates only (no migrations)
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

Env::load(__DIR__ . '/../../.env');

echo "=== Updating Email Templates ===\n\n";

try {
    $db = Database::getInstance();
    
    $templates = [
        [
            'owner_id' => 1,
            'name' => 'Payment Confirmation',
            'type' => 'email',
            'subject' => 'Payment Confirmation - KES {{amount}}',
            'body' => "Hi {{tenant}},\n\nYour payment has been successfully recorded.\n\nPayment Details:\n- Amount: KES {{amount}}\n- Category: {{category}}\n- Date: {{date}}\n- Current Balance: KES {{balance}}\n\n{{invoice_section}}\n\nOr download your invoice directly:\n{{invoice_url}}\n\nThank you for your payment!\n\nBest regards,\nProperty Management",
            'created_at' => date('Y-m-d H:i:s')
        ],
    ];
    
    $updated = 0;
    foreach ($templates as $template) {
        $exists = $db->fetchOne("SELECT id FROM email_templates WHERE owner_id = ? AND name = ?", [$template['owner_id'], $template['name']]);
        if ($exists) {
            $db->query(
                "UPDATE email_templates SET subject = ?, body = ? WHERE owner_id = ? AND name = ?",
                [$template['subject'], $template['body'], $template['owner_id'], $template['name']]
            );
            $updated++;
        }
    }
    
    echo "✓ Updated $updated template(s)\n\n";
    echo "Payment Confirmation template now includes invoice URL placeholders.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Template Update Complete ===\n";
echo "\nNow run: php backend/test_invoice_and_queue.php\n";

exit(0);
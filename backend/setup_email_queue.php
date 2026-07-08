<?php
/**
 * Quick setup script for email queue system
 * Creates the email_queue table and updates templates
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

echo "=== Setting Up Email Queue System ===\n\n";

try {
    $db = Database::getInstance();
    
    // Read and execute the migration SQL (multiple statements)
    $sql = file_get_contents(__DIR__ . '/database/migrations/020_create_email_queue.sql');
    
    // Remove comments and split by semicolon
    $lines = explode("\n", $sql);
    $cleanSql = '';
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comment lines
        if (strpos($line, '--') === 0) continue;
        $cleanSql .= $line . ' ';
    }
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $cleanSql)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->query($statement);
        }
    }
    
    echo "✓ Email queue tables created successfully\n\n";
    
    // Now update the templates
    echo "Updating email templates...\n";
    require_once __DIR__ . '/seeders/seed_email_templates.php';
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nMake sure:\n";
    echo "1. MySQL is running: sudo systemctl start mysql\n";
    echo "2. Database 'rentflow' exists\n";
    echo "3. Credentials in .env are correct\n";
    exit(1);
}

echo "\n=== Setup Complete ===\n";
echo "\nNext steps:\n";
echo "1. Run: php backend/test_invoice_and_queue.php\n";
echo "2. Setup cron: crontab -e\n";
echo "   Add: * * * * * php /opt/lampp/htdocs/RentFlow/backend/cron/email_queue.php\n";

exit(0);
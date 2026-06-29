<?php
/**
 * RentFlow Cron Setup Helper
 * 
 * This script helps set up the rent reminder cron job.
 * Run: php backend/setup_cron.php
 */

echo "=== RentFlow Cron Setup ===\n\n";

// Get the path to the PHP executable and this project
$phpPath = PHP_BINARY;
$scriptPath = __DIR__ . '/cron/rent_reminders.php';

echo "Cron Job Configuration:\n";
echo "-----------------------\n";
echo "Command to run daily at 8:00 AM:\n\n";
echo "  0 8 * * * {$phpPath} {$scriptPath} >> " . __DIR__ . "/logs/cron.log 2>&1\n\n";

echo "Setup Instructions:\n";
echo "-------------------\n";
echo "1. Run 'crontab -e' in terminal\n";
echo "2. Add the line above to the crontab file\n";
echo "3. Save and exit\n\n";

// Check if logs directory exists
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    echo "Creating logs directory... ";
    if (mkdir($logDir, 0755, true)) {
        echo "OK\n";
    } else {
        echo "FAILED\n";
        echo "Please create the logs directory manually: {$logDir}\n";
    }
} else {
    echo "Logs directory exists: {$logDir}\n";
}

// Check if rent_reminders table exists (requires DB connection)
echo "\nChecking database...\n";
require_once __DIR__ . '/app/Core/Env.php';
use App\Core\Env;
use App\Core\Database;

try {
    Env::load();
    $db = Database::getInstance();
    
    // Check if email_templates table exists
    $result = $db->fetchOne("SHOW TABLES LIKE 'email_templates'");
    if (!$result) {
        echo "⚠️  WARNING: email_templates table not found!\n";
        echo "   Run: php backend/database/migrate.php\n";
    } else {
        echo "✅ email_templates table exists\n";
        
        // Check if templates are seeded
        $count = $db->fetchOne("SELECT COUNT(*) as total FROM email_templates WHERE owner_id = 1");
        if ($count && $count['total'] > 0) {
            echo "✅ Email templates seeded ({$count['total']} templates found)\n";
        } else {
            echo "⚠️  No email templates found. Run: php backend/database/seeders/seed_email_templates.php\n";
        }
    }
    
    // Check if rent_reminders table exists
    $result = $db->fetchOne("SHOW TABLES LIKE 'rent_reminders'");
    if (!$result) {
        echo "⚠️  WARNING: rent_reminders table not found!\n";
        echo "   Run: php backend/database/migrate.php\n";
    } else {
        echo "✅ rent_reminders table exists\n";
    }
    
    // Check if email_logs table exists
    $result = $db->fetchOne("SHOW TABLES LIKE 'email_logs'");
    if (!$result) {
        echo "⚠️  WARNING: email_logs table not found!\n";
        echo "   Run: php backend/database/migrate.php\n";
    } else {
        echo "✅ email_logs table exists\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "   Check your .env database configuration\n";
}

echo "\n=== Setup Complete ===\n";
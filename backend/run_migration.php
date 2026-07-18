<?php
/**
 * ONE-TIME USE: Database Migration Runner for TrueHost
 * Access this via browser: https://yourdomain.com/RentalFlow/backend/run_migration.php
 * DELETE THIS FILE AFTER RUNNING MIGRATIONS!
 */

// Security: Only allow localhost access (change to your IP if needed)
$allowedIps = ['127.0.0.1', '::1', 'YOUR_IP_ADDRESS'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIps)) {
    die('Access denied. This script can only be run from the server.');
}

// Load environment
require_once __DIR__ . '/app/Core/Env.php';
App\Core\Env::load(__DIR__ . '/../.env');

// Verify database credentials
if (empty($_ENV['DB_PASS']) || $_ENV['DB_PASS'] === 'CHANGE_THIS_TO_YOUR_DATABASE_PASSWORD') {
    die('ERROR: Please update DB_PASS in .env file with your actual database password before running migrations.');
}

echo '<!DOCTYPE html><html><head><title>Database Migration</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}';
echo '.success{color:#4ec9b0;}.error{color:#f48771;}.info{color:#9cdcfe;}</style>';
echo '</head><body>';
echo '<h1>RentaFlow Database Migration</h1>';
echo '<pre>';

try {
    require_once __DIR__ . '/app/Core/Database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo '<span class="info">✓ Database connection successful</span><br><br>';
    
    // Run migrations in order
    $migrations = glob(__DIR__ . '/database/migrations/*.sql');
    sort($migrations);
    
    echo '<span class="info">Running migrations...</span><br>';
    foreach ($migrations as $file) {
        $filename = basename($file);
        echo "→ {$filename}... ";
        
        $sql = file_get_contents($file);
        if ($conn->multi_query($sql)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
            echo '<span class="success">✓ OK</span><br>';
        } else {
            if ($conn->errno === 0) {
                echo '<span class="success">✓ OK (no results)</span><br>';
            } else {
                echo '<span class="error">✗ ERROR: ' . $conn->error . '</span><br>';
            }
        }
    }
    
    echo '<br><span class="info">=== Migration Complete ===</span><br>';
    echo '<span class="info">Tables created:</span><br>';
    $tables = $conn->query("SHOW TABLES");
    while ($row = $tables->fetch_array()) {
        echo '  • ' . $row[0] . '<br>';
    }
    
    // Run seeders
    echo '<br><span class="info">Running seeders...</span><br>';
    $seeders = glob(__DIR__ . '/database/seeders/*.php');
    sort($seeders);
    foreach ($seeders as $file) {
        $filename = basename($file);
        echo "→ {$filename}... ";
        try {
            require_once $file;
            echo '<span class="success">✓ OK</span><br>';
        } catch (\Throwable $e) {
            echo '<span class="error">✗ ' . $e->getMessage() . '</span><br>';
        }
    }
    
    echo '<br><br>';
    echo '<span class="success" style="font-size:18px;">✓ MIGRATION COMPLETE!</span><br><br>';
    echo '<span class="error">IMPORTANT: DELETE THIS FILE (run_migration.php) NOW FOR SECURITY!</span><br>';
    
} catch (Exception $e) {
    echo '<span class="error">✗ FATAL ERROR: ' . $e->getMessage() . '</span><br>';
    echo '<span class="error">Stack trace:</span><br>';
    echo $e->getTraceAsString();
}

echo '</pre>';
echo '</body></html>';
?>
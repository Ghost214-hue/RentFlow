<?php
/**
 * Database Migration Runner
 * Run: php backend/database/migrate.php
 */

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

echo "=== RentFlow Database Migration ===\n\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Run migrations in order
    $migrations = glob(__DIR__ . '/migrations/*.sql');
    sort($migrations);

    foreach ($migrations as $file) {
        $filename = basename($file);
        echo "Running migration: {$filename}... ";

        $sql = file_get_contents($file);
        if ($conn->multi_query($sql)) {
            // Consume all results
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
            echo "OK\n";
        } else {
            if ($conn->errno === 0) {
                echo "OK (no results)\n";
            } else {
                echo "ERROR: " . $conn->error . "\n";
            }
        }
    }

    echo "\n=== Migration Complete ===\n";
    echo "Tables created:\n";
    $tables = $conn->query("SHOW TABLES");
    while ($row = $tables->fetch_array()) {
        echo "  - {$row[0]}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
<?php
/**
 * Database Setup Checker
 * Run this to verify your database is properly configured
 */

require_once __DIR__ . '/app/Core/Env.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

echo "=== RentFlow Database Setup Check ===\n\n";

// Check .env file
echo "1. Checking .env file...\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo "   ✓ .env file exists\n";
    
    $env = parse_ini_file($envFile);
    $required = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    foreach ($required as $key) {
        if (isset($env[$key])) {
            echo "   ✓ {$key} is set\n";
        } else {
            echo "   ✗ {$key} is NOT set\n";
        }
    }
} else {
    echo "   ✗ .env file NOT found\n";
}

echo "\n2. Testing database connection...\n";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "   ✓ Database connection successful\n";
    
    // Check if tables exist
    echo "\n3. Checking database tables...\n";
    $tables = $conn->query("SHOW TABLES");
    $tableCount = 0;
    while ($row = $tables->fetch_array()) {
        $tableCount++;
        echo "   ✓ {$row[0]}\n";
    }
    
    if ($tableCount === 0) {
        echo "   ✗ No tables found! You need to run migrations.\n";
        echo "\n   To run migrations, execute:\n";
        echo "   php backend/database/migrate.php\n";
    } else {
        echo "\n   Total tables: {$tableCount}\n";
        
        // Check critical tables
        $criticalTables = ['owners', 'properties', 'houses', 'tenants', 'templates'];
        echo "\n4. Checking critical tables...\n";
        foreach ($criticalTables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '{$table}'");
            if ($result->num_rows > 0) {
                echo "   ✓ {$table} exists\n";
            } else {
                echo "   ✗ {$table} MISSING\n";
            }
        }
    }
    
    // Check if owners table has any records
    echo "\n5. Checking existing users...\n";
    $count = $db->fetchOne("SELECT COUNT(*) as count FROM owners");
    echo "   Owners count: " . ($count['count'] ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Database connection FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "\n   Please check your database credentials in .env file\n";
}

echo "\n=== Setup Check Complete ===\n";
echo "\nIf you need to run migrations:\n";
echo "  php backend/database/migrate.php\n\n";
echo "If you need to seed initial data:\n";
echo "  php backend/database/seeders/seed.php\n";
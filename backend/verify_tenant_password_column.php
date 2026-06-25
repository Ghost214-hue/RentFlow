<?php
require_once __DIR__ . '/app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $result = $conn->query("SHOW COLUMNS FROM tenants LIKE 'password'");
    if ($result->num_rows > 0) {
        echo "✓ SUCCESS: 'password' column exists in tenants table\n";
        $row = $result->fetch_assoc();
        echo "  Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}\n";
    } else {
        echo "✗ ERROR: 'password' column NOT found in tenants table\n";
    }
    
    // Also check email index
    $result2 = $conn->query("SHOW INDEX FROM tenants WHERE Key_name = 'idx_tenants_email'");
    if ($result2->num_rows > 0) {
        echo "✓ SUCCESS: Email index 'idx_tenants_email' exists\n";
    } else {
        echo "✗ WARNING: Email index not found\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
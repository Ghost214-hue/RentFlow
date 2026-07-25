<?php
// Directly create the password_reset_tokens table
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

echo "<pre>Creating password_reset_tokens table...\n\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        owner_id INT UNSIGNED NULL,
        tenant_id INT UNSIGNED NULL,
        email VARCHAR(255) NOT NULL,
        code VARCHAR(6) NOT NULL,
        expires_at DATETIME NOT NULL,
        used TINYINT(1) DEFAULT 0,
        attempts TINYINT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE,
        FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
        INDEX idx_email_used (email, used),
        INDEX idx_code (code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql)) {
        echo "SUCCESS: password_reset_tokens table created!\n\n";
        echo "Now test the email endpoint:\n";
        echo "https://rentalflow.co.ke/backend/test_email_endpoints.php\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
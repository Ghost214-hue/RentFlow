-- Rent reminders log table (fixed version)
-- Made idempotent to prevent duplicate table creation errors

SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'rent_reminders');

-- Create table if it doesn't exist
SET @sql = IF(@table_exists = 0,
              'CREATE TABLE rent_reminders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                owner_id INT NOT NULL,
                tenant_id INT DEFAULT NULL,
                house_id INT NOT NULL,
                bill_id INT DEFAULT NULL,
                month VARCHAR(7) NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                days_before_due INT DEFAULT NULL,
                sent_at DATETIME NOT NULL,
                status VARCHAR(50) DEFAULT ''sent'',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_owner_id (owner_id),
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_month (month),
                INDEX idx_sent_at (sent_at)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
              'SELECT 1');
              
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
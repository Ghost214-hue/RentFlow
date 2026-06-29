-- Add month column to payments table for existing databases
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'month');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE payments ADD COLUMN month VARCHAR(7) DEFAULT NULL COMMENT ''''YYYY-MM format for billing period'''' AFTER house_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for performance
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_month');
SET @sql2 = IF(@idx_exists = 0, 'ALTER TABLE payments ADD INDEX idx_payments_month (month)', 'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
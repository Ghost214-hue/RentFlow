-- Add payment method columns to properties table (idempotent)

-- payment_method_type
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'properties' 
                   AND COLUMN_NAME = 'payment_method_type');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE properties ADD COLUMN payment_method_type ENUM(''paybill'',''till'',''bank'',''mobile_money'') DEFAULT NULL AFTER rent', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- paybill_number
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'properties' 
                   AND COLUMN_NAME = 'paybill_number');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE properties ADD COLUMN paybill_number VARCHAR(50) DEFAULT NULL AFTER payment_method_type', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- paybill_account
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'properties' 
                   AND COLUMN_NAME = 'paybill_account');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE properties ADD COLUMN paybill_account VARCHAR(100) DEFAULT NULL AFTER paybill_number', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- till_number
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'properties' 
                   AND COLUMN_NAME = 'till_number');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE properties ADD COLUMN till_number VARCHAR(50) DEFAULT NULL AFTER paybill_account', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- bank_name
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'properties' 
                   AND COLUMN_NAME = 'bank_name');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE properties ADD COLUMN bank_name VARCHAR(255) DEFAULT NULL AFTER till_number', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- bank_account
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'properties' 
                   AND COLUMN_NAME = 'bank_account');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE properties ADD COLUMN bank_account VARCHAR(100) DEFAULT NULL AFTER bank_name', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- bank_branch
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'properties' 
                   AND COLUMN_NAME = 'bank_branch');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE properties ADD COLUMN bank_branch VARCHAR(255) DEFAULT NULL AFTER bank_account', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- mobile_money_number
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'properties' 
                   AND COLUMN_NAME = 'mobile_money_number');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE properties ADD COLUMN mobile_money_number VARCHAR(50) DEFAULT NULL AFTER bank_branch', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

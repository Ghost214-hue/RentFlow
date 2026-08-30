-- Add Next of Kin fields to tenants table

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tenants' AND COLUMN_NAME = 'next_of_kin_name');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE tenants ADD COLUMN next_of_kin_name VARCHAR(255) DEFAULT NULL AFTER id_number', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tenants' AND COLUMN_NAME = 'next_of_kin_phone');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE tenants ADD COLUMN next_of_kin_phone VARCHAR(50) DEFAULT NULL AFTER next_of_kin_name', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tenants' AND COLUMN_NAME = 'next_of_kin_email');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE tenants ADD COLUMN next_of_kin_email VARCHAR(255) DEFAULT NULL AFTER next_of_kin_phone', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
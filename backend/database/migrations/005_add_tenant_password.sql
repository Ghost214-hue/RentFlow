-- Add password column to tenants table
-- This allows tenants to login using their ID number as default password
-- Only add if it doesn't exist (idempotent migration)
SET @dbname = DATABASE();
SET @tablename = 'tenants';
SET @columnname = 'password';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `tenants` ADD COLUMN `password` VARCHAR(255) DEFAULT NULL AFTER `phone`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for email lookups during login (idempotent)
SET @indexname = 'idx_tenants_email';
SET @preparedIndex = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = @indexname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `tenants` ADD INDEX `idx_tenants_email` (`email`)'
));
PREPARE alterIndexIfNotExists FROM @preparedIndex;
EXECUTE alterIndexIfNotExists;
DEALLOCATE PREPARE alterIndexIfNotExists;

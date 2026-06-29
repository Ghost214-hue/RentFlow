-- Add documents field to tenants table for storing uploaded files
-- Only add if it doesn't exist (idempotent migration)
SET @dbname = DATABASE();
SET @tablename = 'tenants';
SET @columnname = 'documents';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `tenants` ADD COLUMN `documents` TEXT DEFAULT NULL COMMENT ''JSON array of uploaded documents'' AFTER `elec_balance`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

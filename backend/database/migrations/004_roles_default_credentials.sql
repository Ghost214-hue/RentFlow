-- Login credentials and assignment metadata for non-owner accounts.

-- Add password column to tenants table (idempotent)
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
  'ALTER TABLE `tenants` ADD COLUMN `password` VARCHAR(255) DEFAULT NULL AFTER `email`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for tenants email (idempotent)
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

-- Add id_number column to caretakers table (idempotent)
SET @dbname = DATABASE();
SET @tablename = 'caretakers';
SET @columnname = 'id_number';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `caretakers` ADD COLUMN `id_number` VARCHAR(100) DEFAULT NULL AFTER `phone`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add indexes to caretakers table (idempotent)
SET @indexname = 'idx_caretakers_email';
SET @preparedIndex = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = @indexname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `caretakers` ADD INDEX `idx_caretakers_email` (`email`)'
));
PREPARE alterIndexIfNotExists FROM @preparedIndex;
EXECUTE alterIndexIfNotExists;
DEALLOCATE PREPARE alterIndexIfNotExists;

SET @indexname = 'idx_caretakers_owner_email';
SET @preparedIndex = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = @indexname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `caretakers` ADD INDEX `idx_caretakers_owner_email` (`owner_id`, `email`)'
));
PREPARE alterIndexIfNotExists FROM @preparedIndex;
EXECUTE alterIndexIfNotExists;
DEALLOCATE PREPARE alterIndexIfNotExists;

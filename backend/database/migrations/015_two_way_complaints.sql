-- Two-way complaints: allow owner/caretaker-initiated notices
-- Make tenant_id nullable for notices not tied to a specific tenant
ALTER TABLE complaints MODIFY COLUMN tenant_id INT UNSIGNED DEFAULT NULL;

-- Add sender tracking (idempotent)
-- sender_role
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'complaints' 
                   AND COLUMN_NAME = 'sender_role');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE complaints ADD COLUMN sender_role ENUM(''tenant'', ''owner'', ''caretaker'') DEFAULT ''tenant''', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- recipient_type
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'complaints' 
                   AND COLUMN_NAME = 'recipient_type');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE complaints ADD COLUMN recipient_type ENUM(''individual'', ''property'', ''all'') DEFAULT ''individual''', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- recipient_ids
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'complaints' 
                   AND COLUMN_NAME = 'recipient_ids');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE complaints ADD COLUMN recipient_ids TEXT DEFAULT NULL COMMENT ''JSON array of tenant IDs for individual; property_id for property-wide''', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- property_id
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'complaints' 
                   AND COLUMN_NAME = 'property_id');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE complaints ADD COLUMN property_id INT UNSIGNED DEFAULT NULL', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for property_id (idempotent)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'complaints' 
                   AND INDEX_NAME = 'idx_complaints_property');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE complaints ADD INDEX idx_complaints_property (property_id)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Track read status (idempotent)
-- read_by
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'complaints' 
                   AND COLUMN_NAME = 'read_by');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE complaints ADD COLUMN read_by TEXT DEFAULT NULL COMMENT ''JSON array of tenant IDs who read this''', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- read_at
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'complaints' 
                   AND COLUMN_NAME = 'read_at');
SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE complaints ADD COLUMN read_at DATETIME DEFAULT NULL', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

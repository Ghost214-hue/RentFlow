-- Indexes and constraints for higher tenant/property volume.
-- These reduce common owner-scoped scans and prevent duplicate monthly bills.

-- Add indexes to tenants table (idempotent)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'tenants' 
                   AND INDEX_NAME = 'idx_tenants_owner_name');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE tenants ADD INDEX idx_tenants_owner_name (owner_id, name)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'tenants' 
                   AND INDEX_NAME = 'idx_tenants_owner_house');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE tenants ADD INDEX idx_tenants_owner_house (owner_id, house_id)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'tenants' 
                   AND INDEX_NAME = 'idx_tenants_owner_lease_end');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE tenants ADD INDEX idx_tenants_owner_lease_end (owner_id, lease_end)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes to houses table (idempotent)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'houses' 
                   AND INDEX_NAME = 'idx_houses_owner_property_status');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE houses ADD INDEX idx_houses_owner_property_status (owner_id, property_id, status)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'houses' 
                   AND INDEX_NAME = 'uniq_houses_owner_property_unit');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE houses ADD UNIQUE KEY uniq_houses_owner_property_unit (owner_id, property_id, unit)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes to payments table (idempotent)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'payments' 
                   AND INDEX_NAME = 'idx_payments_owner_created');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE payments ADD INDEX idx_payments_owner_created (owner_id, created_at)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'payments' 
                   AND INDEX_NAME = 'idx_payments_owner_tenant_created');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE payments ADD INDEX idx_payments_owner_tenant_created (owner_id, tenant_id, created_at)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes to bills table (idempotent)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'bills' 
                   AND INDEX_NAME = 'idx_bills_owner_status_due');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE bills ADD INDEX idx_bills_owner_status_due (owner_id, status, due_date)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'bills' 
                   AND INDEX_NAME = 'uniq_bills_house_month');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE bills ADD UNIQUE KEY uniq_bills_house_month (house_id, month)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes to complaints table (idempotent)
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'complaints' 
                   AND INDEX_NAME = 'idx_complaints_owner_status_created');
SET @sql = IF(@idx_exists = 0, 
              'ALTER TABLE complaints ADD INDEX idx_complaints_owner_status_created (owner_id, status, created_at)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tenant_id column to bills table for existing databases
-- This snapshots the tenant at bill generation time so vacated tenants still show their name
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bills' AND COLUMN_NAME = 'tenant_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE bills ADD COLUMN tenant_id INT UNSIGNED DEFAULT NULL COMMENT ''Snapshotted at generation time'' AFTER house_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Backfill existing bills with tenant_id from houses
UPDATE bills b
JOIN houses h ON b.house_id = h.id
SET b.tenant_id = h.tenant_id
WHERE b.tenant_id IS NULL;

-- Add index for performance
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bills' AND INDEX_NAME = 'idx_bills_tenant');
SET @sql2 = IF(@idx_exists = 0, 'ALTER TABLE bills ADD INDEX idx_bills_tenant (tenant_id)', 'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

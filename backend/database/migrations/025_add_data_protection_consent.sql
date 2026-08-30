-- Add data protection consent tracking for GDPR compliance
-- This tracks when a tenant has consented to data collection

-- Add data_protection_consent_at column to track when tenant gave consent
ALTER TABLE tenants ADD COLUMN IF NOT EXISTS data_protection_consent_at DATETIME DEFAULT NULL COMMENT 'Timestamp when tenant consented to data collection';

-- Add index for efficient checking of consent status
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'tenants' 
                   AND INDEX_NAME = 'idx_tenants_data_protection_consent');
SET @sql = IF(@idx_exists = 0, 
              'CREATE INDEX idx_tenants_data_protection_consent ON tenants(data_protection_consent_at)', 
              'SELECT 1');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
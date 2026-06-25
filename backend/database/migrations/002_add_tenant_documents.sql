-- Add documents field to tenants table for storing uploaded files
ALTER TABLE `tenants` 
ADD COLUMN `documents` TEXT DEFAULT NULL COMMENT 'JSON array of uploaded documents' AFTER `elec_balance`;
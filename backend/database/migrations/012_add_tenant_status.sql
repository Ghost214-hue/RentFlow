-- Add status column to tenants table for existing databases
ALTER TABLE tenants ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'terminated') DEFAULT 'active' AFTER `elec_balance`;
ALTER TABLE tenants ADD INDEX IF NOT EXISTS `idx_tenants_status` (`status`);
-- Add tenant_confirmed column to payments table
ALTER TABLE payments ADD COLUMN IF NOT EXISTS `tenant_confirmed` TINYINT(1) DEFAULT 0 AFTER `status`;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS `confirmed_at` DATETIME NULL DEFAULT NULL AFTER `tenant_confirmed`;
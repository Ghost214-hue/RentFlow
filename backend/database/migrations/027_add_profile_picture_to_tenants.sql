-- Add profile_picture column to tenants table
ALTER TABLE `tenants`
ADD COLUMN `profile_picture` VARCHAR(500) DEFAULT NULL
COMMENT 'Uploaded profile picture URL'
AFTER `email`;

-- Add next_of_kin columns if missing
ALTER TABLE `tenants`
ADD COLUMN IF NOT EXISTS `next_of_kin_name` VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `next_of_kin_phone` VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `next_of_kin_email` VARCHAR(255) DEFAULT NULL;

-- Add documents column if missing
ALTER TABLE `tenants`
ADD COLUMN IF NOT EXISTS `documents` JSON DEFAULT NULL;

-- Add id_kra_pin column if missing
ALTER TABLE `tenants`
ADD COLUMN IF NOT EXISTS `id_kra_pin` VARCHAR(100) DEFAULT NULL;
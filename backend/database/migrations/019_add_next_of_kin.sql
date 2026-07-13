-- Add Next of Kin fields to tenants table
-- Replaces emergency_contact with structured next of kin information

ALTER TABLE `tenants`
    ADD COLUMN `next_of_kin_name` VARCHAR(255) DEFAULT NULL AFTER `id_number`,
    ADD COLUMN `next_of_kin_phone` VARCHAR(50) DEFAULT NULL AFTER `next_of_kin_name`,
    ADD COLUMN `next_of_kin_email` VARCHAR(255) DEFAULT NULL AFTER `next_of_kin_phone`;

-- Optionally migrate existing emergency_contact data if needed
-- This creates a basic migration, actual data migration would be handled separately if emergency_contact has existing important data
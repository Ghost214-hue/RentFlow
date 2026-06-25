-- Login credentials and assignment metadata for non-owner accounts.

ALTER TABLE `tenants`
    ADD COLUMN `password` VARCHAR(255) DEFAULT NULL AFTER `email`,
    ADD INDEX `idx_tenants_email` (`email`);

ALTER TABLE `caretakers`
    ADD COLUMN `id_number` VARCHAR(100) DEFAULT NULL AFTER `phone`,
    ADD INDEX `idx_caretakers_email` (`email`),
    ADD INDEX `idx_caretakers_owner_email` (`owner_id`, `email`);

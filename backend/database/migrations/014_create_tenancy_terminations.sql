-- Tenancy Terminations audit table
CREATE TABLE IF NOT EXISTS `tenancy_terminations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `property_id` INT UNSIGNED DEFAULT NULL,
    `house_id` INT UNSIGNED DEFAULT NULL,
    `initiated_by` ENUM('tenant', 'owner', 'caretaker') NOT NULL DEFAULT 'owner',
    `initiated_by_user_id` INT UNSIGNED DEFAULT NULL COMMENT 'The user who initiated',
    `reason` TEXT DEFAULT NULL,
    `effective_date` DATE NOT NULL,
    `status` ENUM('pending', 'approved', 'completed') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tt_owner` (`owner_id`),
    INDEX `idx_tt_tenant` (`tenant_id`),
    INDEX `idx_tt_status` (`status`),
    CONSTRAINT `fk_tt_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tt_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

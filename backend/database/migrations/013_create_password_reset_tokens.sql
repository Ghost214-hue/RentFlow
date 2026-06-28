CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED DEFAULT NULL,
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL,
    `code` VARCHAR(6) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `attempts` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_code` (`code`),
    INDEX `idx_expires` (`expires_at`),
    INDEX `idx_used` (`used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
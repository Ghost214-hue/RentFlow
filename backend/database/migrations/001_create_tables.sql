-- RentaFlow Database Schema
-- Multi-tenant property management system
-- Each table has owner_id for data isolation

CREATE TABLE IF NOT EXISTS `owners` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `avatar` VARCHAR(10) DEFAULT 'OW',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_owners_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `properties` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `address` TEXT NOT NULL,
    `type` VARCHAR(100) DEFAULT 'Apartment Block',
    `units` INT UNSIGNED DEFAULT 0,
    `occupied` INT UNSIGNED DEFAULT 0,
    `image` VARCHAR(500) DEFAULT NULL,
    `caretaker_id` INT UNSIGNED DEFAULT NULL,
    `rent` DECIMAL(12,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_properties_owner` (`owner_id`),
    CONSTRAINT `fk_properties_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `houses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `property_id` INT UNSIGNED NOT NULL,
    `unit` VARCHAR(50) NOT NULL,
    `type` VARCHAR(100) DEFAULT '1 Bedroom',
    `status` ENUM('occupied', 'vacant') DEFAULT 'vacant',
    `tenant_id` INT UNSIGNED DEFAULT NULL,
    `rent` DECIMAL(12,2) DEFAULT 0.00,
    `water_meter` VARCHAR(100) DEFAULT NULL,
    `elec_meter` VARCHAR(100) DEFAULT NULL,
    `last_reading` DECIMAL(12,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_houses_owner` (`owner_id`),
    INDEX `idx_houses_property` (`property_id`),
    INDEX `idx_houses_status` (`status`),
    CONSTRAINT `fk_houses_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_houses_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `caretakers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(10) DEFAULT 'CT',
    `assigned_properties` TEXT DEFAULT NULL COMMENT 'Comma-separated property IDs',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_caretakers_owner` (`owner_id`),
    CONSTRAINT `fk_caretakers_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `property_id` INT UNSIGNED DEFAULT NULL,
    `house_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `id_number` VARCHAR(100) DEFAULT NULL,
    `id_type` VARCHAR(50) DEFAULT 'National ID',
    `emergency_contact` VARCHAR(255) DEFAULT NULL,
    `lease_start` DATE DEFAULT NULL,
    `lease_end` DATE DEFAULT NULL,
    `deposit` DECIMAL(12,2) DEFAULT 0.00,
    `balance` DECIMAL(12,2) DEFAULT 0.00,
    `water_balance` DECIMAL(12,2) DEFAULT 0.00,
    `elec_balance` DECIMAL(12,2) DEFAULT 0.00,
    `status` ENUM('active', 'terminated') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenants_owner` (`owner_id`),
    INDEX `idx_tenants_status` (`status`),
    INDEX `idx_tenants_property` (`property_id`),
    INDEX `idx_tenants_house` (`house_id`),
    CONSTRAINT `fk_tenants_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tenants_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tenants_house` FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `house_id` INT UNSIGNED DEFAULT NULL,
    `month` VARCHAR(7) DEFAULT NULL COMMENT 'YYYY-MM format for billing period',
    `amount` DECIMAL(12,2) NOT NULL,
    `type` ENUM('Rent', 'Water', 'Electricity', 'Deposit') DEFAULT 'Rent',
    `method` VARCHAR(50) DEFAULT 'M-Pesa',
    `date` DATE DEFAULT NULL,
    `status` ENUM('completed', 'pending', 'failed') DEFAULT 'completed',
    `receipt` VARCHAR(100) DEFAULT NULL,
    `description` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_payments_owner` (`owner_id`),
    INDEX `idx_payments_tenant` (`tenant_id`),
    INDEX `idx_payments_status` (`status`),
    INDEX `idx_payments_month` (`month`),
    CONSTRAINT `fk_payments_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bills` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `house_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED DEFAULT NULL COMMENT 'Snapshotted at generation time',
    `month` VARCHAR(7) NOT NULL COMMENT 'YYYY-MM format',
    `rent` DECIMAL(12,2) DEFAULT 0.00,
    `water` DECIMAL(12,2) DEFAULT 0.00,
    `electricity` DECIMAL(12,2) DEFAULT 0.00,
    `total` DECIMAL(12,2) DEFAULT 0.00,
    `status` ENUM('paid', 'partial', 'pending', 'overdue') DEFAULT 'pending',
    `due_date` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_bills_owner` (`owner_id`),
    INDEX `idx_bills_house` (`house_id`),
    INDEX `idx_bills_tenant` (`tenant_id`),
    INDEX `idx_bills_month` (`month`),
    CONSTRAINT `fk_bills_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bills_house` FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bills_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `complaints` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `house_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) DEFAULT 'Other',
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `status` ENUM('open', 'in-progress', 'resolved') DEFAULT 'open',
    `date` DATE DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `timeline` TEXT DEFAULT NULL COMMENT 'JSON timeline data',
    `comments` TEXT DEFAULT NULL COMMENT 'JSON comments data',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_complaints_owner` (`owner_id`),
    INDEX `idx_complaints_tenant` (`tenant_id`),
    INDEX `idx_complaints_status` (`status`),
    CONSTRAINT `fk_complaints_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_complaints_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `communications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `type` ENUM('whatsapp', 'email', 'sms') DEFAULT 'email',
    `recipient` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(500) DEFAULT NULL,
    `message` TEXT DEFAULT NULL,
    `date` DATE DEFAULT NULL,
    `status` ENUM('sent', 'delivered', 'failed') DEFAULT 'sent',
    `template` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_communications_owner` (`owner_id`),
    CONSTRAINT `fk_communications_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('whatsapp', 'email', 'sms') DEFAULT 'email',
    `subject` VARCHAR(500) DEFAULT NULL,
    `body` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_templates_owner` (`owner_id`),
    CONSTRAINT `fk_templates_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
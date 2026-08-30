-- Add support for bill line items and payment allocations
CREATE TABLE IF NOT EXISTS `bill_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `bill_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending','partial','paid') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_bill_items_bill` (`bill_id`),
    INDEX `idx_bill_items_type` (`type`),
    CONSTRAINT `fk_bill_items_bill` FOREIGN KEY (`bill_id`) REFERENCES `bills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_allocations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `payment_id` INT UNSIGNED NOT NULL,
    `bill_item_id` INT UNSIGNED NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_payment_allocations_payment` (`payment_id`),
    INDEX `idx_payment_allocations_bill_item` (`bill_item_id`),
    CONSTRAINT `fk_payment_allocations_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payment_allocations_bill_item` FOREIGN KEY (`bill_item_id`) REFERENCES `bill_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `payments`
    MODIFY COLUMN `type` ENUM('Rent','Water','Electricity','Deposit','Mixed') DEFAULT 'Rent';

ALTER TABLE `tenants`
    ADD COLUMN IF NOT EXISTS `credit` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `balance`;

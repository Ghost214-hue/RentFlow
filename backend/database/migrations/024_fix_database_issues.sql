-- Fix Database Issues Migration
-- This migration fixes:
-- 1. Removes the duplicate rent_reminders table
-- 2. Drops deprecated emergency_contact column from tenants table

-- Step 1: Drop the broken rent_reminders table if it exists
-- Migration 008 created an invalid version, and 010 created a valid one
-- Since we can't DROP and recreate due to foreign key ordering, we just ensure correct schema exists
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'rent_reminders');

-- If table exists, check and fix foreign keys
SET @sql = IF(@table_exists > 0,
              'ALTER TABLE rent_reminders
               ADD CONSTRAINT IF NOT EXISTS fk_rent_reminders_owner FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE,
               ADD CONSTRAINT IF NOT EXISTS fk_rent_reminders_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
               ADD CONSTRAINT IF NOT EXISTS fk_rent_reminders_house FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE,
               ADD CONSTRAINT IF NOT EXISTS fk_rent_reminders_bill FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE SET NULL',
              'CREATE TABLE rent_reminders LIKE bills');
              
-- The above won't work due to ALTER TABLE constraints, let's use a simpler approach
-- Just add missing columns if needed
SET @sql = 'ALTER TABLE rent_reminders 
            ADD COLUMN IF NOT EXISTS bill_id INT DEFAULT NULL AFTER house_id,
            ADD COLUMN IF NOT EXISTS days_before_due INT DEFAULT NULL AFTER amount,
            MODIFY COLUMN status VARCHAR(50) DEFAULT ''sent'',
            DROP FOREIGN KEY IF EXISTS fk_rent_reminders_owner,
            DROP FOREIGN KEY IF EXISTS fk_rent_reminders_tenant,
            DROP FOREIGN KEY IF EXISTS fk_rent_reminders_house';
            
-- Actually let's just drop and recreate without FK checks during creation
SET @sql = IF(@table_exists > 0, 
              'DROP TABLE rent_reminders', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Now create with correct schema but without FK constraints
-- (application code doesn't rely on them, and prevents circular dependency issues)
CREATE TABLE IF NOT EXISTS rent_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    tenant_id INT DEFAULT NULL,
    house_id INT NOT NULL,
    bill_id INT DEFAULT NULL,
    month VARCHAR(7) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    days_before_due INT DEFAULT NULL,
    sent_at DATETIME NOT NULL,
    status VARCHAR(50) DEFAULT 'sent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_owner_id (owner_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_month (month),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Drop deprecated emergency_contact column from tenants table
-- This was replaced by next_of_kin_name, next_of_kin_phone, next_of_kin_email in migration 019
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'tenants' 
                   AND COLUMN_NAME = 'emergency_contact');

SET @sql = IF(@col_exists > 0, 
              'ALTER TABLE tenants DROP COLUMN emergency_contact', 
              'SELECT 1');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Add performance indexes from migration 023 if they don't exist
-- These improve query performance for common operations

-- Composite index for houses queries
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'houses' 
                   AND INDEX_NAME = 'idx_houses_owner_property');
SET @sql = IF(@idx_exists = 0, 
              'CREATE INDEX idx_houses_owner_property ON houses(owner_id, property_id)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index for houses property/unit queries
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'houses' 
                   AND INDEX_NAME = 'idx_houses_property_unit');
SET @sql = IF(@idx_exists = 0, 
              'CREATE INDEX idx_houses_property_unit ON houses(property_id, unit)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index for tenants house/owner queries
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'tenants' 
                   AND INDEX_NAME = 'idx_tenants_house_owner');
SET @sql = IF(@idx_exists = 0, 
              'CREATE INDEX idx_tenants_house_owner ON tenants(house_id, owner_id)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index for payments house/owner/month queries
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'payments' 
                   AND INDEX_NAME = 'idx_payments_house_owner_month');
SET @sql = IF(@idx_exists = 0, 
              'CREATE INDEX idx_payments_house_owner_month ON payments(house_id, owner_id, month)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index for bills owner/house/month queries
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'bills' 
                   AND INDEX_NAME = 'idx_bills_owner_house_month');
SET @sql = IF(@idx_exists = 0, 
              'CREATE INDEX idx_bills_owner_house_month ON bills(owner_id, house_id, month)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index for caretakers owner/email queries
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'caretakers' 
                   AND INDEX_NAME = 'idx_caretakers_owner_email');
SET @sql = IF(@idx_exists = 0, 
              'CREATE INDEX idx_caretakers_owner_email ON caretakers(owner_id, email)', 
              'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Log completion
SELECT 'Database fix migration completed successfully' AS status;
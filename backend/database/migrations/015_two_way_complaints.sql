-- Two-way complaints: allow owner/caretaker-initiated notices
-- Make tenant_id nullable for notices not tied to a specific tenant
ALTER TABLE complaints MODIFY COLUMN tenant_id INT UNSIGNED DEFAULT NULL;

-- Add sender tracking
ALTER TABLE complaints ADD COLUMN sender_role ENUM('tenant', 'owner', 'caretaker') DEFAULT 'tenant';
ALTER TABLE complaints ADD COLUMN recipient_type ENUM('individual', 'property', 'all') DEFAULT 'individual';
ALTER TABLE complaints ADD COLUMN recipient_ids TEXT DEFAULT NULL COMMENT 'JSON array of tenant IDs for individual; property_id for property-wide';
ALTER TABLE complaints ADD COLUMN property_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE complaints ADD INDEX idx_complaints_property (property_id);

-- Track read status
ALTER TABLE complaints ADD COLUMN read_by TEXT DEFAULT NULL COMMENT 'JSON array of tenant IDs who read this';
ALTER TABLE complaints ADD COLUMN read_at DATETIME DEFAULT NULL;
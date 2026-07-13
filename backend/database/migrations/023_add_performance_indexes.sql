-- Add recommended performance indexes for large list queries
-- Run this migration to improve filtering and pagination performance on key tables.

CREATE INDEX IF NOT EXISTS idx_houses_owner_property ON houses(owner_id, property_id);
CREATE INDEX IF NOT EXISTS idx_houses_property_unit ON houses(property_id, unit);
CREATE INDEX IF NOT EXISTS idx_tenants_house_owner ON tenants(house_id, owner_id);
CREATE INDEX IF NOT EXISTS idx_payments_house_owner_month ON payments(house_id, owner_id, month);
CREATE INDEX IF NOT EXISTS idx_bills_owner_house_month ON bills(owner_id, house_id, month);
CREATE INDEX IF NOT EXISTS idx_caretakers_owner_email ON caretakers(owner_id, email);

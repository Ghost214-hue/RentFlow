-- Add type column to bills table for separate rent/water/electricity bills
ALTER TABLE bills ADD COLUMN IF NOT EXISTS type VARCHAR(20) DEFAULT 'Rent' AFTER month;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS description VARCHAR(255) NULL AFTER type;
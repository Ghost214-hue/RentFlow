ALTER TABLE properties
  ADD COLUMN payment_method_type ENUM('paybill','till','bank','mobile_money') DEFAULT NULL AFTER rent,
  ADD COLUMN paybill_number VARCHAR(50) DEFAULT NULL AFTER payment_method_type,
  ADD COLUMN paybill_account VARCHAR(100) DEFAULT NULL AFTER paybill_number,
  ADD COLUMN till_number VARCHAR(50) DEFAULT NULL AFTER paybill_account,
  ADD COLUMN bank_name VARCHAR(255) DEFAULT NULL AFTER till_number,
  ADD COLUMN bank_account VARCHAR(100) DEFAULT NULL AFTER bank_name,
  ADD COLUMN bank_branch VARCHAR(255) DEFAULT NULL AFTER bank_account,
  ADD COLUMN mobile_money_number VARCHAR(50) DEFAULT NULL AFTER bank_branch;
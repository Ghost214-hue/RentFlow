-- Add a DB-level guarantee that no unit can have more than one bill per billing month.
-- This backs up the application-level idempotency check in BillController::generate().
-- Idempotent: only creates the index if it does not already exist.
--
-- NOTE: If duplicate rows already exist in your `bills` table (house_id + month), this
-- CREATE UNIQUE INDEX will report an ERROR and skip. Clean those up first, e.g.:
--   DELETE b1 FROM bills b1 JOIN bills b2
--     ON b1.house_id = b2.house_id AND b1.month = b2.month AND b1.id > b2.id;
SET @index_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'bills'
      AND INDEX_NAME = 'uq_bills_house_month'
);
SET @sql = IF(@index_exists = 0,
    'CREATE UNIQUE INDEX uq_bills_house_month ON bills (house_id, month)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

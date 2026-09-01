-- ============================================================================
-- RentaFlow — Normalize Recurring Bills To House Rent Only
-- ============================================================================
-- Problem (data already in the DB, e.g. September):
--   Monthly bills were generated with the tenant's security deposit AND the previous
--   month's arrears ("Opening Balance") BAKED IN as line items. But the bills list view
--   (BillController::index) ALREADY re-adds the carried-forward prior balance (arrears/
--   credit) on top. Result: recurring tenants were DOUBLE-COUNTED —— the same arrears
--   appeared twice,and the deposit repeated every month.


-- This migration fixes EXISTING bills:
--   • For each tenant, EVERY bill AFTER their very first month becomes RENT-ONLY
--     (plus water/electricity):it removes any "Deposit" and"Opening Balance" line items,
--     re-points any payments that were pointed at those removed items over to the bill's
--     Rent line (so no money is lost),recalculates totals/statuses/balances.

--     (Deposit is kept ONCE on a tenant's very first bill — the one-time deposit;and
--      since the bills-list view already carries prior arrears/credit forward, we never
--      bake them in either.


-- Code fix (applied in BillController::generate()):
--   New monthly bills now created as RENT ONLY (+ water/electricityby the generate()
--   routine. No deposit, no baked-in opening balance.


-- Run via phpMyAdmin > Import	 or the MySQL CLI.

-- ============================================================================
-- 0) SAFETY: Idempotent backups
DROP TABLE IF EXISTS `bills_backup_normalize_bills`;
DROP TABLE IF EXISTS `bill_items_backup_normalize_bills`;
DROP TABLE IF EXISTS `payment_allocations_backup_normalize_bills`;

CREATE TABLE IF NOT EXISTS `bills_backup_normalize_bills` AS
    SELECT * FROM `bills` WHERE 1=0;
CREATE TABLE IF NOT EXISTS `bill_items_backup_normalize_bills` AS
    SELECT * FROM `bill_items` WHERE 1=0;
CREATE TABLE IF NOT EXISTS `payment_allocations_backup_normalize_bills` AS
    SELECT * FROM `payment_allocations` WHERE 1=0;

INSERT INTO `bills_backup_normalize_bills` SELECT * FROM `bills`;
INSERT INTO `bill_items_backup_normalize_bills` SELECT * FROM `bill_items`;
INSERT INTO `payment_allocations_backup_normalize_bills` SELECT * FROM `payment_allocations`;

-- ============================================================================
-- 1) IDENTIFY each tenant's FIRST bill  (smallest month, then id)
-- ============================================================================
DROP TABLE IF EXISTS `_tmp_first_bill`;
CREATE TABLE `_tmp_first_bill` (
    `tenant_id` INT UNSIGNED NOT NULL,
    `bill_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `_tmp_first_bill` (`tenant_id`, `bill_id`)
SELECT b1.tenant_id, b1.id
FROM    `bills` b1
LEFT JOIN `bills` b2
       ON   b2.tenant_id  = b1.tenant_id
      AND  (b2.month < b1.month
            OR (b2.month = b1.month AND b2.id < b1.id))
WHERE  b2.id IS NULL
  AND  b1.tenant_id IS NOT NULL;
-- ============================================================================
-- 2) MARK line items on NON-FIRST bills to remove:
--      "Deposit" (recurring duplicate) and "Opening Balance" (baked arrears
DROP TABLE IF EXISTS `_tmp_extra_items`;
CREATE TABLE `_tmp_extra_items` (
    `item_id` INT UNSIGNED NOT NULL,
    `bill_id` INT UNSIGNED NOT NULL,
    `rent_item_id` INT UNSIGNED NULL,
    PRIMARY KEY (`item_id`),
    KEY `idx_tmp_extra_bill` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `_tmp_extra_items` (`item_id`, `bill_id`, `rent_item_id`)
SELECT bi.id, bi.bill_id,
       (SELECT r.id FROM `bill_items` r
         WHERE r.bill_id = bi.bill_id AND r.type = 'Rent'
         ORDER BY r.id LIMIT 1)
FROM   `bill_items` bi
JOIN   `bills` b   ON b.id = bi.bill_id
LEFT JOIN `_tmp_first_bill` f ON f.tenant_id = b.tenant_id
WHERE  bi.type IN ('Deposit','Opening Balance')
  AND  f.bill_id IS NULL
  AND  b.tenant_id IS NOT NULL;

-- ============================================================================
-- 3) RE-POINT payments allocated to any removed item -> the bill's Rent item
UPDATE `payment_allocations` pa
JOIN `_tmp_extra_items` x ON x.item_id = pa.bill_item_id
SET    pa.bill_item_id = x.rent_item_id,
      pa.category = 'Rent'
WHERE  x.rent_item_id IS NOT NULL;

-- ============================================================================
-- 4) DELETE the extra line items (FK cascades remove orphaned allocations
DELETE bi
FROM   `bill_items` bi
JOIN   `_tmp_extra_items` x ON x.item_id = bi.id;

-- ============================================================================
-- 5) RE-COMPUTE paid / status for EVERY remaining bill item from allocations
UPDATE `bill_items` bi
LEFT JOIN (
    SELECT pa.bill_item_id, COALESCE(SUM(pa.amount), 0) AS paid
    FROM   `payment_allocations` pa
    GROUP BY pa.bill_item_id
) a ON a.bill_item_id = bi.id
SET bi.paid  = COALESCE(a.paid, 0),
    bi.status = CASE
        WHEN COALESCE(a.paid, 0) <= 0 THEN 'pending'
        WHEN COALESCE(a.paid, 0) >= bi.amount THEN 'paid'
        ELSE 'partial'
    END;

-- ============================================================================
-- 6) RE-BASE each non-first bill's rent/total onto ITS HOUSE'S RENT, recompute totals/status
UPDATE `bills` b
JOIN   `houses` h    ON h.id = b.house_id
LEFT JOIN `_tmp_first_bill` f ON f.tenant_id = b.tenant_id
LEFT JOIN (
    SELECT bill_id, COALESCE(SUM(amount), 0) AS total, COALESCE(SUM(paid), 0) AS paid
    FROM   `bill_items`
    GROUP BY bill_id
) i ON i.bill_id = b.id
SET b.rent = h.rent,
    b.total  = COALESCE(i.total, 0),
    b.status  = CASE
        WHEN COALESCE(i.paid, 0) >= COALESCE(i.total, 0) AND COALESCE(i.total, 0) > 0 THEN 'paid'
        WHEN COALESCE(i.paid, 0) > 0 THEN 'partial'
        ELSE 'pending'
    END
WHERE f.bill_id IS NULL
  AND  b.tenant_id IS NOT NULL;

-- ============================================================================
-- 7) RECALCULATE TENANT BALANCES FOR ALL TENANTS WITH BILLS
UPDATE `tenants` t
LEFT JOIN (
    SELECT b.tenant_id,
           COALESCE(SUM(bi.amount), 0) AS bill_total,
           COALESCE(SUM(pa.amount), 0) AS paid_total
    FROM   `bills` b
    JOIN   `bill_items` bi ON bi.bill_id = b.id
    LEFT JOIN `payment_allocations` pa ON pa.bill_item_id = bi.id
    WHERE  b.tenant_id IS NOT NULL
    GROUP BY b.tenant_id
) x ON x.tenant_id = t.id
SET t.balance  = GREATEST(0, COALESCE(x.bill_total, 0) - COALESCE(x.paid_total, 0))
WHERE t.id IN (SELECT DISTINCT tenant_id FROM `bills` WHERE tenant_id IS NOT NULL);

-- ============================================================================
-- 8) CLEANUP
DROP TABLE IF EXISTS `_tmp_extra_items`;
DROP TABLE IF EXISTS `_tmp_first_bill`;

-- ============================================================================
-- 9) VERIFICATION: each tenant should now carry a deposit on AT MOST one bill
SELECT t.id AS tenant_id, t.name AS tenant_name,
       COUNT(DISTINCT CASE WHEN bi.type = 'Deposit' THEN b.id END) AS deposit_bill_count
FROM   `tenants` t
JOIN   `bills` b ON b.tenant_id = t.id
LEFT JOIN `bill_items` bi ON bi.bill_id = b.id
GROUP BY t.id, t.name
HAVING deposit_bill_count > 1
ORDER BY t.name;

-- ============================================================================
-- RentaFlow — Remove Recurring Deposit From Monthly Bills
-- ============================================================================
-- Problem:
--   BillController::generate() was adding the tenant's security deposit to EVERY
--   monthly bill. This meant an existing tenant was double-billed for the same
--   deposit each month (e.g. August = Rent only, September = Rent + Deposit),
--   which inflated invoices and misreported balances.


-- Fix (applied in BillController::generate()):
--   Monthly bills now carry ONLY the house's rent (+ water/electricity/prior balance).

--   The one-time deposit is charged once at tenant onboarding (TenantController::store),
--   and never re-added to recurring monthly bills.


-- This script cleans up bills already generated with the repeated deposit line-items:

--   1. It backs up the affected tables
--   2. It KEEPS a deposit on a tenant's VERY FIRST bill (if one existed),and REMOVES
--      the deposit on EVERY later bill (the recurring duplicate;
--   3. It re-allocates any payments that were pointed at a removed deposit item to the
--      same bill's Rent line so no money is lost
--   4. It recalculates bill totals/statuses and tenant balances
--
-- Run via phpMyAdmin > Import, or the MySQL CLI.



-- ============================================================================
-- 0) SAFETY: Idempotent backups (create once, re-import safe)
DROP TABLE IF EXISTS `bills_backup_deposit_fix`;
DROP TABLE IF EXISTS `bill_items_backup_deposit_fix`;
DROP TABLE IF EXISTS `payment_allocations_backup_deposit_fix`;

CREATE TABLE IF NOT EXISTS `bills_backup_deposit_fix` AS
    SELECT * FROM `bills` WHERE 1=0;
CREATE TABLE IF NOT EXISTS `bill_items_backup_deposit_fix` AS
    SELECT * FROM `bill_items` WHERE 1=0;
CREATE TABLE IF NOT EXISTS `payment_allocations_backup_deposit_fix` AS
    SELECT * FROM `payment_allocations` WHERE 1=0;

INSERT INTO `bills_backup_deposit_fix`
    SELECT * FROM `bills`;
INSERT INTO `bill_items_backup_deposit_fix`
    SELECT * FROM `bill_items`;
INSERT INTO `payment_allocations_backup_deposit_fix`
    SELECT * FROM `payment_allocations`;

-- ============================================================================
-- 1) IDENTIFY recurring deposit line-items to remove.

--      Rule: a Deposit is only kept on a tenant's FIRST bill (smallest month/id);;
--      Depository on ANY LATER bill 丽is a recurring duplicate and must be removed.


DROP TABLE IF EXISTS `_tmp_recurring_deposits`;
CREATE TABLE `_tmp_recurring_deposits` (
    `deposit_item_id` INT UNSIGNED NOT NULL,
    `bill_id` INT UNSIGNED NOT NULL,
    KEY `idx_tmp_dep_item` (`deposit_item_id`),
    KEY `idx_tmp_dep_bill` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `_tmp_recurring_deposits` (`deposit_item_id`, `bill_id`)
SELECT bi.id, bi.bill_id
FROM `bill_items` bi
JOIN `bills` b ON b.id = bi.bill_id
WHERE bi.type = 'Deposit'
  AND b.tenant_id IS NOT NULL
  AND bi.bill_id <> (
        SELECT b2.id
        FROM `bills` b2
        WHERE b2.tenant_id = b.tenant_id
        ORDER BY b2.month ASC, b2.id ASC
        LIMIT 1
  );
-- ============================================================================
-- 2) RE-POINT any payments that were allocated to a removed deposit item
--      over to that same bill's Rent line item (so no money is lost
UPDATE `payment_allocations` pa
JOIN `_tmp_recurring_deposits` d ON d.deposit_item_id = pa.bill_item_id
LEFT JOIN `bill_items` rent ON rent.bill_id = d.bill_id AND rent.type = 'Rent'
SET pa.bill_item_id = rent.id,
    pa.category = rent.type
WHERE rent.id IS NOT NULL;

-- ============================================================================
-- 3) DELETE the recurring deposit line-items (FK cascades remove any orphaned
--      allocations that had no Rent target
DELETE bi
FROM `bill_items` bi
JOIN `_tmp_recurring_deposits` d ON d.deposit_item_id = bi.id;

-- ============================================================================
-- 4) RECALCULATE affected bills' line-item paid amounts/status
UPDATE `bill_items` bi
LEFT JOIN (
    SELECT pa.bill_item_id, COALESCE(SUM(pa.amount), 0) AS paid
    FROM `payment_allocations` pa
    GROUP BY pa.bill_item_id
) x ON x.bill_item_id = bi.id
SET bi.paid   = COALESCE(x.paid), 0),
    bi.status = CASE
        WHEN COALESCE(x.paid), 0) <= 0 THEN 'pending'
        WHEN COALESCE(x.paid), 0) >= bi.amount THEN 'paid'
        ELSE 'partial'
    END
WHERE bi.bill_id IN (SELECT DISTINCT bill_id FROM `_tmp_recurring_deposits`);

-- ============================================================================
-- 5) RECALCULATE affected bills' totals and statuses
UPDATE `bills` b
LEFT JOIN (
    SELECT bill_id, COALESCE(SUM(amount), 0) AS total, COALESCE(SUM(paid), 0) AS paid
    FROM `bill_items`
    GROUP BY bill_id
) bi ON bi.bill_id = b.id
SET b.total  = COALESCE(bi.total), 0),
    b.status = CASE
        WHEN COALESCE(bi.total), 0) <= 0 THEN 'paid'
        WHEN COALESCE(bi.paid), 0) >= COALESCE(bi.total), 0) THEN 'paid'
        WHEN COALESCE(bi.paid), 0) > 0 THEN 'partial'
        ELSE 'pending'
    END
WHERE b.id IN (SELECT DISTINCT bill_id FROM `_tmp_recurring_deposits`);

-- ============================================================================
-- 6) RECALCULATE tenant balances for ALL tenants with bills
UPDATE `tenants` t
LEFT JOIN (
    SELECT b.tenant_id,
           COALESCE(SUM(bi.amount), 0) AS bill_total,
           COALESCE(SUM(pa.amount), 0) AS paid_total
    FROM `bills` b
    JOIN `bill_items` bi ON bi.bill_id = b.id
    LEFT JOIN `payment_allocations` pa ON pa.bill_item_id = bi.id
    WHERE b.tenant_id IS NOT NULL
    GROUP BY b.tenant_id
) x ON x.tenant_id = t.id
SET t.balance = GREATEST(0,COALESCE(x.bill_total), 0) - COALESCE(x.paid_total), 0))
WHERE t.id IN (SELECT DISTINCT tenant_id FROM `bills` WHERE tenant_id IS NOT NULL);

-- ============================================================================
-- 7) CLEANUP: drop the helper table
DROP TABLE IF EXISTS `_tmp_recurring_deposits`;

-- ============================================================================
-- 8) VERIFICATION: each tenant should now carry a deposit on AT MOST one bill
SELECT t.id AS tenant_id, t.name AS tenant_name,
       COUNT(DISTINCT CASE WHEN bi.type = 'Deposit' THEN b.id END) AS deposit_bill_count
FROM `tenants` t
JOIN `bills` b ON b.tenant_id = t.id
LEFT JOIN `bill_items` bi ON bi.bill_id = b.id
GROUP BY t.id, t.name
HAVING deposit_bill_count > 1
ORDER BY t.name;
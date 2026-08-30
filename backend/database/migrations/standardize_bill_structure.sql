-- ============================================================================
-- RentaFlow — Standardize Bill Structure
-- 
-- Problem: Old tenants have bills with Rent + Deposit, new monthly bills only
-- have Rent. This creates inconsistency.
--
-- Solution: 
--   - Old tenants (created before 2025-08-01): Bills = Rent ONLY
--   - New tenants (created on or after 2025-08-01): Bills = Rent + Deposit
--
-- This script removes Deposit line items from old tenant bills and recalculates
-- all financials (payments, balances, statuses).
-- ============================================================================

-- 0) SAFETY: Create backups
CREATE TABLE IF NOT EXISTS bills_backup_standardization AS 
SELECT * FROM bills WHERE 1=0;
CREATE TABLE IF NOT EXISTS bill_items_backup_standardization AS 
SELECT * FROM bill_items WHERE 1=0;
CREATE TABLE IF NOT EXISTS payment_allocations_backup_standardization AS 
SELECT * FROM payment_allocations WHERE 1=0;

-- Backup current data
INSERT INTO bills_backup_standardization SELECT * FROM bills;
INSERT INTO bill_items_backup_standardization SELECT * FROM bill_items;
INSERT INTO payment_allocations_backup_standardization SELECT * FROM payment_allocations;

-- ============================================================================
-- 1) REMOVE DEPOSIT LINE ITEMS FROM OLD TENANTS' BILLS
-- ============================================================================
-- Delete deposit line items for tenants created before 2025-08-01
DELETE bi 
FROM bill_items bi
JOIN bills b ON b.id = bi.bill_id
JOIN tenants t ON t.id = b.tenant_id
WHERE bi.type = 'Deposit'
  AND t.created_at < '2025-08-01 00:00:00'
  AND bi.bill_id IN (
      SELECT b2.id 
      FROM bills b2
      JOIN tenants t2 ON t2.id = b2.tenant_id
      WHERE t2.created_at < '2025-08-01 00:00:00'
  );

-- ============================================================================
-- 2) RECALCULATE BILL TOTALS FROM LINE ITEMS
-- ============================================================================
UPDATE bills b
LEFT JOIN (
    SELECT bill_id, 
           COALESCE(SUM(amount), 0) AS total
    FROM bill_items
    GROUP BY bill_id
) bi ON bi.bill_id = b.id
SET b.total = bi.total
WHERE b.id IN (
    SELECT DISTINCT b2.id 
    FROM bills b2
    JOIN tenants t ON t.id = b2.tenant_id
    WHERE t.created_at < '2025-08-01 00:00:00'
);

-- ============================================================================
-- 3) REMOVE STALE PAYMENT ALLOCATIONS FOR OLD TENANTS
-- ============================================================================
DELETE pa 
FROM payment_allocations pa
JOIN bill_items bi ON bi.id = pa.bill_item_id
JOIN bills b ON b.id = bi.bill_id
JOIN tenants t ON t.id = b.tenant_id
WHERE t.created_at < '2025-08-01 00:00:00';

-- ============================================================================
-- 4) RE-ALLOCATE PAYMENTS TO OLD TENANTS' BILLS
-- For old tenants, all payments go to Rent line items only (no deposit)
-- ============================================================================
INSERT INTO payment_allocations (payment_id, bill_item_id, category, amount)
SELECT
    p.id AS payment_id,
    bi.id AS bill_item_id,
    bi.type AS category,
    LEAST(bi.amount, p.amount) AS amount
FROM payments p
JOIN bills b ON b.id = (
    SELECT id FROM bills b2
    WHERE b2.house_id = p.house_id
      AND b2.month = p.month
      AND b2.owner_id = p.owner_id
    LIMIT 1
)
JOIN bill_items bi ON bi.bill_id = b.id
JOIN tenants t ON t.id = b.tenant_id
WHERE t.created_at < '2025-08-01 00:00:00'
  AND p.owner_id = b.owner_id
  AND p.status IN ('completed', 'paid', 'confirmed')
  AND bi.type = 'Rent'
  AND NOT EXISTS (
      SELECT 1 FROM payment_allocations pa 
      WHERE pa.payment_id = p.id AND pa.bill_item_id = bi.id
  )
ORDER BY p.id, bi.id;

-- ============================================================================
-- 5) RECALCULATE bill_items.paid AND status FOR OLD TENANTS
-- ============================================================================
UPDATE bill_items bi
JOIN (
    SELECT bill_item_id, SUM(amount) AS paid
    FROM payment_allocations
    GROUP BY bill_item_id
) pa ON pa.bill_item_id = bi.id
JOIN bills b ON b.id = bi.bill_id
JOIN tenants t ON t.id = b.tenant_id
SET bi.paid = pa.paid,
    bi.status = CASE 
        WHEN pa.paid >= bi.amount THEN 'paid'
        WHEN pa.paid > 0 THEN 'partial'
        ELSE 'pending'
    END
WHERE t.created_at < '2025-08-01 00:00:00';

-- ============================================================================
-- 6) RECALCULATE bills.status FOR OLD TENANTS
-- ============================================================================
UPDATE bills b
LEFT JOIN (
    SELECT bill_id,
           COALESCE(SUM(amount), 0) AS total,
           COALESCE(SUM(paid), 0) AS paid
    FROM bill_items
    GROUP BY bill_id
) bi ON bi.bill_id = b.id
JOIN tenants t ON t.id = b.tenant_id
SET b.status = CASE
                 WHEN COALESCE(bi.total, 0) <= 0 THEN 'pending'
                 WHEN COALESCE(bi.paid, 0) >= COALESCE(bi.total, 0) THEN 'paid'
                 WHEN COALESCE(bi.paid, 0) > 0 THEN 'partial'
                 ELSE 'pending'
              END
WHERE t.created_at < '2025-08-01 00:00:00';

-- ============================================================================
-- 7) RECALCULATE TENANT BALANCES FOR OLD TENANTS
-- ============================================================================
UPDATE tenants t
LEFT JOIN (
    SELECT b.tenant_id,
           COALESCE(SUM(bi.amount), 0) AS bill_total,
           COALESCE(SUM(pa.amount), 0) AS paid_total
    FROM bills b
    JOIN bill_items bi ON bi.bill_id = b.id
    LEFT JOIN payment_allocations pa ON pa.bill_item_id = bi.id
    WHERE b.tenant_id IN (
        SELECT id FROM tenants WHERE created_at < '2025-08-01 00:00:00'
    )
    GROUP BY b.tenant_id
) x ON x.tenant_id = t.id
SET t.balance = GREATEST(0, COALESCE(x.bill_total, 0) - COALESCE(x.paid_total, 0))
WHERE t.created_at < '2025-08-01 00:00:00';

-- ============================================================================
-- 8) VERIFICATION - OLD TENANTS
-- ============================================================================
SELECT 
    t.id,
    t.name AS tenant,
    t.created_at,
    b.id AS bill_id,
    b.total AS bill_total,
    b.status AS bill_status,
    COUNT(bi.id) AS item_count,
    GROUP_CONCAT(CONCAT(bi.type, ':', bi.amount, '/', bi.paid) SEPARATOR ' | ') AS items,
    COALESCE(SUM(bi.paid), 0) AS total_paid,
    t.balance AS tenant_balance
FROM tenants t
JOIN bills b ON b.tenant_id = t.id
LEFT JOIN bill_items bi ON bi.bill_id = b.id
WHERE t.created_at < '2025-08-01 00:00:00'
  AND b.month = DATE_FORMAT(NOW(), '%Y-%m')
GROUP BY b.id
ORDER BY t.name;

-- ============================================================================
-- 9) VERIFICATION - NEW TENANTS (should still have Rent + Deposit)
-- ============================================================================
SELECT 
    t.id,
    t.name AS tenant,
    t.created_at,
    b.id AS bill_id,
    b.total AS bill_total,
    b.status AS bill_status,
    COUNT(bi.id) AS item_count,
    GROUP_CONCAT(CONCAT(bi.type, ':', bi.amount, '/', bi.paid) SEPARATOR ' | ') AS items,
    COALESCE(SUM(bi.paid), 0) AS total_paid,
    t.balance AS tenant_balance
FROM tenants t
JOIN bills b ON b.tenant_id = t.id
LEFT JOIN bill_items bi ON bi.bill_id = b.id
WHERE t.created_at >= '2025-08-01 00:00:00'
  AND b.month = DATE_FORMAT(NOW(), '%Y-%m')
GROUP BY b.id
ORDER BY t.name;

-- ============================================================================
-- SUMMARY
-- ============================================================================
SELECT 
    'OLD TENANTS' AS category,
    COUNT(DISTINCT t.id) AS tenant_count,
    SUM(b.total) AS total_billed,
    SUM(COALESCE(SUM(bi.paid), 0)) AS total_paid
FROM tenants t
JOIN bills b ON b.tenant_id = t.id
LEFT JOIN bill_items bi ON bi.bill_id = b.id
WHERE t.created_at < '2025-08-01 00:00:00'
  AND b.month = DATE_FORMAT(NOW(), '%Y-%m')
GROUP BY 'OLD TENANTS'

UNION ALL

SELECT 
    'NEW TENANTS' AS category,
    COUNT(DISTINCT t.id) AS tenant_count,
    SUM(b.total) AS total_billed,
    SUM(COALESCE(SUM(bi.paid), 0)) AS total_paid
FROM tenants t
JOIN bills b ON b.tenant_id = t.id
LEFT JOIN bill_items bi ON bi.bill_id = b.id
WHERE t.created_at >= '2025-08-01 00:00:00'
  AND b.month = DATE_FORMAT(NOW(), '%Y-%m')
GROUP BY 'NEW TENANTS';

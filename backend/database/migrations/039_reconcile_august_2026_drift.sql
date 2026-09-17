-- =====================================================================
-- 039: Reconcile August 2026 line-item drift (phantom KES 500 arrears)
-- =====================================================================
-- ROOT CAUSE BEING FIXED:
--   August 2026 rent line items were stamped with the CURRENT houses.rent
--   (6,500) two days after the bills were created at the historical billed
--   amount (6,000). Tenants paid the billed 6,000 in full and were emailed
--   "Current Balance: KES 0.00". The item/pay drift creates a phantom 500
--   arrears per affected tenant, which then propagates into every later
--   month as "Previous Month Balance" on invoices and the Bills page.
--
--   Affected (evidence: payment emails + payment_allocations):
--     bill 70 (Zahra Zainabu, A1)     item  6: 6500 -> 6000 (paid 6000)
--     bill 79 (Adryan K. Langat, A2)  item 11: 6500 -> 6000 (paid 6000)
--     bill 80 (Sheila G. Mureithi,A3) item 13: 6500 -> 6000 (paid 6000)
--     bill 86 (Juliah W. Kahora, C4)  item 25: 6500 -> 6000 (paid 6000)
--
--   Also fixed: bill 93 (John Stephen, C6) item 34 has amount 0.00 with a
--   KES 7,000 payment allocated to it (charge zeroed AFTER payment). The
--   payment receipt (7,000) is the contract -> amount restored to 7,000.
--
-- SAFETY: run AFTER backing up. All three touched tables are backed up here.
-- VERIFICATION queries at the bottom must all return zero rows.
-- =====================================================================

START TRANSACTION;

-- 0. BACKUP (rollback point)
CREATE TABLE IF NOT EXISTS bill_items_backup_039 AS
    SELECT * FROM bill_items WHERE bill_id IN (70, 79, 80, 86, 93);

CREATE TABLE IF NOT EXISTS bills_backup_039 AS
    SELECT * FROM bills WHERE id IN (70, 79, 80, 86, 93);

CREATE TABLE IF NOT EXISTS tenants_backup_039 AS
    SELECT * FROM tenants
    WHERE id IN (SELECT tenant_id FROM bills
                 WHERE id IN (70, 79, 80, 86, 93) AND tenant_id IS NOT NULL);

-- 1. Correct the four rewritten August rent items (evidence-based:
--    billed & paid 6,000; payment email confirmed balance 0.00)
UPDATE bill_items
SET amount = 6000.00, status = 'paid'
WHERE id IN (6, 11, 13, 25)
  AND type = 'Rent'
  AND paid = 6000.00
  AND amount = 6500.00;

-- 2. Restore bill 93's zeroed charge (payment of 7,000 was allocated to it)
UPDATE bill_items
SET amount = 7000.00, status = 'paid'
WHERE bill_id = 93
  AND type = 'Rent'
  AND amount = 0.00
  AND paid = 7000.00;

-- 3. Re-derive bills.total / bills.status from line items (source of truth)
UPDATE bills b
JOIN (
    SELECT bill_id, SUM(amount) AS item_total, SUM(paid) AS item_paid
    FROM bill_items GROUP BY bill_id
) x ON x.bill_id = b.id
SET b.total  = x.item_total,
    b.status = CASE
        WHEN x.item_paid <= 0 THEN 'pending'
        WHEN x.item_paid + 0.001 >= x.item_total THEN 'paid'
        ELSE 'partial'
    END
WHERE b.id IN (70, 79, 80, 86, 93);

COMMIT;

-- 4. Re-derive tenant-level balance and credit for the affected tenants
--    (balance = max(0, all items - allocated payments)
--     credit  = max(0, all confirmed payments - all items))
UPDATE tenants t
LEFT JOIN (
    SELECT b.tenant_id,
           COALESCE(SUM(bi.amount), 0) AS item_total,
           COALESCE(SUM(bi.paid), 0)   AS item_paid
    FROM bills b
    JOIN bill_items bi ON bi.bill_id = b.id
    WHERE b.tenant_id IS NOT NULL
    GROUP BY b.tenant_id
) x ON x.tenant_id = t.id
SET t.balance = GREATEST(0, COALESCE(x.item_total, 0) - COALESCE(x.item_paid, 0)),
    t.credit  = GREATEST(0, COALESCE(x.item_paid, 0) - COALESCE(x.item_total, 0))
WHERE t.id IN (SELECT DISTINCT tenant_id FROM bills
               WHERE id IN (70, 79, 80, 86, 93) AND tenant_id IS NOT NULL);

-- NOTE: bill_items.paid is kept in sync with payment_allocations by the app
-- (allocatePayment / update). To be extra safe you can afterwards trigger the
-- app-level recalc for each affected tenant
-- (BillingService::recalcTenantCreditAndBalance).

-- =====================================================================
-- 5. VERIFICATION — every query below must return ZERO rows
-- =====================================================================

-- 5a. No remaining item/pay drift (over-allocation) on the corrected bills
SELECT bi.bill_id, bi.amount, bi.paid
FROM bill_items bi
WHERE bi.bill_id IN (70, 79, 80, 86, 93)
  AND bi.paid > bi.amount + 0.01;

-- 5b. No remaining bills.total vs items drift
SELECT b.id, b.total, COALESCE(SUM(bi.amount),0) AS items_total
FROM bills b JOIN bill_items bi ON bi.bill_id = b.id
WHERE b.id IN (70, 79, 80, 86, 93)
GROUP BY b.id
HAVING ABS(b.total - COALESCE(SUM(bi.amount),0)) > 0.01;

-- 5c. No phantom carry-forward remains for September
SELECT t.tenant_id,
       GREATEST(0, COALESCE(pr.charges,0) - COALESCE(pr.paid,0)) AS opening
FROM (
    SELECT DISTINCT tenant_id FROM bills
    WHERE month = '2026-09'
      AND tenant_id IN (SELECT tenant_id FROM bills
                        WHERE id IN (70, 79, 80, 86, 93) AND tenant_id IS NOT NULL)
) t
LEFT JOIN (
    SELECT b.tenant_id,
           SUM(bi.amount) AS charges,
           SUM(COALESCE((SELECT SUM(pa.amount) FROM payment_allocations pa
                         WHERE pa.bill_item_id = bi.id), 0)) AS paid
    FROM bills b
    JOIN bill_items bi ON bi.bill_id = b.id
    WHERE b.month < '2026-09'
    GROUP BY b.tenant_id
) pr ON pr.tenant_id = t.tenant_id
HAVING opening > 0.01;

-- =====================================================================
-- 6. ROLLBACK (only if needed)
-- =====================================================================
-- UPDATE bill_items bi JOIN bill_items_backup_039 bk ON bk.id = bi.id
--   SET bi.amount = bk.amount, bi.paid = bk.paid, bi.status = bk.status;
-- UPDATE bills b JOIN bills_backup_039 bk ON bk.id = b.id
--   SET b.total = bk.total, b.status = bk.status, b.rent = bk.rent;
-- UPDATE tenants t JOIN tenants_backup_039 bk ON bk.id = t.id
--   SET t.balance = bk.balance, t.credit = bk.credit;

-- =====================================================================
-- 7. AFTER VERIFICATION (optional hardening, run separately):
--    Re-attempt the unique idempotency indexes (m030/m032 skip when
--    duplicates exist; drift rows are now fixed):
--
--    CREATE UNIQUE INDEX uq_bills_house_month ON bills (house_id, month);
--    CREATE UNIQUE INDEX uq_bills_owner_tenant_month ON bills (owner_id, tenant_id, month);
--    (If these fail, duplicates remain — run audit 038 query 1 first.)
-- =====================================================================


-- =====================================================================
-- 038: Financial reconciliation AUDIT (READ-ONLY — run before any fix)
-- Produces the evidence needed for the historical data remediation:
-- duplicate bills, duplicated opening balances, negative balances,
-- invoices inconsistent with bills, orphaned allocations, credits.
-- BACKUP FIRST:
--   mysqldump -u root -p rentaflow > rentaflow_backup_$(date +%F).sql
-- =====================================================================

-- 1. Duplicate bills per tenant + billing month
SELECT owner_id, tenant_id, month, COUNT(*) AS bill_count,
       GROUP_CONCAT(id ORDER BY id) AS bill_ids
FROM bills
WHERE tenant_id IS NOT NULL
GROUP BY owner_id, tenant_id, month
HAVING COUNT(*) > 1;

-- 2. Bills whose stored total disagrees with the sum of their line items
SELECT b.id, b.month, b.tenant_id, b.total AS stored_total,
       COALESCE(SUM(bi.amount), 0) AS items_total,
       b.total - COALESCE(SUM(bi.amount), 0) AS drift
FROM bills b
LEFT JOIN bill_items bi ON bi.bill_id = b.id
GROUP BY b.id
HAVING ABS(b.total - COALESCE(SUM(bi.amount), 0)) > 0.01;

-- 3. Bills with duplicated "Opening Balance"/carry-forward line items
SELECT bi.bill_id, b.month, b.tenant_id, COUNT(*) AS opening_lines,
       SUM(bi.amount) AS opening_total
FROM bill_items bi
JOIN bills b ON b.id = bi.bill_id
WHERE bi.type LIKE '%pening%' OR bi.description LIKE '%revious%alance%'
GROUP BY bi.bill_id
HAVING COUNT(*) > 1;

-- 4. Negative stored totals / balances (overpayments mis-modeled as debt)
SELECT id, tenant_id, month, total, status
FROM bills WHERE total < 0;

-- 5. Tenant-level negative balances
SELECT id, name, balance, credit FROM tenants WHERE balance < 0;

-- 6. Payments allocated to items belonging to another tenant's bills
SELECT p.id AS payment_id, p.tenant_id AS payment_tenant, b.tenant_id AS bill_tenant,
       pa.bill_item_id, pa.amount
FROM payment_allocations pa
JOIN payments p ON p.id = pa.payment_id
JOIN bill_items bi ON bi.id = pa.bill_item_id
JOIN bills b ON b.id = bi.bill_id
WHERE p.tenant_id <> b.tenant_id;

-- 7. Over-allocated bill items (paid > amount)
SELECT bi.id, bi.bill_id, bi.amount, COALESCE(SUM(pa.amount),0) AS allocated
FROM bill_items bi
LEFT JOIN payment_allocations pa ON pa.bill_item_id = bi.id
GROUP BY bi.id
HAVING COALESCE(SUM(pa.amount),0) > bi.amount + 0.01;

-- 8. Tenant balance vs recomputed authoritative balance
SELECT t.id, t.name, t.balance AS stored_balance,
       GREATEST(0,
         COALESCE(x.item_total,0) - COALESCE(x.paid_total,0)) AS expected_balance
FROM tenants t
LEFT JOIN (
    SELECT b.tenant_id,
           SUM(bi.amount) AS item_total,
           SUM((SELECT COALESCE(SUM(pa.amount),0) FROM payment_allocations pa
                JOIN payments p ON p.id = pa.payment_id
                WHERE pa.bill_item_id = bi.id
                  AND p.status IN ('confirmed','completed','paid'))) AS paid_total
    FROM bills b JOIN bill_items bi ON bi.bill_id = b.id
    GROUP BY b.tenant_id
) x ON x.tenant_id = t.id
WHERE ABS(t.balance - GREATEST(0, COALESCE(x.item_total,0) - COALESCE(x.paid_total,0))) > 0.01;

-- 9. Orphaned records (missing FK targets)
SELECT bi.id FROM bill_items bi
LEFT JOIN bills b ON b.id = bi.bill_id WHERE b.id IS NULL;
SELECT pa.id FROM payment_allocations pa
LEFT JOIN payments p ON p.id = pa.payment_id WHERE p.id IS NULL;
SELECT pa.id FROM payment_allocations pa
LEFT JOIN bill_items bi ON bi.id = pa.bill_item_id WHERE bi.id IS NULL;

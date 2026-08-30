-- Production repair for mixed tenant/unit bills.
--
-- Assumption:
--   The current houses.tenant_id allocation is correct.
--
-- What this does:
--   For the current billing month only, repair bills whose snapshotted tenant_id
--   does not match the tenant currently assigned to that bill's house.
--
-- Why current month only:
--   Historical bills must remain snapshots. A historical mismatch may be a real
--   move-out/move-in event, so this migration does not rewrite older invoices.
--
-- Safety:
--   - No bills, bill_items, payments, or allocations are deleted.
--   - Every changed row is copied into bill_tenant_snapshot_repair_audit first.
--   - The update is idempotent; re-running it creates no new changes once fixed.

CREATE TABLE IF NOT EXISTS `bill_tenant_snapshot_repair_audit` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `bill_id` INT UNSIGNED NOT NULL,
    `owner_id` INT UNSIGNED NOT NULL,
    `house_id` INT UNSIGNED NOT NULL,
    `month` VARCHAR(7) NOT NULL,
    `old_tenant_id` INT UNSIGNED DEFAULT NULL,
    `new_tenant_id` INT UNSIGNED NOT NULL,
    `old_tenant_name` VARCHAR(255) DEFAULT NULL,
    `new_tenant_name` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_bill_tenant_snapshot_repair_bill` (`bill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @repair_month = DATE_FORMAT(CURRENT_DATE(), '%Y-%m');

INSERT IGNORE INTO `bill_tenant_snapshot_repair_audit`
    (`bill_id`, `owner_id`, `house_id`, `month`, `old_tenant_id`, `new_tenant_id`, `old_tenant_name`, `new_tenant_name`)
SELECT
    b.id,
    b.owner_id,
    b.house_id,
    b.month,
    b.tenant_id,
    h.tenant_id,
    old_t.name,
    new_t.name
FROM bills b
JOIN houses h ON h.id = b.house_id AND h.owner_id = b.owner_id
LEFT JOIN tenants old_t ON old_t.id = b.tenant_id AND old_t.owner_id = b.owner_id
JOIN tenants new_t ON new_t.id = h.tenant_id AND new_t.owner_id = b.owner_id
WHERE b.month = @repair_month
  AND h.status = 'occupied'
  AND h.tenant_id IS NOT NULL
  AND (b.tenant_id IS NULL OR b.tenant_id <> h.tenant_id);

UPDATE bills b
JOIN houses h ON h.id = b.house_id AND h.owner_id = b.owner_id
SET b.tenant_id = h.tenant_id
WHERE b.month = @repair_month
  AND h.status = 'occupied'
  AND h.tenant_id IS NOT NULL
  AND (b.tenant_id IS NULL OR b.tenant_id <> h.tenant_id);

CREATE TABLE IF NOT EXISTS `bill_tenant_month_duplicate_audit` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `month` VARCHAR(7) NOT NULL,
    `bill_count` INT UNSIGNED NOT NULL,
    `bill_ids` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_bill_tenant_month_duplicate` (`owner_id`, `tenant_id`, `month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bill_tenant_month_duplicate_audit`
    (`owner_id`, `tenant_id`, `month`, `bill_count`, `bill_ids`)
SELECT
    owner_id,
    tenant_id,
    month,
    COUNT(*) AS bill_count,
    GROUP_CONCAT(id ORDER BY id SEPARATOR ',') AS bill_ids
FROM bills
WHERE tenant_id IS NOT NULL
GROUP BY owner_id, tenant_id, month
HAVING COUNT(*) > 1
ON DUPLICATE KEY UPDATE
    bill_count = VALUES(bill_count),
    bill_ids = VALUES(bill_ids),
    created_at = CURRENT_TIMESTAMP;

SET @duplicate_tenant_months_after_repair = (
    SELECT COUNT(*) FROM (
        SELECT owner_id, tenant_id, month
        FROM bills
        WHERE tenant_id IS NOT NULL
        GROUP BY owner_id, tenant_id, month
        HAVING COUNT(*) > 1
    ) duplicate_rows
);

SET @tenant_month_index_exists_after_repair = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'bills'
      AND INDEX_NAME = 'uq_bills_owner_tenant_month'
);

SET @tenant_month_index_sql_after_repair = IF(
    @duplicate_tenant_months_after_repair = 0 AND @tenant_month_index_exists_after_repair = 0,
    'CREATE UNIQUE INDEX uq_bills_owner_tenant_month ON bills (owner_id, tenant_id, month)',
    'SELECT 1'
);
PREPARE tenant_month_index_stmt_after_repair FROM @tenant_month_index_sql_after_repair;
EXECUTE tenant_month_index_stmt_after_repair;
DEALLOCATE PREPARE tenant_month_index_stmt_after_repair;

-- Bills are generated as one monthly invoice per tenant.
-- house_id remains the unit snapshot for display, but tenant_id + month is the
-- billing identity. This migration only adds the unique guard when existing
-- data has no duplicate tenant/month rows, so it will not silently delete or
-- merge historical invoices.

SET @duplicate_tenant_months = (
    SELECT COUNT(*) FROM (
        SELECT owner_id, tenant_id, month
        FROM bills
        WHERE tenant_id IS NOT NULL
        GROUP BY owner_id, tenant_id, month
        HAVING COUNT(*) > 1
    ) duplicate_rows
);

SET @index_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'bills'
      AND INDEX_NAME = 'uq_bills_owner_tenant_month'
);

SET @sql = IF(
    @duplicate_tenant_months = 0 AND @index_exists = 0,
    'CREATE UNIQUE INDEX uq_bills_owner_tenant_month ON bills (owner_id, tenant_id, month)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

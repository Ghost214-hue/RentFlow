-- Indexes and constraints for higher tenant/property volume.
-- These reduce common owner-scoped scans and prevent duplicate monthly bills.

ALTER TABLE `tenants`
    ADD INDEX `idx_tenants_owner_name` (`owner_id`, `name`),
    ADD INDEX `idx_tenants_owner_house` (`owner_id`, `house_id`),
    ADD INDEX `idx_tenants_owner_lease_end` (`owner_id`, `lease_end`);

ALTER TABLE `houses`
    ADD INDEX `idx_houses_owner_property_status` (`owner_id`, `property_id`, `status`),
    ADD UNIQUE KEY `uniq_houses_owner_property_unit` (`owner_id`, `property_id`, `unit`);

ALTER TABLE `payments`
    ADD INDEX `idx_payments_owner_created` (`owner_id`, `created_at`),
    ADD INDEX `idx_payments_owner_tenant_created` (`owner_id`, `tenant_id`, `created_at`);

ALTER TABLE `bills`
    ADD INDEX `idx_bills_owner_status_due` (`owner_id`, `status`, `due_date`),
    ADD UNIQUE KEY `uniq_bills_house_month` (`house_id`, `month`);

ALTER TABLE `complaints`
    ADD INDEX `idx_complaints_owner_status_created` (`owner_id`, `status`, `created_at`);

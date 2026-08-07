# Bill Structure Standardization Fix

## Problem Statement

Your production system has an inconsistency in how bills are structured:

### Current Behavior
- **Old tenants** (onboarded before standardization): Bills contain **Rent + Deposit** line items
  - Example: Adryan Kipkirui Langat - Bill shows KES 12,500 (KES 6,500 rent + KES 6,000 deposit)
- **New monthly bills** (via "Generate Bills" button): Bills contain **Rent only**
  - Example: If Adryan's bill was generated monthly, it would show KES 6,500 only

### Impact
- Confusing financial reports
- Inconsistent payment allocation
- Difficult to reconcile tenant balances
- Bills look "ridiculous" with inflated amounts

## Solution

### 1. Database Migration (Fix Existing Data)

**File:** `backend/database/migrations/standardize_bill_structure.sql`

This script will:
- ✅ Remove Deposit line items from old tenants' bills
- ✅ Recalculate bill totals
- ✅ Reallocate existing payments to Rent line items only
- ✅ Recalculate bill statuses and tenant balances
- ✅ Preserve Deposit line items for new tenants (created after cutoff date)

#### How to Apply:

1. **BACKUP YOUR DATABASE FIRST!**
   ```sql
   -- In phpMyAdmin or MySQL CLI
   CREATE DATABASE RentalFlow_backup_YYYYMMDD;
   -- Or use mysqldump
   ```

2. **Update the cutoff date** in the SQL file:
   ```sql
   -- Change this to match when you want to distinguish old vs new tenants
   SET @CUTOFF_DATE = '2025-08-01 00:00:00';
   ```
   - Tenants created **before** this date = Old tenants (Rent only bills)
   - Tenants created **on or after** this date = New tenants (Rent + Deposit bills)

3. **Run the migration:**
   - Open phpMyAdmin
   - Select your production database
   - Go to SQL tab
   - Copy-paste the contents of `standardize_bill_structure.sql`
   - Execute

4. **Verify the results:**
   - The script includes verification queries at the bottom
   - Check that old tenants now show only Rent line items
   - Check that new tenants still have Rent + Deposit

### 2. Code Fix (Prevent Future Inconsistency)

**File Modified:** `backend/app/Controllers/TenantController.php`

**Change:** Removed the code that adds Deposit line item to initial bills during tenant onboarding.

#### What Changed:
```php
// BEFORE (lines 308-314):
$chargeItems = [
    ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => (float)$data['rent']],
];
$depositAmount = (float) ($data['deposit'] ?? 0);
if ($depositAmount > 0) {
    $chargeItems[] = ['type' => 'Deposit', 'description' => 'Security Deposit', 'amount' => $depositAmount];
}

// AFTER (lines 310-312):
$chargeItems = [
    ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => (float)$data['rent']],
];
```

**Impact:** 
- ✅ All new tenants will have consistent bill structure (Rent only)
- ✅ Matches the monthly bill generation logic in `BillController::generate`
- ✅ Deposit amount is still stored in `tenants.deposit` for reference
- ✅ No deposit line items in any bills going forward

### 3. No Changes Needed in Frontend

The frontend files (`tenants.php`, `bills.php`, `payments.php`) don't need any changes because:
- They display whatever is in the database
- After the SQL migration, the data will be consistent
- The bill listing in `bills.php` shows the `bills.total` which will be correct after recalculation

## Standardized Behavior (After Fix)

### All Tenants (Old and New)
- **Bill Structure:** Rent line item only
- **Bill Amount:** Equals the monthly rent amount
- **Monthly Generation:** Consistent with initial bill
- **Deposit Handling:** Stored in `tenants.deposit` for reference, but not billed

### Example (After Fix)

| Tenant | Rent | Deposit | Bill Amount | Bill Line Items |
|--------|------|---------|-------------|-----------------|
| Adryan Kipkirui Langat | 6,500 | 6,000 | **6,500** | Rent: 6,500 |
| Austin Kariuki | 7,000 | 7,000 | **7,000** | Rent: 7,000 |
| New Tenant (future) | 10,000 | 15,000 | **10,000** | Rent: 10,000 |

## Rollback Procedure

If something goes wrong, you can restore from the backup tables created by the migration:

```sql
-- Restore bills
TRUNCATE TABLE bills;
INSERT INTO bills SELECT * FROM bills_backup_standardization;

-- Restore bill_items
TRUNCATE TABLE bill_items;
INSERT INTO bill_items SELECT * FROM bill_items_backup_standardization;

-- Restore payment_allocations
TRUNCATE TABLE payment_allocations;
INSERT INTO payment_allocations SELECT * FROM payment_allocations_backup_standardization;

-- Recalculate tenant balances
UPDATE tenants t
LEFT JOIN (
    SELECT b.tenant_id, COALESCE(SUM(bi.amount),0) AS bill_total, COALESCE(SUM(pa.amount),0) AS paid_total
    FROM bills b
    JOIN bill_items bi ON bi.bill_id = b.id
    LEFT JOIN payment_allocations pa ON pa.bill_item_id = bi.id
    GROUP BY b.tenant_id
) x ON x.tenant_id = t.id
SET t.balance = GREATEST(0, COALESCE(x.bill_total,0) - COALESCE(x.paid_total,0));
```

## Testing Checklist

After applying the fix:

- [ ] Run the SQL migration in a staging environment first
- [ ] Verify old tenants show only Rent line items
- [ ] Verify new tenants still have Rent + Deposit (if any)
- [ ] Check that bill totals match expected rent amounts
- [ ] Verify payment allocations are correct
- [ ] Confirm tenant balances are accurate
- [ ] Test tenant onboarding creates Rent-only bills
- [ ] Test monthly bill generation creates Rent-only bills
- [ ] Generate a test bill and verify it matches the structure

## Questions?

If you need help:
1. Check the verification queries in the SQL file
2. Review the backup tables if you need to rollback
3. Test in staging before applying to production

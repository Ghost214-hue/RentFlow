# RentaFlow: Tenants, Bills & Payments Relationship

## Overview

This document explains the data flow and business logic connecting three core modules:
- **Tenants** (`frontend/pages/tenants.php`) — Onboarding, profile, financial snapshot
- **Bills** (`frontend/pages/bills.php`) — Monthly invoice generation
- **Payments** (`frontend/pages/payments.php`) — Payment recording & allocation

The goal is to clarify why numbers may appear inconsistent across these views and how the database state should be interpreted.

---

## 1. Database Schema (Relevant Tables)

### `tenants`
| Column | Purpose |
|--------|---------|
| `rent` | Snapshot of the monthly rent at the time of onboarding. **Not used for future bill generation.** |
| `deposit` | Security deposit amount collected at move-in. **Only appears in the initial onboarding bill.** |
| `balance` | Cached total outstanding across all bills for this tenant. Recalculated by `recalcTenantCreditAndBalance()`. |
| `credit` | Cached overpayment amount. |
| `water_balance` | Legacy water balance (largely superseded by bill_items). |
| `elec_balance` | Legacy electricity balance (largely superseded by bill_items). |
| `status` | `active` or `terminated`. |

### `houses`
| Column | Purpose |
|--------|---------|
| `rent` | **Source of truth** for monthly rent when bills are generated. |
| `status` | `occupied` or `vacant`. |

### `bills`
| Column | Purpose |
|--------|---------|
| `month` | Billing period in `YYYY-MM` format. |
| `total` | Sum of all `bill_items.amount` for this bill. |
| `status` | `pending`, `partial`, or `paid`. |
| `house_id` | Links to the unit. |
| `tenant_id` | Links to the tenant. |

### `bill_items`
| Column | Purpose |
|--------|---------|
| `type` | `Rent`, `Water`, `Electricity`, `Deposit`, or other. |
| `amount` | Expected charge. |
| `paid` | Amount paid against this specific line item. |
| `status` | `pending`, `partial`, or `paid`. |


---

## 2. Tenant Onboarding Flow

### Frontend (`tenants.php`)
In Step 5 (Finance), the user enters:
- **Monthly Rent (KES)** → stored as `tenants.rent`
- **Security Deposit (KES)** → stored as `tenants.deposit`
- **Opening Balance (KES)** → stored as `tenants.balance`

The rent field is auto-filled from `houses.rent` when a unit is selected, but can be overridden.

### Backend (`TenantController::store`)

```php
// 1. Insert tenant record with rent, deposit, balance
$tenantId = $db->insert('tenants', $insertData);

// 2. Mark house as occupied
$db->update('houses', ['status' => 'occupied', 'tenant_id' => $tenantId], ...);

// 3. Generate initial bill for current month with Rent + Deposit line items
$chargeItems = [
    ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => $rent],
];
if ($depositAmount > 0) {
    $chargeItems[] = ['type' => 'Deposit', 'description' => 'Security Deposit', 'amount' => $depositAmount];
}
$billingService->createBillWithItems($ownerId, $houseId, $tenantId, $month, $dueDate, $chargeItems);
```

**Key insight:** The initial bill created during onboarding includes both Rent and Deposit as separate line items. After this, **deposit is never again added to monthly bills automatically**.

---

## 3. Monthly Bill Generation Flow


---

## 4. Payment Recording & Allocation Flow

### Frontend (`payments.php`)
Owner/caretaker records a payment with:
- Tenant
- Amount
- Type (`Rent`, `Water`, `Electricity`, `Deposit`, `Mixed`)

### Backend (`PaymentController::store`)

```php
// 1. Insert payment record
$paymentId = $db->insert('payments', [...]);

// 2. If a bill exists for this house + month, allocate payment
if ($bill) {
    $billingService->allocatePayment($paymentId, $billId, $amount, $type);
}

// 3. Recalculate tenant-level balance and credit
$billingService->recalcTenantCreditAndBalance($tenantId);
```

### `BillingService::allocatePayment`

The payment is allocated across outstanding `bill_items` in a **fixed priority order**:
1. Rent
2. Water
3. Electricity
### `BillingService::recalcTenantCreditAndBalance`

```php
$itemTotal = sum(bill_items.amount for all bills of this tenant);
$paidAgainstItems = sum(payment_allocations.amount for this tenant);
$totalPayments = sum(payments.amount for this tenant where status is confirmed/completed/paid);

$balance = max(0, $itemTotal - $paidAgainstItems);
$credit  = max(0, $totalPayments - $itemTotal);
```

This updates `tenants.balance` and `tenants.credit`.

---

## 5. Why Numbers May "Not Add Up"

### 5.1 Bills Page vs Tenants Page Balance

| View | How Balance is Calculated |
|------|---------------------------|
| **Bills page** | Per-bill: `bills.total - sum(payments where house_id=X AND month=Y)` |
| **Tenants page** | Per-tenant: `sum(all bill_items) - sum(all payment_allocations)` |

These should usually agree, but discrepancies can occur if:
- `payment_allocations` are missing or out of sync.
- Payments exist for a house+month but no bill exists (or vice versa).
- A payment was recorded with `status = 'pending'` or `'failed'` — it is ignored by the Bills page paid calculation (which filters by `status IN ('confirmed','completed','paid')`) but might still be counted in other contexts.

### 5.2 Deposit Confusion

| Scenario | What Happens |
|----------|--------------|
| Tenant onboarding with deposit | Deposit appears as a line item in the **initial** bill only. |
| Subsequent monthly bill generation | **No deposit line item** is created. |
| Tenant pays deposit in month 1 | Allocated to the Deposit line item of the initial bill. |
### 5.3 Rent Snapshot vs Actual Rent

- `tenants.rent` is set at onboarding and can be manually edited.
- Monthly bills use `houses.rent`.
- If these two values diverge, the Bills page will show one number while the Tenants page may show another.

### 5.4 Credit & Overpayment

If a tenant pays more than their total billed amount:
- `tenants.credit` increases.
- The Bills page per-bill balance cannot go below zero.
- The Tenants page "Balance" column shows `tenants.balance` (which is zero), but the "Opening Balance" or credit field reflects the overpayment.

### 5.5 Payment Type Mismatch

When an owner records a payment with type `Deposit` for a tenant who has no outstanding deposit line item (because deposit was already paid or is on a past bill), the `allocatePayment` function will apply it to the next outstanding item (usually Rent). The payment type is informational and does not constrain allocation.

---

## 6. Correctness Guarantees & Known Edge Cases

### Guaranteed
- A bill for `house_id + month` is created exactly once (idempotent).
- Bill total always equals the sum of its line items.
- Tenant balance and credit are recalculated on every confirmed/completed/paid payment.

### Edge Cases to Watch
1. **Missing `payment_allocations`**: If a payment exists but has no allocations (e.g., bug or manual DB change), `tenants.balance` will be higher than expected, while the Bills page may show the bill as partially paid (because it sums raw payments).

---

## 7. API Endpoints Summary

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/api/tenants` | List tenants (with balance, deposit, rent). |
| `POST` | `/api/tenants` | Register tenant + create initial bill. |
| `PUT` | `/api/tenants/{id}` | Update tenant (including manual balance/deposit edits). |
| `GET` | `/api/bills` | List bills with computed `paid` and `balance`. |
| `POST` | `/api/bills/generate` | Generate monthly bills for occupied units. |
| `POST` | `/api/payments` | Record a payment and allocate to bills. |
| `GET` | `/api/payments/tenant-finance/{id}` | Get tenant monthly rent, arrears, overpaid, suggested payment. |
| `PUT` | `/api/payments/{id}/confirm` | Tenant confirms a payment record. |

---

## 8. Debugging "Not Adding Up" — Checklist

If financial totals appear inconsistent, check in this order:

1. **Run this query to see payment vs allocation mismatch:**
   ```sql
   SELECT p.id, p.amount, p.type, p.status,
          COALESCE(SUM(pa.amount), 0) as allocated
   FROM payments p
   LEFT JOIN payment_allocations pa ON pa.payment_id = p.id
   WHERE p.owner_id = YOUR_OWNER_ID
   GROUP BY p.id
   HAVING allocated != p.amount;
   ```

2. **Check for orphaned payments** (payments without a matching bill for that house+month).

3. **Verify `tenants.balance` matches manual calculation:**
   ```sql
   SELECT t.id, t.name, t.balance, t.credit,
          (SELECT COALESCE(SUM(amount),0) FROM bill_items WHERE bill_id IN (SELECT id FROM bills WHERE tenant_id = t.id)) as total_billed,
          (SELECT COALESCE(SUM(pa.amount),0) FROM payment_allocations pa JOIN payments p ON pa.payment_id = p.id WHERE p.tenant_id = t.id AND p.status IN ('confirmed','completed','paid')) as total_allocated
   FROM tenants t
   WHERE t.owner_id = YOUR_OWNER_ID;
   ```

4. **Check if `houses.rent` differs from `tenants.rent`** — bills use `houses.rent`.

5. **Look for deposit line items only on the first bill** — subsequent bills will not have them.

6. **Check payment statuses** — only `confirmed`, `completed`, and `paid` payments count toward balances.

2. **Deposit line items on old bills**: Bills generated during tenant onboarding have a Deposit line item. Bills generated via the monthly bill generator do not. This is intentional but visually inconsistent.
3. **Termination / Vacate**: When a tenant is terminated, their house is freed. However, their final bills and payments remain. The `tenants.status` is set to `terminated` but financial history is preserved.
4. **Multiple properties**: A tenant belongs to one property at a time. Bills are scoped by `house_id + month`.

| Tenant pays "Deposit" type payment in month 2 | Allocated to outstanding Rent first (priority order), then other items. |

**Common confusion:** The `tenants.deposit` field is a **static snapshot**. It does not mean a deposit charge appears on every bill.

4. Deposit
5. Other

Each allocation creates a row in `payment_allocations` and updates `bill_items.paid` and `bill_items.status`.

**Note:** The `payment.type` (e.g., `Deposit`) does **not** restrict which line items receive the allocation. A `Deposit`-type payment will still be applied to outstanding Rent first if Rent is unpaid.

### Frontend (`bills.php`)
Owner clicks **Generate Bills** → `POST /api/bills/generate` with optional `property_id`.

### Backend (`BillController::generate`)

```php
// For each occupied house:
$chargeItems = [
    ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => $house['rent']],
];
if ($water > 0) {
    $chargeItems[] = ['type' => 'Water', ...];
}
if ($electricity > 0) {
    $chargeItems[] = ['type' => 'Electricity', ...];
}
// NO Deposit line item here.
```

**Idempotency:** If a bill already exists for `house_id + month`, it is **never overwritten**. Existing line items, paid amounts, and allocations are preserved.

**Critical difference from onboarding:** The monthly bill generator reads `houses.rent`, **not** `tenants.rent`. If the house rent was updated after onboarding, the new rent is used for future bills.

### `payments`
| Column | Purpose |
|--------|---------|
| `amount` | Payment amount. |
| `type` | `Rent`, `Water`, `Electricity`, `Deposit`, `Mixed`. |
| `status` | `completed`, `pending`, `failed`. |
| `tenant_confirmed` | Whether the tenant has acknowledged the payment. |
| `house_id` | Which unit the payment applies to. |
| `month` | Billing month the payment is for. |

### `payment_allocations`
| Column | Purpose |
|--------|---------|
| `payment_id` | Links to the payment. |
| `bill_item_id` | Links to the specific bill line item. |
| `amount` | Portion of the payment applied to that line item. |

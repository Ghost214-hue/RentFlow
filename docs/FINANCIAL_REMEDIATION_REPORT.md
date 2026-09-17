# RentFlow Financial Remediation Report — Billing, Payments, Balances, Invoices & Email

## A. Root Cause Report

| Symptom | Root cause (pre-fix) |
|---|---|
| Bills page ≠ Invoice amounts | `BillController::invoice()` independently re-derived totals: appended a display-only "Opening Balance" item, subtracted credit separately, and summed payment allocations across **all** bills with `month <= current`. Prior-month payments were counted both inside the carry-forward balance AND again as payments → mismatch. |
| "Previous Month Balance" duplicated | Carry-forward was added as a line item in the invoice **and** in the bill list view, and the tenant-consolidated invoice path summed stored items + opening balance without netting; retried/double-rendered aggregation produced the `500 + 500` pattern. |
| Email ≠ Invoice | `generate()` built email figures from `SUM(bills.total)` and echoed the same number as "balance", ignoring payment allocations and carry-forward entirely. |
| Negative balances | `index()` and `invoice()` deliberately allowed negative balances ("Allow negative for overpayment") instead of modeling overpayment as tenant credit (`tenants.credit`, already present). |
| Statuses inconsistent | Status derived independently in 4+ places with different rules; no OVERDUE state. |
| Deposit double-billing | Already fixed by migrations 036/037 (recurring deposit stripped; bills standardized to rent-only). |

## B. Architecture — Before vs After

**Before:** each surface recalculated — Bills list math, Invoice math, Email `SUM(bills.total)` — all diverging.

**After (single source of truth):**

```
bills ──► bill_items (charges)            ┐
payment_allocations (confirmed payments)  ├─► BillingService::getAuthoritativeSnapshot()
getTenantCarryForward (opening/credit)    ┘        │
                                     finalizeFinancials(): total = items + opening − credit
                                                           balance = max(0, total − paid)
                                                           status  = paid/partial/pending/overdue
        ├─ GET /bills (index)         → snapshot
        ├─ GET /bills/{id}            → items + allocations
        ├─ GET /bills/{id}/invoice    → snapshot (display-only carry-forward line, ONCE)
        ├─ Billing email (generate)   → snapshot figures
        └─ Tenant portal / reports    → same bill rows + snapshot contract
```

## C. Database Changes

Already present (previous remediation): `bill_items`, `payment_allocations`, `tenants.credit`,
unique indexes `uq_bills_house_month` (030) and `uq_bills_owner_tenant_month` (032).

New: **`038_financial_reconciliation_audit.sql`** — read-only audit queries for the remediation plan below.

## D. Code Changes

| File | Change |
|---|---|
| `app/Services/BillingService.php` | Added `finalizeFinancials()`, `deriveStatus()`, `deriveStatusWithDueDate()`, `getAuthoritativeSnapshot()` — the authoritative derivation. Balances never negative. |
| `app/Controllers/BillController.php` | `index()`: uses snapshot (no negative balance, consistent status). `invoice()`: **no recalculation** — reads persisted items/allocations + carry-forward once; paid = allocations on this bill only (cross-month double-count removed). `generate()`: wrapped in a DB transaction; email figures now come from the snapshot (`amount`, `balance`, `amount_paid`, `outstanding_balance`). |
| `app/Services/EmailService.php` | `stripUnresolvedPlaceholders()` / `containsUnresolvedPlaceholders()`; enforced in `sendTemplate()`, `queueTemplate()` and as a final net in `send()` — `{{recipient_note}}` and `${...}` can never reach a tenant. Added `{{amount_paid}}`/`{{outstanding_balance}}` variables; `replaceVariables()` made static. |
| `frontend/pages/bills.php` | No change needed — already renders authoritative API fields (`amount`, `paid`, `balance`, `status`). |
| `backend/tests/billing_consistency_test.php` | 13 regression tests (all passing). |
| `backend/database/migrations/038_financial_reconciliation_audit.sql` | Audit queries. |

## E. Historical Data Remediation Plan

1. **Backup**: `mysqldump rentaflow > rentaflow_backup_$(date +%F).sql`
2. **Audit**: run `038_financial_reconciliation_audit.sql`; export each result set.
3. **Reconcile** (corrective scripts, no blind deletes):
   - Duplicate tenant/month bills → keep lowest `id`, move `payment_allocations` to surviving bill's items, void (don't delete) duplicates.
   - Duplicate Opening-Balance items → delete surplus `bill_items` rows flagged by query 3 (charges are reconstructible from the carry-forward query), then `UPDATE bills SET total = (SELECT SUM(amount) FROM bill_items WHERE bill_id = bills.id)`.
   - Negative totals → re-derive via snapshot; overpayments go to `tenants.credit`.
   - Mis-allocated payments (query 6/7) → re-allocate oldest-arrears-first.
   - Tenant balances → run `recalcTenantCreditAndBalance($tenantId)` per affected tenant (or the equivalent UPDATE).
4. **Verify**: re-run audit queries 2, 3, 4, 5, 6, 7, 8 — all must return zero rows.

## F. Automated Tests — Results

`php backend/tests/billing_consistency_test.php` → **13 passed, 0 failed**:
T1 fully-paid prior month (opening 0), T2 outstanding carried once (total 7000),
T3 partial, T4 overpayment→credit not negative, T5 zero-bill+payment, T6 idempotent
generation (DB-gated), T8 opening balance appears once, T18 status rules incl.
overdue, T10 placeholder validation (a/b/c), epsilon rounding edge. DB integration
tests auto-skip when the test database is unreachable.

## Acceptance

Bills page, invoice PDF, billing email and tenant portal now all derive from
`getAuthoritativeSnapshot()` on the same persisted rows — identical
`total / paid / balance / status` for the same bill, structurally guaranteed.

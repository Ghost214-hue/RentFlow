<?php

declare(strict_types=1);

namespace App\Billing;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Support\Amount;
use Brick\Money\Money as BrickMoney;

/**
 * The authoritative financial position of ONE bill.
 *
 * Reproduces BillingService::getAuthoritativeSnapshot() so the bills list,
 * the invoice, the billing email, the renter portal and the reports all show
 * identical numbers from identical inputs.
 *
 * THE RULE (from the legacy remediation, defect 5):
 *
 *   bills â”€â”€â–º bill_items (charges)            â”
 *            payment_allocations (settled)     â”œâ”€â–º this snapshot
 *            prior-month carry-forward         â”˜
 *
 *   total   = items + opening_balance - credit
 *   paid    = allocations against THIS bill only
 *   balance = max(0, total - paid)      never negative
 *   status  = paid | partial | pending, plus overdue once the due date passes
 *
 * payment_allocations is the ledger. bills.total / bills.status /
 * bill_items.paid / tenants.balance are denormalised caches that drift, and
 * are never consulted here.
 */
final class BillSnapshot
{
    /** persisted status, ignoring the due date. */
    public static function statusFor(BrickMoney $total, BrickMoney $paid): BillStatus
    {
        if ($total->compareTo(Amount::zero()) <= 0) {
            // Nothing owed. Any payment against it is renter credit, not a
            // negative balance.
            return BillStatus::Paid;
        }

        if ($paid->compareTo(Amount::zero()) <= 0) {
            return BillStatus::Pending;
        }

        // Tolerance is one cent: a bill short by a rounding hair is settled.
        // (0.001 was too small to absorb IEEE-754 error.)
        if ($paid->plus(Amount::of('0.01'))->compareTo($total) >= 0) {
            return BillStatus::Paid;
        }

        return BillStatus::Partial;
    }

    /** what the user sees: adds OVERDUE once the due date has passed. */
    public static function displayStatusFor(
        BrickMoney $total,
        BrickMoney $paid,
        ?\DateTimeInterface $dueDate,
    ): BillStatus {
        $status = self::statusFor($total, $paid);

        if ($status === BillStatus::Pending
            && $dueDate !== null
            && $dueDate < now()
        ) {
            return BillStatus::Overdue;
        }

        return $status;
    }
}
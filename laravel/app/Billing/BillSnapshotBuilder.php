<?php

declare(strict_types=1);

namespace App\Billing;

use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Models\Bill;
use App\Support\Amount;
use Brick\Money\Money as BrickMoney;

/**
 * Builds BillSnapshot from persisted rows only.
 *
 * Split out from BillSnapshot so the derivation rules stay readable: every
 * figure comes from the allocation ledger, never from a denormalised cache.
 */
final class BillSnapshotBuilder
{
    /**
     * Settled payment statuses. 'completed' is what the column stores;
     * 'confirmed' was the legacy vocabulary (defect 2) and is still accepted
     * so historic rows keep counting.
     *
     * @return array<int, string>
     */
    public const SETTLED = ['completed', 'confirmed', 'paid'];

    public function build(Bill $bill): BillSnapshotData
    {
        $itemsTotal = Amount::sum($bill->items()->pluck('amount'));

        // Paid = allocations against THIS bill only. Summing across months is
        // the bug that previously double-counted prior payments.
        $paid = Amount::sum(
            \App\Models\PaymentAllocation::query()
                ->whereHas('billItem', fn ($q) => $q->where('bill_id', $bill->getKey()))
                ->whereHas('payment', fn ($q) => $q->whereIn('status', self::SETTLED))
                ->pluck('amount')
        );

        [$opening, $credit] = $this->carryForward($bill);

        $total = Amount::atLeastZero($itemsTotal->plus($opening)->minus($credit));

        return new BillSnapshotData(
            billId: (int) $bill->getKey(),
            itemsTotal: $itemsTotal,
            openingBalance: $opening,
            creditApplied: $credit,
            total: $total,
            paid: $paid,
            balance: Amount::owed($total, $paid),
            status: BillSnapshot::statusFor($total, $paid),
            displayStatus: BillSnapshot::displayStatusFor($total, $paid, $bill->due_date),
            dueDate: $bill->due_date,
        );
    }

    /**
     * Prior-month position carried into this bill.
     *
     * Positive = arrears brought forward, negative = credit brought forward.
     * Scoped to STRICTLY EARLIER months, so the carry appears exactly once
     * and can never be double-added.
     *
     * @return array{0: BrickMoney, 1: BrickMoney}
     */
    private function carryForward(Bill $bill): array
    {
        if ($bill->tenant_id === null || $bill->month === null) {
            return [Amount::zero(), Amount::zero()];
        }

        $priorBills = Bill::query()
            ->where('tenant_id', $bill->tenant_id)
            ->where('month', '<', $bill->month)
            ->with('items:id,bill_id,amount')
            ->get();

        $priorBilled = Amount::sum(
            $priorBills->flatMap(fn (Bill $b) => $b->items->pluck('amount'))
        );

        $priorPaid = Amount::sum(
            \App\Models\Payment::query()
                ->where('tenant_id', $bill->tenant_id)
                ->whereIn('status', self::SETTLED)
                ->whereHas('allocations.billItem.bill', fn ($q) => $q
                    ->where('tenant_id', $bill->tenant_id)
                    ->where('month', '<', $bill->month))
                ->get()
                ->pluck('amount')
        );

        $difference = $priorBilled->minus($priorPaid);

        return [
            Amount::atLeastZero($difference),
            $difference->isNegative() ? Amount::absolute($difference) : Amount::zero(),
        ];
    }
}
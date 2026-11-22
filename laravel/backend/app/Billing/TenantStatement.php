<?php

declare(strict_types=1);

namespace App\Billing;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Renter;
use App\Support\Amount;
use Brick\Money\Money;
use Carbon\CarbonImmutable;

/**
 * A tenant's account statement ("revenue portfolio"): month by month what was
 * charged, what was paid against it, and what is still owed, plus every payment
 * and which months it settled.
 *
 * Everything comes from the allocation ledger, per bill item, so:
 *   - a month's "unpaid" is only THAT month's own unpaid charges;
 *   - "balance" is the running total owed up to and including that month
 *     (arrears brought forward + this month), never double counted;
 *   - the figures match the dashboard and the bills page.
 *
 * All money is returned as strings; the browser only formats it.
 */
final class TenantStatement
{
    /** @return array<string, mixed> */
    public function build(Renter $renter): array
    {
        $settled = BillSnapshotBuilder::SETTLED;
        $today = CarbonImmutable::today();

        $bills = Bill::query()
            ->where('tenant_id', $renter->getKey())
            ->with('items:id,bill_id,type,amount')
            ->orderBy('month')
            ->orderBy('id')
            ->get();

        $itemIds = $bills->flatMap(fn (Bill $b) => $b->items->pluck('id'))->all();

        $paidByItem = $itemIds === [] ? collect() : PaymentAllocation::query()
            ->selectRaw('bill_item_id, SUM(amount) AS paid')
            ->whereIn('bill_item_id', $itemIds)
            ->whereIn('payment_id', Payment::query()->whereIn('status', $settled)->select('id'))
            ->groupBy('bill_item_id')
            ->pluck('paid', 'bill_item_id');

        $payments = Payment::query()
            ->where('tenant_id', $renter->getKey())
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $receivedByMonth = [];
        foreach ($payments as $p) {
            if (in_array($p->status instanceof \BackedEnum ? $p->status->value : (string) $p->status, $settled, true)) {
                $key = $p->date?->format('Y-m') ?? 'unknown';
                $receivedByMonth[$key] = ($receivedByMonth[$key] ?? Amount::zero())->plus(Amount::of($p->amount));
            }
        }

        $rows = [];
        $carry = Amount::zero();          // unpaid from earlier months
        $totalBilled = Amount::zero();
        $totalPaid = Amount::zero();
        $overdue = Amount::zero();

        foreach ($bills as $bill) {
            $byType = ['Rent' => Amount::zero(), 'Water' => Amount::zero(), 'Electricity' => Amount::zero(), 'Other' => Amount::zero()];
            $charges = Amount::zero();
            $paid = Amount::zero();

            foreach ($bill->items as $item) {
                $amount = Amount::of($item->amount);
                $cleared = $this->min($amount, Amount::of($paidByItem[$item->getKey()] ?? '0'));

                $key = array_key_exists((string) $item->type, $byType) ? (string) $item->type : 'Other';
                $byType[$key] = $byType[$key]->plus($amount);
                $charges = $charges->plus($amount);
                $paid = $paid->plus($cleared);
            }

            $own = Amount::atLeastZero($charges->minus($paid));
            $isOverdue = $own->compareTo(Amount::zero()) > 0 && $bill->due_date !== null && $bill->due_date->lt($today);

            $status = match (true) {
                $own->compareTo(Amount::zero()) <= 0 => 'paid',
                $isOverdue => 'overdue',
                $paid->compareTo(Amount::zero()) > 0 => 'partial',
                default => 'pending',
            };

            $rows[] = [
                'bill_id' => (int) $bill->getKey(),
                'month' => (string) $bill->month,
                'due_date' => $bill->due_date?->toDateString(),
                'rent' => Amount::toString($byType['Rent']),
                'water' => Amount::toString($byType['Water']),
                'electricity' => Amount::toString($byType['Electricity']),
                'other' => Amount::toString($byType['Other']),
                'charges' => Amount::toString($charges),
                'paid' => Amount::toString($paid),
                'unpaid' => Amount::toString($own),
                'brought_forward' => Amount::toString($carry),
                'balance' => Amount::toString($carry->plus($own)),
                'received_in_month' => Amount::toString($receivedByMonth[(string) $bill->month] ?? Amount::zero()),
                'status' => $status,
            ];

            $carry = $carry->plus($own);
            $totalBilled = $totalBilled->plus($charges);
            $totalPaid = $totalPaid->plus($paid);
            if ($isOverdue) {
                $overdue = $overdue->plus($own);
            }
        }

        $received = Amount::sum(
            $payments->filter(fn (Payment $p) => in_array(
                $p->status instanceof \BackedEnum ? $p->status->value : (string) $p->status, $settled, true
            ))->pluck('amount')
        );

        // Which months each payment settled.
        $paymentIds = $payments->pluck('id')->all();
        $allocs = $paymentIds === [] ? collect() : PaymentAllocation::query()
            ->join('bill_items', 'bill_items.id', '=', 'payment_allocations.bill_item_id')
            ->join('bills', 'bills.id', '=', 'bill_items.bill_id')
            ->whereIn('payment_allocations.payment_id', $paymentIds)
            ->selectRaw('payment_allocations.payment_id, bills.month, SUM(payment_allocations.amount) AS amount')
            ->groupBy('payment_allocations.payment_id', 'bills.month')
            ->orderBy('bills.month')
            ->get()
            ->groupBy('payment_id');

        $paymentRows = $payments->map(fn (Payment $p): array => [
            'id' => (int) $p->getKey(),
            'date' => $p->date?->toDateString(),
            'receipt' => $p->receipt,
            'method' => $p->method,
            'type' => $p->type,
            'status' => $p->status instanceof \BackedEnum ? $p->status->value : (string) $p->status,
            'amount' => Amount::toString(Amount::of($p->amount)),
            'applied_to' => ($allocs[$p->getKey()] ?? collect())->map(fn ($a): array => [
                'month' => (string) $a->month,
                'amount' => Amount::toString(Amount::of($a->amount)),
            ])->values()->all(),
        ])->values()->all();

        return [
            'summary' => [
                'billed' => Amount::toString($totalBilled),
                'paid' => Amount::toString($totalPaid),
                'outstanding' => Amount::toString($carry),
                'overdue' => Amount::toString($overdue),
                'received' => Amount::toString($received),
                // Money received that has not been applied to any charge.
                'credit' => Amount::toString(Amount::atLeastZero($received->minus($totalPaid))),
            ],
            // Newest month first for display.
            'months' => array_reverse($rows),
            'payments' => $paymentRows,
        ];
    }

    private function min(Money $a, Money $b): Money
    {
        return $a->compareTo($b) <= 0 ? $a : $b;
    }
}

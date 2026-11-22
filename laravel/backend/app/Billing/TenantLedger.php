<?php

declare(strict_types=1);

namespace App\Billing;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Renter;
use App\Support\Amount;
use Illuminate\Support\Facades\DB;

/**
 * THE one place that reconciles a tenant's bills against their payments.
 *
 * The allocation ledger (payment_allocations) is the source of truth. Every
 * cached column (bill_items.paid/status, bills.status/total, tenants.balance
 * /credit) is rebuilt from it here, so nothing can drift.
 *
 * It is idempotent: running it twice changes nothing the second time. Call it
 * after recording a payment, after reversing one, after generating bills, and
 * nightly (billing:reconcile --fix).
 *
 * What it does, in order:
 *   1. Legacy bills that have a total but NO bill_items (created by the old
 *      send:monthly:bills command) get a Rent item, so they can be paid.
 *   2. Allocations that belong to payments that are no longer settled
 *      (reversed / failed / pending) are removed -- they are phantom money.
 *   3. Any settled money not yet allocated (prepayment made before the bill
 *      existed, or an overpayment) is applied, oldest unpaid item first.
 *      This is what makes a new month's bill "start from zero": last month's
 *      surplus or the tenant's advance payment is absorbed automatically.
 *   4. Caches are rewritten from the ledger.
 */
final class TenantLedger
{
    /** @return array<string, mixed> */
    public function reconcile(int $tenantId): array
    {
        return DB::transaction(function () use ($tenantId): array {
            $renter = Renter::query()->lockForUpdate()->findOrFail($tenantId);
            $settled = BillSnapshotBuilder::SETTLED;

            $report = [
                'tenant_id' => $tenantId,
                'backfilled_items' => 0,
                'stale_allocations_removed' => 0,
                'applied' => '0.00',
                'over_allocated_items' => 0,
            ];

            $bills = Bill::query()
                ->where('tenant_id', $tenantId)
                ->with('items')
                ->orderBy('month')
                ->orderBy('id')
                ->get();

            // 1. Legacy bills without items.
            foreach ($bills as $bill) {
                if ($bill->items->isEmpty() && Amount::of($bill->total)->compareTo(Amount::zero()) > 0) {
                    $item = BillItem::create([
                        'bill_id' => $bill->getKey(),
                        'type' => 'Rent',
                        'description' => 'Monthly Rent',
                        'amount' => Amount::toString(Amount::of($bill->total)),
                        'paid' => '0.00',
                        'status' => 'pending',
                    ]);
                    $bill->setRelation('items', collect([$item]));
                    $report['backfilled_items']++;
                }
            }

            $items = $bills->flatMap(fn (Bill $b) => $b->items)->values();
            $itemIds = $items->pluck('id')->all();

            // 2. Allocations held by payments that are not settled.
            $report['stale_allocations_removed'] = PaymentAllocation::query()
                ->whereIn('bill_item_id', $itemIds === [] ? [0] : $itemIds)
                ->whereIn('payment_id', Payment::query()->whereNotIn('status', $settled)->select('id'))
                ->delete();

            // Settled money already sitting on each item.
            $paidByItem = PaymentAllocation::query()
                ->selectRaw('bill_item_id, SUM(amount) AS paid')
                ->whereIn('bill_item_id', $itemIds === [] ? [0] : $itemIds)
                ->whereIn('payment_id', Payment::query()->whereIn('status', $settled)->select('id'))
                ->groupBy('bill_item_id')
                ->pluck('paid', 'bill_item_id');

            $remaining = [];
            foreach ($items as $item) {
                $paid = Amount::of($paidByItem[$item->getKey()] ?? '0');
                $owed = Amount::of($item->amount)->minus($paid);
                if ($owed->isNegative()) {
                    $report['over_allocated_items']++;
                }
                $remaining[$item->getKey()] = Amount::atLeastZero($owed);
            }

            // 3. Apply unallocated settled money, oldest item first.
            $payments = Payment::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('status', $settled)
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $allocatedByPayment = PaymentAllocation::query()
                ->selectRaw('payment_id, SUM(amount) AS allocated')
                ->whereIn('payment_id', $payments->pluck('id')->all() ?: [0])
                ->groupBy('payment_id')
                ->pluck('allocated', 'payment_id');

            $applied = Amount::zero();

            foreach ($payments as $payment) {
                $left = Amount::atLeastZero(
                    Amount::of($payment->amount)->minus(Amount::of($allocatedByPayment[$payment->getKey()] ?? '0'))
                );

                foreach ($items as $item) {
                    if ($left->compareTo(Amount::zero()) <= 0) {
                        break;
                    }

                    $owed = $remaining[$item->getKey()];
                    if ($owed->compareTo(Amount::zero()) <= 0) {
                        continue;
                    }

                    $take = $owed->compareTo($left) <= 0 ? $owed : $left;

                    PaymentAllocation::create([
                        'payment_id' => $payment->getKey(),
                        'bill_item_id' => $item->getKey(),
                        'category' => (string) $item->type,
                        'amount' => Amount::toString($take),
                    ]);

                    $remaining[$item->getKey()] = $owed->minus($take);
                    $left = $left->minus($take);
                    $applied = $applied->plus($take);
                    $paidByItem[$item->getKey()] = Amount::toString(
                        Amount::of($paidByItem[$item->getKey()] ?? '0')->plus($take)
                    );
                }
            }

            $report['applied'] = Amount::toString($applied);

            // 4. Rebuild caches from the ledger.
            foreach ($bills as $bill) {
                $billItems = $bill->items;
                $itemsTotal = Amount::sum($billItems->pluck('amount'));
                $billPaid = Amount::zero();

                foreach ($billItems as $item) {
                    $paid = Amount::of($paidByItem[$item->getKey()] ?? '0');
                    $billPaid = $billPaid->plus($paid);
                    $item->update([
                        'paid' => Amount::toString($paid),
                        'status' => $paid->plus(Amount::of('0.01'))->compareTo(Amount::of($item->amount)) >= 0
                            ? 'paid'
                            : ($paid->compareTo(Amount::zero()) > 0 ? 'partial' : 'pending'),
                    ]);
                }

                // Own status: this bill's charges vs the money allocated to it.
                $bill->update([
                    'total' => Amount::toString($itemsTotal),
                    'status' => BillSnapshot::statusFor($itemsTotal, $billPaid)->value,
                ]);
            }

            $billed = Amount::sum($items->pluck('amount'));
            $received = Amount::sum($payments->pluck('amount'));
            $difference = $billed->minus($received);

            $renter->update([
                'balance' => Amount::toString(Amount::atLeastZero($difference)),
                'credit' => Amount::toString($difference->isNegative() ? Amount::absolute($difference) : Amount::zero()),
            ]);

            $report['balance'] = Amount::toString(Amount::atLeastZero($difference));
            $report['credit'] = Amount::toString($difference->isNegative() ? Amount::absolute($difference) : Amount::zero());

            return $report;
        });
    }
}

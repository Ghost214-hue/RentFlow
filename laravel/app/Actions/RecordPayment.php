<?php

declare(strict_types=1);

namespace App\Actions;

use App\Billing\BillSnapshotBuilder;
use App\Enums\PaymentStatus;
use App\Mail\PortfolioMailer;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Renter;
use App\Support\Amount;
use Brick\Money\Money as BrickMoney;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Record a payment and allocate it across the renter's arrears.
 *
 * THE ALLOCATION RULE (defect 1). The legacy code only ever allocated to the
 * single bill whose month equalled the payment month, so paying in September
 * left an unpaid August bill untouched and arrears rolled forward forever.
 * The correct order is:
 *
 *   1. the explicitly chosen month's bill, first
 *   2. then the OLDEST unpaid bills, in month order
 *   3. the remainder becomes renter credit
 *
 * Everything happens in one transaction with the renter's row locked, so two
 * concurrent payments cannot over-allocate the same balance.
 */
final class RecordPayment
{
    private readonly PortfolioMailer $mailer;

    public function __construct(
        private readonly BillSnapshotBuilder $snapshots = new BillSnapshotBuilder,
        ?PortfolioMailer $mailer = null,
    ) {
        // Actions are constructed by hand (`new RecordPayment()`), so the
        // mailer is resolved here instead of being demanded at every call
        // site. Resolved eagerly so it can never be null at send time.
        $this->mailer = $mailer ?? app(PortfolioMailer::class);
    }

    /**
     * @param  array{tenant_id: int, amount: string, month?: string, type?: string, method?: string, date?: string, description?: string, receipt?: string, status?: string}  $attributes
     */
    public function handle(array $attributes): Payment
    {
        $payment = DB::transaction(function () use ($attributes): Payment {
            $renter = Renter::query()->lockForUpdate()->findOrFail($attributes['tenant_id']);

            $amount = Amount::of($attributes['amount']);

            $payment = Payment::create([
                'tenant_id' => $renter->getKey(),
                'house_id' => $renter->house_id,
                'month' => $attributes['month'] ?? now()->format('Y-m'),
                'amount' => Amount::toString($amount),
                'type' => $attributes['type'] ?? 'Rent',
                'method' => $attributes['method'] ?? 'M-Pesa',
                'date' => $attributes['date'] ?? now()->toDateString(),
                // Defect 2: the column is an ENUM that does NOT contain
                // 'confirmed', which is what renter self-pay used to write.
                'status' => $attributes['status'] ?? PaymentStatus::Completed->value,
                'receipt' => $attributes['receipt'] ?? $this->nextReceipt(),
                'description' => $attributes['description'] ?? null,
            ]);

            $this->allocate($payment, $renter, $amount, (string) ($attributes['month'] ?? ''), (string) ($attributes['type'] ?? 'Mixed'));

            $this->refreshCachedBalances($renter);

            return $payment->fresh(['allocations', 'renter', 'house']);
        });

        /*
         * Notify AFTER the transaction commits, never inside it.
         *
         * Mail runs through the queue, and with QUEUE_CONNECTION=sync that
         * means inline: sending inside the closure would dispatch before the
         * commit, so a rollback could leave a renter holding a receipt for a
         * payment that does not exist. After the commit there is nothing to
         * undo. Best-effort -- a mail outage must not undo a recorded payment.
         */
        $this->mailer->paymentReceived($payment);

        return $payment;
    }

    /**
     * Apply the payment to the renter's bills in the documented order.
     * Returns the remainder, which becomes renter credit.
     */
    private function allocate(
        Payment $payment,
        Renter $renter,
        BrickMoney $amount,
        string $chosenMonth,
        string $paymentType,
    ): BrickMoney {
        // Only settled payments allocate.
        if ($payment->status !== PaymentStatus::Completed) {
            return $amount;
        }

        $remaining = $this->unallocated($payment, $amount);

        foreach ($this->candidateBills($renter, $chosenMonth) as $bill) {
            if ($remaining->compareTo(Amount::zero()) <= 0) {
                break;
            }

            $absorbed = $this->allocateToBill($payment, $bill, $remaining, $paymentType);
            $remaining = $remaining->minus($absorbed);
        }

        return Amount::atLeastZero($remaining);
    }

    /**
     * Bills in allocation order: the chosen month first, then the oldest
     * unpaid. "Unpaid" is derived from the ledger via BillSnapshot, never
     * from bills.status.
     *
     * @return Collection<int, Bill>
     */
    private function candidateBills(Renter $renter, string $chosenMonth)
    {
        $outstanding = collect();

        $bills = Bill::query()
            ->with('items')
            ->where('tenant_id', $renter->getKey())
            ->orderBy('month')
            ->orderBy('id')
            ->get();

        foreach ($bills as $bill) {
            if ($this->snapshots->build($bill)->balance->compareTo(Amount::zero()) > 0) {
                $outstanding->push($bill);
            }
        }

        if ($chosenMonth === '') {
            return $outstanding;
        }

        return $outstanding->sortBy(
            fn (Bill $bill) => $bill->month === $chosenMonth ? 0 : 1,
            SORT_REGULAR,
        )->values();
    }

    /** Spread the payment across one bill's outstanding items. */
    private function allocateToBill(Payment $payment, Bill $bill, BrickMoney $remaining, string $paymentType): BrickMoney
    {
        $category = $this->categoryFor($paymentType);
        $items = $bill->items()->orderBy('id')->get();

        $outstanding = $items
            ->map(fn (BillItem $item) => [
                'item' => $item,
                'remaining' => Amount::atLeastZero(
                    Amount::of($item->amount)->minus($this->itemPaid($item)),
                ),
            ])
            ->filter(fn ($row) => $row['remaining']->compareTo(Amount::zero()) > 0)
            ->values();

        // A categorised payment settles that category first, then the rest.
        if ($category !== null) {
            $outstanding = $outstanding->sortBy(
                fn (array $row) => $row['item']->type === $category ? 0 : 1,
                SORT_REGULAR,
            )->values();
        }

        $absorbed = Amount::zero();

        foreach ($outstanding as $row) {
            if ($remaining->compareTo(Amount::zero()) <= 0) {
                break;
            }

            /** @var BillItem $item */
            $item = $row['item'];
            /** @var BrickMoney $owed */
            $owed = $row['remaining'];

            $allocation = $owed->compareTo($remaining) <= 0 ? $owed : $remaining;

            PaymentAllocation::create([
                'payment_id' => $payment->getKey(),
                'bill_item_id' => $item->getKey(),
                'category' => (string) $item->type,
                'amount' => Amount::toString($allocation),
            ]);

            $remaining = $remaining->minus($allocation);
            $absorbed = $absorbed->plus($allocation);

            // Keep the denormalised cache in step for legacy readers.
            $paid = $this->itemPaid($item);
            $item->update([
                'paid' => Amount::toString($paid),
                'status' => $paid->plus(Amount::of('0.01'))->compareTo(Amount::of($item->amount)) >= 0
                    ? 'paid'
                    : 'partial',
            ]);
        }

        return $absorbed;
    }

    /** How much of this payment is still unallocated (idempotency guard). */
    private function unallocated(Payment $payment, BrickMoney $amount): BrickMoney
    {
        $allocated = Amount::sum(
            $payment->allocations()->pluck('amount')
        );

        return Amount::atLeastZero($amount->minus($allocated));
    }

    /** Amount settled against one item, straight from the ledger. */
    private function itemPaid(BillItem $item): BrickMoney
    {
        return Amount::sum(
            $item->allocations()
                ->whereHas('payment', fn ($q) => $q->whereIn('status', BillSnapshotBuilder::SETTLED))
                ->pluck('amount')
        );
    }

    private function categoryFor(string $paymentType): ?string
    {
        $categories = ['Rent', 'Deposit', 'Water', 'Electricity', 'Other'];

        return ($paymentType !== 'Mixed' && in_array($paymentType, $categories, true))
            ? $paymentType
            : null;
    }

    /**
     * Recompute the renter's cached balance and credit from the ledger.
     *
     * These columns are denormalised and drift (defect 5), so they are
     * derived here rather than incremented.
     */
    private function refreshCachedBalances(Renter $renter): void
    {
        $billed = Amount::sum(
            Bill::query()
                ->where('tenant_id', $renter->getKey())
                ->with('items')
                ->get()
                ->flatMap(fn (Bill $b) => $b->items->pluck('amount'))
        );

        /*
         * CREDIT must be measured against the money actually RECEIVED, not the
         * money that reached bill items.
         *
         * Comparing billed-vs-allocated silently destroys credit: an
         * overpayment is never allocated (there is nothing left to allocate it
         * to), so it appears in neither figure and vanishes. Measured against
         * received money, an overpayment correctly shows as credit.
         */
        $received = Amount::sum(
            Payment::query()
                ->where('tenant_id', $renter->getKey())
                ->whereIn('status', BillSnapshotBuilder::SETTLED)
                ->pluck('amount')
        );

        $difference = $billed->minus($received);

        $renter->update([
            'balance' => Amount::toString(Amount::atLeastZero($difference)),
            'credit' => Amount::toString(
                $difference->isNegative() ? Amount::absolute($difference) : Amount::zero()
            ),
        ]);
    }

    /**
     * Collision-free receipt (defect 4).
     *
     * The old scheme was 'RCP-' . date('Y') . '-' . str_pad(time() % 10000),
     * which collided for any two payments in the same second and wrapped
     * every ~2.8 hours.
     */
    private function nextReceipt(): string
    {
        $year = now()->format('Y');
        $prefix = 'RCP-'.$year.'-';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $count = Payment::withoutGlobalScopes()
                ->where('receipt', 'like', $prefix.'%')
                ->count();

            $candidate = $prefix.str_pad((string) ($count + 1 + $attempt), 5, '0', STR_PAD_LEFT);

            $clash = Payment::withoutGlobalScopes()
                ->where('receipt', $candidate)
                ->exists();

            if (! $clash) {
                return $candidate;
            }
        }

        return $prefix.now()->format('His');
    }
}

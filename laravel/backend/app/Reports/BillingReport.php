<?php

declare(strict_types=1);

namespace App\Reports;

use App\Models\Bill;
use App\Models\PaymentAllocation;
use App\Support\Amount;
use App\Support\Rate;
use Brick\Money\Money;
use Carbon\CarbonImmutable;

/**
 * Billing health: what was charged, what settled, and what is still owed.
 *
 * Ported from the legacy BillsReportController, which reported a bill status
 * split and an overdue count. Both are preserved, but "overdue" is no longer a
 * stored status we trust -- a bill left on 'pending' after its due date is just
 * as overdue as one somebody bothered to flip. Overdue is DERIVED from due_date
 * here, which is why it cannot silently disagree with the invoices a renter has.
 *
 * Money is Brick\Money throughout, so a report figure can never disagree with
 * what a renter was actually charged.
 */
final class BillingReport
{
    /** Bill statuses that can still owe money. */
    private const UNSETTLED = ['pending', 'partial', 'overdue'];

    public function __construct(private readonly ReportScope $scope) {}

    /** @var array<int, string>|null Settled money allocated per bill id. */
    private ?array $paidByBill = null;

    /**
     * Status split with money, plus derived arrears.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $counts = $this->scope->bills()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $moneyByStatus = $this->scope->bills()
            ->selectRaw('status, SUM(total) AS total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $arrears = $this->arrears();

        return [
            'counts' => [
                'total' => (int) array_sum(array_map('intval', $counts)),
                'paid' => (int) ($counts['paid'] ?? 0),
                'partial' => (int) ($counts['partial'] ?? 0),
                'pending' => (int) ($counts['pending'] ?? 0),
                // Derived, not the stored 'overdue' status -- see arrears().
                'overdue' => $arrears['count'],
            ],
            'money' => [
                'billed' => Amount::toString(Amount::sum(
                    $this->scope->bills()->pluck('total')
                )),
                'paid' => Amount::toString(Amount::of($moneyByStatus['paid'] ?? '0')),
                'partial' => Amount::toString(Amount::of($moneyByStatus['partial'] ?? '0')),
                'pending' => Amount::toString(Amount::of($moneyByStatus['pending'] ?? '0')),
            ],
            'arrears' => $arrears,
        ];
    }

    /**
     * Outstanding money that is past its due date, aged into buckets.
     *
     * Owed is total-minus-allocated rather than the raw total, because a bill
     * can be part-paid; a part-paid bill is just as much arrears as an unpaid
     * one.
     *
     * @return array{count: int, amount: string, oldest_month: string|null, aging: array<int, array{label: string, count: int, amount: string, share: string}>}
     */
    public function arrears(): array
    {
        $today = CarbonImmutable::today();
        $rows = $this->scope->bills()
            ->whereIn('status', self::UNSETTLED)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->get(['id', 'month', 'due_date', 'total']);

        // Accumulated in Money, not strings, then formatted once at the end.
        $buckets = [
            ['label' => '1-30 days', 'count' => 0, 'money' => Amount::zero()],
            ['label' => '31-60 days', 'count' => 0, 'money' => Amount::zero()],
            ['label' => '61-90 days', 'count' => 0, 'money' => Amount::zero()],
            ['label' => '90+ days', 'count' => 0, 'money' => Amount::zero()],
        ];

        if ($rows->isEmpty()) {
            return [
                'count' => 0,
                'amount' => '0.00',
                'oldest_month' => null,
                'aging' => $this->formatAging($buckets, Amount::zero()),
            ];
        }

        $paid = $this->paidPerBill();
        $owedTotal = Amount::zero();
        $oldest = null;

        foreach ($rows as $bill) {
            $owed = Amount::owed(Amount::of($bill->total), Amount::of($paid[$bill->getKey()] ?? '0'));

            // A bill can be fully allocated yet still flagged unpaid. It owes
            // nothing, so it is not arrears and must not inflate the figure.
            if ($owed->isZero()) {
                continue;
            }

            $owedTotal = $owedTotal->plus($owed);

            $days = (int) $bill->due_date->diffInDays($today, absolute: true);
            $index = match (true) {
                $days <= 30 => 0,
                $days <= 60 => 1,
                $days <= 90 => 2,
                default => 3,
            };

            $buckets[$index]['count']++;
            $buckets[$index]['money'] = $buckets[$index]['money']->plus($owed);

            if ($oldest === null || (string) $bill->month < $oldest) {
                $oldest = (string) $bill->month;
            }
        }

        return [
            'count' => (int) array_sum(array_column($buckets, 'count')),
            'amount' => Amount::toString($owedTotal),
            'oldest_month' => $oldest,
            'aging' => $this->formatAging($buckets, $owedTotal),
        ];
    }

    /**
     * Format the buckets for transport, with each one's share of the arrears.
     *
     * The share is computed HERE so the browser never divides money by money --
     * every percentage on this page arrives from the server as a string.
     *
     * @param  array<int, array{label: string, count: int, money: Money}>  $buckets
     * @return array<int, array{label: string, count: int, amount: string, share: string}>
     */
    private function formatAging(array $buckets, Money $owedTotal): array
    {
        return array_map(fn (array $b): array => [
            'label' => $b['label'],
            'count' => $b['count'],
            'amount' => Amount::toString($b['money']),
            'share' => Rate::of($b['money'], $owedTotal),
        ], $buckets);
    }

    /**
     * What the monthly bill is made of: rent against utilities.
     *
     * The split matters because utilities are recovered while rent is profit,
     * and the two get conflated when only a total is shown.
     *
     * @return array<int, array{label: string, amount: string, share: string}>
     */
    public function composition(?string $month = null): array
    {
        $month ??= CarbonImmutable::now()->format('Y-m');

        $row = $this->scope->bills()
            ->where('month', $month)
            ->selectRaw('COALESCE(SUM(rent), 0) AS rent,
                         COALESCE(SUM(water), 0) AS water,
                         COALESCE(SUM(electricity), 0) AS electricity')
            ->first();

        $rent = Amount::of($row?->rent ?? '0');
        $water = Amount::of($row?->water ?? '0');
        $electricity = Amount::of($row?->electricity ?? '0');
        $total = $rent->plus($water)->plus($electricity);

        return collect([
            ['label' => 'Rent', 'amount' => $rent],
            ['label' => 'Water', 'amount' => $water],
            ['label' => 'Electricity', 'amount' => $electricity],
        ])->map(fn (array $row): array => [
            'label' => $row['label'],
            'amount' => Amount::toString($row['amount']),
            'share' => Rate::of($row['amount'], $total),
        ])->all();
    }

    /**
     * Per-unit collection for one month, worst first.
     *
     * This is the operational view: which unit is not paying, rather than what
     * the portfolio total happens to be.
     *
     * @return array<int, array{house_id: int, unit: string, property: string|null, billed: string, collected: string, outstanding: string}>
     */
    public function byHouse(?string $month = null, int $limit = 10): array
    {
        $month ??= CarbonImmutable::now()->format('Y-m');

        $bills = $this->scope->bills()
            ->where('month', $month)
            ->with('house.property')
            ->get();

        $paid = $this->paidPerBill();

        return $bills->map(function (Bill $bill) use ($paid): array {
            $billed = Amount::of($bill->total);
            $collected = Amount::atLeastZero(Amount::of($paid[$bill->getKey()] ?? '0'));

            return [
                'house_id' => (int) $bill->house_id,
                'unit' => (string) ($bill->house?->unit ?? '--'),
                'property' => $bill->house?->property?->name,
                'billed' => Amount::toString($billed),
                'collected' => Amount::toString($collected),
                'outstanding' => Amount::toString(Amount::owed($billed, $collected)),
            ];
        })
            ->sortByDesc(fn (array $row): int => (int) Amount::of($row['outstanding'])->getMinorAmount()->toInt())
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Settled money allocated per bill, keyed by bill id.
     *
     * One grouped query rather than one per bill: the arrears list can touch
     * hundreds of rows, and a query per row turns a report page into a timeout.
     *
     * @return array<int, string>
     */
    private function paidPerBill(): array
    {
        if ($this->paidByBill !== null) {
            return $this->paidByBill;
        }

        $settled = $this->scope->payments()
            ->whereIn('status', ['completed', 'confirmed', 'paid'])
            ->select('id');

        return $this->paidByBill = PaymentAllocation::query()
            ->selectRaw('bill_items.bill_id, SUM(payment_allocations.amount) AS paid')
            ->join('bill_items', 'bill_items.id', '=', 'payment_allocations.bill_item_id')
            ->join('bills', 'bills.id', '=', 'bill_items.bill_id')
            ->whereIn('payment_allocations.payment_id', $settled)
            ->whereIn('bills.id', $this->scope->bills()->select('id'))
            ->groupBy('bill_items.bill_id')
            ->pluck('paid', 'bill_items.bill_id')
            ->map(fn ($v): string => Amount::toString(Amount::of($v)))
            ->all();
    }
}

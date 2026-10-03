<?php

declare(strict_types=1);

namespace App\Reports;

use App\Models\BillItem;
use App\Models\Complaint;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\PaymentAllocation;
use App\Models\Property;
use App\Models\Renter;
use App\Support\Amount;
use Brick\Money\Money;

/**
 * Computed portfolio figures.
 *
 * There is NO reports table: everything here is derived at read time from the
 * bill and allocation ledger. Money is computed with Brick\Money so a report can
 * never disagree with what a renter is actually charged.
 */
final class PortfolioReport
{
    /** Payment statuses that represent settled money. */
    private const SETTLED = ['completed', 'confirmed', 'paid'];

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $houses = House::query()->count();
        $occupied = House::query()->where('status', 'occupied')->count();

        $billedToDate = Amount::sum(BillItem::query()->pluck('amount'));

        $receivedToDate = Amount::sum(
            PaymentAllocation::query()
                ->whereHas('payment', fn ($q) => $q->whereIn('status', self::SETTLED))
                ->pluck('amount')
        );

        return [
            'counts' => [
                'properties' => Property::query()->count(),
                'houses' => $houses,
                'occupied' => $occupied,
                'vacant' => max(0, $houses - $occupied),
                'renters' => Renter::query()->count(),
                'active_renters' => Renter::query()->where('status', 'active')->count(),
                'open_complaints' => Complaint::query()
                    ->whereIn('status', ['open', 'in-progress'])->count(),
                'open_maintenance' => MaintenanceRecord::query()
                    ->whereIn('status', ['pending', 'in-progress'])->count(),
            ],
            'money' => [
                'billed_to_date' => Amount::toString($billedToDate),
                'received_to_date' => Amount::toString($receivedToDate),
                // Clamped: an overpayment is credit, not negative debt.
                'outstanding' => Amount::toString(
                    Amount::atLeastZero($billedToDate->minus($receivedToDate))
                ),
                'collection_rate' => $this->collectionRate($billedToDate, $receivedToDate),
            ],
        ];
    }

    /**
     * Percentage of billed money settled, as a decimal string.
     *
     * Computed in integer basis points so it is exact and never a float.
     */
    private function collectionRate(Money $billed, Money $received): string
    {
        // Money::getMinorAmount() applies the currency scale (2 dp). BigDecimal,
        // which Money::getAmount() returns, has no toMinorUnit().
        $billedMinor = (int) $billed->getMinorAmount()->toInt();
        $receivedMinor = (int) $received->getMinorAmount()->toInt();

        if ($billedMinor === 0) {
            return '0.00';
        }

        return number_format(\intdiv($receivedMinor * 10000, $billedMinor) / 100, 2);
    }

    /**
     * Per-month billed vs received, for a chart.
     *
     * @return array<int, array{month: string, billed: string, received: string, outstanding: string}>
     */
    public function monthly(int $limit = 12): array
    {
        $billed = \App\Models\Bill::query()
            ->selectRaw('month, SUM(total) AS billed')
            ->groupBy('month')
            ->pluck('billed', 'month');

        $received = \App\Models\Payment::query()
            ->whereNotNull('month')
            ->whereIn('status', self::SETTLED)
            ->selectRaw('month, SUM(amount) AS received')
            ->groupBy('month')
            ->pluck('received', 'month');

        return collect(array_unique([...$billed->keys(), ...$received->keys()]))
            ->filter()
            ->sortDesc()
            ->take($limit)
            ->map(function (string $month) use ($billed, $received): array {
                $b = Amount::of((string) ($billed[$month] ?? '0'));
                $r = Amount::of((string) ($received[$month] ?? '0'));

                return [
                    'month' => $month,
                    'billed' => Amount::toString($b),
                    'received' => Amount::toString($r),
                    'outstanding' => Amount::toString(Amount::atLeastZero($b->minus($r))),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Renters owing the most, derived from the ledger.
     *
     * @return array<int, array{id: int, name: string, outstanding: string}>
     */
    public function topDebtors(int $limit = 10): array
    {
        $rows = Renter::query()
            ->select(['id', 'name'])
            ->get()
            ->map(function (Renter $r): array {
                $billed = Amount::sum(
                    BillItem::query()
                        ->whereIn('bill_id', $r->bills()->select('id'))
                        ->pluck('amount')
                );

                $paid = Amount::sum(
                    PaymentAllocation::query()
                        ->whereHas('payment', fn ($q) => $q
                            ->where('tenant_id', $r->getKey())
                            ->whereIn('status', self::SETTLED))
                        ->pluck('amount')
                );

                return [
                    'id' => (int) $r->getKey(),
                    'name' => (string) $r->name,
                    'outstanding' => Amount::toString(Amount::atLeastZero($billed->minus($paid))),
                ];
            })
            ->filter(fn (array $row) => $row['outstanding'] !== '0.00');

        // Sort on the integer minor unit, not the formatted string.
        return $rows
            ->sortByDesc(fn (array $row) => (int) \App\Support\Amount::of($row['outstanding'])
                ->getMinorAmount()
                ->toInt())
            ->take($limit)
            ->values()
            ->all();
    }
}
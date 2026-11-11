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
use App\Support\Rate;
use Carbon\CarbonImmutable;

/**
 * Computed portfolio figures.
 *
 * There is NO reports table: everything here is derived at read time from the
 * bill and allocation ledger. Money is computed with Brick\Money so a report can
 * never disagree with what a renter is actually charged.
 *
 * Ported from the legacy ReportController, which reported monthly revenue,
 * per-property performance, collection rate and occupancy. All four are here.
 */
final class PortfolioReport
{
    /** Payment statuses that represent settled money. */
    private const SETTLED = ['completed', 'confirmed', 'paid'];

    public function __construct(private readonly ReportScope $scope) {}

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $houses = $this->scope->whereProperty(House::query());
        $totalHouses = (clone $houses)->count();
        $occupied = (clone $houses)->where('status', 'occupied')->count();

        $billedToDate = Amount::sum(
            BillItem::query()->whereIn('bill_id', $this->scope->bills()->select('id'))->pluck('amount')
        );

        $receivedToDate = Amount::sum(
            PaymentAllocation::query()
                ->whereIn('payment_id', $this->scope->payments()->whereIn('status', self::SETTLED)->select('id'))
                ->pluck('amount')
        );

        return [
            'counts' => [
                'properties' => $this->scope->wherePropertyRow(Property::query())->count(),
                'houses' => $totalHouses,
                'occupied' => $occupied,
                'vacant' => max(0, $totalHouses - $occupied),
                'renters' => $this->scope->whereRenter(Renter::query())->count(),
                'active_renters' => $this->scope->whereRenter(Renter::query())->where('status', 'active')->count(),
                'open_complaints' => $this->scope->whereHouse(Complaint::query())
                    ->whereIn('status', ['open', 'in-progress'])->count(),
                'open_maintenance' => $this->scope->whereHouse(MaintenanceRecord::query())
                    ->whereIn('status', ['pending', 'in-progress'])->count(),
            ],
            'money' => [
                'billed_to_date' => Amount::toString($billedToDate),
                'received_to_date' => Amount::toString($receivedToDate),
                // Clamped: an overpayment is credit, not negative debt.
                'outstanding' => Amount::toString(
                    Amount::atLeastZero($billedToDate->minus($receivedToDate))
                ),
                'collection_rate' => Rate::of($receivedToDate, $billedToDate),
            ],
        ];
    }

    /**
     * Per-property performance for one month.
     *
     * Collection is matched per house and then rolled up, so a strong property
     * cannot hide a dead unit inside it.
     *
     * @return array<int, array{property_id: int, name: string, units: int, occupied: int, billed: string, collected: string, outstanding: string, collection_rate: string}>
     */
    public function propertyPerformance(?string $month = null): array
    {
        $month ??= CarbonImmutable::now()->format('Y-m');

        $billedByHouse = $this->scope->bills()
            ->where('month', $month)
            ->selectRaw('house_id, SUM(total) AS billed')
            ->groupBy('house_id')
            ->pluck('billed', 'house_id');

        $settled = $this->scope->payments()->whereIn('status', self::SETTLED);

        $collectedByHouse = $this->scope->bills()
            ->where('month', $month)
            ->join('bill_items', 'bill_items.bill_id', '=', 'bills.id')
            ->join('payment_allocations', 'payment_allocations.bill_item_id', '=', 'bill_items.id')
            ->whereIn('payment_allocations.payment_id', $settled->select('id'))
            ->selectRaw('bills.house_id, SUM(payment_allocations.amount) AS collected')
            ->groupBy('bills.house_id')
            ->pluck('collected', 'bills.house_id');

        return $this->scope->wherePropertyRow(Property::query())
            ->with('houses')
            ->get()
            ->map(function (Property $property) use ($billedByHouse, $collectedByHouse): array {
                $billed = Amount::zero();
                $collected = Amount::zero();
                $occupied = 0;

                foreach ($property->houses as $house) {
                    $id = $house->getKey();
                    $billed = $billed->plus(Amount::of($billedByHouse[$id] ?? '0'));

                    // Clamp: money over-allocated against this month's bills is
                    // credit, and must not read as negative collection.
                    $collected = $collected->plus(Amount::atLeastZero(
                        Amount::of($collectedByHouse[$id] ?? '0')
                    ));

                    if ($house->status === 'occupied') {
                        $occupied++;
                    }
                }

                return [
                    'property_id' => (int) $property->getKey(),
                    'name' => (string) $property->name,
                    'units' => $property->houses->count(),
                    'occupied' => $occupied,
                    'billed' => Amount::toString($billed),
                    'collected' => Amount::toString($collected),
                    'outstanding' => Amount::toString(Amount::owed($billed, $collected)),
                    'collection_rate' => Rate::of($collected, $billed),
                ];
            })
            ->sortByDesc(fn (array $row): int => (int) Amount::of($row['billed'])->getMinorAmount()->toInt())
            ->values()
            ->all();
    }

    /**
     * Per-month billed vs received, for a chart.
     *
     * @return array<int, array{month: string, billed: string, received: string, outstanding: string}>
     */
    public function monthly(int $limit = 12): array
    {
        $billed = $this->scope->bills()
            ->selectRaw('month, SUM(total) AS billed')
            ->groupBy('month')
            ->pluck('billed', 'month');

        $received = $this->scope->payments()
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
        $rows = $this->scope->whereRenter(Renter::query())
            ->select(['id', 'name'])
            ->get()
            ->map(function (Renter $r): array {
                $billed = Amount::sum(
                    BillItem::query()
                        ->whereIn('bill_id', $this->scope->bills()
                            ->whereIn('tenant_id', [$r->getKey()])->select('id'))
                        ->pluck('amount')
                );

                $paid = Amount::sum(
                    PaymentAllocation::query()
                        ->whereIn('payment_id', $this->scope->payments()
                            ->where('tenant_id', $r->getKey())
                            ->whereIn('status', self::SETTLED)->select('id'))
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
            ->sortByDesc(fn (array $row): int => (int) Amount::of($row['outstanding'])
                ->getMinorAmount()
                ->toInt())
            ->take($limit)
            ->values()
            ->all();
    }
}

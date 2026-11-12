<?php

declare(strict_types=1);

namespace App\Reports;

use App\Models\House;
use App\Models\Property;
use App\Models\Renter;
use App\Models\TenancyTermination;
use App\Support\Amount;
use App\Support\Rate;

/**
 * Occupancy and vacancy, per property and across the portfolio.
 *
 * Ported from the legacy TenancyVacancyReportController, which reported unit
 * counts, occupancy and vacancy rates, and active versus terminated tenants.
 *
 * Two things are added. Vacancy is reported as LOST RENT, because a vacancy
 * rate alone does not tell an owner whether to chase tenants or fill units. And
 * terminations are counted, which is only possible now that ending a tenancy
 * leaves an audit row instead of deleting the renter.
 *
 * Rates are computed from actual House rows rather than properties.units. That
 * denormalised column is maintained separately and can drift from the units
 * that really exist; the rows cannot.
 */
final class OccupancyReport
{
    public function __construct(private readonly ReportScope $scope) {}

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $houses = $this->scope->whereProperty(House::query());
        $total = (int) (clone $houses)->count();
        $occupied = (int) (clone $houses)->where('status', 'occupied')->count();
        $vacant = max(0, $total - $occupied);

        return [
            'units' => [
                'total' => $total,
                'occupied' => $occupied,
                'vacant' => $vacant,
                'occupancy_rate' => Rate::percent($occupied, $total),
                'vacancy_rate' => Rate::percent($vacant, $total),
            ],
            'money' => [
                'potential_monthly' => Amount::toString(
                    Amount::sum((clone $houses)->pluck('rent'))
                ),
                'collecting_monthly' => Amount::toString(
                    Amount::sum((clone $houses)->where('status', 'occupied')->pluck('rent'))
                ),
                // Rent a vacant unit could be earning at its own asking rent.
                'lost_monthly' => Amount::toString(
                    Amount::sum((clone $houses)->where('status', 'vacant')->pluck('rent'))
                ),
            ],
            'renters' => [
                'active' => (int) $this->scope->whereRenter(Renter::query())->where('status', 'active')->count(),
                'pending_termination' => (int) $this->scope->whereRenter(Renter::query())->where('status', 'pending_termination')->count(),
                'terminated' => (int) $this->scope->whereRenter(Renter::query())->where('status', 'terminated')->count(),
            ],
            'terminations' => [
                'pending' => (int) $this->scope->whereHouse(TenancyTermination::query())->where('status', 'pending')->count(),
                'approved' => (int) $this->scope->whereHouse(TenancyTermination::query())->whereIn('status', ['approved', 'completed'])->count(),
                'by_actor' => $this->scope->whereHouse(TenancyTermination::query())
                    ->selectRaw('initiated_by AS label, COUNT(*) AS total')
                    ->groupBy('initiated_by')
                    ->pluck('total', 'label')
                    ->all(),
            ],
        ];
    }

    /**
     * Per-property occupancy, worst-occupied first so the gaps surface.
     *
     * @return array<int, array{property_id: int, name: string, units: int, occupied: int, vacant: int, occupancy_rate: string, lost_monthly: string}>
     */
    public function byProperty(): array
    {
        return $this->scope->wherePropertyRow(Property::query())
            ->with('houses')
            ->get()
            ->map(function (Property $property): array {
                $total = $property->houses->count();
                $occupied = $property->houses->where('status', 'occupied')->count();

                return [
                    'property_id' => (int) $property->getKey(),
                    'name' => (string) $property->name,
                    'units' => $total,
                    'occupied' => $occupied,
                    'vacant' => max(0, $total - $occupied),
                    'occupancy_rate' => Rate::percent($occupied, $total),
                    'lost_monthly' => Amount::toString(Amount::sum(
                        $property->houses->where('status', 'vacant')->pluck('rent')
                    )),
                ];
            })
            ->sortBy('occupancy_rate')
            ->values()
            ->all();
    }

    /**
     * The vacant units themselves, longest empty first.
     *
     * Vacancy has no vacant-since column of its own, so "how long" falls back to
     * the unit's creation date, which is an honest lower bound rather than a
     * guess at when it emptied.
     *
     * @return array<int, array{house_id: int, unit: string, property: string|null, rent: string, vacant_since: string|null}>
     */
    public function vacantUnits(): array
    {
        return $this->scope->whereProperty(House::query())
            ->with('property')
            ->where('status', 'vacant')
            ->orderBy('created_at')
            ->get()
            ->map(fn (House $house): array => [
                'house_id' => (int) $house->getKey(),
                'unit' => (string) $house->unit,
                'property' => $house->property?->name,
                'rent' => Amount::toString(Amount::of($house->rent)),
                'vacant_since' => $house->created_at?->toDateString(),
            ])
            ->all();
    }
}

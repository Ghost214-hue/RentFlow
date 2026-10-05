<?php

declare(strict_types=1);

namespace App\Reports;

use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\House;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

/**
 * Which slice of the portfolio a report may see.
 *
 * An owner sees everything they own. A caretaker sees ONLY the properties they
 * are assigned to. That restriction is enforced pervasively elsewhere (see
 * BillController), so a report that skipped it would quietly widen a caretaker's
 * reach: they can manage one building but would see the owner's whole
 * portfolio's money, vacancies and disputes. So it is applied here too.
 *
 * A renter never reaches a report at all -- the controller refuses them, since
 * every figure is portfolio-wide.
 *
 * Restricted scopes resolve their property, house and renter ids ONCE and reuse
 * them, so a reports page does not re-run the same lookup per query.
 */
final class ReportScope
{
    /** @var list<int>|null Resolved lazily; null until first use. */
    private ?array $houseIds = null;

    /** @var list<int>|null Resolved lazily; null until first use. */
    private ?array $renterIds = null;

    /**
     * @param  list<int>|null  $propertyIds  null means unrestricted (an owner).
     */
    private function __construct(private readonly ?array $propertyIds) {}

    public static function for(mixed $actor): self
    {
        return new self($actor instanceof Caretaker ? $actor->assignedPropertyIds() : null);
    }

    /** True when this actor may see their owner's entire portfolio. */
    public function isUnrestricted(): bool
    {
        return $this->propertyIds === null;
    }

    /**
     * Constrain a query on a model carrying `house_id` (bills, payments,
     * allocations, complaints, maintenance).
     *
     * A caretaker with NO assigned properties gets an impossible id list, so
     * every query returns empty rather than everything. Failing open here would
     * hand an unassigned caretaker the whole portfolio.
     */
    public function whereHouse(Builder $query): Builder
    {
        if ($this->isUnrestricted()) {
            return $query;
        }

        return $query->whereIn('house_id', $this->houseIds());
    }

    /** Constrain a query on the houses table itself. */
    public function whereProperty(Builder $query): Builder
    {
        return $this->isUnrestricted()
            ? $query
            : $query->whereIn('property_id', $this->propertyIds());
    }

    /** Constrain a query on the properties table itself. */
    public function wherePropertyRow(Builder $query): Builder
    {
        return $this->isUnrestricted()
            ? $query
            : $query->whereIn('id', $this->propertyIds());
    }

    /** Constrain a query on the renters table. */
    public function whereRenter(Builder $query): Builder
    {
        return $this->isUnrestricted()
            ? $query
            : $query->whereIn('id', $this->renterIds());
    }

    /**
     * An already-constrained Bill query.
     *
     * Bill items and payment allocations carry no house_id of their own, so they
     * are scoped through a SUBQUERY on bills rather than a materialised id
     * list. A portfolio with ten thousand bills would otherwise load ten
     * thousand integers into PHP just to filter a sum.
     */
    public function bills(): Builder
    {
        return $this->whereHouse(Bill::query());
    }

    /** An already-constrained Payment query, for the same reason. */
    public function payments(): Builder
    {
        return $this->whereHouse(Payment::query());
    }

    /** @return list<int> */
    private function propertyIds(): array
    {
        // Only reached when restricted, so the promoted value is non-null; the
        // fallback just satisfies the static type.
        return $this->propertyIds ?? [];
    }

    /** @return list<int> */
    private function houseIds(): array
    {
        if ($this->houseIds !== null) {
            return $this->houseIds;
        }

        $ids = $this->propertyIds;

        return $this->houseIds = ($ids === null || $ids === []) ? [] : House::query()
            ->whereIn('property_id', $ids)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /** @return list<int> */
    private function renterIds(): array
    {
        if ($this->renterIds !== null) {
            return $this->renterIds;
        }

        $houses = $this->houseIds();

        return $this->renterIds = $houses === [] ? [] : House::query()
            ->whereIn('id', $houses)
            ->whereNotNull('tenant_id')
            ->pluck('tenant_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}

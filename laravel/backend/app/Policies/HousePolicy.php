<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\House;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;

/**
 * Who may do what with a House.
 *
 * DENY BY DEFAULT: there is no catch-all `before()` that grants access, so a
 * new ability is refused until it is written here explicitly.
 *
 * Three tiers:
 *   - owner     : their own houses (the global scope already restricts to
 *                 their portfolio, so ownership is the only question)
 *   - caretaker : only houses inside their assigned properties
 *   - renter    : only the house they live in
 */
class HousePolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        // Renters have no houses index in the legacy UI.
        return $actor instanceof Owner || $actor instanceof Caretaker;
    }

    public function view(Owner|Caretaker|Renter $actor, House $house): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            return $actor->isAssignedTo((int) $house->property_id);
        }

        return (int) $house->tenant_id === (int) $actor->getKey();
    }

    /** Only owners add or edit units; caretakers cannot. */
    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner;
    }

    public function update(Owner|Caretaker|Renter $actor, House $house): bool
    {
        return $actor instanceof Owner;
    }

    /**
     * Owners may delete, but only a VACANT unit — deleting an occupied unit
     * would orphan the renter's bills and payment history.
     */
    public function delete(Owner|Caretaker|Renter $actor, House $house): bool
    {
        return $actor instanceof Owner && ! $house->isOccupied();
    }
}
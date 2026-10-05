<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;

/**
 * Who may do what with a Property.
 *
 * DENY BY DEFAULT — see HousePolicy for the same rationale.
 */
class PropertyPolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner || $actor instanceof Caretaker;
    }

    public function view(Owner|Caretaker|Renter $actor, Property $property): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        // Caretakers see the properties they are assigned to.
        return $actor instanceof Caretaker
            && $actor->isAssignedTo((int) $property->getKey());
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner;
    }

    public function update(Owner|Caretaker|Renter $actor, Property $property): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, Property $property): bool
    {
        return $actor instanceof Owner;
    }
}
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Services\PermissionService;

class PropertyPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'properties.view');
    }

    public function view(Owner|Caretaker|Renter $actor, Property $property): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        return $actor instanceof Caretaker
            && $actor->isAssignedTo((int) $property->getKey());
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'properties.manage');
    }

    public function update(Owner|Caretaker|Renter $actor, Property $property): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, Property $property): bool
    {
        return $actor instanceof Owner && ! $property->isOccupied();
    }
}
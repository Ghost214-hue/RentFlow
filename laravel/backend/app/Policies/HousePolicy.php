<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\House;
use App\Models\Owner;
use App\Models\Renter;
use App\Services\PermissionService;

class HousePolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
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

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner;
    }

    public function update(Owner|Caretaker|Renter $actor, House $house): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, House $house): bool
    {
        return $actor instanceof Owner && ! $house->isOccupied();
    }
}

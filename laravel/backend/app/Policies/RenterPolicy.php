<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use App\Services\PermissionService;

class RenterPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'renters.view');
    }

    public function view(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            return $actor->isAssignedTo((int) $renter->property_id);
        }

        // A renter sees only their own record.
        return (int) $renter->getKey() === (int) $actor->getKey();
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'renters.create');
    }

    public function update(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            return $this->permissions->allows($actor, 'renters.update_contact')
                && $actor->isAssignedTo((int) $renter->property_id);
        }

        return false;
    }

    public function delete(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        return false;
    }

    public function terminate(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            return $this->permissions->allows($actor, 'renters.terminate')
                && $actor->isAssignedTo((int) $renter->property_id);
        }

        return false;
    }
}

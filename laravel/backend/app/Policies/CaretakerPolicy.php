<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use App\Services\PermissionService;

class CaretakerPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'caretakers.view');
    }

    public function view(Owner|Caretaker|Renter $actor, Caretaker $caretaker): bool
    {
        return $actor instanceof Owner
            || ($actor instanceof Caretaker && (int) $actor->getKey() === (int) $caretaker->getKey());
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'caretakers.manage');
    }

    public function update(Owner|Caretaker|Renter $actor, Caretaker $caretaker): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, Caretaker $caretaker): bool
    {
        return $actor instanceof Owner;
    }
}
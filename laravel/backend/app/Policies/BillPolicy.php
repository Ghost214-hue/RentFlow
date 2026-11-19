<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Services\PermissionService;

class BillPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'bills.view');
    }

    public function view(Owner|Caretaker|Renter $actor, Bill $bill): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            $house = \App\Models\House::find($bill->house_id);

            return $house !== null && $actor->isAssignedTo((int) $house->property_id);
        }

        return (int) $bill->tenant_id === (int) $actor->getKey();
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'bills.generate');
    }

    public function update(Owner|Caretaker|Renter $actor, Bill $bill): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, Bill $bill): bool
    {
        if (! $actor instanceof Owner) {
            return false;
        }

        return ! $bill->items()->whereHas('allocations')->exists();
    }

    private function propertyIdOf(Bill $bill): ?int
    {
        $house = \App\Models\House::find($bill->house_id);

        return $house === null ? null : (int) $house->property_id;
    }
}
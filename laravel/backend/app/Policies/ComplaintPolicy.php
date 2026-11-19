<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Complaint;
use App\Models\Owner;
use App\Models\Renter;
use App\Services\PermissionService;

class ComplaintPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    public function view(Owner|Caretaker|Renter $actor, Complaint $complaint): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            if ($complaint->sender_role === 'caretaker') {
                return true;
            }

            $propertyId = $this->propertyIdOf($complaint);

            return $propertyId !== null && $actor->isAssignedTo($propertyId);
        }

        return (int) $complaint->tenant_id === (int) $actor->getKey()
            || in_array((int) $actor->getKey(), $complaint->recipientIds(), true);
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    public function update(Owner|Caretaker|Renter $actor, Complaint $complaint): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        $actorId = (int) $actor->getKey();

        if ($complaint->sender_role === 'tenant' && (int) $complaint->tenant_id === $actorId) {
            return true;
        }

        return $complaint->sender_role === 'caretaker' && $actor instanceof Caretaker;
    }

    public function delete(Owner|Caretaker|Renter $actor, Complaint $complaint): bool
    {
        return $actor instanceof Owner;
    }

    private function propertyIdOf(Complaint $complaint): ?int
    {
        if ($complaint->property_id !== null) {
            return (int) $complaint->property_id;
        }

        $house = \App\Models\House::find($complaint->house_id);

        return $house === null ? null : (int) $house->property_id;
    }
}
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Owner;
use App\Models\Renter;
use App\Services\PermissionService;

class MaintenanceRecordPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'maintenance.view');
    }

    public function view(Owner|Caretaker|Renter $actor, MaintenanceRecord $record): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            $propertyId = $record->property_id ?? House::whereKey($record->house_id)
                ->value('property_id');

            return $record->assigned_to === $actor->name
                || ($propertyId !== null && $actor->isAssignedTo((int) $propertyId));
        }

        return (int) $record->tenant_id === (int) $actor->getKey()
            || in_array((int) $actor->getKey(), $record->recipientIds(), true);
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    public function update(Owner|Caretaker|Renter $actor, MaintenanceRecord $record): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            return $this->permissions->allows($actor, 'maintenance.view');
        }

        return (int) $record->tenant_id === (int) $actor->getKey() && ! $record->isClosed();
    }

    public function advance(Owner|Caretaker|Renter $actor, MaintenanceRecord $record): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        return $actor instanceof Caretaker && $this->permissions->allows($actor, 'maintenance.advance');
    }

    public function delete(Owner|Caretaker|Renter $actor, MaintenanceRecord $record): bool
    {
        return $actor instanceof Owner;
    }
}
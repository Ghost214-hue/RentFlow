<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Owner;
use App\Models\Renter;

/**
 * Who may do what with a MaintenanceRecord. DENY BY DEFAULT.
 *
 * A renter may raise a request against their own unit and read anything
 * addressed to them, but only staff may move a record through its statuses:
 * a renter changing their own record to "completed" would let them self-close
 * an unresolved repair.
 */
class MaintenanceRecordPolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    public function view(Owner|Caretaker|Renter $actor, MaintenanceRecord $record): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            // Their assigned properties, plus anything they raised themselves.
            $propertyId = $record->property_id ?? House::withoutOwnerScope()
                ->whereKey($record->house_id)
                ->value('property_id');

            return $record->assigned_to === $actor->name
                || ($propertyId !== null && $actor->isAssignedTo((int) $propertyId));
        }

        // A renter sees their own requests and any broadcast naming them.
        return (int) $record->tenant_id === (int) $actor->getKey()
            || in_array((int) $actor->getKey(), $record->recipientIds(), true);
    }

    /** Anyone in the portfolio may raise a request or broadcast a notice. */
    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    /**
     * Only staff advance a record's status. A renter may still edit their own
     * open request, e.g. to correct the description.
     */
    public function update(Owner|Caretaker|Renter $actor, MaintenanceRecord $record): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            return $this->view($actor, $record);
        }

        // Renters may edit their own request while it is still open.
        return (int) $record->tenant_id === (int) $actor->getKey() && ! $record->isClosed();
    }

    /**
     * Moving a record through its statuses is a STAFF action.
     *
     * Deliberately separate from update(): update() lets a renter edit their
     * own open request, and reusing it for advance() would let a renter
     * self-complete a repair that was never carried out.
     */
    public function advance(Owner|Caretaker|Renter $actor, MaintenanceRecord $record): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        return $actor instanceof Caretaker && $this->view($actor, $record);
    }

    /** Only the owner deletes a record. */
    public function delete(Owner|Caretaker|Renter $actor, MaintenanceRecord $record): bool
    {
        return $actor instanceof Owner;
    }
}
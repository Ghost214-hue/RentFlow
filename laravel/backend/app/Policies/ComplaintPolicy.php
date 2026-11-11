<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Complaint;
use App\Models\Owner;
use App\Models\Renter;

/**
 * Who may do what with a Complaint. DENY BY DEFAULT.
 *
 * A complaint is addressed either to a single renter or broadcast to many, so
 * visibility is decided by the recipient list rather than by the sender.
 */
class ComplaintPolicy
{
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
            // Caretakers see complaints in their assigned properties, plus the
            // ones they raised themselves.
            if ($complaint->sender_role === 'caretaker') {
                return true;
            }

            $propertyId = $this->propertyIdOf($complaint);

            return $propertyId !== null && $actor->isAssignedTo($propertyId);
        }

        // A renter sees complaints raised by anyone that name them as a
        // recipient, as well as their own.
        return (int) $complaint->tenant_id === (int) $actor->getKey()
            || in_array((int) $actor->getKey(), $complaint->recipientIds(), true);
    }

    /** Anyone in the portfolio may raise a complaint or broadcast a notice. */
    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    /**
     * The sender, or the owner, may update. A renter who is only a recipient
     * may not rewrite someone else's complaint.
     */
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

    /** Only the owner deletes a complaint. */
    public function delete(Owner|Caretaker|Renter $actor, Complaint $complaint): bool
    {
        return $actor instanceof Owner;
    }

    private function propertyIdOf(Complaint $complaint): ?int
    {
        if ($complaint->property_id !== null) {
            return (int) $complaint->property_id;
        }

        $house = \App\Models\House::withoutOwnerScope()->find($complaint->house_id);

        return $house === null ? null : (int) $house->property_id;
    }
}
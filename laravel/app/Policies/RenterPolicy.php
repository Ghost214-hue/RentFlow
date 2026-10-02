<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;

/**
 * Who may do what with a Renter (DB table `tenants`).
 *
 * DENY BY DEFAULT — see HousePolicy for the rationale.
 *
 * Field-level edit rights are enforced in StoreRenterRequest: owners and
 * caretakers edit everything, renters may only change their own contact
 * details.
 */
class RenterPolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return true;
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

    /** Only owners onboard a new renter. */
    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner;
    }

    /**
     * Owners and caretakers edit any renter in scope. Renters may edit
     * themselves, but only contact fields (enforced in the form request).
     */
    public function update(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        return $this->view($actor, $renter);
    }

    /**
     * Only an owner may terminate or delete, and never a renter who still
     * owes money — the bills and payment history must be preserved.
     */
    public function delete(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        return $actor instanceof Owner && $this->hasNoOutstandingBalance($renter);
    }

    /**
     * Refuse deletion while the renter owes money, so financial history is
     * never orphaned. Credit (overpayment) does not block deletion.
     */
    private function hasNoOutstandingBalance(Renter $renter): bool
    {
        return (float) $renter->balance <= 0.0;
    }

    /** Termination frees the unit; same owner-only rule. */
    public function terminate(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        return $actor instanceof Owner;
    }
}
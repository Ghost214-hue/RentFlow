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
     * A renter can never be HARD DELETED.
     *
     * Deleting the row would orphan their bills, payments and the
     * tenancy_terminations audit trail, and would destroy the record of who
     * occupied which unit. A tenancy now ends by being TERMINATED, which
     * frees the unit, scrubs the personal data and keeps the financial
     * history.
     *
     * Denied unconditionally rather than conditionally, so there is no path --
     * not even for an owner with a fully settled account -- that removes the
     * row. There is also no DELETE route for /renters/{renter}.
     */
    public function delete(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        return false;
    }

    /**
     * Only an owner terminates, and a caretaker may act only on renters in
     * their assigned properties.
     *
     * A renter cannot terminate themselves: they REQUEST to leave, which
     * creates a pending record the owner then approves.
     */
    public function terminate(Owner|Caretaker|Renter $actor, Renter $renter): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            return $renter->property_id !== null
                && $actor->isAssignedTo((int) $renter->property_id);
        }

        return false;
    }
}
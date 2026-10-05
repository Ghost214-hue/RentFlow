<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Renter;

/**
 * Who may do what with a Payment. DENY BY DEFAULT.
 *
 * A renter may record a payment for themselves (self-pay) and see only their
 * own payments. Corrections and deletions are owner-only: a renter must not
 * be able to edit the record of money handed over.
 */
class PaymentPolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    public function view(Owner|Caretaker|Renter $actor, Payment $payment): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            $house = \App\Models\House::withoutOwnerScope()->find($payment->house_id);

            return $house !== null && $actor->isAssignedTo((int) $house->property_id);
        }

        return (int) $payment->tenant_id === (int) $actor->getKey();
    }

    /** Owners, caretakers and renters (self-pay) may all record a payment. */
    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    /**
     * Only an owner corrects a payment. Renters may confirm their own
     * payment (acknowledgement) but never edit its value or status.
     */
    public function update(Owner|Caretaker|Renter $actor, Payment $payment): bool
    {
        return $actor instanceof Owner;
    }

    /**
     * Only an owner deletes, and never once the payment has been allocated to
     * a bill -- the allocation ledger must stay intact.
     */
    public function delete(Owner|Caretaker|Renter $actor, Payment $payment): bool
    {
        return $actor instanceof Owner && ! $payment->allocations()->exists();
    }
}
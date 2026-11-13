<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\House;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Renter;
use App\Services\PermissionService;

/**
 * Who may do what with a Payment. DENY BY DEFAULT.
 *
 * A renter may record a payment for themselves (self-pay) and see only their
 * own payments. Corrections and deletions are owner-only: a renter must not
 * be able to edit the record of money handed over.
 */
class PaymentPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'payments.view');
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

    /** Only roles with payments.record ability may record a payment. Renters only for themselves. */
    public function create(Owner|Caretaker|Renter $actor): bool
    {
        if ($actor instanceof Renter) {
            return true; // self-pay always allowed
        }

        return $this->permissions->allows($actor, 'payments.record');
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

    /** Reverse a payment — owner only, and caretakers with explicit payments.reverse grant. */
    public function reverse(Owner|Caretaker|Renter $actor, Payment $payment): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        return $this->permissions->allows($actor, 'payments.reverse');
    }
}
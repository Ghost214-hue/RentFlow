<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;

/**
 * Who may do what with a Bill. DENY BY DEFAULT.
 */
class BillPolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    public function view(Owner|Caretaker|Renter $actor, Bill $bill): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        if ($actor instanceof Caretaker) {
            return $this->propertyIdOf($bill) !== null
                && $actor->isAssignedTo($this->propertyIdOf($bill));
        }

        return (int) $bill->tenant_id === (int) $actor->getKey();
    }

    /** Only owners and caretakers generate bills; renters never do. */
    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner || $actor instanceof Caretaker;
    }

    public function update(Owner|Caretaker|Renter $actor, Bill $bill): bool
    {
        return $this->view($actor, $bill);
    }

    /**
     * Only an owner may delete, and never a bill that money has been
     * allocated to -- that would destroy the payment trail.
     */
    public function delete(Owner|Caretaker|Renter $actor, Bill $bill): bool
    {
        if (! $actor instanceof Owner) {
            return false;
        }

        return ! $bill->items()->whereHas('allocations')->exists();
    }

    private function propertyIdOf(Bill $bill): ?int
    {
        $house = \App\Models\House::withoutOwnerScope()->find($bill->house_id);

        return $house === null ? null : (int) $house->property_id;
    }
}
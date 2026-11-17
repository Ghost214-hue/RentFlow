<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\House;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Renter;
use App\Services\PermissionService;

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
            $house = App\Models\House::withoutOwnerScope()->find($payment->house_id);

            return $house !== null && $actor->isAssignedTo((int) $house->property_id);
        }

        return (int) $payment->tenant_id === (int) $actor->getKey();
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return false;
    }

    public function update(Owner|Caretaker|Renter $actor, Payment $payment): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, Payment $payment): bool
    {
        return $actor instanceof Owner && ! $payment->allocations()->exists();
    }

    public function reverse(Owner|Caretaker|Renter $actor, Payment $payment): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        return $this->permissions->allows($actor, 'payments.reverse');
    }
}
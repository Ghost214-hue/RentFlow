<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;

/**
 * Who may do what with a Caretaker account. DENY BY DEFAULT.
 *
 * Caretakers are staff: only the owner who owns the portfolio manages them.
 * A caretaker may view their own account but never edit it, and a renter has
 * no access at all.
 */
class CaretakerPolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner;
    }

    public function view(Owner|Caretaker|Renter $actor, Caretaker $caretaker): bool
    {
        // A caretaker may see themselves.
        return $actor instanceof Owner
            || ($actor instanceof Caretaker && (int) $actor->getKey() === (int) $caretaker->getKey());
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return $actor instanceof Owner;
    }

    public function update(Owner|Caretaker|Renter $actor, Caretaker $caretaker): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, Caretaker $caretaker): bool
    {
        return $actor instanceof Owner;
    }
}
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\Expense;
use App\Models\Owner;
use App\Models\Renter;
use App\Services\PermissionService;

class ExpensePolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        return $this->permissions->allows($actor, 'expenses.view');
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        return $this->permissions->allows($actor, 'expenses.record');
    }

    public function update(Owner|Caretaker|Renter $actor, Expense $expense): bool
    {
        return $actor instanceof Owner;
    }

    public function delete(Owner|Caretaker|Renter $actor, Expense $expense): bool
    {
        return $actor instanceof Owner;
    }
}

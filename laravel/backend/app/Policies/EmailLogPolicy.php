<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\EmailLog;
use App\Models\Owner;
use App\Models\Renter;
use App\Services\PermissionService;

class EmailLogPolicy
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return $this->permissions->allows($actor, 'reports.view');
    }

    public function view(Owner|Caretaker|Renter $actor, EmailLog $log): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        return $actor instanceof Renter && $log->to_email === $actor->email;
    }

    public function create(Owner|Caretaker|Renter $actor): bool
    {
        return false;
    }

    public function update(Owner|Caretaker|Renter $actor, EmailLog $log): bool
    {
        return false;
    }

    public function delete(Owner|Caretaker|Renter $actor, EmailLog $log): bool
    {
        return false;
    }
}
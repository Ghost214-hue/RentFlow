<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Caretaker;
use App\Models\EmailLog;
use App\Models\Owner;
use App\Models\Renter;

/**
 * The email delivery log is READ ONLY.
 *
 * Nothing may be created, edited or deleted through the UI: the log is the
 * evidence of what was actually sent to a renter, and the mail queue writes it.
 */
class EmailLogPolicy
{
    public function viewAny(Owner|Caretaker|Renter $actor): bool
    {
        return true;
    }

    public function view(Owner|Caretaker|Renter $actor, EmailLog $log): bool
    {
        if ($actor instanceof Owner) {
            return true;
        }

        // A renter sees only mail addressed to them.
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
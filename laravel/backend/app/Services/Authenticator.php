<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Support\Facades\Hash;

/**
 * Authenticate against the three separate actor tables.
 *
 * RentFlow stores owners, caretakers and renters in their OWN tables (the DB
 * calls renters "tenants"). There is no unified users table, so credentials
 * are checked against each in turn. Because the response never says WHICH
 * table matched, this does not leak account existence.
 *
 * NOTE: these lookups deliberately bypass the BelongsToOwner scope. At login
 * there is no owner context yet -- that is precisely what we are resolving --
 * and a scope that throws on a null context would make signing in impossible.
 * Safety comes from matching on email+password and loading only that one row.
 */
final class Authenticator
{
    public function __construct(
        private readonly string $email,
        private readonly string $password,
    ) {}

    /**
     * Find an actor row by email across every actor table.
     *
     * Bypasses the tenant scope because at login there is no owner context
     * yet -- resolving it is the whole point of this step. Loading a single
     * row matched on email + password hash is safe; nothing is listed.
     *
     * @param  class-string<Owner|Caretaker|Renter>  $model
     * @return Owner|Caretaker|Renter|null
     */
    private function findByEmail(string $model): Owner|Caretaker|Renter|null
    {
        $query = $model::query();

        // Owner has no owner_id (it IS the tenant), so it has no scope. The
        // others carry the BelongsToOwner scope, which throws without a tenant
        // context -- and at login there is none yet, because resolving it is
        // the point of this step.
        if ($model !== Owner::class) {
            $query->withoutGlobalScopes();
        }

        return $query
            ->where('email', $this->email)
            // A terminated renter must not be able to sign in.
            ->when($model === Renter::class, fn ($q) => $q->where('status', 'active'))
            ->first();
    }

    /**
     * @return Owner|Caretaker|Renter|null
     */
    public function attempt(): Owner|Caretaker|Renter|null
    {
        // Owners first, then caretakers, then renters.
        foreach ([Owner::class, Caretaker::class, Renter::class] as $model) {
            $actor = $this->findByEmail($model);

            if ($actor !== null && $this->passwordMatches((string) $actor->password)) {
                if ($actor instanceof Owner && $actor->isSuspended()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => 'Your account has been suspended. Please contact support.',
                    ]);
                }

                if ($actor instanceof Caretaker || $actor instanceof Renter) {
                    $owner = Owner::find($actor->owner_id);
                    if ($owner !== null && $owner->isSuspended()) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'email' => 'This account has been suspended. Please contact support.',
                        ]);
                    }
                }

                if (
                    config('hashing.rehash_on_login', true) &&
                    Hash::needsRehash((string) $actor->password)
                ) {
                    $actor->forceFill([
                        'password' => Hash::make($this->password),
                    ])->save();
                }

                return $actor;
            }
        }

        return null;
    }

    private function passwordMatches(string $hashedPassword): bool
    {
        if (strlen($hashedPassword) === 0) {
            return false;
        }

        // password_verify() is algorithm-agnostic: it reads the algorithm
        // prefix ($2y$, $argon2i$, $argon2id$) from the hash itself, so it
        // correctly verifies bcrypt, argon2i, and argon2id hashes alike.
        // Using Laravel's Hash facade here would throw on algorithm mismatch
        // (e.g. an imported argon2i hash checked by an argon2id hasher).
        return password_verify($this->password, $hashedPassword);
    }

    /**
     * True when the email exists but the password was wrong. Used only to
     * count throttling attempts; never surfaced to the user.
     */
    public function emailExists(): bool
    {
        foreach ([Owner::class, Caretaker::class, Renter::class] as $model) {
            // Uses the same scope-bypassing lookup as attempt().
            if ($this->findByEmail($model) !== null) {
                return true;
            }
        }

        return false;
    }
}
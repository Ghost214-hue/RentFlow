<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;

/**
 * Resolves and holds the owner ("SaaS tenant") for the current request.
 *
 * RentFlow has three actor kinds but ONE tenancy axis: the owner.
 *   - owner     -> owner_id is their own id
 *   - caretaker -> resolves through caretakers.owner_id
 *   - renter    -> resolves through tenants.owner_id
 *
 * Resolving every actor to an owner is what lets a single global scope
 * enforce isolation for all three, instead of three divergent code paths.
 *
 * A caretaker's *additional* restriction (only assigned properties) and a
 * renter's (only their own rows) are enforced by Policies, not here.
 */
final class TenantContext
{
    private static ?int $ownerId = null;

    private static bool $resolved = false;

    public static function id(): ?int
    {
        return self::$ownerId;
    }

    public static function isResolved(): bool
    {
        return self::$resolved;
    }

    /**
     * Set the owner for this request/command. Called by the session guard and
     * by the legacy JWT bridge.
     */
    public static function set(?int $ownerId): void
    {
        self::$ownerId = $ownerId;
        self::$resolved = true;
    }

    public static function clear(): void
    {
        self::$ownerId = null;
        self::$resolved = false;
    }

    /**
     * Derive the owner id from an authenticated actor.
     *
     * An Owner IS the tenancy, so its own id is the owner id. A Caretaker or
     * Renter belongs to an owner, so theirs is the `owner_id` column.
     *
     * NOTE: getKey() takes no arguments and always returns the primary key.
     * Passing 'owner_id' to it looks like it reads that column but silently
     * returns the primary key instead, which would resolve a renter's context
     * to the RENTER's own id and scope every query to a non-existent owner.
     * The attribute is therefore read explicitly.
     */
    public static function resolveForActor(Owner|Caretaker|Renter $actor): ?int
    {
        if ($actor instanceof Owner) {
            return (int) $actor->getKey();
        }

        $ownerId = $actor->getAttribute('owner_id');

        return $ownerId === null ? null : (int) $ownerId;
    }
}
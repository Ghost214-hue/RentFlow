<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * `tenants.status` in the database.
 *
 * The column is ENUM('active','pending_termination','terminated'). Note that
 * 'pending_termination' is a real state the legacy code writes but never
 * reads, so a renter can be left stuck mid-termination.
 */
enum RenterStatus: string
{
    case Active = 'active';
    case PendingTermination = 'pending_termination';
    case Terminated = 'terminated';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
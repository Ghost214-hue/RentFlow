<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Actor roles in RentFlow.
 *
 * An owner is one tenant of the SaaS: a landlord with their own portfolio.
 * "Tenants" in the database mean renters. A caretaker is limited to assigned
 * properties; a renter sees only their own records.
 */
enum Role: string
{
    case Owner = 'owner';
    case Caretaker = 'caretaker';
    case Tenant = 'tenant';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Caretaker => 'Caretaker',
            self::Tenant => 'Renter',
        };
    }
}
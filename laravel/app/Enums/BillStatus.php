<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Bill status as persisted in `bills.status`.
 *
 * NOTE: the stored column is a denormalised cache. `payment_allocations` is
 * the single source of truth and BillSnapshot derives the authoritative status
 * at read time. See docs/migration/TRACEABILITY.md (defect 5).
 */
enum BillStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Paid = 'paid';
    /** Derived at read time only — never persisted. */
    case Overdue = 'overdue';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
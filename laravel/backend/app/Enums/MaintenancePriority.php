<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * `maintenance_records.priority` — ENUM('low','medium','high','urgent').
 *
 * NOTE: this has an 'urgent' value that complaints.priority does NOT have.
 * The two tables must not share one enum.
 */
enum MaintenancePriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
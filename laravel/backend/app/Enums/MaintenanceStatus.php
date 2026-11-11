<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * `maintenance_records.status`.
 *
 * Note this is a DIFFERENT enum from complaints.status: maintenance starts at
 * 'pending' where complaints start at 'open', and maintenance can be
 * 'cancelled' where complaints cannot.
 */
enum MaintenanceStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in-progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }

    /**
     * The next status a staff member moves this record to, or null when the
     * record has reached a terminal state.
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Pending => self::InProgress,
            self::InProgress => self::Completed,
            self::Completed, self::Cancelled => null,
        };
    }
}
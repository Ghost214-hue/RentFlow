<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * `complaints.status`.
 *
 * NOTE: the live column is ENUM('open','in-progress','resolved') -- it does NOT
 * contain 'pending_approval', even though the legacy ComplaintController
 * filtered on it. That filter therefore never matched anything.
 */
enum ComplaintStatus: string
{
    case Open = 'open';
    case InProgress = 'in-progress';
    case Resolved = 'resolved';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
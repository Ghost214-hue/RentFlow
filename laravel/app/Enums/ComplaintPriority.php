<?php

declare(strict_types=1);

namespace App\Enums;

/** `complaints.priority` — ENUM('low','medium','high'). */
enum ComplaintPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
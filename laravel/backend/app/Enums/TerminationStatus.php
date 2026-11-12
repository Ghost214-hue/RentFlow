<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * `tenancy_terminations.status` — ENUM('pending','approved','completed').
 *
 * A tenant-initiated request starts PENDING and needs owner approval. An
 * owner-initiated termination is written as COMPLETED straight away.
 */
enum TerminationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Completed = 'completed';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
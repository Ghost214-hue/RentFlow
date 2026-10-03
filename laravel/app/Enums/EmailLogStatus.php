<?php

declare(strict_types=1);

namespace App\Enums;

/** `email_logs.status` — ENUM('sent','failed','pending'). */
enum EmailLogStatus: string
{
    case Sent = 'sent';
    case Failed = 'failed';
    case Pending = 'pending';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
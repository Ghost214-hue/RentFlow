<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * `payments.status` in the database.
 *
 * The DB column is an ENUM of ('completed','pending','failed'). Legacy code
 * also wrote 'confirmed', which the column cannot store (defect 2), so that
 * vocabulary is normalised on the way in and is not part of this enum.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }

    /**
     * Normalise legacy input, including the historical 'confirmed'/'paid'
     * spellings that the ENUM does not contain.
     */
    public static function normalise(string $value): self
    {
        return match (strtolower($value)) {
            'confirmed', 'paid', 'completed' => self::Completed,
            'failed', 'declined' => self::Failed,
            default => self::Pending,
        };
    }

    /** A settled payment counts towards a bill. */
    public function isSettled(): bool
    {
        return $this === self::Completed;
    }
}
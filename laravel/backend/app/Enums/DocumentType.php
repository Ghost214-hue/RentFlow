<?php

declare(strict_types=1);

namespace App\Enums;

/** `property_documents.type` — ENUM('rules','regulations','policy','other'). */
enum DocumentType: string
{
    case Rules = 'rules';
    case Regulations = 'regulations';
    case Policy = 'policy';
    case Other = 'other';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
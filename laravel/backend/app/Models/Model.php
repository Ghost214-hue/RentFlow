<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * Base model for RentFlow's shared-schema, owner-scoped domain tables.
 *
 * Kept deliberately small: it exists so cross-cutting conventions (money as
 * strings, timestamps, factory guessing) have one home rather than being
 * repeated on every model. Tenancy is NOT applied here — opt in per model
 * with the BelongsToOwner trait, because `owners`, `email_templates` and the
 * shared lookup tables have no owner_id.
 */
abstract class Model extends BaseModel
{
    /**
     * Money must never be a float. Any decimal column cast to 'decimal:2'
     * returns a string, matching the TypeScript contract.
     *
     * @var array<int, string>
     */
    protected const MONEY_CASTS = [
        'amount', 'total', 'paid', 'balance', 'credit', 'rent', 'deposit',
        'monthly_rent', 'water_balance', 'elec_balance', 'arrears', 'overpaid',
    ];

    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }
}
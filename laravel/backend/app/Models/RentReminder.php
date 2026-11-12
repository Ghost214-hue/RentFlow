<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;

/**
 * A rent reminder that has already been sent.
 *
 * Maps the EXISTING `rent_reminders` table, which the legacy app created but
 * never wrote to.
 *
 * THIS TABLE IS THE IDEMPOTENCY KEY, not just a history.
 *
 * Reminders run on a schedule, so the same bill would otherwise be chased every
 * single day from generation until the due date. The pair (bill_id,
 * days_before_due) is unique in practice: one reminder at that lead time, ever.
 * Without this row a renter gets "friendly reminder" emails stacked up behind
 * each other, which is how landlords end up being reported to spam.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $bill_id
 * @property string $month
 * @property string $amount
 * @property int $days_before_due
 * @property string|null $sent_at
 * @property string $status
 */
class RentReminder extends Model
{
    use BelongsToOwner;

    protected $table = 'rent_reminders';

    /**
     * Only created_at exists on this table -- there is no updated_at.
     *
     * A reminder row is immutable once written (it exists to prove a send
     * happened), so there is nothing for updated_at to track. Left on, every
     * insert fails with "Unknown column 'updated_at'".
     */
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'house_id',
        'bill_id',
        'month',
        'amount',
        'days_before_due',
        'sent_at',
        'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sent_at' => 'datetime',
            'days_before_due' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Has this exact reminder already gone out for this bill?
     *
     * Scoped off deliberately: the reminder command spans every owner and checks
     * this BEFORE resolving an owner's context, so the owner scope would throw
     * rather than answer.
     */
    public static function alreadySent(int $billId, int $daysBeforeDue): bool
    {
        return self::withoutOwnerScope()
            ->where('bill_id', $billId)
            ->where('days_before_due', $daysBeforeDue)
            ->exists();
    }
}

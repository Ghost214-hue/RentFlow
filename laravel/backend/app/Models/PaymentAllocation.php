<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * The allocation ledger: how much of a payment settled a specific bill item.
 *
 * This is the SINGLE SOURCE OF TRUTH for money in RentFlow. `bill_items.paid`,
 * `bills.status` and `tenants.balance/credit` are denormalised caches that
 * drift; every financial figure is derived from these rows instead (defect 5).
 *
 * There is NO owner_id here either, so tenancy is scoped through the payment.
 *
 * @property int $id
 * @property int $payment_id
 * @property int $bill_item_id
 * @property string $category
 * @property string $amount
 */
class PaymentAllocation extends Model
{
    protected $table = 'payment_allocations';

    /** No `updated_at` column in the live schema (see SHOW COLUMNS). */
    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'bill_item_id',
        'category',
        'amount',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ownerThroughPayment', static function (Builder $query): void {
            $ownerId = TenantContext::id();

            if ($ownerId === null) {
                throw new RuntimeException(sprintf(
                    'No owner context resolved for %s. Refusing to run an unscoped query.',
                    static::class,
                ));
            }

            $query->whereHas(
                'payment',
                static fn (Builder $p): Builder => $p->where('owner_id', $ownerId),
            );
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function billItem(): BelongsTo
    {
        return $this->belongsTo(BillItem::class, 'bill_item_id');
    }
}
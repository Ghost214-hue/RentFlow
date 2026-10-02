<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * A charge line on a bill. Maps the EXISTING `bill_items` table.
 *
 * There is NO owner_id on this table, so tenancy is scoped THROUGH the parent
 * bill. The global scope below restricts reads and writes to items whose bill
 * belongs to the current owner; without it, a leaked item id would expose
 * another owner's charges.
 *
 * @property int $id
 * @property int $bill_id
 * @property string $type     Rent|Deposit|Water|Electricity|Other
 * @property string $amount
 * @property string $paid     Denormalised cache of allocations; drifts (defect 5)
 * @property string $status
 */
class BillItem extends Model
{
    protected $table = 'bill_items';

    /** @return array<int, string> */
    protected $fillable = [
        'bill_id',
        'type',
        'description',
        'amount',
        'paid',
        'status',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ownerThroughBill', static function (Builder $query): void {
            $ownerId = TenantContext::id();

            if ($ownerId === null) {
                throw new RuntimeException(sprintf(
                    'No owner context resolved for %s. Refusing to run an unscoped query.',
                    static::class,
                ));
            }

            $query->whereHas(
                'bill',
                static fn (Builder $bill): Builder => $bill->where('owner_id', $ownerId),
            );
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'bill_item_id');
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillStatus;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A monthly invoice. Maps the EXISTING `bills` table.
 *
 * The table carries BOTH legacy denormalised columns (rent, water,
 * electricity, total, status) AND normalised `bill_items`. `bill_items` is
 * authoritative; the legacy columns are a compatibility cache that legacy
 * PHP pages still read, so they are kept in step on write but never trusted
 * for financial truth.
 *
 * @property int $id
 * @property int $owner_id
 * @property int $house_id
 * @property int|null $tenant_id
 * @property string $month   YYYY-MM
 * @property string $total
 * @property string $due_date
 * @property BillStatus|string $status
 */
class Bill extends Model
{
    /** @use HasFactory<\Database\Factories\BillFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'bills';

    protected $fillable = [
        'house_id',
        'tenant_id',
        'month',
        'type',
        'description',
        'rent',
        'water',
        'electricity',
        'total',
        'status',
        'due_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rent' => 'decimal:2',
            'water' => 'decimal:2',
            'electricity' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => BillStatus::class,
            'due_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(Renter::class, 'tenant_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class, 'bill_id');
    }

    /** Is this bill still owed anything? Derived from the allocation ledger. */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereHas('items', static function (Builder $items): void {
            $items->whereColumn('bill_items.amount', '>', $items
                ->selectRaw('COALESCE(SUM(pa.amount), 0)')
                ->from('payment_allocations')
                ->whereColumn('bill_items.id', 'payment_allocations.bill_item_id'));
        });
    }
}
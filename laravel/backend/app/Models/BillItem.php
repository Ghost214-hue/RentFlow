<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A charge line on a bill. Maps the EXISTING `bill_items` table.
 *
 * owner_id is stamped on create so the BelongsToOwner global scope can
 * restrict reads/writes to the current owner's rows directly, without
 * traversing through the parent bill.
 *
 * @property int $id
 * @property int $owner_id
 * @property int $bill_id
 * @property string $type     Rent|Deposit|Water|Electricity|Other
 * @property string $amount
 * @property string $paid     Denormalised cache of allocations; drifts (defect 5)
 * @property string $status
 */
class BillItem extends Model
{
    use BelongsToOwner;

    protected $table = 'bill_items';

    /** @return array<int, string> */
    protected $fillable = [
        'owner_id',
        'bill_id',
        'type',
        'description',
        'amount',
        'paid',
        'status',
    ];

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
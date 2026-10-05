<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A received payment. Maps the EXISTING `payments` table.
 *
 * A payment is not tied to one bill: it cascades across the renter's arrears
 * (chosen month first, then oldest, remainder to credit). The link lives in
 * `payment_allocations`.
 *
 * @property int $id
 * @property int $owner_id
 * @property int $tenant_id
 * @property string $amount
 * @property string $month
 * @property PaymentStatus|string $status
 * @property string $receipt
 */
class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'payments';

    /**
     * The payments table has `created_at` but NO `updated_at` column (see the
     * live schema), so Eloquent must not try to maintain a timestamp on
     * update -- doing so throws "Unknown column 'updated_at'" on every insert.
     */
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'house_id',
        'month',
        'amount',
        'type',
        'method',
        'date',
        'status',
        'tenant_confirmed',
        'confirmed_at',
        'receipt',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'tenant_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
            'date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(Renter::class, 'tenant_id');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_id');
    }
}
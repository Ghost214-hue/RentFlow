<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A tenancy termination request or completion.
 *
 * Maps the EXISTING `tenancy_terminations` table, which the legacy app wrote
 * but the port never used: the renter status enum has `pending_termination`
 * and nothing could put a renter into it.
 *
 * Two flows land here:
 *   initiated_by = tenant  -> status pending, the owner must approve
 *   initiated_by = owner   -> status completed, applied immediately
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $initiated_by  tenant|owner|caretaker
 * @property string|null $reason
 * @property string $effective_date
 * @property string $status        pending|approved|completed
 */
class TenancyTermination extends Model
{
    /** @use HasFactory<\Database\Factories\TenancyTerminationFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'tenancy_terminations';

    /** @return array<int, string> */
    protected $fillable = [
        'tenant_id',
        'property_id',
        'house_id',
        'initiated_by',
        'initiated_by_user_id',
        'reason',
        'effective_date',
        'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(Renter::class, 'tenant_id');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
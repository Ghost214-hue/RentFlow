<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A rentable unit. Maps the EXISTING `houses` table.
 *
 * IMPORTANT: `houses.rent` is the source of truth for monthly bill
 * generation — NOT `tenants.rent`, which is only an onboarding snapshot.
 *
 * @property int $id
 * @property int $owner_id
 * @property int $property_id
 * @property string $unit
 * @property string $rent
 * @property string $status   occupied|vacant
 */
class House extends Model
{
    /** @use HasFactory<\Database\Factories\HouseFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'houses';

    protected $fillable = [
        'property_id',
        'unit',
        'type',
        'status',
        'tenant_id',
        'rent',
        'water_meter',
        'elec_meter',
        'last_reading',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rent' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(Renter::class, 'tenant_id');
    }

    /** Bills raised against this unit. */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'house_id');
    }

    /** Maintenance requests raised against this unit. */
    public function maintenance(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'house_id');
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }
}
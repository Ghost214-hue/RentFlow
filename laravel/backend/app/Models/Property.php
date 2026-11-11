<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A landlord's building. Maps the EXISTING `properties` table.
 *
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $rent
 */
class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'properties';

    protected $fillable = [
        'name',
        'address',
        'type',
        'units',
        'occupied',
        'image',
        'caretaker_id',
        'rent',
        'payment_method_type',
        'paybill_number',
        'paybill_account',
        'till_number',
        'bank_name',
        'bank_account',
        'bank_branch',
        'mobile_money_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rent' => 'decimal:2',
            'units' => 'integer',
            'occupied' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function houses(): HasMany
    {
        return $this->hasMany(House::class, 'property_id');
    }

    /**
     * The caretaker assigned to this property.
     *
     * properties.caretaker_id points at caretakers.id. It is NULLABLE and the
     * row may have been deleted since, so the relation is resolved lazily and
     * callers must handle null.
     */
    public function caretaker(): BelongsTo
    {
        return $this->belongsTo(Caretaker::class, 'caretaker_id');
    }
}
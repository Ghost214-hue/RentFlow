<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A caretaker, limited to a subset of the owner's properties.
 *
 * `assigned_properties` is a CSV of property ids in the existing schema. It
 * is normalised here into a typed list so authorisation logic never parses a
 * string by hand.
 *
 * @property int $id
 * @property int $owner_id
 * @property string|null $assigned_properties  CSV, e.g. "3,7,9"
 */
class Caretaker extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\CaretakerFactory> */
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'caretakers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'id_number',
        'password',
        'avatar',
        'assigned_properties',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function role(): Role
    {
        return Role::Caretaker;
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Property ids this caretaker may act on.
     *
     * Reads from the caretaker_property pivot table instead of the legacy
     * CSV `assigned_properties` column.
     *
     * @return array<int, int>
     */
    public function assignedPropertyIds(): array
    {
        $ids = $this->properties()
            ->pluck('properties.id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (empty($ids) && ! empty($this->assigned_properties)) {
            return array_values(array_filter(
                array_map('intval', explode(',', (string) $this->assigned_properties))
            ));
        }

        return $ids;
    }

    public function isAssignedTo(int $propertyId): bool
    {
        return in_array($propertyId, $this->assignedPropertyIds(), true);
    }

    /**
     * Properties this caretaker is assigned to (via the pivot table).
     */
    public function properties(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Property::class,
            'caretaker_property',
            'caretaker_id',
            'property_id'
        )->withPivot('owner_id')->wherePivot('owner_id', $this->owner_id);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
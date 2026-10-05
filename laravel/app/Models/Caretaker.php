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

    /**
     * Property ids this caretaker may act on.
     *
     * @return array<int, int>
     */
    public function assignedPropertyIds(): array
    {
        if ($this->assigned_properties === null || trim($this->assigned_properties) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $id): int => (int) trim($id),
            explode(',', $this->assigned_properties),
        )));
    }

    public function isAssignedTo(int $propertyId): bool
    {
        return in_array($propertyId, $this->assignedPropertyIds(), true);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A landlord. Each owner is one tenant of the SaaS.
 *
 * Maps the EXISTING `owners` table — no schema changes (expand/contract).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 */
class Owner extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\OwnerFactory> */
    use HasFactory;
    use Notifiable;

    protected $table = 'owners';

    /** owners.id is a plain INT, not auto-increment, in the existing schema. */
    public $incrementing = true;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Money is DECIMAL(12,2) and must never become a float. Casting to
     * 'decimal:2' returns a string, which is what the TypeScript side expects.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function role(): Role
    {
        return Role::Owner;
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    public function renters(): HasMany
    {
        return $this->hasMany(Renter::class, 'owner_id');
    }

    public function caretakers(): HasMany
    {
        return $this->hasMany(Caretaker::class, 'owner_id');
    }
}
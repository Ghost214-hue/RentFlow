<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Unified identity. One login can hold several profiles (owner, caretaker,
 * renter). Actors link to users via user_id; users.email is globally unique.
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $status        active | suspended | terminated
 * @property string|null $last_login
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery
 * @property bool $two_factor_required
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',
        'status',
        'last_login',
        'two_factor_secret',
        'two_factor_recovery',
        'two_factor_required',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery',
    ];

    protected $casts = [
        'two_factor_required' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->hasOne(Owner::class, 'user_id');
    }

    public function caretaker()
    {
        return $this->hasOne(Caretaker::class, 'user_id');
    }

    public function renter()
    {
        return $this->hasOne(Renter::class, 'user_id');
    }

    public function profiles(): array
    {
        $profiles = [];

        if ($this->owner) {
            $profiles[] = ['type' => 'owner', 'id' => $this->owner->id, 'owner_id' => $this->owner->id];
        }

        if ($this->caretaker) {
            $profiles[] = [
                'type' => 'caretaker',
                'id' => $this->caretaker->id,
                'owner_id' => (int) $this->caretaker->owner_id,
            ];
        }

        if ($this->renter) {
            $profiles[] = [
                'type' => 'tenant',
                'id' => $this->renter->id,
                'owner_id' => (int) $this->renter->owner_id,
            ];
        }

        return $profiles;
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}

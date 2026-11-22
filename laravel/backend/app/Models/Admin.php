<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Platform administrator. Separate from owners — super admin staff only.
 *
 * Two-factor authentication is mandatory before any admin page loads.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $status        active | suspended
 * @property string $admin_role    super_admin | support | auditor
 */
class Admin extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'admin_role',
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
        // Encrypted at rest (needs the text column from migration 2026_11_21_000004).
        'two_factor_secret' => 'encrypted',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function role(): string
    {
        return 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->admin_role === 'super_admin';
    }
}

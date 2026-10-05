<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single-use password reset code.
 *
 * Maps the EXISTING `password_reset_tokens` table, which the legacy app built
 * around a six-digit emailed code rather than a long opaque token.
 *
 * DELIBERATELY NOT OWNER-SCOPED. This is the one table that must be readable
 * before anyone is authenticated: a caretaker resetting their password has no
 * owner session, and an owner_id scope would make their reset impossible. It
 * is reachable only through PasswordResetService, never from a controller.
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $tenant_id
 * @property string $email
 * @property string $code
 * @property CarbonImmutable $expires_at
 * @property bool $used
 * @property int $attempts
 */
class PasswordResetToken extends Model
{
    /** No updated_at on this table; it is append-only and immutable once used. */
    public $timestamps = false;

    protected $table = 'password_reset_tokens';

    protected $fillable = [
        'owner_id',
        'tenant_id',
        'email',
        'code',
        'expires_at',
        'used',
        'attempts',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used' => 'boolean',
            'attempts' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function renter(): BelongsTo
    {
        return $this->belongsTo(Renter::class, 'tenant_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return ! $this->used && ! $this->isExpired();
    }
}

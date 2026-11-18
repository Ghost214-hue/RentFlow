<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A time-limited grant letting an admin see one owner's data.
 *
 * The grant is soft-revoked (revoked_at) rather than deleted so the audit
 * trail survives. Re-granting after a revoke reuses the same row, which is
 * why the (owner_id, admin_id) unique index must not block a second insert.
 */
class SupportAccessGrant extends Model
{
    use BelongsToOwner;
    use HasFactory;

    protected $table = 'support_access_grants';

    /** @var list<string> */
    protected $fillable = [
        'owner_id',
        'admin_id',
        'scope',
        'reason',
        'expires_at',
        'revoked_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'scope' => 'array',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    /**
     * Whether this grant covers a named scope (names, phones, id_numbers,
     * payment_lines).
     */
    public function covers(string $scope): bool
    {
        return $this->isActive()
            && in_array($scope, $this->scope ?? [], true);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * A one-time invitation letting a renter or caretaker choose their first
 * password.
 *
 * Maps the EXISTING `password_setup_tokens` table, which the legacy app wrote
 * but Laravel never read.
 *
 * THE TOKEN IS STORED HASHED. Legacy generated 32 random bytes, hex-encoded
 * them, and wrote the plaintext into this table -- so a database leak handed an
 * attacker every outstanding invitation, and those links set passwords.
 *
 * `token` is VARCHAR(128), which comfortably fits a SHA-256 digest, so the
 * plaintext goes in the emailed link and only its hash is stored. Comparison is
 * a plain equality on an already-uniform hash, so no constant-time trick is
 * needed here (unlike the 6-digit reset code, which must be compared in
 * constant time because the search space is tiny).
 *
 * NOT owner-scoped, for the same reason as PasswordResetToken: an invited
 * caretaker has no session, and the token must resolve before anyone is
 * authenticated.
 *
 * @property int $id
 * @property string $user_type tenant|caretaker
 * @property int $user_id
 * @property int $owner_id
 * @property string $token SHA-256 hex digest of the token that was emailed
 * @property CarbonImmutable $expires_at
 * @property CarbonImmutable|null $used_at
 */
class PasswordSetupToken extends Model
{
    /** created_at only; there is no updated_at and the row is never edited. */
    public $timestamps = false;

    protected $table = 'password_setup_tokens';

    protected $fillable = [
        'user_type',
        'user_id',
        'owner_id',
        'token',
        'expires_at',
        'used_at',
        'created_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    /** The live token matching a plaintext value from an emailed link, or null. */
    public static function findUsable(string $plainToken): ?self
    {
        return self::query()
            ->where('token', self::hash($plainToken))
            ->whereNull('used_at')
            ->where('expires_at', '>', CarbonImmutable::now())
            ->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

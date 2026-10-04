<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Role;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\Renter;

/**
 * Verifies the legacy app's HS256 JWT during coexistence.
 *
 * Must stay byte-compatible with backend/app/Core/JWT.php:
 *   header  {alg: HS256, typ: JWT}
 *   payload includes iat/exp, plus role, owner_id, actor_id and (renters) tenant_id
 *   signature HMAC-SHA256 over base64url(header) . "." . base64url(payload)
 *
 * Verification is constant-time (hash_equals) and fails closed: any
 * structural problem returns null rather than a partially-trusted payload.
 *
 * REMOVE AT CUTOVER alongside AuthenticateLegacyJwt.
 */
final class LegacyJwt
{
    /**
     * @return array<string, mixed>|null
     */
    public static function decode(string $token): ?array
    {
        // A bridge with no usable secret is switched OFF, not open. Checking
        // this first means the rest of the method never has to reason about a
        // blank key.
        if (self::hasSecret() === false) {
            return null;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $signature = self::base64UrlDecode($signatureB64);

        if ($signature === false) {
            return null;
        }

        $expected = hash_hmac(
            'sha256',
            $headerB64.'.'.$payloadB64,
            self::secret(),
            true,
        );

        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $payloadJson = self::base64UrlDecode($payloadB64);

        if ($payloadJson === false) {
            return null;
        }

        $payload = json_decode($payloadJson, true);

        if (! is_array($payload)) {
            return null;
        }

        // Expiry is mandatory. A token without one is not trusted.
        if (! isset($payload['exp']) || ! is_numeric($payload['exp'])) {
            return null;
        }

        if ((int) $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Load the actor a token refers to, scoped to the owner it claims.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function resolveActor(array $payload): Owner|Caretaker|Renter|null
    {
        $role = isset($payload['role']) ? Role::tryFrom((string) $payload['role']) : null;
        $ownerId = isset($payload['owner_id']) ? (int) $payload['owner_id'] : null;

        if ($role === null || $ownerId === null || $ownerId <= 0) {
            return null;
        }

        return match ($role) {
            Role::Owner => Owner::find($ownerId),

            Role::Caretaker => Caretaker::query()
                ->where('owner_id', $ownerId)
                ->whereKey((int) ($payload['actor_id'] ?? 0))
                ->first(),

            // The legacy token carries tenant_id for renters; the DB table is
            // `tenants` but the model is Renter.
            Role::Tenant => Renter::query()
                ->where('owner_id', $ownerId)
                ->whereKey((int) ($payload['tenant_id'] ?? $payload['actor_id'] ?? 0))
                ->first(),
        };
    }

    /** Is the bridge usable at all? Empty or missing secret means no. */
    public static function hasSecret(): bool
    {
        return trim((string) env('JWT_SECRET', '')) !== '';
    }

    /**
     * The shared HS256 secret.
     *
     * FAILS CLOSED. hash_hmac() accepts an EMPTY key and still produces a
     * valid, verifiable MAC -- so an empty JWT_SECRET means anyone can
     * compute a correct signature for a token of their choosing and
     * impersonate any owner. This project shipped with JWT_SECRET= (present,
     * but zero characters), which is exactly that condition.
     *
     * A missing or empty secret therefore disables the bridge entirely rather
     * than accepting anything. Signing in still works through the normal
     * Laravel session; only the legacy cookie is refused.
     *
     * @throws MissingJwtSecret
     */
    private static function secret(): string
    {
        $secret = trim((string) env('JWT_SECRET', ''));

        if ($secret === '') {
            throw MissingJwtSecret::make();
        }

        return $secret;
    }

    private static function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;

        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}

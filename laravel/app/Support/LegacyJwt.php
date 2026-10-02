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

    private static function secret(): string
    {
        // Shared with the legacy app for the duration of coexistence.
        $secret = (string) env('JWT_SECRET', '');

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
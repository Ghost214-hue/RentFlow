<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * The legacy JWT bridge has no usable shared secret.
 *
 * Thrown when something asks for the secret directly. decode() checks
 * hasSecret() first and returns null quietly, so a normal request never sees
 * this -- it exists so that generating the secret is loud, and so a test can
 * assert the failure rather than the absence of one.
 */
final class MissingJwtSecret extends RuntimeException
{
    public static function make(): self
    {
        return new self(
            'JWT_SECRET is missing or empty. Generate one with '
            .'`php artisan jwt:secret` and set the same value in the legacy app. '
            .'Until then the legacy sign-in bridge is disabled and only Laravel '
            .'session sign-in works.',
        );
    }
}

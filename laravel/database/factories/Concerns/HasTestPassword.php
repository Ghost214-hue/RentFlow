<?php

declare(strict_types=1);

namespace Database\Factories\Concerns;

/**
 * Lets a test state a known plaintext password.
 *
 * Several actor models cast `password` to 'hashed', so passing a plain string
 * through state() works directly. This helper exists for the cases where the
 * value must already be a hash, and keeps the intent readable at the call site.
 */
trait HasTestPassword
{
    public function withPassword(string $plain): static
    {
        return $this->state(fn (): array => ['password' => $plain]);
    }
}
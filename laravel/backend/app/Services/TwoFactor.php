<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP enrolment and verification for admin accounts.
 *
 * Secrets are encrypted at rest (Admin model cast); recovery codes are stored
 * hashed, comma-separated.
 *
 * Hardening over the previous version:
 *  - input is normalised ("123 456", "123-456", recovery codes in any case);
 *  - a tolerance window absorbs clock drift (the most common "valid code
 *    rejected" cause);
 *  - a code can be used ONCE (replay guard), even inside its window;
 *  - a corrupt/non-base32 stored secret fails closed instead of a 500.
 */
final class TwoFactor
{
    public function __construct(private readonly Google2FA $google2fa)
    {
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function qrCodeUrl(Admin $admin, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            (string) config('app.name', 'RentFlow').' Admin',
            (string) $admin->email,
            $secret,
        );
    }

    /** Verify a code against a raw secret (used during enrolment). */
    public function verify(string $secret, string $code): bool
    {
        $code = $this->digits($code);

        if ($secret === '' || strlen($code) !== 6) {
            return false;
        }

        try {
            return (bool) $this->google2fa->verifyKey($secret, $code, (int) config('admin.totp_window', 2));
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /** Verify a login code for an enrolled admin; each code works once. */
    public function verifyCode(Admin $admin, string $code): bool
    {
        $code = $this->digits($code);

        if (! $this->verify((string) $admin->two_factor_secret, $code)) {
            return false;
        }

        // Cache::add is atomic: false if this code was already used recently.
        return Cache::add('admin2fa:used:'.$admin->getKey().':'.$code, 1, 180);
    }

    /** @return list<string> */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }

        return $codes;
    }

    /** @param list<string> $codes */
    public function hashRecoveryCodes(array $codes): string
    {
        return implode(',', array_map(
            static fn (string $code): string => Hash::make($code),
            $codes,
        ));
    }

    /** Consume a recovery code. A used code is removed so it cannot be replayed. */
    public function consumeRecoveryCode(Admin $admin, string $code): bool
    {
        $code = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $code));

        if ($code === '') {
            return false;
        }

        $stored = array_values(array_filter(explode(',', (string) $admin->two_factor_recovery)));

        foreach ($stored as $index => $hash) {
            if (! Hash::check($code, $hash)) {
                continue;
            }

            unset($stored[$index]);

            $admin->forceFill(['two_factor_recovery' => implode(',', $stored)])->save();

            return true;
        }

        return false;
    }

    private function digits(string $code): string
    {
        return (string) preg_replace('/\D/', '', $code);
    }
}

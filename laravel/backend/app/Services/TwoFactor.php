<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP enrolment and verification for admin accounts.
 *
 * Secrets are stored encrypted (see the Admin model cast) and recovery codes
 * are stored hashed, one per comma-separated entry.
 */
final class TwoFactor
{
    public function __construct(private readonly Google2FA $google2fa)
    {
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function qrCodeUrl(Admin $admin, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            (string) config('app.name', 'RentFlow'),
            (string) $admin->email,
            $secret,
        );
    }

    public function verify(string $secret, string $code): bool
    {
        if ($secret === '' || $code === '') {
            return false;
        }

        return $this->google2fa->verifyKey($secret, $code);
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

    /**
     * Consume a recovery code. A used code is removed so it cannot be replayed.
     */
    public function consumeRecoveryCode(Admin $admin, string $code): bool
    {
        $stored = array_values(array_filter(
            explode(',', (string) $admin->two_factor_recovery),
        ));

        foreach ($stored as $index => $hash) {
            if (! Hash::check($code, $hash)) {
                continue;
            }

            unset($stored[$index]);

            $admin->forceFill([
                'two_factor_recovery' => implode(',', $stored),
            ])->save();

            return true;
        }

        return false;
    }
}

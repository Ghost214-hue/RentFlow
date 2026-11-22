<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * The Admin model now encrypts two_factor_secret. Convert what is stored:
 *  - valid plaintext base32 secrets (from the enrolment screen) are encrypted;
 *  - secrets the old seeder invented (random hex, not base32, nobody holds the
 *    authenticator) and plaintext recovery codes are CLEARED, so that admin
 *    simply enrols properly at next login instead of being locked out.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('admins')->get(['id', 'two_factor_secret', 'two_factor_recovery']) as $row) {
            $secret = (string) $row->two_factor_secret;
            $recovery = (string) $row->two_factor_recovery;

            if ($secret === '') {
                continue;
            }

            if ($this->alreadyEncrypted($secret)) {
                continue;
            }

            $update = [];

            if (preg_match('/^[A-Z2-7]{16,}$/', $secret) === 1) {
                $update['two_factor_secret'] = Crypt::encryptString($secret);
            } else {
                $update['two_factor_secret'] = null;
                $update['two_factor_recovery'] = null;
            }

            // Recovery codes must be bcrypt hashes; plaintext ones are unusable.
            if ($recovery !== '' && ! str_starts_with($recovery, '$2y$')) {
                $update['two_factor_recovery'] = null;
            }

            DB::table('admins')->where('id', $row->id)->update($update);
        }
    }

    public function down(): void
    {
        // Irreversible by design.
    }

    private function alreadyEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};

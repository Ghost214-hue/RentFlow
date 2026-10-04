<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\Mailer;
use App\Mail\MailTemplate;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\PasswordResetToken;
use App\Models\Renter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Issues, verifies and consumes six-digit password reset codes.
 *
 * Accounts live in three tables (owners, tenants, caretakers), so a reset has
 * to find the right one. The legacy app returned 404 "No account found with
 * that email address" -- which is an account-enumeration oracle: anyone could
 * harvest which addresses have RentFlow accounts and then phish exactly those
 * people. Every method here is deliberately indistinguishable between "no such
 * account" and "wrong code", so the response cannot be used to discover who has
 * an account.
 *
 * SECURITY POSTURE
 *   - Codes are 6 digits, so they expire fast (15 minutes) and are capped at a
 *     handful of wrong guesses before the code dies.
 *   - Issuing a new code invalidates every previous one for that address.
 *   - Comparison is constant-time, so response timing cannot leak the code.
 *   - The code is stored in plaintext because `code` is VARCHAR(6) and cannot
 *     hold a hash. That is inherited from the legacy schema; see the note on
 *     IsPlaintextStorage below.
 */
final class PasswordResetService
{
    /** Short on purpose: a 6-digit code has only a million possibilities. */
    private const LIFETIME_MINUTES = 15;

    /** Guesses allowed before the code is burned. */
    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly Mailer $mailer) {}

    /**
     * Whether reset codes are stored unhashed.
     *
     * True today because the legacy `code` column is VARCHAR(6) and a bcrypt
     * hash does not fit. Anyone with read access to this table can reset any
     * account, so it is worth a migration adding a `code_hash` column --
     * see README/scratch notes. Kept as a named constant so the day that
     * migration lands, the answer is in one obvious place.
     */
    public const IS_CODE_STORED_IN_PLAINTEXT = true;

    /**
     * Start a reset.
     *
     * Returns null when no account matches -- but callers MUST NOT surface
     * that difference to the user. It is returned only so tests and the log can
     * tell the two cases apart.
     */
    public function request(string $email): ?PasswordResetToken
    {
        $email = strtolower(trim($email));
        $account = $this->findAccount($email);

        if ($account === null) {
            // Same work, same cost as a real send, so a timing probe cannot
            // distinguish "unknown address" from "known address" either.
            usleep(250_000);

            return null;
        }

        $code = $this->generateCode();

        // The token identifies the ACCOUNT, not the tenancy. A renter's row
        // carries tenant_id and NOT owner_id: filling in owner_id as well
        // would make every renter reset overwrite the landlord's password
        // instead of their own, because owner_id is checked first on reset.
        $token = PasswordResetToken::query()->create([
            'owner_id' => $account['token_owner_id'],
            'tenant_id' => $account['token_tenant_id'],
            'email' => $email,
            'code' => $code,
            'expires_at' => CarbonImmutable::now()->addMinutes(self::LIFETIME_MINUTES),
            'used' => false,
            'attempts' => 0,
        ]);

        $this->mailer->trySend(
            MailTemplate::PasswordReset,
            // The log is filed under the OWNER, because email_logs is
            // owner-scoped -- but that is separate from who is being reset.
            (int) $account['log_owner_id'],
            $email,
            (string) $account['name'],
            [
                'name' => $account['name'],
                'code' => $code,
                'expires' => self::LIFETIME_MINUTES.' minutes',
            ],
        );

        return $token;
    }

    /**
     * The live (unexpired, unused) code for an address, or null.
     *
     * Returns the ROW rather than a boolean so the reset step can consume the
     * exact token that was verified, instead of re-selecting and risking a
     * newer code being used in place of the one just checked.
     */
    public function findUsableToken(string $email, string $code): ?PasswordResetToken
    {
        $token = PasswordResetToken::query()
            ->where('email', strtolower(trim($email)))
            ->where('used', false)
            ->orderByDesc('id')
            ->first();

        if ($token === null) {
            return null;
        }

        if ($token->attempts >= self::MAX_ATTEMPTS || $token->isExpired()) {
            return null;
        }

        // Constant-time: a short-circuiting === leaks the code byte by byte.
        if (! hash_equals($token->code, $code)) {
            $token->increment('attempts');

            return null;
        }

        return $token;
    }

    /**
     * Consume a verified token and set the new password.
     *
     * Both halves run in one transaction: burning the code and changing the
     * password together, so a crash cannot leave a live code next to an
     * unchanged password (or the reverse).
     */
    public function reset(string $email, string $code, string $password): bool
    {
        $token = $this->findUsableToken($email, $code);

        if ($token === null) {
            return false;
        }

        $hashed = Hash::make($password);
        $normalised = strtolower(trim($email));

        return DB::transaction(function () use ($token, $normalised, $hashed): bool {
            $updated = match (true) {
                $token->owner_id !== null => Owner::query()
                    ->withoutGlobalScopes()->whereKey($token->owner_id)
                    ->update(['password' => $hashed]) > 0,

                $token->tenant_id !== null => Renter::query()
                    ->withoutGlobalScopes()->whereKey($token->tenant_id)
                    ->update(['password' => $hashed]) > 0,

                default => $this->resetCaretaker($normalised, $hashed),
            };

            if (! $updated) {
                // The account was deleted between request and reset.
                return false;
            }

            $token->forceFill(['used' => true])->save();

            // Burn every other live code for this address, so an older email
            // in an inbox cannot be used after a successful reset.
            PasswordResetToken::query()
                ->where('email', $normalised)
                ->where('used', false)
                ->whereKeyNot($token->getKey())
                ->update(['used' => true]);

            return true;
        });
    }

    /**
     * The `password_reset_tokens` table has no caretaker_id column (a gap in
     * the legacy schema), so a caretaker is matched on the address itself.
     */
    private function resetCaretaker(string $email, string $hashed): bool
    {
        return Caretaker::query()
            ->withoutGlobalScopes()
            ->where('email', $email)
            ->update(['password' => $hashed]) > 0;
    }

    /**
     * Resolve an email to exactly one account, across all three user tables.
     *
     * A terminated renter is excluded: their tenancy is closed and their
     * personal data is scrubbed, so there is nothing to sign in to. This
     * matches the legacy rule.
     *
     * @return array{token_owner_id: int|null, token_tenant_id: int|null, log_owner_id: int, name: string}|null
     */
    private function findAccount(string $email): ?array
    {
        if ($owner = Owner::query()->withoutGlobalScopes()->where('email', $email)->first()) {
            return [
                'token_owner_id' => (int) $owner->getKey(),
                'token_tenant_id' => null,
                'log_owner_id' => (int) $owner->getKey(),
                'name' => (string) $owner->name,
            ];
        }

        if ($renter = Renter::query()->withoutGlobalScopes()
            ->where('email', $email)
            ->where('status', '!=', 'terminated')
            ->first()) {
            return [
                'token_owner_id' => null,
                'token_tenant_id' => (int) $renter->getKey(),
                // The delivery log is owner-scoped, so a renter's mail is filed
                // under their landlord. Who is being RESET is a separate thing.
                'log_owner_id' => (int) $renter->owner_id,
                'name' => (string) $renter->name,
            ];
        }

        if ($caretaker = Caretaker::query()->withoutGlobalScopes()->where('email', $email)->first()) {
            return [
                // No caretaker_id column exists, so the token carries neither id
                // and the reset matches on the address instead.
                'token_owner_id' => null,
                'token_tenant_id' => null,
                'log_owner_id' => (int) $caretaker->owner_id,
                'name' => (string) $caretaker->name,
            ];
        }

        return null;
    }

    private function generateCode(): string
    {
        // str_pad keeps it six characters, so '004821' rather than '4821'.
        return str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
    }
}

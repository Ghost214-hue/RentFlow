<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\Mailer;
use App\Mail\MailTemplate;
use App\Models\Caretaker;
use App\Models\PasswordSetupToken;
use App\Models\Renter;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Invites a renter or caretaker to choose their first password.
 *
 * Only owners and their staff have accounts created FOR them; nobody sets a
 * password until they follow an emailed link. That is the point: it means a
 * landlord never handles a tenant's credentials.
 *
 * Legacy stored the invitation token in PLAINTEXT, so a database leak handed an
 * attacker every outstanding link -- and those links set passwords. Here the
 * token goes out in the email and only its SHA-256 digest is stored.
 */
final class AccountSetupService
{
    /** Long enough that a real person gets a day or two, short enough to matter. */
    private const LIFETIME_HOURS = 48;

    public function __construct(private readonly Mailer $mailer) {}

    /**
     * Issue an invitation and email the link.
     *
     * Returns the link that was emailed, not just the stored row, because the
     * plaintext token exists ONLY in that link -- the table holds a digest. The
     * caller is owner-side code that has just authorised the invitation, so
     * handing back the link it sent adds no exposure.
     *
     * @param  'tenant'|'caretaker'  $userType
     */
    public function invite(string $userType, int $userId, int $ownerId): string
    {
        $account = $this->resolveAccount($userType, $userId);

        if ($account === null || ($account['email'] ?? '') === '') {
            return '';
        }

        // 32 random bytes is 256 bits; the digest fits VARCHAR(128) comfortably.
        $plain = bin2hex(random_bytes(32));

        /*
         * Issuing a NEW invitation retires any older live ones for this account.
         *
         * Re-sending a link means "this is the current one" -- otherwise a link
         * that leaked into an old inbox stays usable for the rest of the 48h
         * window, leaving two live credentials for one account. Password reset
         * already behaves this way, so the two stay consistent.
         */
        PasswordSetupToken::query()
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->update(['used_at' => CarbonImmutable::now()]);

        $token = PasswordSetupToken::query()->create([
            'user_type' => $userType,
            'user_id' => $userId,
            'owner_id' => $ownerId,
            // Only the digest is persisted. See the model docblock.
            'token' => PasswordSetupToken::hash($plain),
            'expires_at' => CarbonImmutable::now()->addHours(self::LIFETIME_HOURS),
            'used_at' => null,
            'created_at' => CarbonImmutable::now(),
        ]);

        $link = route('password.setup', ['token' => $plain]);

        $this->mailer->trySend(
            $userType === 'caretaker' ? MailTemplate::CaretakerWelcome : MailTemplate::TenantWelcome,
            $ownerId,
            (string) $account['email'],
            (string) $account['name'],
            [
                ($userType === 'caretaker' ? 'name' : 'tenant') => (string) $account['name'],
                'name' => (string) $account['name'],
                'property' => $account['property'] ?? 'your property',
                'house' => $account['house'] ?? 'your unit',
                'email' => (string) $account['email'],
                'setup_link' => $link,
            ],
        );

        return $link;
    }

    /** The live token for a plaintext link value, or null. */
    public function findUsable(string $plainToken): ?PasswordSetupToken
    {
        return PasswordSetupToken::findUsable(trim($plainToken));
    }

    /**
     * Consume an invitation and set the password.
     *
     * Burning the token and setting the password happen in one transaction, so
     * a crash can never leave a live invitation next to an unchanged password.
     */
    public function complete(string $plainToken, string $password): bool
    {
        $token = $this->findUsable($plainToken);

        if ($token === null) {
            return false;
        }

        $hashed = Hash::make($password);

        return DB::transaction(function () use ($token, $hashed): bool {
            $updated = match ($token->user_type) {
                'tenant' => Renter::query()
                    ->withoutGlobalScopes()
                    ->whereKey($token->user_id)
                    ->update(['password' => $hashed]) > 0,

                'caretaker' => Caretaker::query()
                    ->withoutGlobalScopes()
                    ->whereKey($token->user_id)
                    ->update(['password' => $hashed]) > 0,

                default => false,
            };

            if (! $updated) {
                // The account was deleted between invitation and use.
                return false;
            }

            $token->forceFill(['used_at' => CarbonImmutable::now()])->save();

            // Burn every other live invitation for this account, so an older
            // email in an inbox cannot be reused.
            PasswordSetupToken::query()
                ->where('user_type', $token->user_type)
                ->where('user_id', $token->user_id)
                ->whereNull('used_at')
                ->whereKeyNot($token->getKey())
                ->update(['used_at' => CarbonImmutable::now()]);

            return true;
        });
    }

    /**
     * Resend an invitation for an account, best-effort.
     *
     * Always returns null rather than reporting whether the account existed, so
     * this cannot be used to probe for valid accounts.
     */
    public function resend(string $userType, int $userId): ?PasswordSetupToken
    {
        $account = $this->resolveAccount($userType, $userId);

        if ($account === null) {
            return null;
        }

        try {
            return $this->invite($userType, $userId, (int) $account['owner_id']);
        } catch (\Throwable $e) {
            Log::warning('Setup invitation could not be sent.', [
                'user_type' => $userType,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Load the account an invitation refers to, WITH its tenancy details.
     *
     * The relations are owner-scoped, so the tenancy has to be resolved before
     * `house.property` is eager-loaded or the scope throws with no context. Two
     * queries instead of one, which is the price of that safety rule.
     *
     * @return array{email: string|null, name: string, owner_id: int, property?: string|null, house?: string|null}|null
     */
    private function resolveAccount(string $userType, int $userId): ?array
    {
        if ($userType === 'tenant') {
            $renter = Renter::query()->withoutGlobalScopes()->find($userId);

            if ($renter === null) {
                return null;
            }

            $previous = TenantContext::id();
            TenantContext::set((int) $renter->owner_id);

            try {
                $house = $renter->house()->with('property')->first();

                return [
                    'email' => $renter->email,
                    'name' => (string) $renter->name,
                    'owner_id' => (int) $renter->owner_id,
                    'property' => $house?->property?->name,
                    'house' => $house?->unit,
                ];
            } finally {
                TenantContext::set($previous);
            }
        }

        if ($userType === 'caretaker') {
            $caretaker = Caretaker::query()->withoutGlobalScopes()->find($userId);

            return $caretaker === null ? null : [
                'email' => $caretaker->email,
                'name' => (string) $caretaker->name,
                'owner_id' => (int) $caretaker->owner_id,
            ];
        }

        return null;
    }
}

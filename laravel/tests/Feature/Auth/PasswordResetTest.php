<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Password reset
|--------------------------------------------------------------------------
|
| This flow did not exist in the Laravel app at all: /forgot-password was
| linked from the login screen and 404'd. These tests pin the two halves of
| that bug (route present, and the whole request -> code -> reset round trip)
| plus the security properties that matter more than the plumbing:
|
|   1. The response is IDENTICAL for a known and an unknown address. The
|      legacy endpoint answered 404 "No account found", which is an
|      account-enumeration oracle.
|   2. Codes are single-use, expire, and die after a few wrong guesses.
|   3. Issuing a new code invalidates the old one.
|   4. A mail failure never blocks the reset.
|
*/

use App\Jobs\DeliverMail;
use App\Mail\RenderedMail;
use App\Models\Caretaker;
use App\Models\Owner;
use App\Models\PasswordResetToken;
use App\Models\Renter;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Mail::fake();

    TenantContext::set(null);
    $this->owner = Owner::factory()->withPassword('secret123')->create([
        'email' => 'owner@example.test',
        'name' => 'Grace Owner',
    ]);

    TenantContext::set((int) $this->owner->id);
    $this->renter = Renter::factory()->create([
        'email' => 'renter@example.test',
        'name' => 'Ada Renter',
        'password' => Hash::make('secret123'),
    ]);
    $this->caretaker = Caretaker::factory()->create([
        'email' => 'care@example.test',
        'name' => 'Cal Caretaker',
        'password' => Hash::make('secret123'),
    ]);
    TenantContext::clear();
});

// --- the reported bug --------------------------------------------------

it('has a forgot password route', function (): void {
    $this->get('/forgot-password')->assertOk();
});

it('has a reset password route', function (): void {
    $this->get('/reset-password')->assertOk();
});

// --- enumeration -------------------------------------------------------

/*
 * THE IMPORTANT ONE.
 *
 * Both requests must produce the same redirect and the same wording, or the
 * endpoint becomes a way to discover who has an account here.
 */
it('answers identically for a known and an unknown address', function (): void {
    $known = $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $unknown = $this->post('/forgot-password', ['email' => 'nobody@example.test']);

    expect($known->status())->toBe($unknown->status())
        ->and($known->getSession()->get('status'))
        ->toBe($unknown->getSession()->get('status'))
        ->and($unknown->isRedirect())->toBeTrue();
});

it('does not disclose whether an account exists in the wording', function (): void {
    $this->post('/forgot-password', ['email' => 'nobody@example.test'])
        ->assertSessionHasNoErrors();

    $message = (string) session('status');

    expect(strtolower($message))->not->toContain('not found')
        ->and(strtolower($message))->not->toContain('no account')
        ->and(strtolower($message))->not->toContain('unknown')
        ->and(strtolower($message))->toContain('if that address');
});

it('sends no email to an unknown address but still succeeds', function (): void {
    $this->post('/forgot-password', ['email' => 'nobody@example.test'])->assertRedirect();

    Mail::assertNothingSent();
});

it('sends exactly one reset email to a real account', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test'])->assertRedirect();

    Mail::assertSentCount(1);
    Mail::assertSent(function (RenderedMail $message): bool {
        return $message->hasTo('renter@example.test');
    });
});

// --- happy paths across all three account types ------------------------

it('resets an owner password', function (): void {
    $this->post('/forgot-password', ['email' => 'owner@example.test']);

    $code = PasswordResetToken::query()->where('email', 'owner@example.test')->value('code');

    $this->post('/reset-password', [
        'email' => 'owner@example.test',
        'code' => $code,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertRedirect('/login');

    expect(Hash::check('brand-new-secret', Owner::withoutGlobalScopes()->find($this->owner->id)->password))->toBeTrue();
});

it('resets a renter password', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $code = PasswordResetToken::query()->where('email', 'renter@example.test')->value('code');

    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => $code,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertRedirect('/login');

    expect(Hash::check('brand-new-secret', Renter::withoutGlobalScopes()->find($this->renter->id)->password))->toBeTrue();
});

/*
 * Caretakers are the awkward case: `password_reset_tokens` has no caretaker_id
 * column, so the account is resolved by address instead. Worth pinning.
 */
it('resets a caretaker password', function (): void {
    $this->post('/forgot-password', ['email' => 'care@example.test']);
    $code = PasswordResetToken::query()->where('email', 'care@example.test')->value('code');

    $this->post('/reset-password', [
        'email' => 'care@example.test',
        'code' => $code,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertRedirect('/login');

    expect(Hash::check('brand-new-secret', Caretaker::withoutGlobalScopes()->find($this->caretaker->id)->password))->toBeTrue();
});

it('refuses a reset for a terminated renter', function (): void {
    TenantContext::set((int) $this->owner->id);
    $gone = Renter::factory()->create([
        'email' => 'gone@example.test',
        'status' => 'terminated',
    ]);
    TenantContext::clear();

    $this->post('/forgot-password', ['email' => 'gone@example.test'])->assertRedirect();

    // Same answer as anyone unknown: a closed tenancy has nothing to sign in to.
    Mail::assertNothingSent();
});

// --- the code itself ---------------------------------------------------

it('issues a six digit code', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);

    $token = PasswordResetToken::query()->where('email', 'renter@example.test')->firstOrFail();

    expect($token->code)->toMatch('/^\d{6}$/');
});

it('expires the code after fifteen minutes', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);

    $token = PasswordResetToken::query()->where('email', 'renter@example.test')->firstOrFail();

    // Compared as instants rather than diffed: Carbon's diff* sign convention
    // has changed between versions, and a signed float is not the thing under
    // test here.
    expect($token->expires_at->getTimestamp())->toBeGreaterThan(now()->addMinutes(14)->getTimestamp())
        ->and($token->expires_at->getTimestamp())->toBeLessThanOrEqual(now()->addMinutes(16)->getTimestamp());
});

it('refuses an expired code', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $token = PasswordResetToken::query()->where('email', 'renter@example.test')->firstOrFail();

    $token->forceFill(['expires_at' => now()->subMinute()])->save();

    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => $token->code,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertSessionHasErrors('code');

    // The old password must still work: a failed reset changes nothing.
    expect(Hash::check('secret123', Renter::withoutGlobalScopes()->find($this->renter->id)->password))->toBeTrue();
});

it('refuses a wrong code', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);

    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => '000000',
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertSessionHasErrors('code');
});

it('burns a code after a handful of wrong guesses', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $token = PasswordResetToken::query()->where('email', 'renter@example.test')->firstOrFail();

    // Six digits is only a million possibilities, so guessing must stop.
    foreach (range(1, 5) as $ignored) {
        $this->post('/reset-password', [
            'email' => 'renter@example.test',
            'code' => '000000',
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ]);
    }

    expect($token->fresh()->attempts)->toBe(5);

    // Even the CORRECT code now fails, because the attempts cap killed it.
    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => $token->code,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertSessionHasErrors('code');
});

it('burns a code once it has been used', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $code = PasswordResetToken::query()->where('email', 'renter@example.test')->value('code');

    $payload = [
        'email' => 'renter@example.test',
        'code' => $code,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ];

    $this->post('/reset-password', $payload)->assertRedirect('/login');
    $this->post('/reset-password', $payload)->assertSessionHasErrors('code');
});

it('invalidates an earlier code when a new one is issued', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $old = PasswordResetToken::query()->where('email', 'renter@example.test')->value('code');

    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $new = PasswordResetToken::query()->where('email', 'renter@example.test')->orderByDesc('id')->value('code');

    // The older email sitting in an inbox must stop working.
    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => $old,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertSessionHasErrors('code');

    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => $new,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertRedirect('/login');
});

// --- abuse -------------------------------------------------------------

it('throttles repeated reset requests for one address', function (): void {
    foreach (range(1, 5) as $ignored) {
        $this->post('/forgot-password', ['email' => 'renter@example.test']);
    }

    $this->post('/forgot-password', ['email' => 'renter@example.test'])
        ->assertSessionHasErrors('email');
});

it('rejects a malformed address before any lookup', function (): void {
    $this->post('/forgot-password', ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');

    Mail::assertNothingSent();
});

it('requires a matching confirmation password', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $code = PasswordResetToken::query()->where('email', 'renter@example.test')->value('code');

    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => $code,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'something-else',
    ])->assertSessionHasErrors('password');
});

it('refuses a short password', function (): void {
    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $code = PasswordResetToken::query()->where('email', 'renter@example.test')->value('code');

    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => $code,
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');
});

/*
 * A code is only useful if it reaches the inbox, so delivery must never be on
 * the critical path of the reset itself. Faking the queue models a worker that
 * is down entirely: the job never runs, and the password must still change.
 */
it('still resets the password when the mail queue never runs', function (): void {
    Queue::fake();

    $this->post('/forgot-password', ['email' => 'renter@example.test']);
    $code = PasswordResetToken::query()->where('email', 'renter@example.test')->value('code');

    $this->post('/reset-password', [
        'email' => 'renter@example.test',
        'code' => $code,
        'password' => 'brand-new-secret',
        'password_confirmation' => 'brand-new-secret',
    ])->assertRedirect('/login');

    expect(Hash::check('brand-new-secret', Renter::withoutGlobalScopes()->find($this->renter->id)->password))->toBeTrue();

    Queue::assertPushed(DeliverMail::class);
});

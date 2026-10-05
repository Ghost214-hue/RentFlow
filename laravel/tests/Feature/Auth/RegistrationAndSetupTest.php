<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Registration and first-password setup
|--------------------------------------------------------------------------
|
| Both flows were missing entirely: /signup and /setup-password were linked or
| referenced but had no routes, so the login screen carried two dead links.
|
| The setup flow is deliberately STRICTER than the legacy one. Legacy generated a
| 32-byte invitation token and stored it in PLAINTEXT, so a database leak handed
| an attacker every outstanding link -- and those links set passwords. Here the
| plaintext goes out in the email and only a SHA-256 digest is stored, which
| fits the existing VARCHAR(128) column without a migration.
|
*/

use App\Mail\RenderedMail;
use App\Models\Caretaker;
use App\Models\House;
use App\Models\Owner;
use App\Models\PasswordSetupToken;
use App\Models\Property;
use App\Models\Renter;
use App\Services\AccountSetupService;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
    TenantContext::set(null);
    $this->owner = Owner::factory()->withPassword('secret123')->create([
        'email' => 'landlord@example.test',
    ]);
    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create();
    $this->house = House::factory()->forProperty($this->property)->occupied()
        ->create(['unit' => 'A1']);
    $this->renter = Renter::factory()->inHouse($this->house)->create([
        'email' => 'renter@example.test',
        'password' => null,
    ]);
    TenantContext::clear();
});

// --- the dead links ---------------------------------------------------

it('has a registration route', function (): void {
    $this->get('/register')->assertOk();
});

it('has a setup password route', function (): void {
    $this->get('/setup-password')->assertOk();
});

// --- registration -----------------------------------------------------

it('registers an owner', function (): void {
    $this->post('/register', [
        'name' => 'Grace Wanjiru Mbuthia',
        'email' => 'new@example.test',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirect('/login');

    $created = Owner::query()->where('email', 'new@example.test')->firstOrFail();

    expect(Hash::check('secret123', $created->password))->toBeTrue()
        // Initials from the name, as the legacy endpoint did.
        ->and($created->avatar)->toBe('GW');
});

it('does not sign the new owner in automatically', function (): void {
    // Legacy returned a JWT immediately, creating a session nobody asked for.
    $this->post('/register', [
        'name' => 'Grace', 'email' => 'new@example.test',
        'password' => 'secret123', 'password_confirmation' => 'secret123',
    ])->assertRedirect('/login');

    $this->assertGuest();
});

it('refuses an email already used by an owner', function (): void {
    $this->post('/register', [
        'name' => 'Someone', 'email' => 'landlord@example.test',
        'password' => 'secret123', 'password_confirmation' => 'secret123',
    ])->assertSessionHasErrors('email');
});

/*
 * The legacy check only looked at `owners`. That matters here because password
 * reset resolves an account by address -- if a renter's address could also be
 * registered as an owner, the two would collide.
 */
it('refuses an email already used by a renter', function (): void {
    $this->post('/register', [
        'name' => 'Impostor', 'email' => 'renter@example.test',
        'password' => 'secret123', 'password_confirmation' => 'secret123',
    ])->assertSessionHasErrors('email');
});

it('refuses an email already used by a caretaker', function (): void {
    TenantContext::set((int) $this->owner->id);
    $caretaker = Caretaker::factory()->create(['email' => 'carer@example.test']);
    TenantContext::clear();

    $this->post('/register', [
        'name' => 'Impostor', 'email' => 'carer@example.test',
        'password' => 'secret123', 'password_confirmation' => 'secret123',
    ])->assertSessionHasErrors('email');
});

it('requires a stronger password than the legacy six characters', function (): void {
    $this->post('/register', [
        'name' => 'Grace', 'email' => 'new@example.test',
        'password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh',
    ])->assertSessionHasErrors('password');
});

it('requires a matching confirmation', function (): void {
    $this->post('/register', [
        'name' => 'Grace', 'email' => 'new@example.test',
        'password' => 'secret123', 'password_confirmation' => 'different1',
    ])->assertSessionHasErrors('password');
});

// --- setup invitations ------------------------------------------------

it('stores only a hash of the invitation token', function (): void {
    $link = app(AccountSetupService::class)->invite('tenant', (int) $this->renter->id, (int) $this->owner->id);

    parse_str((string) parse_url($link, PHP_URL_QUERY), $query);
    $plain = (string) ($query['token'] ?? '');
    $stored = PasswordSetupToken::query()->latest('id')->value('token');

    // 64 hex chars: a digest, never the value that was emailed.
    expect($plain)->toHaveLength(64)
        ->and($stored)->toMatch('/^[a-f0-9]{64}$/')
        ->and($stored)->not->toBe($plain)
        ->and($stored)->toBe(PasswordSetupToken::hash($plain));
});

it('emails the renter a setup link', function (): void {
    app(AccountSetupService::class)->invite('tenant', (int) $this->renter->id, (int) $this->owner->id);

    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('renter@example.test');
    });
});

it('sets a renter password from the emailed link', function (): void {
    $plain = issuePlainToken();

    $this->post('/setup-password', [
        'token' => $plain,
        'password' => 'brand-new-secret9',
        'password_confirmation' => 'brand-new-secret9',
    ])->assertRedirect('/login');

    expect(Hash::check('brand-new-secret9', Renter::withoutGlobalScopes()->find($this->renter->id)->password))
        ->toBeTrue();
});

it('sets a caretaker password from the emailed link', function (): void {
    TenantContext::set((int) $this->owner->id);
    $caretaker = Caretaker::factory()->create(['email' => 'carer@example.test']);
    TenantContext::clear();

    $plain = issuePlainToken('caretaker', (int) $caretaker->id);

    $this->post('/setup-password', [
        'token' => $plain,
        'password' => 'brand-new-secret9',
        'password_confirmation' => 'brand-new-secret9',
    ])->assertRedirect('/login');

    expect(Hash::check('brand-new-secret9', Caretaker::withoutGlobalScopes()->find($caretaker->id)->password))
        ->toBeTrue();
});

it('refuses a token that was never issued', function (): void {
    $this->post('/setup-password', [
        'token' => bin2hex(random_bytes(32)),
        'password' => 'brand-new-secret9',
        'password_confirmation' => 'brand-new-secret9',
    ])->assertSessionHasErrors('token');
});

it('refuses a used invitation', function (): void {
    $plain = issuePlainToken();

    $payload = [
        'token' => $plain,
        'password' => 'brand-new-secret9',
        'password_confirmation' => 'brand-new-secret9',
    ];

    $this->post('/setup-password', $payload)->assertRedirect('/login');
    // An invitation is the credential; it must not work twice.
    $this->post('/setup-password', $payload)->assertSessionHasErrors('token');
});

it('refuses an expired invitation', function (): void {
    $plain = issuePlainToken();

    PasswordSetupToken::query()
        ->where('user_id', $this->renter->id)
        ->update(['expires_at' => now()->subHour()]);

    $this->post('/setup-password', [
        'token' => $plain,
        'password' => 'brand-new-secret9',
        'password_confirmation' => 'brand-new-secret9',
    ])->assertSessionHasErrors('token');
});

it('supersedes an older invitation when a new one is issued', function (): void {
    $first = issuePlainToken();
    $second = issuePlainToken();

    // The older email sitting in an inbox must stop working.
    $this->post('/setup-password', [
        'token' => $first,
        'password' => 'brand-new-secret9',
        'password_confirmation' => 'brand-new-secret9',
    ])->assertSessionHasErrors('token');

    $this->post('/setup-password', [
        'token' => $second,
        'password' => 'brand-new-secret9',
        'password_confirmation' => 'brand-new-secret9',
    ])->assertRedirect('/login');
});

/*
 * Used, expired and forged must be indistinguishable, or the page becomes a way
 * to learn who has an account.
 */
it('shows an invalid link without saying which kind it was', function (): void {
    foreach ([bin2hex(random_bytes(32)), 'x', str_repeat('a', 64)] as $token) {
        $props = $this->get('/setup-password?token='.$token)->viewData('page')['props'];

        expect($props['valid'])->toBeFalse()
            ->and($props['name'])->toBeNull();
    }
});

it('shows a valid invitation as valid', function (): void {
    $plain = issuePlainToken();

    $props = $this->get('/setup-password?token='.$plain)->viewData('page')['props'];

    expect($props['valid'])->toBeTrue()
        ->and($props['name'])->toBe((string) $this->renter->name);
});

it('answers a resend the same way whether or not the account exists', function (): void {
    $known = $this->post('/setup-password/resend', ['user_type' => 'tenant', 'user_id' => $this->renter->id]);
    $unknown = $this->post('/setup-password/resend', ['user_type' => 'tenant', 'user_id' => 999999]);

    expect($known->getSession()->get('status'))->toBe($unknown->getSession()->get('status'));
});

it('sends an invitation when a renter is onboarded', function (): void {
    TenantContext::set((int) $this->owner->id);
    $vacant = House::factory()->forProperty($this->property)->create(['unit' => 'A2']);
    TenantContext::clear();

    $this->actingAs($this->owner)->post('/renters', [
        'property_id' => $this->property->id,
        'house_id' => $vacant->id,
        'name' => 'Bea Newcomer',
        'email' => 'bea@example.test',
        'phone' => '0700000001',
        'id_type' => 'National ID',
        'lease_start' => now()->toDateString(),
        'rent' => '40000.00',
        'deposit' => '40000.00',
        'next_of_kin_name' => 'Kin',
        'next_of_kin_phone' => '0700000002',
    ])->assertRedirect();

    Mail::assertSent(function (RenderedMail $m): bool {
        return $m->hasTo('bea@example.test');
    });
});

/** Issue an invitation and return the token from the link that was emailed. */
function issuePlainToken(string $type = 'tenant', ?int $userId = null): string
{
    $link = app(AccountSetupService::class)->invite(
        $type,
        $userId ?? test()->renter->id,
        (int) test()->owner->id,
    );

    // Read the token back out of the link, exactly as the recipient would from
    // their inbox. Going through the service means the test exercises the real
    // path -- including invalidating any earlier invitation -- rather than
    // re-implementing it here.
    parse_str((string) parse_url($link, PHP_URL_QUERY), $query);

    return (string) ($query['token'] ?? '');
}

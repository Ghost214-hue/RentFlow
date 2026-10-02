<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Session authentication
|--------------------------------------------------------------------------
|
| Covers the three actor types. The renter cases exist because renter login was
| completely non-functional: Renter extended App\Models\Model instead of
| Authenticatable, so SessionGuard::login() fataled with a TypeError. Owner-only
| testing never exercised it.
|
*/

use App\Models\Caretaker;
use App\Models\House;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->owner = Owner::factory()->withPassword('secret123')->create();

    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create();
    $this->house = House::factory()->forProperty($this->property)->create();

    $this->renter = Renter::factory()->inHouse($this->house)->withPassword('secret123')->create();
    $this->caretaker = Caretaker::factory()
        ->assignedTo([(int) $this->property->id])
        ->create(['password' => 'secret123']);

    TenantContext::clear();
});

/*
 * REGRESSION: ResolveTenantContext called $model::withoutOwnerScope() for every
 * actor type. Owner is the tenancy ROOT -- it has no owner_id column and does
 * not use BelongsToOwner, so it has no such method. Any request carrying a
 * stale owner session died with
 * "Call to undefined method App\Models\Owner::withoutOwnerScope()".
 */
it('rehydrates an owner session without calling a scope it does not have', function (): void {
    // Log in as an owner, exactly as a browser would.
    $this->post('/login', ['email' => $this->owner->email, 'password' => 'secret123']);

    // The dashboard must render. This is the request that previously 500ed.
    $this->get('/')->assertOk();
    $this->assertAuthenticatedAs($this->owner);
});

it('rehydrates a renter session', function (): void {
    $this->post('/login', ['email' => $this->renter->email, 'password' => 'secret123']);

    $this->get('/renter/dashboard')->assertOk();
    $this->assertAuthenticatedAs($this->renter);
});

it('rehydrates a caretaker session', function (): void {
    $this->post('/login', ['email' => $this->caretaker->email, 'password' => 'secret123']);

    $this->get('/')->assertOk();
    $this->assertAuthenticatedAs($this->caretaker);
});

/*
 * A session can outlive its row: the account is deleted, or the database is
 * replaced underneath it. That must read as "not signed in" and send the user to
 * the login page, never as a 500.
 */
it('treats a session pointing at a deleted owner as signed out', function (): void {
    $deletedId = (int) $this->owner->id;
    DB::table('owners')->where('id', $deletedId)->delete();

    $this->withSession(['rf_actor_type' => 'owner', 'rf_actor_id' => $deletedId])
        ->get('/')
        ->assertRedirect(route('login'));
});

it('treats a session pointing at a deleted renter as signed out', function (): void {
    $deletedId = (int) $this->renter->id;
    DB::table('tenants')->where('id', $deletedId)->delete();

    $this->withSession(['rf_actor_type' => 'renter', 'rf_actor_id' => $deletedId])
        ->get('/')
        ->assertRedirect(route('login'));
});

it('survives a session carrying a nonsense actor type', function (): void {
    $this->withSession(['rf_actor_type' => 'martian', 'rf_actor_id' => 1])
        ->get('/')
        ->assertRedirect(route('login'));
});

it('logs an owner in', function (): void {
    $this->post('/login', [
        'email' => $this->owner->email,
        'password' => 'secret123',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($this->owner);
});

/*
 * REGRESSION: no renter could ever sign in. Renter did not implement
 * Authenticatable, so login() threw a TypeError and every renter page 500ed.
 */
it('logs a renter in', function (): void {
    $this->post('/login', [
        'email' => $this->renter->email,
        'password' => 'secret123',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($this->renter);
});

it('logs a caretaker in', function (): void {
    $this->post('/login', [
        'email' => $this->caretaker->email,
        'password' => 'secret123',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($this->caretaker);
});

it('rejects a wrong password', function (): void {
    $this->post('/login', [
        'email' => $this->owner->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

/*
 * A terminated renter must not be able to sign in. Authenticator::findByEmail
 * filters on status='active' for renters only.
 */
it('refuses a terminated renter', function (): void {
    $this->renter->update(['status' => 'pending_termination']);

    $this->post('/login', [
        'email' => $this->renter->email,
        'password' => 'secret123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('stores the actor table in the session so it can be rehydrated', function (): void {
    /*
     * The session guard's provider is Owner, so a Caretaker or Renter session
     * cannot be reloaded by the guard on the next request. The recorded type is
     * what lets the tenancy middleware rebuild the right model.
     */
    $this->post('/login', ['email' => $this->renter->email, 'password' => 'secret123']);

    expect(session('rf_actor_type'))->toBe('renter')
        ->and((int) session('rf_actor_id'))->toBe((int) $this->renter->id);
});

it('logs out', function (): void {
    $this->actingAs($this->owner)->post('/logout')->assertRedirect();

    $this->assertGuest();
});
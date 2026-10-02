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
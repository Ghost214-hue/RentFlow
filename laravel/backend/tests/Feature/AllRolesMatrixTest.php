<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Route x role matrix
|--------------------------------------------------------------------------
|
| Walks EVERY authenticated GET route for EVERY actor type and asserts the
| response is a real one: 200, a redirect to a landing page, or 403. A 500 or an
| unexpected 404 is a failure.
|
| This is the sweep that catches a controller written for one role being
| reached by another -- the caretaker dashboard leak was invisible to
| single-role tests because nothing asserted what a caretaker SEES.
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

/** Every authenticated GET route, with the landing pages each role hits. */
function routes(): array
{
    return [
        // Staff pages
        '/', '/properties', '/renters', '/houses', '/payments', '/bills',
        '/caretakers', '/complaints', '/maintenance', '/documents',
        '/reports', '/email-logs', '/caretaker/dashboard',

        // Renter pages
        '/renter/dashboard', '/renter/profile',
    ];
}

it('serves every route to an owner without error', function (string $path) {
    $response = $this->actingAs($this->owner)->get($path);

    /*
     * 200, or 302 for a landing redirect. 403 is also correct here: an owner
     * has no caretaker dashboard and no renter dashboard, and both refuse them
     * by design (asserted separately below). What must never happen is a 500.
     */
    expect($response->status())->toBeIn([200, 302, 403]);

    if ($response->status() === 200) {
        // The Inertia shell must actually carry a page component.
        expect($response->viewData('page')['component'])->not->toBeEmpty();
    }
})->with(routes());

it('serves every route to a caretaker without error', function (string $path) {
    $response = $this->actingAs($this->caretaker)->get($path);

    // A caretaker may be redirected to their own dashboard, or refused a page
    // that is owner-only -- but never a 500.
    expect($response->status())->toBeIn([200, 302, 403]);
})->with(routes());

it('serves every route to a renter without error', function (string $path) {
    $response = $this->actingAs($this->renter)->get($path);

    // A renter is redirected away from staff pages or refused them, and sees
    // their own pages -- but never a 500.
    expect($response->status())->toBeIn([200, 302, 403]);
})->with(routes());

/*
 * The specific leaks this sweep exists to prevent.
 */
it('does not show an owner portfolio figures to a caretaker', function (): void {
    $body = $this->actingAs($this->caretaker)
        ->get('/caretaker/dashboard')
        ->viewData('page')['props'];

    // Only their own property is listed.
    expect($body['stats']['properties'])->toBe(1);
});

it('does not let a renter reach the reports', function (): void {
    $this->actingAs($this->renter)->get('/reports')->assertForbidden();
});

it('does not let a renter reach the caretaker dashboard', function (): void {
    $this->actingAs($this->renter)->get('/caretaker/dashboard')->assertForbidden();
});

it('does not let a renter reach the owner caretaker list', function (): void {
    $this->actingAs($this->renter)->get('/caretakers')->assertForbidden();
});

it('does not let an owner reach the renter pages', function (): void {
    $this->actingAs($this->owner)->get('/renter/dashboard')->assertForbidden();
    $this->actingAs($this->owner)->get('/renter/profile')->assertForbidden();
});

it('redirects each role to its own landing page', function (): void {
    $this->actingAs($this->owner)->get('/')->assertOk();

    $this->actingAs($this->caretaker)->get('/')
        ->assertRedirect(route('caretaker.dashboard'));

    $this->actingAs($this->renter)->get('/')
        ->assertRedirect(route('renter.dashboard'));
});

it('redirects a guest on every route to login', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with(routes());
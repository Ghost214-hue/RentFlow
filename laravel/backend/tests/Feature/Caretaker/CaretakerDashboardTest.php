<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Caretaker dashboard
|--------------------------------------------------------------------------
|
| A caretaker manages an ASSIGNED SUBSET of the owner's portfolio. The owner
| dashboard aggregates every property, bill and payment in the tenant scope, so
| pointing a caretaker at it would hand them the whole portfolio's money.
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
    $this->owner = Owner::factory()->create();

    TenantContext::set((int) $this->owner->id);
    $this->mine = Property::factory()->create(['name' => 'Mine']);
    $this->theirs = Property::factory()->create(['name' => 'Theirs']);
    $this->mineHouse = House::factory()->forProperty($this->mine)->create();
    $this->theirHouse = House::factory()->forProperty($this->theirs)->create();
    $this->renter = Renter::factory()->inHouse($this->mineHouse)->create();
    $this->caretaker = Caretaker::factory()->assignedTo([(int) $this->mine->id])->create();
    TenantContext::clear();
});

/*
 * REGRESSION: DashboardController authorised viewAny(Property), which a
 * caretaker passes, then aggregated over the WHOLE tenant scope. A caretaker
 * landing there saw every property and every payment, not just their own.
 */
it('scopes the caretaker dashboard to assigned properties only', function (): void {
    $response = $this->actingAs($this->caretaker)->get('/caretaker/dashboard');
    $response->assertOk();

    $props = $response->viewData('page')['props'];

    // One assigned property, so exactly one is counted -- not two.
    expect($props['stats']['properties'])->toBe(1)
        ->and($props['stats']['units'])->toBe(1);

    expect($props['properties'])->toHaveCount(1)
        ->and($props['properties'][0]['name'])->toBe('Mine');
});

it('does not leak another property name to a caretaker', function (): void {
    $body = $this->actingAs($this->caretaker)
        ->get('/caretaker/dashboard')
        ->viewData('page')['props'];

    $names = array_column($body['properties'], 'name');

    expect($names)->toContain('Mine')
        ->and($names)->not->toContain('Theirs');
});

it('sends a caretaker to their own dashboard from the root', function (): void {
    $this->actingAs($this->caretaker)->get('/')->assertRedirect(route('caretaker.dashboard'));
});

it('keeps an owner on the owner dashboard', function (): void {
    $this->actingAs($this->owner)->get('/')->assertOk();
});

/*
 * A renter has no business on the staff dashboard at all.
 */
it('sends a renter to their own dashboard from the root', function (): void {
    $this->actingAs($this->renter)->get('/')->assertRedirect(route('renter.dashboard'));
});

it('refuses the caretaker dashboard to a renter', function (): void {
    $this->actingAs($this->renter)->get('/caretaker/dashboard')->assertForbidden();
});

it('shows a caretaker with no assignments an empty dashboard', function (): void {
    TenantContext::set((int) $this->owner->id);
    $unassigned = Caretaker::factory()->assignedTo([])->create();
    TenantContext::clear();

    $props = $this->actingAs($unassigned)
        ->get('/caretaker/dashboard')
        ->viewData('page')['props'];

    expect($props['stats']['properties'])->toBe(0)
        ->and($props['properties'])->toBe([]);
});
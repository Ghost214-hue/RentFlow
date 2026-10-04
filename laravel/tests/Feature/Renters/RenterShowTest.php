<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Renter detail prop shape
|--------------------------------------------------------------------------
|
| The page renders renter.name directly. If the server hands Inertia an
| unresolved JsonResource, the prop arrives as {"data": {...}}, every field
| reads as undefined, and the page dies on `undefined.split` while rendering.
|
| These tests assert the SHAPE the browser receives, not merely that the
| route returned 200 -- a 200 with a malformed prop is exactly the failure.
|
*/

use App\Models\House;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Support\TenantContext;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->owner = Owner::factory()->create();

    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create();
    $this->house = House::factory()->forProperty($this->property)->create();
    $this->renter = Renter::factory()->inHouse($this->house)->create([
        'name' => 'Adrian Mbai',
        'email' => 'adrian@example.test',
    ]);
    TenantContext::clear();
});

/**
 * Props as the browser receives them.
 *
 * @return array<string, mixed>
 */
function renterProps(int $renterId): array
{
    $response = test()->actingAs(test()->owner)
        ->get("/renters/{$renterId}")
        ->assertOk();

    return $response->viewData('page')['props'];
}

/*
 * REGRESSION: the controller passed `new RenterResource($renter)` straight
 * through, so Inertia serialised the resource WRAPPER and the prop became
 * {"data": {"name": ...}}. The page read renter.name as undefined and threw
 * "Cannot read properties of undefined (reading 'split')".
 */
it('sends the renter as a flat object, not a resource wrapper', function (): void {
    $props = renterProps((int) $this->renter->id);

    expect($props['renter'])->toBeArray()
        ->and($props['renter'])->not->toHaveKey('data')
        ->and($props['renter'])->toHaveKey('name')
        ->and($props['renter']['name'])->toBe('Adrian Mbai');
});

it('gives every field the page reads a defined key', function (): void {
    $renter = renterProps((int) $this->renter->id)['renter'];

    // The detail page renders these directly; a missing one blanks it.
    foreach ([
        'name', 'email', 'phone', 'status', 'profile_picture',
        'id_type', 'id_number', 'property_name', 'house_unit',
        'rent', 'deposit', 'balance', 'credit',
        'lease_start', 'lease_end',
        'next_of_kin_name', 'next_of_kin_phone', 'next_of_kin_email',
    ] as $field) {
        expect($renter)->toHaveKey($field);
    }
});

it('emits money as decimal strings, never numbers', function (): void {
    $renter = renterProps((int) $this->renter->id)['renter'];

    foreach (['rent', 'deposit', 'balance', 'credit'] as $field) {
        expect($renter[$field])->toBeString()
            ->and($renter[$field])->toMatch('/^\d+\.\d{2}$/');
    }
});

it('supplies the history sections the page renders', function (): void {
    $history = renterProps((int) $this->renter->id)['history'];

    expect($history)->toBeArray()
        ->and($history)->toHaveKeys(['bills', 'payments', 'complaints', 'maintenance', 'unpaid_total'])
        ->and($history['bills'])->toBeArray()
        ->and($history['unpaid_total'])->toBeString();
});

it('serialises the page payload with no unresolved resource wrapper', function (): void {
    $body = $this->actingAs($this->owner)->get("/renters/{$this->renter->id}")->getContent();

    // A resource wrapper shows up as an escaped {"data":{ inside the payload.
    expect($body)->toContain('Adrian Mbai')
        ->and($body)->not->toContain('&quot;data&quot;:{&quot;id&quot;');
});
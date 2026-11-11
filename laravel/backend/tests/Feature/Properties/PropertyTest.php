<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Properties
|--------------------------------------------------------------------------
|
| The rule that matters most: unit counts shown to the user are DERIVED from
| the house rows. properties.units and properties.occupied are caches that drift
| whenever a unit is added or vacated outside the property form.
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
    $this->otherOwner = Owner::factory()->create();

    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create(['name' => 'Alpha Tower']);

    // The cached columns deliberately disagree with the houses, so a test that
    // trusts them fails.
    $this->property->update(['units' => 99, 'occupied' => 99]);

    $this->houseA = House::factory()->forProperty($this->property)->occupied()->create();
    $this->houseB = House::factory()->forProperty($this->property)->create();
    TenantContext::clear();
});

it('lists properties for an owner', function (): void {
    $this->actingAs($this->owner)->get('/properties')->assertOk();
});

it('creates a property', function (): void {
    $this->actingAs($this->owner)
        ->post('/properties', [
            'name' => 'Beta Heights',
            'address' => 'Nairobi',
            'type' => 'Apartment Block',
            'rent' => '45000.00',
            'payment_method_type' => 'mobile_money',
            'mobile_money_number' => '0712345678',
        ])
        ->assertRedirect();

    $created = Property::query()->where('name', 'Beta Heights')->firstOrFail();

    expect((string) $created->rent)->toBe('45000.00')
        ->and((int) $created->owner_id)->toBe((int) $this->owner->id);
});

it('requires a name and an address', function (): void {
    // address is NOT NULL in the live schema.
    $this->actingAs($this->owner)
        ->post('/properties', ['name' => '', 'address' => ''])
        ->assertSessionHasErrors(['name', 'address']);
});

it('rejects a non-decimal rent', function (): void {
    $this->actingAs($this->owner)
        ->post('/properties', [
            'name' => 'Bad Rent',
            'address' => 'Nairobi',
            'rent' => 'free',
        ])
        ->assertSessionHasErrors('rent');
});

it('derives unit counts from houses, not the cached columns', function (): void {
    $response = $this->actingAs($this->owner)->get('/properties');

    $response->assertOk();

    $properties = $response->viewData('page')['props']['properties']['data'];
    $alpha = collect($properties)->firstWhere('name', 'Alpha Tower');

    // Two houses, one occupied -- despite units_recorded/occupied_recorded
    // being 99 in the database.
    expect($alpha['units'])->toBe(2)
        ->and($alpha['occupied'])->toBe(1)
        ->and($alpha['units_recorded'])->toBe(99);
});

it('refuses to delete a property that still has units', function (): void {
    $this->actingAs($this->owner)
        ->delete("/properties/{$this->property->id}")
        ->assertSessionHas('error');

    expect(Property::query()->find($this->property->id))->not->toBeNull();
});

it('deletes a property with no units', function (): void {
    // Seeding needs the tenancy context that the creating hook requires.
    TenantContext::set((int) $this->owner->id);
    $empty = Property::factory()->create(['name' => 'Empty Lot']);
    TenantContext::clear();

    $this->actingAs($this->owner)->delete("/properties/{$empty->id}")->assertRedirect();

    expect(Property::query()->find($empty->id))->toBeNull();
});

it('refuses the property list to a renter', function (): void {
    TenantContext::set((int) $this->owner->id);
    $house = House::factory()->forProperty($this->property)->create();
    $renter = Renter::factory()->inHouse($house)->create();
    TenantContext::clear();

    $this->actingAs($renter)->get('/properties')->assertForbidden();
});

it('shows a caretaker only their assigned properties', function (): void {
    TenantContext::set((int) $this->owner->id);
    $mine = Property::factory()->create(['name' => 'Mine']);
    $theirs = Property::factory()->create(['name' => 'Theirs']);
    $caretaker = Caretaker::factory()->assignedTo([(int) $mine->id])->create();
    TenantContext::clear();

    $response = $this->actingAs($caretaker)->get('/properties');
    $response->assertOk();

    $names = array_column($response->viewData('page')['props']['properties']['data'], 'name');

    expect($names)->toContain('Mine')
        ->and($names)->not->toContain('Theirs');
});

it('cannot delete another owner property', function (): void {
    TenantContext::set((int) $this->otherOwner->id);
    $theirProperty = Property::factory()->create();
    TenantContext::clear();

    $this->actingAs($this->owner)
        ->delete("/properties/{$theirProperty->id}")
        ->assertNotFound();
});
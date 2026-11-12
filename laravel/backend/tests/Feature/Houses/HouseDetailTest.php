<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| House (unit) detail
|--------------------------------------------------------------------------
|
| Ported from frontend/pages/house-details.php. The outstanding figure is
| derived from unpaid bills, NOT from the cached house or tenant columns.
|
*/

use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Support\TenantContext;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->owner = Owner::factory()->create();

    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create(['name' => 'Alpha Tower']);
    $this->house = House::factory()->forProperty($this->property)
        ->occupied()->create(['unit' => 'A1', 'rent' => '45000.00']);
    $this->renter = Renter::factory()->inHouse($this->house)->create();
    TenantContext::clear();
});

it('shows a unit to its owner', function (): void {
    $this->actingAs($this->owner)->get("/houses/{$this->house->id}")->assertOk();
});

it('shows the current tenant on the unit', function (): void {
    $props = $this->actingAs($this->owner)
        ->get("/houses/{$this->house->id}")
        ->viewData('page')['props'];

    expect($props['currentTenant'])->not->toBeNull()
        ->and($props['currentTenant']['name'])->toBe($this->renter->name);
});

it('reports a vacant unit with no tenant', function (): void {
    TenantContext::set((int) $this->owner->id);
    $vacant = House::factory()->forProperty($this->property)->create(['unit' => 'B2']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)
        ->get("/houses/{$vacant->id}")
        ->viewData('page')['props'];

    expect($props['currentTenant'])->toBeNull()
        ->and($props['house']['status'])->toBe('vacant');
});

/*
 * The outstanding figure must come from the bills. The cached tenant.balance
 * is deliberately set wrong here so a test that trusted it would fail.
 */
it('sums unpaid bills into the outstanding figure', function (): void {
    TenantContext::set((int) $this->owner->id);
    $this->renter->update(['balance' => '0.00']);

    foreach (['2026-01', '2026-02'] as $month) {
        Bill::factory()->create([
            'house_id' => $this->house->id,
            'tenant_id' => $this->renter->id,
            'month' => $month,
            'total' => '45000.00',
            'status' => 'pending',
        ]);
    }
    TenantContext::clear();

    $props = $this->actingAs($this->owner)
        ->get("/houses/{$this->house->id}")
        ->viewData('page')['props'];

    expect($props['financials']['unpaid_total'])->toBe('90000.00')
        ->and($props['bills'])->toHaveCount(2);
});

it('excludes paid bills from the outstanding figure', function (): void {
    TenantContext::set((int) $this->owner->id);
    Bill::factory()->create([
        'house_id' => $this->house->id, 'tenant_id' => $this->renter->id,
        'month' => '2026-01', 'total' => '45000.00', 'status' => 'paid',
    ]);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)
        ->get("/houses/{$this->house->id}")
        ->viewData('page')['props'];

    expect($props['financials']['unpaid_total'])->toBe('0.00');
});

it('lists the unit maintenance requests', function (): void {
    TenantContext::set((int) $this->owner->id);
    MaintenanceRecord::factory()->forTenant((int) $this->renter->id)->create([
        'house_id' => $this->house->id,
        'title' => 'Leaking tap',
    ]);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)
        ->get("/houses/{$this->house->id}")
        ->viewData('page')['props'];

    expect($props['maintenance'])->toHaveCount(1)
        ->and($props['maintenance'][0]['title'])->toBe('Leaking tap');
});

/*
 * HousePolicy already lets a renter view the unit they occupy, so 200 is the
 * correct answer here. What must NOT happen is that they see a PREVIOUS
 * tenant's bills for the same unit.
 */
it('lets a renter view the unit they occupy', function (): void {
    $this->actingAs($this->renter)->get("/houses/{$this->house->id}")->assertOk();
});

/*
 * REGRESSION: a re-let unit keeps the previous tenant's bills. The history was
 * built from $house->bills(), which returned every bill for the unit, so a new
 * tenant could read the last occupant's charges.
 */
it('hides a previous tenant bills from the current renter', function (): void {
    // A former occupant with their own bills on this same unit.
    TenantContext::set((int) $this->owner->id);
    $former = Renter::factory()->create([
        'house_id' => $this->house->id,
        'property_id' => $this->property->id,
    ]);
    Bill::factory()->create([
        'house_id' => $this->house->id,
        'tenant_id' => $former->id,
        'month' => '2025-12',
        'total' => '99999.00',
        'status' => 'pending',
    ]);
    TenantContext::clear();

    $props = $this->actingAs($this->renter)
        ->get("/houses/{$this->house->id}")
        ->viewData('page')['props'];

    $months = array_column($props['bills'], 'month');

    expect($months)->not->toContain('2025-12')
        ->and($props['financials']['unpaid_total'])->not->toContain('99999.00');
});

it('shows the whole unit history to an owner', function (): void {
    TenantContext::set((int) $this->owner->id);
    $former = Renter::factory()->create([
        'house_id' => $this->house->id,
        'property_id' => $this->property->id,
    ]);
    Bill::factory()->create([
        'house_id' => $this->house->id,
        'tenant_id' => $former->id,
        'month' => '2025-12',
        'total' => '99999.00',
        'status' => 'pending',
    ]);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)
        ->get("/houses/{$this->house->id}")
        ->viewData('page')['props'];

    // Staff legitimately see the unit's full financial history.
    expect(array_column($props['bills'], 'month'))->toContain('2025-12');
});

it('shows a caretaker a unit in an assigned property', function (): void {
    TenantContext::set((int) $this->owner->id);
    $caretaker = Caretaker::factory()->assignedTo([(int) $this->property->id])->create();
    TenantContext::clear();

    $this->actingAs($caretaker)->get("/houses/{$this->house->id}")->assertOk();
});

it('refuses a caretaker a unit outside their properties', function (): void {
    TenantContext::set((int) $this->owner->id);
    $otherProperty = Property::factory()->create();
    $otherHouse = House::factory()->forProperty($otherProperty)->create();
    $caretaker = Caretaker::factory()->assignedTo([(int) $this->property->id])->create();
    TenantContext::clear();

    $this->actingAs($caretaker)->get("/houses/{$otherHouse->id}")->assertForbidden();
});

it('cannot view another owner unit', function (): void {
    $otherOwner = Owner::factory()->create();

    $this->actingAs($otherOwner)->get("/houses/{$this->house->id}")->assertNotFound();
});
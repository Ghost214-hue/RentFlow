<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Tenancy termination
|--------------------------------------------------------------------------
|
| Ported from TenantController::terminate() and the tenant-initiated request.
|
| The tenancy_terminations table existed in the legacy schema and the renter
| status enum has pending_termination, but the port never wrote to either --
| nothing could end a tenancy. These tests pin both flows.
|
*/

use App\Models\Caretaker;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Models\TenancyTermination;
use App\Support\TenantContext;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->owner = Owner::factory()->create();

    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create();
    $this->house = House::factory()->forProperty($this->property)
        ->occupied()->create();
    $this->renter = Renter::factory()->inHouse($this->house)->create();
    TenantContext::clear();
});

// --- renter requests to leave -------------------------------------------

it('records a renter request as pending', function (): void {
    $this->actingAs($this->renter)
        ->post("/renters/{$this->renter->id}/termination", ['reason' => 'Moving away'])
        ->assertRedirect();

    $renter = Renter::withoutOwnerScope()->find($this->renter->id);
    expect($renter->status->value)->toBe('pending_termination');

    $termination = TenancyTermination::withoutOwnerScope()->firstOrFail();
    expect($termination->status)->toBe('pending')
        ->and($termination->initiated_by)->toBe('tenant')
        ->and($termination->reason)->toBe('Moving away');
});

it('refuses a second pending request', function (): void {
    $this->actingAs($this->renter)
        ->post("/renters/{$this->renter->id}/termination", ['reason' => 'First'])
        ->assertRedirect();

    $this->actingAs($this->renter)
        ->post("/renters/{$this->renter->id}/termination", ['reason' => 'Second'])
        ->assertSessionHas('error');

    expect(TenancyTermination::withoutOwnerScope()->count())->toBe(1);
});

it('will not let a renter request another renters termination', function (): void {
    TenantContext::set((int) $this->owner->id);
    $other = Renter::factory()->inHouse(House::factory()->forProperty($this->property)->create())->create();
    TenantContext::clear();

    $this->actingAs($this->renter)
        ->post("/renters/{$other->id}/termination")
        ->assertForbidden();
});

// --- owner terminates ---------------------------------------------------

it('frees the unit and terminates the renter', function (): void {
    $this->actingAs($this->owner)
        ->post("/renters/{$this->renter->id}/terminate", ['reason' => 'Eviction'])
        ->assertRedirect();

    $house = House::withoutOwnerScope()->find($this->house->id);
    $renter = Renter::withoutOwnerScope()->find($this->renter->id);

    expect($house->status)->toBe('vacant')
        ->and($house->tenant_id)->toBeNull()
        ->and($renter->status->value)->toBe('terminated')
        ->and($renter->lease_end?->toDateString())->toBe(now()->toDateString());
});

/*
 * Termination scrubs personal data but KEEPS the unit link, so the tenancy
 * history stays traceable.
 */
it('scrubs personal data but keeps the tenancy history', function (): void {
    TenantContext::set((int) $this->owner->id);
    $this->renter->update([
        'profile_picture' => 'photo.jpg',
        'next_of_kin_name' => 'Jane',
        'next_of_kin_phone' => '0700',
        'documents' => '["id.pdf"]',
    ]);
    TenantContext::clear();

    $this->actingAs($this->owner)
        ->post("/renters/{$this->renter->id}/terminate", ['reason' => 'Done'])
        ->assertRedirect();

    $renter = Renter::withoutOwnerScope()->find($this->renter->id);

    expect($renter->profile_picture)->toBeNull()
        ->and($renter->next_of_kin_name)->toBeNull()
        ->and($renter->documents)->toBeNull()
        ->and($renter->password)->toBeNull()
        // History is preserved.
        ->and((int) $renter->house_id)->toBe((int) $this->house->id);
});

it('records damages as completed maintenance rows', function (): void {
    $this->actingAs($this->owner)
        ->post("/renters/{$this->renter->id}/terminate", [
            'reason' => 'End of lease',
            'damages' => [
                ['title' => 'Broken window', 'cost' => '2500.50', 'vendor_name' => 'Glazing Ltd'],
                ['title' => 'Wall damage', 'cost' => '1000.00'],
            ],
        ])
        ->assertRedirect();

    $damages = MaintenanceRecord::withoutOwnerScope()
        ->where('category', 'Damage')
        ->get();

    expect($damages)->toHaveCount(2);

    // Money must be stored exactly, with no float rounding.
    $brokenWindow = $damages->firstWhere('title', 'Broken window');
    expect((string) $brokenWindow->cost)->toBe('2500.50')
        ->and($brokenWindow->status->value)->toBe('completed')
        ->and($brokenWindow->vendor_name)->toBe('Glazing Ltd');
});

it('rejects a malformed damage cost', function (): void {
    $this->actingAs($this->owner)
        ->post("/renters/{$this->renter->id}/terminate", [
            'damages' => [['title' => 'X', 'cost' => 'free']],
        ])
        ->assertSessionHasErrors('damages.0.cost');

    // Nothing changed: the whole request is rejected.
    expect(House::withoutOwnerScope()->find($this->house->id)->status)->toBe('occupied');
});

it('writes a completed termination record', function (): void {
    $this->actingAs($this->owner)
        ->post("/renters/{$this->renter->id}/terminate", ['reason' => 'Mutual'])
        ->assertRedirect();

    $termination = TenancyTermination::withoutOwnerScope()->firstOrFail();

    expect($termination->status)->toBe('completed')
        ->and($termination->initiated_by)->toBe('owner');
});

it('completes a renter pending request instead of duplicating it', function (): void {
    // The renter asked to leave...
    $this->actingAs($this->renter)
        ->post("/renters/{$this->renter->id}/termination", ['reason' => 'Leaving'])
        ->assertRedirect();

    // ...and the owner approved it.
    $this->actingAs($this->owner)
        ->post("/renters/{$this->renter->id}/termination/approve", ['reason' => 'Approved'])
        ->assertRedirect();

    $terminations = TenancyTermination::withoutOwnerScope()->get();

    // One row, upgraded to completed -- not a second row.
    expect($terminations)->toHaveCount(1)
        ->and($terminations->first()->status)->toBe('completed');
});

it('refuses termination from a renter', function (): void {
    $this->actingAs($this->renter)
        ->post("/renters/{$this->renter->id}/terminate")
        ->assertForbidden();

    expect(House::withoutOwnerScope()->find($this->house->id)->status)->toBe('occupied');
});

it('refuses a caretaker terminating outside their properties', function (): void {
    TenantContext::set((int) $this->owner->id);
    $otherProperty = Property::factory()->create();
    $otherRenter = Renter::factory()->inHouse(
        House::factory()->forProperty($otherProperty)->create()
    )->create();
    $caretaker = Caretaker::factory()->assignedTo([(int) $this->property->id])->create();
    TenantContext::clear();

    $this->actingAs($caretaker)
        ->post("/renters/{$otherRenter->id}/terminate")
        ->assertForbidden();
});

it('lists terminations for an owner', function (): void {
    TenantContext::set((int) $this->owner->id);
    TenancyTermination::factory()->create(['tenant_id' => $this->renter->id]);
    TenantContext::clear();

    $this->actingAs($this->owner)->get('/terminations')->assertOk();
});
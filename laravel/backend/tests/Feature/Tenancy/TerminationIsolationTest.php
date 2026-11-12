<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Tenancy termination isolation
|--------------------------------------------------------------------------
|
| Companion to OwnerScopeTest. Termination moves money and frees units, so it
| has to hold the same owner-scope guarantees as every other write.
|
| The headline change: a tenancy is ENDED, never deleted. A hard DELETE would
| orphan the bills, payments and the termination audit trail, so the route,
| the controller action and the policy all refuse it outright.
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
use Illuminate\Routing\RouteNotFoundException;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->ownerA = Owner::factory()->withPassword('secret123')->create();

    TenantContext::set((int) $this->ownerA->id);
    $this->propertyA = Property::factory()->create();
    $this->houseA = House::factory()->forProperty($this->propertyA)
        ->occupied()->create(['unit' => 'A1']);
    $this->renterA = Renter::factory()->inHouse($this->houseA)->create();

    $this->ownerB = Owner::factory()->withPassword('secret123')->create();

    TenantContext::set((int) $this->ownerB->id);
    $this->propertyB = Property::factory()->create();
    $this->houseB = House::factory()->forProperty($this->propertyB)
        ->occupied()->create(['unit' => 'B1']);
    $this->renterB = Renter::factory()->inHouse($this->houseB)->create();
    $this->caretakerB = Caretaker::factory()
        ->assignedTo([(int) $this->propertyB->id])
        ->create(['password' => 'secret123']);

    TenantContext::clear();
});

afterEach(function (): void {
    TenantContext::clear();
});

// --- a tenancy is ended, never deleted ---------------------------------

it('has no route that deletes a renter', function (): void {
    $names = collect(app('router')->getRoutes()->getRoutes())
        ->map(fn ($r) => $r->getName())
        ->filter()
        ->all();

    expect($names)->not->toContain('renters.destroy');
});

it('refuses to delete a renter even for the owner', function (): void {
    expect($this->ownerA->can('delete', $this->renterA))->toBeFalse();
});

it('leaves the renter row in place after termination', function (): void {
    $this->actingAs($this->ownerA)
        ->post("/renters/{$this->renterA->id}/terminate", ['reason' => 'Done'])
        ->assertRedirect();

    // Terminated, not gone: the history has to survive.
    $renter = Renter::withoutOwnerScope()->find($this->renterA->id);

    expect($renter)->not->toBeNull()
        ->and($renter->status->value)->toBe('terminated');
});

// --- owner isolation ---------------------------------------------------

it('cannot terminate another owner renter', function (): void {
    $this->actingAs($this->ownerA)
        ->post("/renters/{$this->renterB->id}/terminate")
        ->assertNotFound();

    // B's unit is untouched.
    expect(House::withoutOwnerScope()->find($this->houseB->id)->status)->toBe('occupied');
});

it('cannot approve another owner renter request', function (): void {
    TenantContext::set((int) $this->ownerB->id);
    TenancyTermination::factory()->create(['tenant_id' => $this->renterB->id]);
    TenantContext::clear();

    $this->actingAs($this->ownerA)
        ->post("/renters/{$this->renterB->id}/termination/approve")
        ->assertNotFound();

    expect(TenancyTermination::withoutOwnerScope()
        ->where('tenant_id', $this->renterB->id)
        ->where('status', 'pending')
        ->exists())->toBeTrue();
});

it('writes the termination row under the acting owner', function (): void {
    $this->actingAs($this->ownerA)
        ->post("/renters/{$this->renterA->id}/terminate", ['reason' => 'Ended'])
        ->assertRedirect();

    $termination = TenancyTermination::withoutOwnerScope()->firstOrFail();

    expect((int) $termination->owner_id)->toBe((int) $this->ownerA->id)
        ->and($termination->initiated_by)->toBe('owner');
});

// --- renter-initiated --------------------------------------------------

it('lets a renter request their own termination', function (): void {
    $this->actingAs($this->renterA)
        ->post("/renters/{$this->renterA->id}/termination", ['reason' => 'Moving'])
        ->assertRedirect();

    expect(Renter::withoutOwnerScope()->find($this->renterA->id)->status->value)
        ->toBe('pending_termination');
});

/*
 * A renter must not be able to terminate themselves directly -- only REQUEST.
 * Terminating frees the unit and scrubs their data; that is the owner's call.
 */
it('refuses a renter terminating themselves', function (): void {
    $this->actingAs($this->renterA)
        ->post("/renters/{$this->renterA->id}/terminate")
        ->assertForbidden();

    expect(House::withoutOwnerScope()->find($this->houseA->id)->status)->toBe('occupied')
        ->and(Renter::withoutOwnerScope()->find($this->renterA->id)->status->value)->toBe('active');
});

/*
 * renterA and renterB belong to DIFFERENT owners, so the route-model binding
 * never resolves the row and the request 404s before the controller runs. That
 * is the stronger guarantee: a cross-owner renter is not merely refused, it is
 * invisible.
 */
it('hides another owner renter from a renter entirely', function (): void {
    $this->actingAs($this->renterB)
        ->post("/renters/{$this->renterA->id}/termination")
        ->assertNotFound();

    expect(Renter::withoutOwnerScope()->find($this->renterA->id)->status->value)->toBe('active');
});

/*
 * The genuine same-owner case, where the row IS visible and only the
 * self-check stops it. Without that check a renter could put a neighbour into
 * pending_termination and have the landlord notified about them.
 */
it('refuses a renter requesting a neighbour in the same property', function (): void {
    TenantContext::set((int) $this->ownerA->id);
    $secondHouse = House::factory()->forProperty($this->propertyA)->occupied()->create();
    $neighbour = Renter::factory()->inHouse($secondHouse)->create();
    TenantContext::clear();

    $this->actingAs($this->renterA)
        ->post("/renters/{$neighbour->id}/termination")
        ->assertForbidden();

    expect(Renter::withoutOwnerScope()->find($neighbour->id)->status->value)->toBe('active');
});

/*
 * A caretaker terminating outside their assigned properties. The caretaker and
 * the renter share an owner here, so the row IS visible -- only the property
 * check stops it.
 */
it('refuses a caretaker terminating outside their properties', function (): void {
    TenantContext::set((int) $this->ownerA->id);
    $stranger = Caretaker::factory()->assignedTo([(int) $this->propertyB->id])->create();
    TenantContext::clear();

    $this->actingAs($stranger)
        ->post("/renters/{$this->renterA->id}/terminate")
        ->assertForbidden();

    expect(House::withoutOwnerScope()->find($this->houseA->id)->status)->toBe('occupied');
});

it('hides a cross-owner renter from a caretaker as not found', function (): void {
    // caretakerB belongs to owner B, renterA to owner A.
    $this->actingAs($this->caretakerB)
        ->post("/renters/{$this->renterA->id}/terminate")
        ->assertNotFound();

    expect(House::withoutOwnerScope()->find($this->houseA->id)->status)->toBe('occupied');
});

it('lets a caretaker terminate a renter in their own property', function (): void {
    $this->actingAs($this->caretakerB)
        ->post("/renters/{$this->renterB->id}/terminate", ['reason' => 'Vacating'])
        ->assertRedirect();

    expect(House::withoutOwnerScope()->find($this->houseB->id)->status)->toBe('vacant');
});

// --- audit trail -------------------------------------------------------

it('keeps an audit row for every termination', function (): void {
    $this->actingAs($this->ownerA)
        ->post("/renters/{$this->renterA->id}/terminate", ['reason' => 'Reason here'])
        ->assertRedirect();

    $termination = TenancyTermination::withoutOwnerScope()->firstOrFail();

    expect($termination->reason)->toBe('Reason here')
        ->and((int) $termination->house_id)->toBe((int) $this->houseA->id)
        ->and($termination->effective_date)->not->toBeNull();
});

it('does not leave damages on another owner unit', function (): void {
    $this->actingAs($this->ownerA)
        ->post("/renters/{$this->renterA->id}/terminate", [
            'damages' => [['title' => 'Broken window', 'cost' => '1000.00']],
        ])
        ->assertRedirect();

    $damages = MaintenanceRecord::withoutOwnerScope()->get();

    expect($damages)->toHaveCount(1)
        ->and((int) $damages->first()->owner_id)->toBe((int) $this->ownerA->id)
        ->and((int) $damages->first()->house_id)->toBe((int) $this->houseA->id);
});
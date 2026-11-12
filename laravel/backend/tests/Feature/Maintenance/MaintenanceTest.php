<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Maintenance requests
|--------------------------------------------------------------------------
|
| The central rule under test: a renter may RAISE a request but never CLOSE
| one, and may never write the staff-only fields (cost, vendor, scheduling).
|
*/

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
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
    $this->property = Property::factory()->create();
    $this->house = House::factory()->forProperty($this->property)->create();
    $this->renter = Renter::factory()->inHouse($this->house)->create();
    $this->otherRenter = Renter::factory()->inHouse($this->house)->create();
    $this->caretaker = Caretaker::factory()
        ->assignedTo([(int) $this->property->id])
        ->create();

    TenantContext::clear();
});

it('lets a renter raise a request against their own unit', function (): void {
    $this->actingAs($this->renter)
        ->post('/maintenance', [
            'title' => 'Bathroom tap leaking',
            'description' => 'Drips constantly',
            'priority' => 'high',
        ])
        ->assertRedirect();

    $record = MaintenanceRecord::withoutOwnerScope()
        ->where('tenant_id', $this->renter->id)
        ->firstOrFail();

    expect($record->title)->toBe('Bathroom tap leaking')
        ->and($record->status)->toBe(MaintenanceStatus::Pending)
        ->and((string) $record->cost)->toBe('0.00');
});

/*
 * A renter must not be able to self-complete a repair, so update() and
 * advance() are separate abilities. Reusing update() here let a renter close
 * their own request.
 */
it('stops a renter advancing their own request', function (): void {
    $record = createFor(MaintenanceRecord::class, [
        'tenant_id' => $this->renter->id,
        'title' => 'Leak',
        'description' => 'Dripping',
        'status' => 'pending',
    ], (int) $this->owner->id);

    $this->actingAs($this->renter)
        ->post("/maintenance/{$record->id}/advance")
        ->assertForbidden();

    expect(MaintenanceRecord::withoutOwnerScope()->find($record->id)->status)
        ->toBe(MaintenanceStatus::Pending);
});

/*
 * Staff-only fields are `prohibited` for renters, so the WHOLE request is
 * rejected rather than silently stripped. A renter cannot post a zero-cost
 * "completed" job, nor name themselves as another unit.
 */
it('rejects a renter request carrying staff-only fields', function (): void {
    $this->actingAs($this->renter)
        ->post('/maintenance', [
            'title' => 'Sneaky job',
            'description' => 'trying to self-complete',
            'status' => 'completed',
            'cost' => '0',
        ])
        ->assertSessionHasErrors('status');

    expect(MaintenanceRecord::withoutOwnerScope()->where('title', 'Sneaky job')->count())
        ->toBe(0);
});

/*
 * A renter may not file a request against ANOTHER renter. tenant_id is
 * `prohibited` for a renter: recordAttributes() would overwrite it with the
 * actor anyway, so rejecting it outright is stricter and more honest than
 * accepting a value and silently discarding it.
 */
it('rejects a renter supplying a foreign tenant_id', function (): void {
    $this->actingAs($this->renter)
        ->post('/maintenance', [
            'title' => 'Not mine',
            'description' => 'someone else unit',
            'tenant_id' => $this->otherRenter->id,
        ])
        ->assertSessionHasErrors('tenant_id');

    expect(MaintenanceRecord::withoutOwnerScope()->count())->toBe(0);
});

it('lets an owner record a cost and set an urgent priority', function (): void {
    $this->actingAs($this->owner)
        ->post('/maintenance', [
            'title' => 'Replace bathroom fittings',
            'description' => 'Whole set needs replacing',
            'tenant_id' => $this->renter->id,
            'priority' => 'urgent',
            'cost' => '2500.50',
        ])
        ->assertRedirect();

    $record = MaintenanceRecord::withoutOwnerScope()->latest('id')->firstOrFail();

    // 'urgent' is valid for maintenance and would be INVALID for complaints.
    expect($record->priority)->toBe(MaintenancePriority::Urgent)
        ->and((string) $record->cost)->toBe('2500.50');
});

it('advances a record through its lifecycle and stamps the completion date', function (): void {
    $record = createFor(MaintenanceRecord::class, [
        'tenant_id' => $this->renter->id,
        'title' => 'Broken window',
        'description' => 'Will not close',
        'status' => 'pending',
    ], (int) $this->owner->id);

    $this->actingAs($this->owner)->post("/maintenance/{$record->id}/advance")->assertRedirect();
    expect(MaintenanceRecord::withoutOwnerScope()->find($record->id)->status)
        ->toBe(MaintenanceStatus::InProgress);

    $this->actingAs($this->owner)->post("/maintenance/{$record->id}/advance")->assertRedirect();

    $completed = MaintenanceRecord::withoutOwnerScope()->find($record->id);
    expect($completed->status)->toBe(MaintenanceStatus::Completed)
        ->and($completed->completed_date)->not->toBeNull();
});

it('leaves a completed record completed when advanced again', function (): void {
    $record = createFor(MaintenanceRecord::class, [
        'tenant_id' => $this->renter->id,
        'title' => 'Done job',
        'description' => 'Already finished',
        'status' => 'completed',
        'completed_date' => now()->toDateString(),
    ], (int) $this->owner->id);

    $this->actingAs($this->owner)->post("/maintenance/{$record->id}/advance")->assertRedirect();

    expect(MaintenanceRecord::withoutOwnerScope()->find($record->id)->status)
        ->toBe(MaintenanceStatus::Completed);
});

it('refuses a caretaker a request in a property they are not assigned to', function (): void {
    TenantContext::set((int) $this->owner->id);
    $otherProperty = Property::factory()->create();
    $otherHouse = House::factory()->forProperty($otherProperty)->create();
    TenantContext::clear();

    $outsideRecord = createFor(MaintenanceRecord::class, [
        'property_id' => $otherProperty->id,
        'house_id' => $otherHouse->id,
        'title' => 'Elsewhere',
        'description' => 'Not their property',
        'status' => 'pending',
    ], (int) $this->owner->id);

    $this->actingAs($this->caretaker)
        ->post("/maintenance/{$outsideRecord->id}/advance")
        ->assertForbidden();
});

it('refuses the caretaker list to a renter', function (): void {
    $this->actingAs($this->renter)->get('/caretakers')->assertForbidden();
});

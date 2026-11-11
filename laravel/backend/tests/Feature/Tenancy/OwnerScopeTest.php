<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Owner scope / tenancy isolation
|--------------------------------------------------------------------------
|
| These tests pin down behaviour that was silently broken: the named-scope
| bypass, the actor-to-owner resolution, and cross-owner isolation. Each has a
| regression note describing the defect it guards against.
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
    /*
     * Seeding runs with no owner context, which the creating hook correctly
     * refuses. Each actor is therefore created while its own owner is the
     * context, which is also what happens in production: the context is set by
     * the session before any model is touched.
     */
    TenantContext::set(null);
    $this->ownerA = Owner::factory()->create();

    TenantContext::set((int) $this->ownerA->id);
    $this->propertyA = Property::factory()->create();
    $this->houseA = House::factory()->forProperty($this->propertyA)->create();
    $this->renterA = Renter::factory()->create();

    $this->ownerB = Owner::factory()->create();

    TenantContext::set((int) $this->ownerB->id);
    $this->propertyB = Property::factory()->create();
    $this->renterB = Renter::factory()->create();

    TenantContext::clear();
});

afterEach(function (): void {
    TenantContext::clear();
});

it('refuses an unscoped query when no owner context is resolved', function (): void {
    // Fails CLOSED. Returning every owner's rows would be a data leak.
    expect(fn (): int => Property::query()->count())
        ->toThrow(RuntimeException::class, 'No owner context resolved');
});

it('scopes queries to the resolved owner', function (): void {
    TenantContext::set((int) $this->ownerA->getKey());

    expect(Property::query()->count())->toBe(1)
        ->and((int) Property::query()->first()->id)->toBe((int) $this->propertyA->id);
});

/*
 * REGRESSION: withoutOwnerScope() used to remove the scope by static::class,
 * but the scope was registered as an anonymous closure. The lookup found
 * nothing, the scope stayed in place, and every "unscoped" call threw.
 */
it('withoutOwnerScope actually bypasses the scope', function (): void {
    TenantContext::clear();

    expect(Property::withoutOwnerScope()->count())->toBe(2);
});

it('stamps owner_id from the context on create, ignoring a foreign one', function (): void {
    TenantContext::set((int) $this->ownerB->getKey());

    // owner_id is not fillable, so a supplied value cannot win over the session.
    $house = House::factory()->forProperty($this->propertyA)->create();

    expect((int) $house->owner_id)->toBe((int) $this->ownerB->id);
});

it('cannot read another owner rows through normal Eloquent usage', function (): void {
    // B's house must be created while B is the context: the creating hook
    // stamps owner_id from the context, so seeding it under A would quietly
    // make it A's row. That the hook makes cross-owner seeding impossible is
    // itself part of the guarantee.
    TenantContext::set((int) $this->ownerB->getKey());
    $bHouse = House::factory()->forProperty($this->propertyB)->create();
    expect((int) $bHouse->owner_id)->toBe((int) $this->ownerB->id);

    TenantContext::set((int) $this->ownerA->getKey());

    expect(House::query()->find($bHouse->id))->toBeNull();
});

/*
 * REGRESSION: resolveForActor() called getKey('owner_id'), but getKey() takes
 * no arguments and always returns the PRIMARY KEY. A renter's context resolved
 * to the renter's own id, scoping every query to an owner that does not exist.
 */
it('resolves an owner actor to their own id', function (): void {
    expect(TenantContext::resolveForActor($this->ownerA))->toBe((int) $this->ownerA->id);
});

it('resolves a renter actor to their owner, not to themselves', function (): void {
    expect((int) $this->renterA->owner_id)->not->toBe((int) $this->renterA->id)
        ->and(TenantContext::resolveForActor($this->renterA))->toBe((int) $this->ownerA->id);
});

it('resolves a caretaker actor to their owner, not to themselves', function (): void {
    // caretakers carry the owner scope, so seeding one needs a context.
    TenantContext::set((int) $this->ownerA->getKey());
    $caretaker = Caretaker::factory()->assignedTo([(int) $this->propertyA->id])->create();

    expect((int) $caretaker->id)->not->toBe((int) $this->ownerA->id)
        ->and(TenantContext::resolveForActor($caretaker))->toBe((int) $this->ownerA->id);
});

it('scopes a renter queries to the owner they belong to', function (): void {
    TenantContext::set(TenantContext::resolveForActor($this->renterA));

    expect(Renter::query()->pluck('id')->all())->toBe([(int) $this->renterA->id]);
});

it('isolates cross-owner writes', function (): void {
    TenantContext::set((int) $this->ownerB->getKey());
    $bHouse = House::factory()->forProperty($this->propertyB)->create();

    TenantContext::set((int) $this->ownerA->getKey());

    // Even holding the row id, an owner-scoped update must touch nothing.
    $affected = House::query()->whereKey($bHouse->id)->update(['unit' => 'Hijacked']);

    expect($affected)->toBe(0)
        ->and(DB::table('houses')->where('id', $bHouse->id)->value('unit'))
        ->not->toBe('Hijacked');
});
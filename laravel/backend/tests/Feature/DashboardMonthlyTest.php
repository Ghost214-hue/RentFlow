<?php

declare(strict_types=1);

use App\Models\Bill;
use App\Models\House;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Renter;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;

it('provides six months of server-calculated dashboard billing and collection stats', function (): void {
    TenantContext::set(null);
    $owner = Owner::factory()->create();
    TenantContext::set((int) $owner->id);

    $property = Property::factory()->create();
    $house = House::factory()->forProperty($property)->create();
    $renter = Renter::factory()->inHouse($house)->create();

    $currentMonth = CarbonImmutable::now()->startOfMonth();
    Bill::factory()->forTenant($renter)->forMonth($currentMonth->format('Y-m'))->create([
        'total' => '10000.00',
    ]);
    Payment::factory()->forTenant($renter)->ofAmount('4000.00')->create([
        'date' => $currentMonth->toDateString(),
        'status' => 'completed',
    ]);

    TenantContext::clear();

    $props = $this->actingAs($owner)->get('/')
        ->assertOk()
        ->viewData('page')['props'];

    $monthly = $props['stats']['monthly'];

    expect($monthly['months'])->toHaveCount(6)
        ->and($monthly['current']['billed'])->toBe('10000.00')
        ->and($monthly['current']['collected'])->toBe('4000.00')
        ->and($monthly['current']['outstanding'])->toBe('6000.00')
        ->and($monthly['current']['collectionRate'])->toBe('40.00');
});

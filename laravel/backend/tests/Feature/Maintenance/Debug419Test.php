<?php

declare(strict_types=1);

use App\Models\House;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Support\TenantContext;

it('debug 419 on POST maintenance', function (): void {
    TenantContext::set(null);
    $owner = Owner::factory()->create();
    TenantContext::set((int) $owner->id);
    $property = Property::factory()->create();
    $house = House::factory()->forProperty($property)->create();
    $renter = Renter::factory()->inHouse($house)->create();
    TenantContext::clear();

    // First try as owner
    $ownerResp = $this->actingAs($owner)
        ->post('/maintenance', [
            'title' => 'Debug test',
            'description' => 'Testing 419',
            'tenant_id' => $renter->id,
            'priority' => 'high',
        ]);
    dump('Owner status: ' . $ownerResp->status());
    if ($ownerResp->status() === 419) {
        dump('Owner 419 body:', $ownerResp->getContent());
    }

    // Then try as renter
    $renterResp = $this->actingAs($renter)
        ->post('/maintenance', [
            'title' => 'Renter debug test',
            'description' => 'Testing 419',
            'priority' => 'high',
        ]);
    dump('Renter status: ' . $renterResp->status());
    if ($renterResp->status() === 419) {
        dump('Renter 419 body:', $renterResp->getContent());
    }

    expect(true)->toBeTrue();
});

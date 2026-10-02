<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Renter-facing pages
|--------------------------------------------------------------------------
|
| Two rules under test:
|   1. These pages are renter-only. An owner or caretaker gets a 403, never
|      another actor's data.
|   2. A renter edits contact details only. Tenancy fields (status, deposit,
|      balance, lease) belong to the owner and must be unreachable, not merely
|      ignored.
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
    $this->property = Property::factory()->create();
    $this->house = House::factory()->forProperty($this->property)->create();
    $this->renter = Renter::factory()->inHouse($this->house)->create();
    $this->caretaker = Caretaker::factory()
        ->assignedTo([(int) $this->property->id])
        ->create();

    TenantContext::clear();
});

it('shows the renter their own dashboard', function (): void {
    $this->actingAs($this->renter)->get('/renter/dashboard')->assertOk();
});

it('shows the renter their own profile', function (): void {
    $this->actingAs($this->renter)->get('/renter/profile')->assertOk();
});

it('refuses the renter dashboard to an owner', function (): void {
    // 403, not an empty page: an owner must not reach a renter's figures here.
    $this->actingAs($this->owner)->get('/renter/dashboard')->assertForbidden();
});

it('refuses the renter profile to a caretaker', function (): void {
    $this->actingAs($this->caretaker)->get('/renter/profile')->assertForbidden();
});

it('refuses guests', function (): void {
    $this->get('/renter/dashboard')->assertUnauthorized();
    $this->get('/renter/profile')->assertUnauthorized();
});

it('lets a renter update their contact details', function (): void {
    $this->actingAs($this->renter)
        ->put('/renter/profile', [
            'name' => 'Rita R. Mutua',
            'email' => $this->renter->email,
            'phone' => '0700999888',
        ])
        ->assertRedirect();

    $fresh = Renter::withoutOwnerScope()->find($this->renter->id);

    expect($fresh->name)->toBe('Rita R. Mutua')
        ->and($fresh->phone)->toBe('0700999888');
});

/*
 * Tenancy belongs to the owner. These fields are absent from the validation
 * rules, so a crafted request cannot reach them at all.
 */
it('stops a renter changing their own tenancy', function (): void {
    $before = Renter::withoutOwnerScope()->find($this->renter->id);

    $this->actingAs($this->renter)
        ->put('/renter/profile', [
            'name' => $this->renter->name,
            'email' => $this->renter->email,
            'status' => 'terminated',
            'balance' => '0.00',
            'deposit' => '0.00',
            'credit' => '999999.00',
        ])
        ->assertRedirect();

    $after = Renter::withoutOwnerScope()->find($this->renter->id);

    expect($after->status)->toBe($before->status)
        ->and((string) $after->balance)->toBe((string) $before->balance)
        ->and((string) $after->deposit)->toBe((string) $before->deposit)
        ->and((string) $after->credit)->toBe((string) $before->credit);
});

it('requires the current password to change a password', function (): void {
    $this->actingAs($this->renter)
        ->put('/renter/profile', [
            'name' => $this->renter->name,
            'email' => $this->renter->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertSessionHasErrors('current_password');
});

it('validates the renter profile input', function (): void {
    $this->actingAs($this->renter)
        ->put('/renter/profile', ['name' => '', 'email' => 'not-an-email'])
        ->assertSessionHasErrors(['name', 'email']);
});

it('lets a renter see the complaints and maintenance lists', function (): void {
    $this->actingAs($this->renter)->get('/complaints')->assertOk();
    $this->actingAs($this->renter)->get('/maintenance')->assertOk();
});
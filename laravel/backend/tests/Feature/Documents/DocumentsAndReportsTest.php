<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Documents, reports and the email log
|--------------------------------------------------------------------------
|
| Three access rules matter most here:
|   1. A renter READS the rules but never authors them.
|   2. A renter never sees the portfolio reports: those expose other
|      households' finances.
|   3. The email log is READ ONLY, and a renter sees only their own mail.
|
*/

use App\Models\EmailLog;
use App\Models\House;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\Renter;
use App\Support\TenantContext;
use Illuminate\Routing\RouteNotFoundException;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->owner = Owner::factory()->create();

    TenantContext::set((int) $this->owner->id);
    $this->property = Property::factory()->create();
    $this->house = House::factory()->forProperty($this->property)->create();
    $this->renter = Renter::factory()->inHouse($this->house)->create();
    TenantContext::clear();
});

/*
 * Seeding an owner-scoped row needs the tenancy context that the creating hook
 * requires, so these helpers set it and always restore it afterwards.
 */
function seedAs(int $ownerId, Closure $callback): mixed
{
    TenantContext::set($ownerId);

    try {
        return $callback();
    } finally {
        TenantContext::clear();
    }
}

function makeDoc(int $ownerId, array $attributes = []): PropertyDocument
{
    return seedAs($ownerId, fn () => PropertyDocument::factory()->create($attributes));
}

// --- documents ---------------------------------------------------------

it('lists documents for an owner', function (): void {
    makeDoc($this->owner->id, ['title' => 'Noise policy']);

    $this->actingAs($this->owner)->get('/documents')->assertOk();
});

it('lets an owner publish a document', function (): void {
    $this->actingAs($this->owner)
        ->post('/documents', [
            'title' => 'Pet policy',
            'content' => 'No pets without written consent.',
            'type' => 'policy',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(PropertyDocument::query()->where('title', 'Pet policy')->exists())->toBeTrue();
});

it('requires a title and content on a document', function (): void {
    // content is NOT NULL in the live schema.
    $this->actingAs($this->owner)
        ->post('/documents', ['title' => '', 'content' => ''])
        ->assertSessionHasErrors(['title', 'content']);
});

it('rejects an unknown document type', function (): void {
    $this->actingAs($this->owner)
        ->post('/documents', ['title' => 'X', 'content' => 'Y', 'type' => 'nonsense'])
        ->assertSessionHasErrors('type');
});

/*
 * The rules are the landlord's terms: a renter may read them but may never
 * create, edit or delete one.
 */
it('stops a renter authoring a document', function (): void {
    $this->actingAs($this->renter)
        ->post('/documents', ['title' => 'My rules', 'content' => 'Anything I like'])
        ->assertForbidden();
});

it('stops a renter editing a document', function (): void {
    $doc = makeDoc($this->owner->id, ['title' => 'Rules']);

    $this->actingAs($this->renter)
        ->put("/documents/{$doc->id}", ['title' => 'Mine now', 'content' => 'x'])
        ->assertForbidden();
});

it('stops a renter deleting a document', function (): void {
    $doc = makeDoc($this->owner->id, ['title' => 'Rules']);

    $this->actingAs($this->renter)->delete("/documents/{$doc->id}")->assertForbidden();
});

it('shows a renter only active documents', function (): void {
    makeDoc($this->owner->id, ['title' => 'Active rule', 'is_active' => true, 'property_id' => null]);
    makeDoc($this->owner->id, ['title' => 'Draft rule', 'is_active' => false, 'property_id' => null]);

    $response = $this->actingAs($this->renter)->get('/documents');
    $response->assertOk();

    $titles = array_column($response->viewData('page')['props']['documents'], 'title');

    expect($titles)->toContain('Active rule')
        ->and($titles)->not->toContain('Draft rule');
});

// --- reports -----------------------------------------------------------

it('refuses portfolio reports to a renter', function (): void {
    // The aggregates would expose other households' finances.
    $this->actingAs($this->renter)->get('/reports')->assertForbidden();
});

it('shows reports to an owner', function (): void {
    $this->actingAs($this->owner)->get('/reports')->assertOk();
});

it('reports money as decimal strings, never floats', function (): void {
    $response = $this->actingAs($this->owner)->get('/reports');
    $response->assertOk();

    $money = $response->viewData('page')['props']['summary']['money'];

    foreach (['billed_to_date', 'received_to_date', 'outstanding', 'collection_rate'] as $key) {
        expect($money[$key])->toBeString()
            ->and($money[$key])->toMatch('/^\d+\.\d{2}$/');
    }
});

// --- email log ---------------------------------------------------------

it('has no write routes for the email log', function (): void {
    /*
     * The log is evidence of what was actually sent. There are deliberately no
     * update or destroy routes, so this asserts their ABSENCE from the route
     * table. Adding one later has to be a conscious act, not an oversight.
     */
    $names = collect(app('router')->getRoutes()->getRoutes())
        ->map(fn ($r) => $r->getName())
        ->filter()
        ->all();

    expect($names)->toContain('email-logs.index')
        ->and($names)->not->toContain('email-logs.update')
        ->and($names)->not->toContain('email-logs.destroy')
        ->and($names)->not->toContain('email-logs.store');
});

it('shows a renter only their own email history', function (): void {
    $ownerId = (int) $this->owner->id;

    seedAs($ownerId, function () use ($ownerId): void {
        EmailLog::factory()->create(['to_email' => $this->renter->email, 'subject' => 'Mine']);
        EmailLog::factory()->create(['to_email' => 'someone.else@example.test', 'subject' => 'Not mine']);
    });

    $response = $this->actingAs($this->renter)->get('/email-logs');
    $response->assertOk();

    $subjects = array_column($response->viewData('page')['props']['logs']['data'], 'subject');

    expect($subjects)->toContain('Mine')
        ->and($subjects)->not->toContain('Not mine');
});

it('shows an owner the whole delivery log', function (): void {
    $ownerId = (int) $this->owner->id;

    seedAs($ownerId, function (): void {
        EmailLog::factory()->create(['subject' => 'One']);
        EmailLog::factory()->create(['subject' => 'Two']);
    });

    $response = $this->actingAs($this->owner)->get('/email-logs');
    $response->assertOk();

    expect($response->viewData('page')['props']['logs']['data'])->toHaveCount(2);
});

it('summarises the delivery log by status', function (): void {
    $ownerId = (int) $this->owner->id;

    seedAs($ownerId, function (): void {
        EmailLog::factory()->count(2)->create(['status' => 'sent']);
        EmailLog::factory()->failed()->create();
    });

    $summary = $this->actingAs($this->owner)
        ->get('/email-logs')
        ->viewData('page')['props']['summary'];

    expect($summary['sent'])->toBe(2)
        ->and($summary['failed'])->toBe(1);
});
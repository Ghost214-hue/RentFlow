<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Route access
|--------------------------------------------------------------------------
|
| Every authenticated route must refuse a guest. This is a cheap sweep that
| catches a route added without the legacy.jwt guard: it would otherwise return
| 200 or a 500 instead of 401.
|
| Replaces the stock ExampleTest, which asserted GET / returned 200. That is
| wrong for this application: the dashboard is authenticated, so a guest must
| get 401.
|
*/

use App\Models\Owner;
use App\Support\TenantContext;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->owner = Owner::factory()->create();
    TenantContext::clear();
});

/*
 * A guest must never reach a protected page. A BROWSER navigation is redirected
 * to the login page (302) rather than shown a bare 401 error page, which used to
 * look like a broken application. A JSON/API request still gets a real 401.
 */
it('sends a guest to the login page on every authenticated route', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with([
    '/',
    '/renters',
    '/houses',
    '/payments',
    '/bills',
    '/caretakers',
    '/complaints',
    '/maintenance',
    '/renter/dashboard',
    '/renter/profile',
]);

it('remembers where a guest was heading so sign-in returns them there', function (): void {
    $this->get('/bills')->assertRedirect(route('login'));

    expect(session('url.intended'))->toBe(url('/bills'));
});

it('still returns a real 401 to a JSON request', function (): void {
    // The client needs a status code it can branch on, not an HTML redirect.
    $this->getJson('/bills')->assertUnauthorized();
});

it('lets an authenticated owner reach the owner routes', function (): void {
    foreach ([
        '/', '/renters', '/houses',
        '/payments', '/bills', '/caretakers', '/complaints', '/maintenance',
    ] as $path) {
        $this->actingAs($this->owner)->get($path)->assertOk();
    }
});

it('keeps the renter pages unreachable to an owner', function (): void {
    foreach (['/renter/dashboard', '/renter/profile'] as $path) {
        $this->actingAs($this->owner)->get($path)->assertForbidden();
    }
});
<?php

declare(strict_types=1);

use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests bind to Tests\TestCase and wrap each test in a transaction.
|
| WHY TRANSACTIONS AND NOT RefreshDatabase
|
| RefreshDatabase runs `migrate:fresh`, which DROPS every table and rebuilds it
| from database/migrations. RentFlow has no migrations: it maps an EXISTING
| production schema, so migrate:fresh emptied the test database down to a bare
| `migrations` table. RefreshDatabase is therefore actively destructive here and
| must not be used.
|
| WHY THE REAL MySQL SCHEMA AND NOT sqlite
|
| Several behaviours depend on the exact production DDL, and sqlite would not
| reproduce any of them:
|   - maintenance_records and complaints have DIFFERENT enum sets (pending vs
|     open, urgent vs high);
|   - tenants.status is an enum including pending_termination;
|   - tenants has no `rent` column at all, and houses.rent is the billing source
|     of truth;
|   - MariaDB has no CAST(... AS JSON), which changes how recipient_ids can be
|     queried.
|
| The test database is recreated from the dev schema by the artisan task
| `rentflow:test-db`, so it always matches production structure.
|
*/

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');
uses(DatabaseTransactions::class)->in('Feature');

/*
| TenantContext is a static singleton and is NOT reset by the database
| transaction. Leaking a resolved owner into the next test would silently widen
| its scope, so it is cleared after every test.
*/
afterEach(function (): void {
    TenantContext::clear();
});
/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| Seed records through `createFor`, which sets the owner context that the
| BelongsToOwner creating hook requires. Calling `create()` directly in a test
| throws "No owner context resolved", which is the guard working as intended but
| makes tests noisy.
|
*/

/**
 * Create a row while `ownerId` is the resolved tenant context.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @param  class-string<TModel>  $model
 * @param  array<string, mixed>  $attributes
 * @return TModel
 */
function createFor(string $model, array $attributes, int $ownerId): \Illuminate\Database\Eloquent\Model
{
    TenantContext::set($ownerId);

    try {
        return $model::create($attributes);
    } finally {
        // Leave the context as the test found it.
        TenantContext::clear();
    }
}
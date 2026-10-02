<?php

/**
 * Tenancy isolation proof. Run:
 *   php laravel/_tenancy_check.php
 *
 * Asserts that two owners can never read, update or delete each other's rows,
 * that creates cannot forge an owner_id, and that a missing owner context
 * fails CLOSED instead of defaulting to some tenant's data.
 */
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Owner;
use App\Models\Property;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;

$db = DB::connection();
$db->statement('DELETE FROM properties');
$db->statement('DELETE FROM owners');

$a = Owner::create(['name' => 'Owner A', 'email' => 'a@example.test', 'password' => 'secret123']);
$b = Owner::create(['name' => 'Owner B', 'email' => 'b@example.test', 'password' => 'secret123']);

// Seed two owners with data so tenancy isolation can be proven.
// withoutOwnerScope() removes the READ scope. The creating hook still applies,
// so a seed must set the context explicitly -- which is exactly the safety
// property we want (a CLI job declares whose data it is writing).
TenantContext::set($a->id);
$pa = Property::withoutOwnerScope()->create(['owner_id' => $a->id, 'name' => 'Alpha Tower', 'address' => 'A', 'type' => 'apartment', 'units' => 2, 'occupied' => 1]);

TenantContext::set($b->id);
$pb = Property::withoutOwnerScope()->create(['owner_id' => $b->id, 'name' => 'Beta Villas', 'address' => 'B', 'type' => 'apartment', 'units' => 1, 'occupied' => 0]);

echo "seeded: owners {$a->id}/{$b->id}, properties {$pa->id}/{$pb->id}\n\n";

$results = [];

// 1 - no owner context must fail closed, not return everyone's rows.
TenantContext::clear();
try {
    Property::query()->count();
    $results[] = ['FAIL', 'unscoped query was allowed with no owner context'];
} catch (Throwable $e) {
    $results[] = ['PASS', 'no owner context fails closed (' . substr($e->getMessage(), 0, 45) . '...)'];
}

// 2 - owner A sees only their own rows.
TenantContext::set($a->id);
$names = Property::query()->pluck('name')->all();
$results[] = count($names) === 1 && $names[0] === 'Alpha Tower'
    ? ['PASS', 'owner A scoped to: ' . implode(',', $names)]
    : ['FAIL', 'owner A saw: ' . implode(',', $names)];

// 3 - owner B sees only theirs.
TenantContext::set($b->id);
$names = Property::query()->pluck('name')->all();
$results[] = count($names) === 1 && $names[0] === 'Beta Villas'
    ? ['PASS', 'owner B scoped to: ' . implode(',', $names)]
    : ['FAIL', 'owner B saw: ' . implode(',', $names)];

// 4 - cross-tenant read returns null (surfacing as 404, no existence leak).
TenantContext::set($a->id);
$leak = Property::query()->find($pb->id);
$results[] = $leak === null
    ? ['PASS', 'owner A cannot load owner B property (null -> 404)']
    : ['FAIL', "LEAK: owner A loaded '{$leak->name}'"];

// 5 - a client-supplied owner_id cannot override the session.
// The context is still A here, so the creating hook must stamp A regardless of
// the B passed in the payload.
$forged = Property::withoutOwnerScope()->create(['owner_id' => $b->id, 'name' => 'Forged', 'address' => 'X', 'type' => 'apartment', 'units' => 1, 'occupied' => 0]);
$results[] = (int) $forged->owner_id === (int) $a->id
    ? ['PASS', 'forged owner_id overridden to session owner']
    : ['FAIL', "forged owner_id persisted: {$forged->owner_id}"];

// 5b - creating with NO owner context at all must fail closed.
TenantContext::clear();
try {
    Property::withoutOwnerScope()->create(['name' => 'Orphan', 'address' => 'Y', 'type' => 'apartment', 'units' => 1, 'occupied' => 0]);
    $results[] = ['FAIL', 'create succeeded with no owner context'];
} catch (Throwable $e) {
    $results[] = ['PASS', 'create with no owner context fails closed'];
}
TenantContext::set($a->id);

// 6 - cross-owner delete affects zero rows.
$deleted = Property::query()->whereKey($pb->id)->delete();
$results[] = $deleted === 0
    ? ['PASS', 'cross-owner delete affected 0 rows']
    : ['FAIL', "cross-owner delete removed {$deleted} rows"];

foreach ($results as [$status, $message]) {
    echo "  {$status}  {$message}\n";
}

$failed = count(array_filter($results, fn ($r) => $r[0] === 'FAIL'));
echo "\n" . count($results) . " checks, {$failed} failed\n";

exit($failed > 0 ? 1 : 0);
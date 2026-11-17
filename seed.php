<?php
// Seed sample data into the database using DB facade
require 'laravel/backend/vendor/autoload.php';
$app = require_once 'laravel/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check current state
echo "Current counts:\n";
$tables = ['bills', 'bill_items', 'admins', 'owners', 'tenants', 'properties', 'houses', 'caretakers'];
foreach ($tables as $t) {
    echo "$t: " . DB::table($t)->count() . " rows\n";
}

// Insert sample owners using DB facade
echo "\nInserting owners...\n";
for ($i = 1; $i <= 5; $i++) {
    DB::table('owners')->insert([
        'name' => "Owner $i",
        'email' => "owner$i@rentflow.test",
        'password' => '$2y$12$k7L1uuM7a4WvzuAz88B1juz6Dh0Hs1GNEf5jZZ5NrJ7SuOfPT4FRG',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "Owners: " . DB::table('owners')->count() . " rows\n";

// Insert sample tenants using DB facade
echo "\nInserting tenants...\n";
for ($i = 1; $i <= 3; $i++) {
    DB::table('tenants')->insert([
        'name' => "Tenant $i",
        'email' => "tenant$i@rentflow.test",
        'password' => '$2y$12$k7L1uuM7a4WvzuAz88B1juz6Dh0Hs1GNEf5jZZ5NrJ7SuOfPT4FRG',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "Tenants: " . DB::table('tenants')->count() . " rows\n";

// Insert sample properties using DB facade
echo "\nInserting properties...\n";
for ($i = 1; $i <= 3; $i++) {
    DB::table('properties')->insert([
        'owner_id' => $i,
        'name' => "Property $i",
        'address' => "Address $i",
        'type' => ' apartment',
        'units' => 2 + $i,
        'occupied' => 1 + $i,
        'rent' => 5000 + $i * 1000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "Properties: " . DB::table('properties')->count() . " rows\n";

// Insert sample houses using DB facade
echo "\nInserting houses...\n";
for ($i = 1; $i <= 5; $i++) {
    DB::table('houses')->insert([
        'property_id' => ceil($i / 2),
        'unit' => "Unit $i",
        'rent' => 2000 + $i * 500,
        'status' => 'occupied',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "Houses: " . DB::table('houses')->count() . " rows\n";

// Insert sample bills using DB facade
echo "\nInserting bills...\n";
$month = '2026-08';
for ($i = 1; $i <= 5; $i++) {
    $houseId = ceil($i / 2);
    $ownerId = $i;
    DB::table('bills')->insert([
        'owner_id' => $ownerId,
        'house_id' => $houseId,
        'month' => $month,
        'type' => 'Rent',
        'rent' => 5000 + $i * 1000,
        'water' => 200,
        'electricity' => 300,
        'total' => 5500 + $i * 1000,
        'status' => 'paid',
        'due_date' => '2026-08-05',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "Bills: " . DB::table('bills')->count() . " rows\n";

// Insert bill items - need the bill_id, let's get it after inserting
$lastBillId = DB::table('bills')->max('id');

echo "\nBill items (using bill_id $lastBillId):\n";
for ($i = 1; $i <= 5; $i++) {
    DB::table('bill_items')->insert([
        'owner_id' => $i,
        'bill_id' => $lastBillId,
        'type' => 'Rent',
        'description' => 'Monthly Rent',
        'amount' => 5000 + $i * 1000,
        'paid' => 5000 + $i * 1000,
        'status' => 'paid',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "Bill items: " . DB::table('bill_items')->count() . " rows\n";

echo "\nDone! Sample data inserted.\n";
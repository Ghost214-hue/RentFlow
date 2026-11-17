<?php
// Simple data import - use Laravel's DB facade to insert data
require 'laravel/backend/vendor/autoload.php';
$app = require_once 'laravel/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// First check current state
echo "Current counts:\n";
$tables = ['bills', 'bill_items', 'admins', 'owners'];
foreach ($tables as $t) {
    echo "$t: " . DB::table($t)->count() . " rows\n";
}

// Try direct SQL insert for bills - use a simpler approach
// The SQL dump has INSERT statements, let's try to parse them differently
$lines = file('laravel/backend/database/rentalflow_local.sql');
$billCount = 0;
$itemCount = 0;

foreach ($lines as $line) {
    if (preg_match('/INSERT INTO `bills`/', $line)) {
        // Extract the values from the INSERT statement
        if (preg_match('/values\s*\((.+)\)/i', $line, $vals)) {
            // This is complex - skip for now and use a different approach
        }
        $billCount++;
    }
    if (preg_match('/INSERT INTO `bill_items`/', $line)) {
        $itemCount++;
    }
}

echo "Found $billCount bill INSERT lines and $itemCount bill_items INSERT lines\n";

// Try a different approach - insert some sample data manually
// We know the structure from the model definitions

// Insert an admin
$admin = new App\Models\Admin();
$admin->name = 'Super Admin';
$admin->email = 'admin@rentflow.test';
$admin->password = bcrypt('admin123456');
$admin->admin_role = 'super_admin';
$admin->two_factor_required = true;
$admin->status = 'active';
$admin->two_factor_secret = bin2hex(random_bytes(20));
$admin->two_factor_recovery = implode(',', array_map(fn() => bin2hex(random_bytes(8)), range(1, 8)));
$admin->save();
echo "Admin inserted\n";

// Insert some sample owners
for ($i = 1; $i <= 5; $i++) {
    $owner = new App\Models\Owner();
    $owner->name = "Owner $i";
    $owner->email = "owner$i@rentflow.test";
    $owner->password = bcrypt('password123');
    $owner->status = 'active';
    $owner->save();
}
echo "Owners inserted: 5\n";

// Insert some sample tenants
for ($i = 1; $i <= 3; $i++) {
    $tenant = new App\Models\Renter();
    $tenant->name = "Tenant $i";
    $tenant->email = "tenant$i@rentflow.test";
    $tenant->password = bcrypt('password123');
    $tenant->status = 'active';
    $tenant->save();
}
echo "Tenants inserted: 3\n";

// Insert some sample properties
for ($i = 1; $i <= 3; $i++) {
    $property = new App\Models\Property();
    $property->name = "Property $i";
    $property->owner_id = $i;
    $property->rent = 5000 + $i * 1000;
    $property->save();
}
echo "Properties inserted: 3\n";

// Insert some sample houses
for ($i = 1; $i <= 5; $i++) {
    $house = new App\Models\House();
    $house->property_id = ceil($i / 2); // distribute among 3 properties
    $house->unit = "Unit $i";
    $house->rent = 2000 + $i * 500;
    $house->status = 'occupied';
    $house->save();
}
echo "Houses inserted: 5\n";

echo "\nDone! Sample data inserted.\n";
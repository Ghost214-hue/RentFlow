<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
|
| These cover the consolidation of the five legacy report screens (portfolio,
| bills, financial, tenancy/vacancy, complaints) into one page, plus the
| maintenance aggregate that did not exist before.
|
| Two rules are load-bearing:
|   1. A renter never reaches a report: every figure is portfolio-wide.
|   2. A caretaker sees ONLY their assigned properties -- the same restriction
|      BillController applies. Reports used to skip that and expose the owner's
|      whole portfolio to a caretaker assigned to a single building, which
|      ReportScope now closes.
|
*/

use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\Complaint;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Property;
use App\Models\Renter;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    TenantContext::set(null);
    $this->owner = Owner::factory()->withPassword('secret123')->create();

    TenantContext::set((int) $this->owner->id);
    $this->propertyA = Property::factory()->create(['name' => 'Apartment One']);
    $this->propertyB = Property::factory()->create(['name' => 'Apartment Two']);

    $this->houseA = House::factory()->forProperty($this->propertyA)->occupied()
        ->create(['unit' => 'A1', 'rent' => '40000.00']);
    $this->houseB = House::factory()->forProperty($this->propertyB)->occupied()
        ->create(['unit' => 'B1', 'rent' => '60000.00']);

    $this->renterA = Renter::factory()->inHouse($this->houseA)->create(['name' => 'Ada A']);
    $this->renterB = Renter::factory()->inHouse($this->houseB)->create(['name' => 'Bob B']);

    TenantContext::clear();
});

afterEach(function (): void {
    TenantContext::clear();
});

/** Seed a bill with a rent line, allocated against by a settled payment. */
function seedBill(
    Renter $renter,
    string $total,
    string $paid,
    string $status = 'pending',
    ?string $due = null,
    ?string $month = null,
): Bill {
    TenantContext::set((int) $renter->owner_id);

    // bills is unique per house per month, so a caller wanting a second bill for
    // the same renter has to move the month.
    [$bill, $item] = Bill::factory()->forTenant($renter)->createWithItem([
        'month' => $month ?? CarbonImmutable::now()->format('Y-m'),
        'rent' => $total,
        'water' => '0.00',
        'electricity' => '0.00',
        'total' => $total,
        'status' => $status,
        'due_date' => $due ?? CarbonImmutable::now()->addDays(7)->toDateString(),
    ]);

    if ($paid !== '0.00') {
        $payment = Payment::factory()->forTenant($renter)->ofAmount($paid)->create([
            'status' => 'completed',
            'method' => 'M-Pesa',
            'type' => 'Rent',
        ]);

        PaymentAllocation::query()->create([
            'payment_id' => $payment->getKey(),
            'bill_item_id' => $item->getKey(),
            'category' => 'Rent',
            'amount' => $paid,
        ]);
    }

    TenantContext::clear();

    return $bill;
}

// --- access ------------------------------------------------------------

it('refuses reports to a renter', function (): void {
    // Every figure is portfolio-wide and would expose other households.
    $this->actingAs($this->renterA)->get('/reports')->assertForbidden();
});

it('serves reports to an owner', function (): void {
    $this->actingAs($this->owner)->get('/reports')->assertOk();
});

it('serves reports to a caretaker', function (): void {
    TenantContext::set((int) $this->owner->id);
    $caretaker = Caretaker::factory()->assignedTo([(int) $this->propertyA->id])->create();
    TenantContext::clear();

    $this->actingAs($caretaker)->get('/reports')->assertOk();
});

/*
 * THE REGRESSION THIS PAGE FIXED.
 *
 * A caretaker is restricted to their assigned properties everywhere else
 * (BillController), so a report that ignored that would show them the owner's
 * entire portfolio -- both buildings, all renters, all the money.
 */
it('scopes a caretaker report to their assigned properties only', function (): void {
    seedBill($this->renterA, '40000.00', '0.00');
    seedBill($this->renterB, '60000.00', '0.00');

    TenantContext::set((int) $this->owner->id);
    $caretaker = Caretaker::factory()->assignedTo([(int) $this->propertyA->id])->create();
    TenantContext::clear();

    $props = $this->actingAs($caretaker)->get('/reports')->viewData('page')['props'];

    // One building, one renter's bill -- not the owner's whole portfolio.
    expect((int) $props['summary']['counts']['houses'])->toBe(1)
        ->and((int) $props['summary']['counts']['properties'])->toBe(1)
        ->and($props['summary']['money']['billed_to_date'])->toBe('40000.00');
});

it('shows the owner both buildings while their caretaker sees one', function (): void {
    seedBill($this->renterA, '40000.00', '0.00');
    seedBill($this->renterB, '60000.00', '0.00');

    TenantContext::set((int) $this->owner->id);
    $caretaker = Caretaker::factory()->assignedTo([(int) $this->propertyA->id])->create();
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect((int) $props['summary']['counts']['houses'])->toBe(2)
        ->and((int) $props['summary']['counts']['properties'])->toBe(2)
        ->and($props['summary']['money']['billed_to_date'])->toBe('100000.00');
});

it('gives an unassigned caretaker nothing rather than everything', function (): void {
    seedBill($this->renterA, '40000.00', '0.00');

    TenantContext::set((int) $this->owner->id);
    $stranger = Caretaker::factory()->assignedTo([])->create();
    TenantContext::clear();

    $props = $this->actingAs($stranger)->get('/reports')->viewData('page')['props'];

    // Failing OPEN here would hand an unassigned caretaker the whole portfolio.
    expect((int) $props['summary']['counts']['houses'])->toBe(0)
        ->and($props['summary']['money']['billed_to_date'])->toBe('0.00');
});

it('keeps one owner out of another owner reports', function (): void {
    seedBill($this->renterA, '40000.00', '0.00');

    $other = Owner::factory()->withPassword('secret123')->create();
    TenantContext::set((int) $other->id);
    $otherProperty = Property::factory()->create();
    $otherHouse = House::factory()->forProperty($otherProperty)->occupied()->create(['rent' => '99000.00']);
    Renter::factory()->inHouse($otherHouse)->create();
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect((int) $props['summary']['counts']['houses'])->toBe(2)
        ->and($props['summary']['money']['billed_to_date'])->toBe('40000.00');
});

// --- money stays money -------------------------------------------------

it('reports every money value as a decimal string', function (): void {
    seedBill($this->renterA, '40000.00', '15000.00', 'partial');

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    $walk = function (array $node) use (&$walk): void {
        foreach ($node as $value) {
            if (is_array($value)) {
                $walk($value);

                continue;
            }

            // Any decimal-looking string must be a properly scaled string, and
            // no figure may arrive as a JS number.
            if (is_string($value) && preg_match('/^\d+\.\d{1,2}$/', $value) === 1) {
                expect($value)->toMatch('/^\d+\.\d{2}$/');
            }
        }
    };

    $walk($props);
    expect($props['summary']['money']['billed_to_date'])->toBe('40000.00')
        ->and($props['billing']['money']['billed'])->toBe('40000.00');
});

it('never divides by a float when computing a rate', function (): void {
    seedBill($this->renterA, '300.00', '100.00', 'partial');

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    // 100/300 = 33.33..., rounded once, on the server, as a string.
    expect($props['summary']['money']['collection_rate'])->toBe('33.33');
});

it('rounds rather than truncating a repeating rate', function (): void {
    seedBill($this->renterA, '3.00', '2.00', 'partial');

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    // 2/3 = 66.666..., which must read 66.67 and not 66.66.
    expect($props['summary']['money']['collection_rate'])->toBe('66.67');
});

it('reports a zero collection rate instead of dividing by zero', function (): void {
    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    // Nothing billed is 0.00%, never 100% and never a division error.
    expect($props['summary']['money']['collection_rate'])->toBe('0.00');
});

it('reports a zero occupancy rate for an empty portfolio', function (): void {
    // A separate owner with no units at all: 0/0 must read 0.00, and
    // certainly must not read 100%.
    $empty = Owner::factory()->withPassword('secret123')->create();
    TenantContext::set((int) $empty->id);
    Property::factory()->create();
    TenantContext::clear();

    $props = $this->actingAs($empty)->get('/reports')->viewData('page')['props'];

    expect($props['occupancy']['units']['occupancy_rate'])->toBe('0.00')
        ->and($props['occupancy']['units']['vacancy_rate'])->toBe('0.00');
});

// --- arrears -----------------------------------------------------------

it('derives overdue from the due date, not the stored status', function (): void {
    // Left on 'pending' and long past due: nobody bothered to flip it.
    seedBill($this->renterA, '20000.00', '0.00', 'pending', CarbonImmutable::now()->subDays(45)->toDateString());

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect((int) $props['billing']['counts']['overdue'])->toBe(1)
        ->and($props['billing']['arrears']['amount'])->toBe('20000.00');
});

it('does not count a bill as overdue before its due date', function (): void {
    seedBill($this->renterA, '20000.00', '0.00', 'pending', CarbonImmutable::now()->addDays(5)->toDateString());

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect((int) $props['billing']['counts']['overdue'])->toBe(0)
        ->and($props['billing']['arrears']['amount'])->toBe('0.00');
});

it('counts only the unpaid part of a part-paid bill as arrears', function (): void {
    seedBill($this->renterA, '20000.00', '8000.00', 'partial', CarbonImmutable::now()->subDays(10)->toDateString());

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect($props['billing']['arrears']['amount'])->toBe('12000.00');
});

it('excludes a fully paid but stale-status bill from arrears', function (): void {
    // Paid in full yet still flagged pending: it owes nothing, so counting it
    // would overstate what the owner is owed.
    seedBill($this->renterA, '20000.00', '20000.00', 'pending', CarbonImmutable::now()->subDays(90)->toDateString());

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect((int) $props['billing']['counts']['overdue'])->toBe(0)
        ->and($props['billing']['arrears']['amount'])->toBe('0.00');
});

it('ages arrears into buckets', function (): void {
    // bills is unique per house per month, so the third bill moves month rather
    // than colliding with the first.
    seedBill($this->renterA, '10000.00', '0.00', 'pending', CarbonImmutable::now()->subDays(10)->toDateString());
    seedBill($this->renterB, '20000.00', '0.00', 'pending', CarbonImmutable::now()->subDays(45)->toDateString());
    seedBill(
        $this->renterA,
        '30000.00',
        '0.00',
        'pending',
        CarbonImmutable::now()->subDays(120)->toDateString(),
        CarbonImmutable::now()->subMonth()->format('Y-m'),
    );

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];
    $aging = collect($props['billing']['arrears']['aging'])->keyBy('label');

    expect($aging['1-30 days']['amount'])->toBe('10000.00')
        ->and($aging['31-60 days']['amount'])->toBe('20000.00')
        ->and($aging['90+ days']['amount'])->toBe('30000.00')
        ->and((int) $props['billing']['arrears']['count'])->toBe(3)
        ->and($props['billing']['arrears']['oldest_month'])->not->toBeNull();
});

it('shares out arrears across the aging buckets on the server', function (): void {
    seedBill($this->renterA, '25000.00', '0.00', 'pending', CarbonImmutable::now()->subDays(5)->toDateString());
    seedBill($this->renterB, '75000.00', '0.00', 'pending', CarbonImmutable::now()->subDays(200)->toDateString());

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];
    $aging = collect($props['billing']['arrears']['aging'])->keyBy('label');

    // 25% / 75%, computed server-side so the browser never divides money.
    expect($aging['1-30 days']['share'])->toBe('25.00')
        ->and($aging['90+ days']['share'])->toBe('75.00');
});

// --- occupancy ---------------------------------------------------------

it('reports occupancy and vacancy rates', function (): void {
    TenantContext::set((int) $this->owner->id);
    House::factory()->forProperty($this->propertyA)->create(['unit' => 'A2', 'rent' => '30000.00']);
    House::factory()->forProperty($this->propertyA)->create(['unit' => 'A3', 'rent' => '20000.00']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    // 2 occupied of 4 units.
    expect($props['occupancy']['units']['occupancy_rate'])->toBe('50.00')
        ->and($props['occupancy']['units']['vacancy_rate'])->toBe('50.00')
        ->and((int) $props['occupancy']['units']['vacant'])->toBe(2);
});

it('values vacancy as rent lost', function (): void {
    TenantContext::set((int) $this->owner->id);
    House::factory()->forProperty($this->propertyA)->create(['unit' => 'A2', 'rent' => '30000.00']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    // A vacancy rate alone does not say whether to chase tenants or fill units.
    expect($props['occupancy']['money']['lost_monthly'])->toBe('30000.00');
});

it('lists the vacant units', function (): void {
    TenantContext::set((int) $this->owner->id);
    House::factory()->forProperty($this->propertyA)->create(['unit' => 'A2', 'rent' => '30000.00']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect($props['vacantUnits'])->toHaveCount(1)
        ->and($props['vacantUnits'][0]['unit'])->toBe('A2')
        ->and($props['vacantUnits'][0]['rent'])->toBe('30000.00');
});

// --- revenue -----------------------------------------------------------

it('keeps failed and pending payments out of collected money', function (): void {
    TenantContext::set((int) $this->renterA->owner_id);
    Payment::factory()->forTenant($this->renterA)->ofAmount('5000.00')->create(['status' => 'pending']);
    Payment::factory()->forTenant($this->renterA)->ofAmount('3000.00')->create(['status' => 'failed']);
    Payment::factory()->forTenant($this->renterA)->ofAmount('7000.00')->create([
        'status' => 'completed', 'method' => 'Cash', 'type' => 'Rent',
    ]);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    // Counting in-flight money is how a portfolio looks solvent while rent is unpaid.
    expect($props['revenue']['collected'])->toBe('7000.00')
        ->and($props['revenue']['pending'])->toBe('5000.00')
        ->and($props['revenue']['failed'])->toBe('3000.00')
        ->and($props['revenue']['in_flight'])->toBe('8000.00');
});

it('splits revenue by payment method', function (): void {
    TenantContext::set((int) $this->renterA->owner_id);
    Payment::factory()->forTenant($this->renterA)->ofAmount('6000.00')
        ->create(['status' => 'completed', 'method' => 'M-Pesa', 'type' => 'Rent']);
    Payment::factory()->forTenant($this->renterA)->ofAmount('4000.00')
        ->create(['status' => 'completed', 'method' => 'Cash', 'type' => 'Deposit']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];
    $methods = collect($props['revenue']['method_share'])->keyBy('label');

    expect($methods)->toHaveKeys(['M-Pesa', 'Cash'])
        ->and($methods['M-Pesa']['share'])->toBe('60.00')
        ->and($methods['Cash']['share'])->toBe('40.00');
});

// --- complaints --------------------------------------------------------

it('reports a resolution rate and high priority backlog', function (): void {
    TenantContext::set((int) $this->owner->id);
    Complaint::factory()->create(['status' => 'resolved', 'priority' => 'high']);
    Complaint::factory()->create(['status' => 'open', 'priority' => 'high']);
    Complaint::factory()->create(['status' => 'open', 'priority' => 'low']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect((int) $props['complaints']['total'])->toBe(3)
        ->and((int) $props['complaints']['resolved'])->toBe(1)
        ->and($props['complaints']['resolution_rate'])->toBe('33.33')
        ->and((int) $props['complaints']['high_priority_open'])->toBe(1);
});

it('does not count a resolved high-priority complaint as open', function (): void {
    TenantContext::set((int) $this->owner->id);
    Complaint::factory()->create(['status' => 'resolved', 'priority' => 'high']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect((int) $props['complaints']['high_priority_open'])->toBe(0);
});

// --- maintenance (new in this port) ------------------------------------

it('separates spent repair cost from cost still committed', function (): void {
    TenantContext::set((int) $this->owner->id);
    MaintenanceRecord::factory()->create(['status' => 'completed', 'cost' => '10000.00']);
    MaintenanceRecord::factory()->create(['status' => 'completed', 'cost' => '20000.00']);
    MaintenanceRecord::factory()->create(['status' => 'pending', 'cost' => '50000.00']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    // Mixing them makes both numbers meaningless.
    expect($props['maintenance']['money']['spent'])->toBe('30000.00')
        ->and($props['maintenance']['money']['committed'])->toBe('50000.00')
        ->and($props['maintenance']['money']['per_job'])->toBe('15000.00');
});

it('reports a zero average repair cost rather than dividing by zero', function (): void {
    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect($props['maintenance']['money']['per_job'])->toBe('0.00');
});

it('rounds a repeating average repair cost', function (): void {
    TenantContext::set((int) $this->owner->id);
    MaintenanceRecord::factory()->create(['status' => 'completed', 'cost' => '10000.00']);
    MaintenanceRecord::factory()->count(2)->create(['status' => 'completed', 'cost' => '0.00']);
    TenantContext::clear();

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    // 10000/3 = 3333.333..., which must round to 3333.33 not truncate.
    expect($props['maintenance']['money']['per_job'])->toBe('3333.33');
});

// --- month filter ------------------------------------------------------

it('defaults to a month that has bills', function (): void {
    seedBill($this->renterA, '40000.00', '0.00');

    $props = $this->actingAs($this->owner)->get('/reports')->viewData('page')['props'];

    expect($props['month'])->toBe(CarbonImmutable::now()->format('Y-m'))
        ->and($props['months'])->toContain($props['month']);
});

it('ignores a malformed month rather than erroring', function (): void {
    seedBill($this->renterA, '40000.00', '0.00');

    // A junk filter must not take the reports page down.
    $props = $this->actingAs($this->owner)
        ->get('/reports?month=not-a-month')
        ->viewData('page')['props'];

    expect($props['month'])->toBe(CarbonImmutable::now()->format('Y-m'));
});

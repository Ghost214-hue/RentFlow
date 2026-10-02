<?php
/**
 * Billing / Payments / Balance / Invoice / Email consistency regression tests.
 *
 * Run:  php backend/tests/billing_consistency_test.php
 *
 * Section 1 (pure logic) runs anywhere.
 * Section 2 (DB integration) runs only when a MySQL test database is
 * reachable (set BILLING_TEST_DB env var, default rentalflow_test).
 */
declare(strict_types=1);

require_once __DIR__ . '/../app/Services/BillingService.php';
require_once __DIR__ . '/../app/Services/EmailService.php';
require_once __DIR__ . '/../app/Core/Env.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$pass = 0;
$fail = 0;
$failures = [];

function check(string $name, bool $cond, string $detail = ''): void
{
    global $pass, $fail, $failures;
    if ($cond) {
        $pass++;
        echo "  PASS  {$name}\n";
    } else {
        $fail++;
        $failures[] = $name . ($detail ? " — {$detail}" : '');
        echo "  FAIL  {$name}" . ($detail ? " — {$detail}" : '') . "\n";
    }
}

use App\Services\BillingService;
use App\Services\EmailService;

echo "=== Section 1: Authoritative financial derivation (pure logic) ===\n";

// Test 1 — Fully paid previous month
$s = BillingService::finalizeFinancials(6500.0, 0.0, 0.0, 0.0);
check('T1: fully-paid prior month → opening balance 0, total 6500',
    $s['opening_balance'] === 0.0 && $s['total'] === 6500.0 && $s['balance'] === 6500.0,
    json_encode($s));

// Test 2 — Outstanding previous balance (carried once)
$s = BillingService::finalizeFinancials(6500.0, 0.0, 500.0, 0.0);
check('T2: outstanding prior balance carried once → total 7000',
    $s['total'] === 7000.0 && $s['opening_balance'] === 500.0 && $s['balance'] === 7000.0,
    json_encode($s));

// Test 3 — Partial payment
$s = BillingService::finalizeFinancials(13000.0, 6500.0);
check('T3: partial payment → paid 6500, balance 6500, status partial',
    $s['paid'] === 6500.0 && $s['balance'] === 6500.0 && $s['status'] === 'partial',
    json_encode($s));

// Test 4 — Overpayment becomes credit, never a negative balance
$s = BillingService::finalizeFinancials(6000.0, 7000.0);
check('T4: overpayment → balance 0, status paid (credit tracked at tenant level)',
    $s['balance'] === 0.0 && $s['status'] === 'paid' && $s['paid'] === 6000.0,
    json_encode($s));

// Test 5 — Zero amount with payment must not produce -7000
$s = BillingService::finalizeFinancials(0.0, 7000.0);
check('T5: zero bill + 7000 payment → balance 0, not -7000, status paid',
    $s['balance'] === 0.0 && $s['status'] === 'paid' && $s['total'] === 0.0,
    json_encode($s));

// Test 8 — Opening balance appears exactly once when aggregating
$aggregated = BillingService::finalizeFinancials(6500.0, 0.0, 500.0, 0.0);
$aggregated2 = BillingService::finalizeFinancials(6500.0, 0.0, $aggregated['opening_balance'], 0.0);
check('T8: opening balance is idempotent across derivations (appears once)',
    $aggregated['total'] === $aggregated2['total'] && $aggregated['total'] === 7000.0);

// Test 18 — Status derivation incl. overdue
check('T18a: status pending → overdue after due date',
    BillingService::deriveStatusWithDueDate(5000.0, 0.0, date('Y-m-d', strtotime('-10 days'))) === 'overdue');
check('T18b: paid bill is never overdue',
    BillingService::deriveStatusWithDueDate(5000.0, 5000.0, date('Y-m-d', strtotime('-10 days'))) === 'paid');
check('T18c: fully paid → paid; none → pending; some → partial',
    BillingService::deriveStatus(6000.0, 6000.0) === 'paid'
    && BillingService::deriveStatus(6000.0, 0.0) === 'pending'
    && BillingService::deriveStatus(6000.0, 2500.0) === 'partial');

// Test 10 — Email placeholder validation
$dirty = "Dear {{recipient_name}},\n\nAmount Due: {{amount}}\nNote: {{recipient_note}}\nRef: \${account_ref}\nBalance: {{balance}}\n";
$clean = EmailService::stripUnresolvedPlaceholders($dirty);
check('T10a: no {{...}} or ${...} survives sanitization',
    !EmailService::containsUnresolvedPlaceholders($clean), $clean);
check('T10b: known variables are replaced before the safety net',
    strpos(EmailService::replaceVariables('Hello {{tenant}}, balance {{balance}}', ['tenant' => 'Ada', 'balance' => '500.00']), '{{') === false);
check('T10c: detector flags unresolved placeholders',
    EmailService::containsUnresolvedPlaceholders('x {{recipient_note}} y')
    && EmailService::containsUnresolvedPlaceholders('x ${foo} y'));

// Edge — floating-point epsilon treated as fully paid
$s = BillingService::finalizeFinancials(100.0, 99.995);
check('T-edge: floating-point epsilon treated as fully paid',
    $s['status'] === 'paid', json_encode($s));

echo "\n=== Section 2: DB integration (optional) ===\n";
$dbName = getenv('BILLING_TEST_DB') ?: 'rentalflow_test';
$canRunDb = false;
try {
    $cfg = require __DIR__ . '/../config/database.php';
    $tmp = new mysqli($cfg['host'], $cfg['username'], $cfg['password'], $dbName, (int)($cfg['port'] ?? 3306));
    $tmp->close();
    $canRunDb = true;
} catch (Throwable $e) {
    echo "  SKIP  MySQL '{$dbName}' not reachable ({$e->getMessage()}). Integration tests skipped.\n";
}

// Sentinel fixture ids, outside the range of real data. They must be POSITIVE
// because bills.house_id is INT UNSIGNED with a foreign key to houses(id).
const FIX_OWNER   = 900001;
const FIX_TENANT  = 900002;
const FIX_HOUSE   = 900003;
const FIX_PROPERTY = 900004;

if ($canRunDb) {
    // Test 6 — duplicate bill generation (Test 7's invoice/page/email equality
    // is guaranteed structurally: all three now share getAuthoritativeSnapshot).
    require_once __DIR__ . '/../app/Core/Database.php';
    $db = Database::getInstance();

    /** Remove every fixture row, children first so FK constraints are satisfied. */
    $purgeFixtures = function () use ($db) {
        $o = FIX_OWNER;
        $db->query("DELETE FROM payment_allocations WHERE payment_id IN (SELECT id FROM payments WHERE owner_id = $o)");
        $db->query("DELETE FROM payments WHERE owner_id = $o");
        $db->query("DELETE FROM bill_items WHERE bill_id IN (SELECT id FROM bills WHERE owner_id = $o)");
        $db->query("DELETE FROM bills WHERE owner_id = $o");
        $db->query("UPDATE houses SET tenant_id = NULL WHERE owner_id = $o");
        $db->query("DELETE FROM houses WHERE owner_id = $o");
        $db->query("DELETE FROM tenants WHERE owner_id = $o");
        $db->query("DELETE FROM properties WHERE owner_id = $o");
        $db->query("DELETE FROM owners WHERE id = $o");
    };

    // owners is the root parent of nearly every table via owner_id FK, so the
    // fixture must seed it first. owners.id is not AUTO_INCREMENT.
    $purgeFixtures();
    $db->insert('owners', [
        'id' => FIX_OWNER, 'name' => 'Fixture Owner',
        'email' => 'owner@example.test', 'password' => '$2y$10$fixturefixturefixturefixturefixturefixturefixturefixturefi',
    ]);
    $propertyId = $db->insert('properties', [
        'owner_id' => FIX_OWNER, 'name' => 'Fixture Property', 'address' => 'Test',
        'type' => 'apartment', 'units' => 1, 'occupied' => 1,
    ]);
    $houseId = $db->insert('houses', [
        'owner_id' => FIX_OWNER, 'property_id' => $propertyId, 'unit' => 'FIX-T6',
        'type' => 'flat', 'status' => 'occupied', 'rent' => 6500.0,
    ]);

    $svc = new BillingService();
    $id1 = $svc->createBillWithItems(FIX_OWNER, $houseId, null, '2099-01', '2099-01-05', [
        ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => 6500.0],
    ]);
    $id2 = $svc->createBillWithItems(FIX_OWNER, $houseId, null, '2099-01', '2099-01-05', [
        ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => 6500.0],
    ]);
    check('T6: duplicate generation returns the same bill id', $id1 === $id2);
    $count = $db->fetchOne("SELECT COUNT(*) c FROM bill_items WHERE bill_id = ?", [$id1]);
    check('T6: no duplicate bill items after re-generation', (int)$count['c'] === 1);
    $purgeFixtures();

    // =============================================================
    // Section 3: Payment allocation across months (defect 1)
    //
    // THE RULE: a payment is applied to
    //   1. the explicitly chosen month's bill, first
    //   2. then the oldest unpaid bills in month order
    //   3. the remainder becomes renter credit
    // It must NEVER be limited to the single bill whose month happens
    // to equal the payment month -- that is what left arrears unpaid.
    // =============================================================
    echo "\n=== Section 3: Payment allocation across months (defect 1) ===\n";

    $OWNER = FIX_OWNER;
    $TENANT = FIX_TENANT;
    $HOUSE  = FIX_HOUSE;
    $PROPERTY = FIX_PROPERTY;

    /** Clean two-month arrears: July + August, both unpaid, 5,000 each. */
    // The property/house/tenant ids are AUTO_INCREMENT, so we must use the ids the
    // database actually assigns rather than guessing them.
    $seedArrears = function () use ($db, $purgeFixtures, $OWNER) {
        $purgeFixtures();

        $db->insert('owners', [
            'id' => $OWNER, 'name' => 'Fixture Owner',
            'email' => 'owner@example.test',
            'password' => '$2y$10$fixturefixturefixturefixturefixturefixturefixturefixturefi',
        ]);
        $propertyId = $db->insert('properties', [
            'owner_id' => $OWNER, 'name' => 'Fixture Property', 'address' => 'Test',
            'type' => 'apartment', 'units' => 1, 'occupied' => 1,
        ]);
        $tenantId = $db->insert('tenants', [
            'owner_id' => $OWNER, 'property_id' => $propertyId,
            'name' => 'Fixture Renter', 'email' => 'fixture@example.test',
            'balance' => 0, 'credit' => 0, 'status' => 'active',
        ]);
        $houseId = $db->insert('houses', [
            'owner_id' => $OWNER, 'property_id' => $propertyId, 'unit' => 'FIX-1',
            'type' => 'flat', 'status' => 'occupied', 'tenant_id' => $tenantId, 'rent' => 5000.0,
        ]);

        $svc = new BillingService();
        $jul = $svc->createBillWithItems($OWNER, $houseId, $tenantId, '2099-07', '2099-07-05', [
            ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => 5000.0],
        ]);
        $aug = $svc->createBillWithItems($OWNER, $houseId, $tenantId, '2099-08', '2099-08-05', [
            ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => 5000.0],
        ]);
        // Return the real ids so callers allocate against actual rows.
        return ['jul' => $jul, 'aug' => $aug, 'tenant' => $tenantId, 'house' => $houseId];
    };

    /** Record a payment row for the fixture renter. */
    $makePayment = function (string $receipt, string $month, float $amount, string $status = 'completed', int $tenantId = 0, int $houseId = 0) use ($db, $OWNER) {
        return $db->insert('payments', [
            'owner_id' => $OWNER, 'tenant_id' => $tenantId, 'house_id' => $houseId,
            'month' => $month, 'amount' => $amount, 'type' => 'Rent',
            'method' => 'M-Pesa', 'date' => $month . '-06',
            'status' => $status, 'receipt' => $receipt,
        ]);
    };

    /** Total allocated to a bill across all of its items. */
    $billPaid = function (int $billId) use ($db) {
        $r = $db->fetchOne(
            "SELECT COALESCE(SUM(pa.amount),0) t FROM payment_allocations pa
             JOIN bill_items bi ON bi.id = pa.bill_item_id
             JOIN payments p ON p.id = pa.payment_id
             WHERE bi.bill_id = ? AND p.status IN ('confirmed','completed','paid')",
            [$billId]
        );
        return (float) $r['t'];
    };

    $svc = new BillingService();

    // --- T19: WITHOUT an explicit month, an August payment clears unpaid JULY first.
    // (With an explicit month that bill wins first — see T21.)
    $f = $seedArrears();
    $pid = $makePayment('FIX-19', '2099-08', 5000.0, 'completed', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 5000.0, '', 'Rent');
    check('T19: with no month chosen, the OLDEST unpaid bill (July) is settled first',
        abs($billPaid($f['jul']) - 5000.0) < 0.005 && $billPaid($f['aug']) == 0.0,
        'jul=' . $billPaid($f['jul']) . ' aug=' . $billPaid($f['aug']));

    // --- T19b: an explicit later month takes precedence over the older arrears
    $f = $seedArrears();
    $pid = $makePayment('FIX-19b', '2099-08', 5000.0, 'completed', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 5000.0, '2099-08', 'Rent');
    check('T19b: an explicitly chosen month beats older arrears',
        abs($billPaid($f['aug']) - 5000.0) < 0.005 && $billPaid($f['jul']) == 0.0,
        'jul=' . $billPaid($f['jul']) . ' aug=' . $billPaid($f['aug']));

    // --- T19c: the OLD behaviour (oldest-first) is what actually settles arrears
    // when the chosen month is already fully paid: money cascades onward.
    $f = $seedArrears();
    $pid = $makePayment('FIX-19c', '2099-08', 10000.0, 'completed', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 10000.0, '', 'Rent');
    check('T19c: a large payment settles every month in order',
        abs($billPaid($f['jul']) - 5000.0) < 0.005 && abs($billPaid($f['aug']) - 5000.0) < 0.005,
        'jul=' . $billPaid($f['jul']) . ' aug=' . $billPaid($f['aug']));

    // --- T20: more than one month's arrears cascades into the next month
    $f = $seedArrears();
    $pid = $makePayment('FIX-20', '2099-08', 12000.0, 'completed', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 12000.0, '2099-08', 'Rent');
    check('T20: overpayment cascades oldest-first then fills the next month',
        abs($billPaid($f['jul']) - 5000.0) < 0.005 && abs($billPaid($f['aug']) - 5000.0) < 0.005,
        'jul=' . $billPaid($f['jul']) . ' aug=' . $billPaid($f['aug']));

    // --- T21: the explicitly chosen month is honoured first
    $f = $seedArrears();
    $db->update('bills', ['month' => '2099-09'], 'id = ?', [$f['aug']]); // push Aug bill newest
    $pid = $makePayment('FIX-21', '2099-07', 5000.0, 'completed', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 5000.0, '2099-07', 'Rent');
    check('T21: the explicitly chosen month is paid first',
        abs($billPaid($f['jul']) - 5000.0) < 0.005, 'jul=' . $billPaid($f['jul']));

    // --- T22: remainder beyond ALL arrears becomes credit, is never lost
    $f = $seedArrears();
    $pid = $makePayment('FIX-22', '2099-08', 14000.0, 'completed', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 14000.0, '2099-08', 'Rent');
    $svc->recalcTenantCreditAndBalance($f['tenant']);
    $t = $db->fetchOne("SELECT balance, credit FROM tenants WHERE id = ?", [$f['tenant']]);
    check('T22: 14,000 against 10,000 arrears -> 4,000 credit, 0 balance',
        abs((float)$t['balance'] - 0.0) < 0.005 && abs((float)$t['credit'] - 4000.0) < 0.005,
        json_encode($t));

    // --- T23: payment arriving BEFORE the month's bills exist becomes credit
    $f = $seedArrears();
    $db->query("DELETE FROM bill_items WHERE bill_id IN (SELECT id FROM bills WHERE owner_id = $OWNER)");
    $db->query("DELETE FROM bills WHERE owner_id = $OWNER");
    $pid = $makePayment('FIX-23', '2099-08', 3000.0, 'completed', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 3000.0, '2099-08', 'Rent');
    $svc->recalcTenantCreditAndBalance($f['tenant']);
    $t = $db->fetchOne("SELECT balance, credit FROM tenants WHERE id = ?", [$f['tenant']]);
    check('T23: payment before the month\'s bills exist becomes credit',
        abs((float)$t['credit'] - 3000.0) < 0.005 && abs((float)$t['balance']) < 0.005,
        json_encode($t));

    // --- T24: allocation is idempotent (safe to re-run)
    $f = $seedArrears();
    $pid = $makePayment('FIX-24', '2099-08', 5000.0, 'completed', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 5000.0, '2099-08', 'Rent');
    $once = $billPaid($f['jul']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 5000.0, '2099-08', 'Rent');
    check('T24: re-allocating the same payment does not double-count',
        abs($billPaid($f['jul']) - $once) < 0.005, 'once=' . $once . ' twice=' . $billPaid($f['jul']));

    // --- T25: a pending payment allocates nothing
    $f = $seedArrears();
    $pid = $makePayment('FIX-25', '2099-08', 5000.0, 'pending', $f['tenant'], $f['house']);
    $svc->allocatePaymentToOutstandingBills($pid, $f['tenant'], 5000.0, '2099-08', 'Rent');
    check('T25: a pending payment allocates nothing',
        $billPaid($f['jul']) == 0.0 && $billPaid($f['aug']) == 0.0,
        'jul=' . $billPaid($f['jul']));

    // --- T26: every allocation traces back to exactly one real payment row
    $orphan = $db->fetchOne(
        "SELECT COUNT(*) c FROM payment_allocations pa
         LEFT JOIN payments p ON p.id = pa.payment_id
         LEFT JOIN bill_items bi ON bi.id = pa.bill_item_id
         WHERE pa.payment_id IN (SELECT id FROM payments WHERE owner_id = $OWNER)
           AND (p.id IS NULL OR bi.id IS NULL)"
    );
    check('T26: no orphaned allocation rows are created', (int)$orphan['c'] === 0, json_encode($orphan));

    // cleanup fixtures
    $purgeFixtures();
}

echo "\n=====================================\n";
echo "Results: {$pass} passed, {$fail} failed\n";
foreach ($failures as $f) {
    echo "  FAILED: {$f}\n";
}
exit($fail > 0 ? 1 : 0);


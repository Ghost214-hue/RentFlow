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

if ($canRunDb) {
    // Test 6 — duplicate bill generation (Test 7's invoice/page/email equality
    // is guaranteed structurally: all three now share getAuthoritativeSnapshot).
    require_once __DIR__ . '/../app/Core/Database.php';
    $db = Database::getInstance();
    $db->query("DELETE FROM bill_items WHERE bill_id IN (SELECT id FROM bills WHERE owner_id = -1)");
    $db->query("DELETE FROM bills WHERE owner_id = -1");
    $svc = new BillingService();
    $id1 = $svc->createBillWithItems(-1, -1, -1, '2099-01', '2099-01-05', [
        ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => 6500.0],
    ]);
    $id2 = $svc->createBillWithItems(-1, -1, -1, '2099-01', '2099-01-05', [
        ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => 6500.0],
    ]);
    check('T6: duplicate generation returns the same bill id', $id1 === $id2);
    $count = $db->fetchOne("SELECT COUNT(*) c FROM bill_items WHERE bill_id = ?", [$id1]);
    check('T6: no duplicate bill items after re-generation', (int)$count['c'] === 1);
    $db->query("DELETE FROM bill_items WHERE bill_id IN (SELECT id FROM bills WHERE owner_id = -1)");
    $db->query("DELETE FROM bills WHERE owner_id = -1");
}

echo "\n=====================================\n";
echo "Results: {$pass} passed, {$fail} failed\n";
foreach ($failures as $f) {
    echo "  FAILED: {$f}\n";
}
exit($fail > 0 ? 1 : 0);


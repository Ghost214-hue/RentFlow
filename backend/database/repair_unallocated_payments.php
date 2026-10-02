<?php
/**
 * Repair historical payments that never reached a bill (defect 1).
 *
 * The legacy PaymentController only allocated a payment to the single bill
 * whose month equalled the payment month. Any payment made while earlier
 * months were still unpaid left those bills untouched, so the renter's
 * arrears never cleared and the cached tenants.balance stayed wrong.
 *
 * This script finds settled payments whose allocations do not account for
 * their full amount and re-runs allocation with the corrected rule:
 *   chosen month first -> oldest unpaid bills -> remainder as credit
 *
 * SAFE TO RUN TWICE: allocation is idempotent per payment (it only allocates
 * what is still unallocated), and every affected row is reported.
 *
 * Usage:
 *   php backend/database/repair_unallocated_payments.php --dry-run
 *   php backend/database/repair_unallocated_payments.php
 *   php backend/database/repair_unallocated_payments.php --tenant=47
 *
 * NEVER run against production without taking a backup first.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Services/BillingService.php';

use App\Core\Database;
use App\Services\BillingService;

$options = getopt('', ['dry-run', 'tenant::']);
$dryRun = array_key_exists('dry-run', $options);
$onlyTenant = isset($options['tenant']) ? (int) $options['tenant'] : 0;

// Refuse to touch production unless the operator insists.
$dbName = (string) (getenv('BILLING_TEST_DB') ?: \App\Core\Env::get('DB_NAME', 'rentaflow'));
$isProduction = \App\Core\Env::get('APP_ENV', 'development') === 'production';
if ($isProduction && !getenv('I_UNDERSTAND_THIS_IS_PRODUCTION')) {
    fwrite(STDERR, "Refusing to run against a production database ($dbName).\n"
        . "Take a backup, then re-run with I_UNDERSTAND_THIS_IS_PRODUCTION=1.\n");
    exit(1);
}

$db = Database::getInstance();
$billing = new BillingService();

echo "RentFlow — repair unallocated payments\n";
echo "Database: {$dbName}\n";
echo "Mode:     " . ($dryRun ? 'DRY RUN (no writes)' : 'LIVE') . "\n";
if ($onlyTenant > 0) {
    echo "Scope:    tenant {$onlyTenant} only\n";
}
echo str_repeat('=', 62) . "\n";

// Settled payments whose allocations do not add up to the payment amount.
$sql = "SELECT p.id, p.owner_id, p.tenant_id, p.month, p.amount, p.receipt, p.type,
               COALESCE(a.allocated, 0) AS allocated
        FROM payments p
        LEFT JOIN (
            SELECT payment_id, SUM(amount) AS allocated
            FROM payment_allocations GROUP BY payment_id
        ) a ON a.payment_id = p.id
        WHERE p.status IN ('confirmed','completed','paid')
          AND COALESCE(a.allocated, 0) < p.amount - 0.005";
$params = [];

if ($onlyTenant > 0) {
    $sql .= " AND p.tenant_id = ?";
    $params[] = $onlyTenant;
}
$sql .= " ORDER BY p.owner_id, p.tenant_id, p.date, p.id";

$rows = $db->fetchAll($sql, $params);

if (empty($rows)) {
    echo "No unallocated payments found. Nothing to repair.\n";
    exit(0);
}

printf("%-6s %-8s %-9s %-10s %12s %12s %s\n", 'ID', 'TENANT', 'MONTH', 'RECEIPT', 'AMOUNT', 'ALLOCATED', 'UNALLOCATED');
echo str_repeat('-', 88) . "\n";

$totalUnallocated = 0.0;
$fixed = 0;
$skipped = 0;

foreach ($rows as $row) {
    $amount = (float) $row['amount'];
    $allocated = (float) $row['allocated'];
    $gap = $amount - $allocated;
    $totalUnallocated += $gap;

    printf(
        "%-6s %-8s %-9s %-10s %12s %12s %s\n",
        $row['id'],
        $row['tenant_id'],
        $row['month'],
        (string) ($row['receipt'] ?? '-'),
        number_format($amount, 2),
        number_format($allocated, 2),
        number_format($gap, 2)
    );

    if ((int) $row['tenant_id'] <= 0) {
        $skipped++;
        echo "        SKIP: payment has no tenant_id (cannot allocate)\n";
        continue;
    }

    if (!$dryRun) {
        $db->beginTransaction();
        try {
            $remainder = $billing->allocatePaymentToOutstandingBills(
                (int) $row['id'],
                (int) $row['tenant_id'],
                $gap,
                (string) $row['month'],
                (string) ($row['type'] ?? 'Mixed')
            );
            $billing->recalcTenantCreditAndBalance((int) $row['tenant_id']);
            $db->commit();
            $fixed++;
            echo "        FIXED: allocated " . number_format($gap - $remainder, 2)
                . ($remainder > 0 ? ", " . number_format($remainder, 2) . " became renter credit" : "") . "\n";
        } catch (\Throwable $e) {
            $db->rollback();
            $skipped++;
            echo "        ERROR: " . $e->getMessage() . " (rolled back)\n";
        }
    } else {
        echo "        would allocate " . number_format($gap, 2) . "\n";
    }
}

echo str_repeat('=', 88) . "\n";
printf(
    "Payments examined: %d\nTotal unallocated: KES %s\n%s: %d fixed, %d skipped\n",
    count($rows),
    number_format($totalUnallocated, 2),
    $dryRun ? 'DRY RUN' : 'LIVE',
    $fixed,
    $skipped
);

if ($dryRun) {
    echo "\nRe-run without --dry-run to apply these changes.\n";
}

exit($skipped > 0 && !$dryRun ? 1 : 0);
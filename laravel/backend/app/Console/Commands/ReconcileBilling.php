<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Billing\TenantLedger;
use App\Models\Owner;
use App\Models\Renter;
use App\Support\CrossTenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reconcile bills against payments for every tenant.
 *
 *   php artisan billing:reconcile            dry run: report only, changes nothing
 *   php artisan billing:reconcile --fix      apply
 *   php artisan billing:reconcile --tenant=12 --fix
 *
 * Replaces hand-editing the database: missing items on legacy bills, payments
 * that were never applied, allocations held by reversed payments, drifted
 * statuses and balances are all rebuilt from the allocation ledger.
 */
class ReconcileBilling extends Command
{
    protected $signature = 'billing:reconcile {--fix : Apply the changes (default is a dry run)} {--tenant= : Only this tenant id}';

    protected $description = 'Reconcile bills against payments from the allocation ledger';

    public function handle(): int
    {
        $apply = (bool) $this->option('fix');
        $only = $this->option('tenant') !== null ? (int) $this->option('tenant') : null;
        $rows = [];

        $ownerIds = CrossTenant::run('list owners for reconciliation', fn () => Owner::query()->pluck('id')->all());

        foreach ($ownerIds as $ownerId) {
            CrossTenant::runAsOwner((int) $ownerId, 'billing reconciliation', function () use ($apply, $only, $ownerId, &$rows): void {
                $ids = Renter::query()
                    ->when($only !== null, fn ($q) => $q->whereKey($only))
                    ->pluck('id');

                foreach ($ids as $id) {
                    $report = $apply
                        ? (new TenantLedger)->reconcile((int) $id)
                        : $this->dryRun((int) $id);

                    if ($report['backfilled_items'] || $report['stale_allocations_removed']
                        || $report['applied'] !== '0.00' || $report['over_allocated_items']) {
                        $rows[] = [
                            $ownerId, $id, $report['backfilled_items'], $report['stale_allocations_removed'],
                            $report['applied'], $report['over_allocated_items'], $report['balance'], $report['credit'],
                        ];
                    }
                }
            });
        }

        if ($rows === []) {
            $this->components->info('Everything reconciles. Nothing to change.');

            return self::SUCCESS;
        }

        $this->table(['owner', 'tenant', 'items added', 'stale allocs', 'unapplied money', 'over-allocated items', 'balance', 'credit'], $rows);
        $this->components->info($apply ? 'Applied.' : 'Dry run only. Re-run with --fix to apply.');

        return self::SUCCESS;
    }

    /** Run the real logic, then roll it back. */
    private function dryRun(int $tenantId): array
    {
        DB::beginTransaction();

        try {
            return (new TenantLedger)->reconcile($tenantId);
        } finally {
            DB::rollBack();
        }
    }
}

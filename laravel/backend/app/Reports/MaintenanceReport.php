<?php

declare(strict_types=1);

namespace App\Reports;

use App\Models\MaintenanceRecord;
use App\Support\Amount;

/**
 * Maintenance: what repairs cost and what is still outstanding.
 *
 * This report did not exist in the legacy backend -- there were individual
 * maintenance endpoints but no aggregate, so an owner could see the cost of a
 * single job and never the running total.
 *
 * That total is easy to get wrong. Summing `cost` across every record mixes
 * completed repairs (already spent) with pending ones (not yet), which makes
 * both figures meaningless. They are separated here.
 */
final class MaintenanceReport
{
    public function __construct(private readonly ReportScope $scope) {}

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $settled = $this->scope->whereHouse(MaintenanceRecord::query())->where('status', 'completed');
        $open = $this->scope->whereHouse(MaintenanceRecord::query())->whereIn('status', ['pending', 'in-progress']);

        $spent = Amount::sum((clone $settled)->pluck('cost'));
        $committed = Amount::sum((clone $open)->pluck('cost'));
        $completedCount = (int) (clone $settled)->count();

        return [
            'counts' => [
                'total' => (int) $this->scope->whereHouse(MaintenanceRecord::query())->count(),
                'open' => (int) (clone $open)->count(),
                'completed' => $completedCount,
            ],
            'money' => [
                'spent' => Amount::toString($spent),
                'committed' => Amount::toString($committed),
                'per_job' => Amount::toString(Amount::average($spent, $completedCount)),
            ],
            'by_status' => $this->scope->whereHouse(MaintenanceRecord::query())
                ->selectRaw('status AS label, COUNT(*) AS total')
                ->groupBy('status')
                ->pluck('total', 'label')
                ->all(),
            'by_priority' => $this->scope->whereHouse(MaintenanceRecord::query())
                ->whereIn('status', ['pending', 'in-progress'])
                ->selectRaw('priority AS label, COUNT(*) AS total')
                ->groupBy('priority')
                ->pluck('total', 'label')
                ->all(),
            'by_category' => $this->scope->whereHouse(MaintenanceRecord::query())
                ->whereNotNull('category')
                ->selectRaw('category AS label, COUNT(*) AS total, SUM(cost) AS amount')
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(fn (object $row): array => [
                    'label' => (string) $row->label,
                    'count' => (int) $row->total,
                    'amount' => Amount::toString(Amount::of($row->amount)),
                ])
                ->all(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Reports;

use App\Models\Complaint;
use App\Models\Renter;
use App\Support\Rate;

/**
 * Complaint statistics, derived at read time.
 *
 * A renter must never reach these aggregates: they expose the landlord's
 * portfolio-wide view of other people's disputes. The controller refuses them
 * outright; the actor check here is a second line of defence.
 *
 * Ported from the legacy ComplaintsReportController, which also reported a
 * resolution rate and a high-priority-open count. Both are here, because a raw
 * count of complaints tells an owner nothing about whether they are coping.
 */
final class ComplaintsReport
{
    public function __construct(private readonly ReportScope $scope) {}

    /** @return array<string, mixed> */
    public function summary(mixed $actor): array
    {
        $base = $this->scope->whereHouse(Complaint::query());

        // A renter, if one ever got here, sees only their own disputes.
        $scope = $actor instanceof Renter
            ? $base->where('tenant_id', $actor->getKey())
            : $base;

        $total = (clone $scope)->count();

        $byStatus = (clone $scope)
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $resolved = (int) ($byStatus['resolved'] ?? 0);

        // Unresolved work, and the urgent slice of it that needs answering today.
        $unresolved = (clone $scope)->whereIn('status', ['open', 'in-progress']);
        $highPriorityOpen = (clone $unresolved)->where('priority', 'high')->count();

        return [
            'total' => $total,
            'resolved' => $resolved,
            'unresolved' => (int) $unresolved->count(),
            'resolution_rate' => Rate::percent($resolved, $total),
            'high_priority_open' => (int) $highPriorityOpen,
            'by_status' => $byStatus,
            'by_priority' => (clone $scope)
                ->selectRaw('priority, COUNT(*) AS total')
                ->groupBy('priority')
                ->pluck('total', 'priority')
                ->all(),
            'by_category' => (clone $scope)
                ->whereNotNull('category')
                ->selectRaw('category, COUNT(*) AS total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'category')
                ->all(),
        ];
    }
}

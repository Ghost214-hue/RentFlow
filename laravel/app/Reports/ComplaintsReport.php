<?php

declare(strict_types=1);

namespace App\Reports;

use App\Models\Complaint;
use App\Models\Caretaker;
use App\Models\Renter;

/**
 * Complaint statistics, derived at read time.
 *
 * A renter must never reach these aggregates: they expose the landlord's
 * portfolio-wide view of other people's disputes.
 */
final class ComplaintsReport
{
    /** @return array<string, mixed> */
    public function summary(mixed $actor): array
    {
        // Tenants see only their own disputes.
        $scope = $actor instanceof Renter
            ? Complaint::query()->where('tenant_id', $actor->getKey())
            : Complaint::query();

        return [
            'total' => (clone $scope)->count(),
            'by_status' => (clone $scope)
                ->selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all(),
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
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\House;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Owner/caretaker landing page.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        // Money is aggregated in SQL (never in the browser) and emitted as
        // strings. See Amount:: for the aggregation rules.
        $totals = (object) [
            'expected' => Bill::query()->sum('total'),
            'collected' => Payment::query()->where('status', 'completed')->sum('amount'),
        ];

        $units = House::query()->selectRaw(
            "COUNT(*) AS total,
             SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS occupied"
        )->first();

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'properties' => Property::query()->count(),
                'units' => (int) ($units->total ?? 0),
                'occupiedUnits' => (int) ($units->occupied ?? 0),
                'expected' => (string) ($totals->expected ?? '0.00'),
                'collected' => (string) ($totals->collected ?? '0.00'),
            ],
        ]);
    }
}
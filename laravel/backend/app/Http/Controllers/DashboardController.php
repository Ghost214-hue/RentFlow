<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Billing\BillSnapshotBuilder;
use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\House;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Renter;
use App\Support\Amount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The root landing page. Dispatched by role.
 *
 * Previously this rendered the owner dashboard for everyone who could pass
 * PropertyPolicy::viewAny -- which includes caretakers. That handed a caretaker
 * the owner's whole portfolio: every property, every bill total, every payment.
 *
 * Each role now gets its own landing page:
 *   owner     -> the portfolio dashboard
 *   caretaker -> CaretakerDashboardController, scoped to assigned properties
 *   renter    -> the renter's own dashboard
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $actor = $request->user();

        // Staff landing pages are separate controllers with their own scoping.
        if ($actor instanceof Caretaker) {
            return redirect()->route('caretaker.dashboard');
        }

        if ($actor instanceof Renter) {
            return redirect()->route('renter.dashboard');
        }

        $this->authorize('viewAny', Property::class);

        return Inertia::render('Dashboard/Index', [
            'stats' => $this->ownerStats(),
        ]);
    }

    /** @return array<string, mixed> */
    private function ownerStats(): array
    {
        // Money is aggregated in SQL (never in the browser) and emitted as
        // strings. Amount::of() normalises the SUM() result, which MySQL may
        // return as a float for a DECIMAL column.
        $expected = Amount::sum([Bill::query()->sum('total')]);
        $collected = Amount::sum([
            Payment::query()->whereIn('status', BillSnapshotBuilder::SETTLED)->sum('amount'),
        ]);

        $units = House::query()->selectRaw(
            "COUNT(*) AS total,
             SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS occupied"
        )->first();

        $total = (int) ($units->total ?? 0);
        $occupied = (int) ($units->occupied ?? 0);

        return [
            'properties' => Property::query()->count(),
            'units' => $total,
            'occupiedUnits' => $occupied,
            'vacantUnits' => max(0, $total - $occupied),
            'expected' => Amount::toString($expected),
            'collected' => Amount::toString($collected),
            'outstanding' => Amount::toString(
                Amount::atLeastZero($expected->minus($collected))
            ),
        ];
    }
}
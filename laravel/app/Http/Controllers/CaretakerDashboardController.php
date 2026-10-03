<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Caretaker;
use App\Models\Complaint;
use App\Models\House;
use App\Models\MaintenanceRecord;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Renter;
use App\Support\Amount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The caretaker's dashboard.
 *
 * Ported from frontend/pages/caretaker-dashboard.php.
 *
 * CRITICAL SCOPE RULE: a caretaker manages only their ASSIGNED properties. The
 * owner dashboard aggregates the whole tenant scope, so a caretaker must never
 * be pointed at it -- every query here is filtered to the assigned property ids.
 * With no assignments the dashboard is empty rather than defaulting to
 * everything.
 */
class CaretakerDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $actor = $request->user();

        abort_unless($actor instanceof Caretaker, 403);

        $propertyIds = $actor->assignedPropertyIds();

        /*
         * An empty id list would produce `whereIn ('id', [])`, which matches
         * nothing -- the correct result. It is passed through deliberately
         * rather than replaced with "all", so an unassigned caretaker sees an
         * empty dashboard instead of the owner's whole portfolio.
         */
        $properties = Property::query()
            ->whereIn('id', $propertyIds)
            ->withCount(['houses'])
            ->orderBy('name')
            ->get();

        $units = House::query()
            ->whereIn('property_id', $propertyIds)
            ->selectRaw("COUNT(*) AS total,
                         SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS occupied")
            ->first();

        $billQuery = fn () => Bill::query()->whereIn('house_id',
            House::query()->whereIn('property_id', $propertyIds)->select('id'));

        $paymentQuery = fn () => Payment::query()
            ->whereHas('house', fn ($q) => $q->whereIn('property_id', $propertyIds));

        /*
         * Money is aggregated in SQL and emitted as decimal strings, so the
         * browser can only format it. Amount::of() normalises the SUM() result,
         * which MySQL may return as a float for a DECIMAL column.
         */
        $expected = Amount::sum([$billQuery()->sum('total')]);
        $collected = Amount::sum([$paymentQuery()->whereIn('status', ['completed', 'confirmed', 'paid'])->sum('amount')]);

        $openComplaints = Complaint::query()
            ->whereIn('sender_role', ['tenant'])
            ->whereHas('house', fn ($q) => $q->whereIn('property_id', $propertyIds))
            ->whereIn('status', ['open', 'in-progress'])
            ->count();

        $myTasks = MaintenanceRecord::query()
            ->where(function ($q) use ($propertyIds, $actor): void {
                $q->whereIn('property_id', $propertyIds)->orWhere('assigned_to', $actor->name);
            })
            ->whereIn('status', ['pending', 'in-progress'])
            ->count();

        $rentersHere = Renter::query()
            ->whereIn('property_id', $propertyIds)
            ->where('status', 'active')
            ->count();

        return Inertia::render('Caretaker/Dashboard', [
            'caretaker' => [
                'name' => (string) $actor->name,
                'email' => (string) $actor->email,
                'phone' => $actor->phone,
                'assigned_count' => count($propertyIds),
            ],
            'stats' => [
                'properties' => $properties->count(),
                'units' => (int) ($units->total ?? 0),
                'occupiedUnits' => (int) ($units->occupied ?? 0),
                'vacantUnits' => max(0, (int) ($units->total ?? 0) - (int) ($units->occupied ?? 0)),
                'renters' => $rentersHere,
                'openComplaints' => $openComplaints,
                'myTasks' => $myTasks,
                'expected' => Amount::toString($expected),
                'collected' => Amount::toString($collected),
                'outstanding' => Amount::toString(Amount::atLeastZero($expected->minus($collected))),
            ],
            'properties' => $properties->map(fn (Property $p) => [
                'id' => (int) $p->getKey(),
                'name' => (string) $p->name,
                'address' => $p->address,
                'units' => (int) $p->houses_count,
                'rent' => Amount::toString(Amount::of($p->rent)),
            ])->all(),
        ]);
    }
}
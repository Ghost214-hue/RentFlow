<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RenterStatus;
use App\Enums\TerminationStatus;
use App\Http\Resources\BillResource;
use App\Http\Resources\MaintenanceRecordResource;
use App\Models\Complaint;
use App\Models\MaintenanceRecord;
use App\Models\Payment;
use App\Models\Renter;
use App\Models\TenancyTermination;
use App\Support\Amount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The renter's own dashboard.
 *
 * Everything is scoped to the authenticated renter. Outstanding figures are
 * computed from the bills and payment_allocations tables rather than read from
 * the cached tenants.balance / tenants.credit columns, which drift.
 */
class RenterDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $renter = $this->currentRenter($request);

        $bills = $renter->bills()
            ->with('house:id,unit')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        // Outstanding = sum of unpaid bill totals, computed with Money so no
        // float ever touches a currency value.
        $outstandingBills = $renter->bills()->whereIn('status', ['pending', 'partial', 'overdue'])->get();
        $outstanding = Amount::sum($outstandingBills->pluck('total'));

        $credit = $renter->credit !== null ? Amount::of($renter->credit) : Amount::zero();

        $recentPayments = $renter->payments()
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $openComplaints = Complaint::query()
            ->where('tenant_id', $renter->getKey())
            ->whereIn('status', ['open', 'in-progress'])
            ->count();

        $openMaintenance = MaintenanceRecord::query()
            ->where('tenant_id', $renter->getKey())
            ->whereIn('status', ['pending', 'in-progress'])
            ->count();

        return Inertia::render('Renter/Dashboard', [
            'renter' => [
                'id' => (int) $renter->getKey(),
                'name' => (string) $renter->name,
                'email' => (string) $renter->email,
                'phone' => $renter->phone,
                'status' => $renter->status instanceof \BackedEnum ? $renter->status->value : (string) $renter->status,
                'property_name' => $renter->property?->name,
                'house_unit' => $renter->house?->unit,
                'lease_start' => $renter->lease_start?->toDateString(),
                'lease_end' => $renter->lease_end?->toDateString(),
            ],
            'summary' => [
                'outstanding' => Amount::toString($outstanding),
                'credit' => Amount::toString($credit),
                'unpaid_bill_count' => $outstandingBills->count(),
                'open_complaints' => $openComplaints,
                'open_maintenance' => $openMaintenance,
            ],
            /*
             * Tenancy status, so the dashboard can offer the right action:
             *   active              -> "Request to leave"
             *   pending_termination -> "Request submitted, awaiting approval"
             *   terminated          -> nothing; the tenancy has ended
             */
            'tenancy' => [
                'status' => $renter->status instanceof \BackedEnum
                    ? $renter->status->value
                    : (string) $renter->status,
                'can_request_termination' => $renter->status instanceof RenterStatus
                    ? $renter->status === RenterStatus::Active
                    : false,
                'pending_request' => TenancyTermination::query()
                    ->where('tenant_id', $renter->getKey())
                    ->where('status', TerminationStatus::Pending->value)
                    ->exists(),
                'effective_date' => TenancyTermination::query()
                    ->where('tenant_id', $renter->getKey())
                    ->orderByDesc('id')
                    ->value('effective_date'),
            ],
            'recentBills' => BillResource::collection($bills)->resolve(),
            'recentPayments' => $recentPayments->map(fn (Payment $p) => [
                'id' => (int) $p->getKey(),
                'amount' => Amount::toString(Amount::of($p->amount)),
                'method' => $p->method,
                'status' => $p->status,
                // The column is `date`, not `paid_at`.
                'paid_at' => $p->date?->toDateString(),
            ])->all(),
        ]);
    }

    /**
     * The authenticated actor must be a renter.
     *
     * Exported so RenterProfileController can reuse the same guard rather than
     * duplicating the role check.
     */
    public function currentRenter(Request $request): Renter
    {
        $renter = $request->user();

        abort_unless($renter instanceof Renter, 403);

        return $renter;
    }
}
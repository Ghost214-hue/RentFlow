<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\House;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Support\Amount;
use App\Support\PlatformContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /*
         * Every figure below is a platform-wide read, so it runs inside
         * PlatformContext::run(). That clears the owner scope and writes one
         * audit_logs row for the whole dashboard load.
         *
         * The previous version called BillSnapshot::currentMonth(), which is
         * owner-scoped and throws when no owner context is set.
         */
        $totals = PlatformContext::run('Admin dashboard platform totals', function (): array {
            $owners = Owner::query()->get(['id', 'status', 'created_at']);

            $propertyCount = Property::withoutOwnerScope()->count();
            $unitCount = House::withoutOwnerScope()->count();
            $occupiedUnits = House::withoutOwnerScope()->where('status', 'occupied')->count();

            $activeRenterCount = Renter::withoutOwnerScope()->where('status', 'active')->count();

            // Ledger-based totals across ALL owners (same maths as every other
            // screen). The old code called Money::subtract(), which does not
            // exist (Brick uses minus()), so this page threw a 500 after login.
            $t = \App\Billing\PortfolioTotals::compute(
                \App\Models\Bill::withoutOwnerScope(),
                \App\Models\Payment::withoutOwnerScope(),
            );

            $billed = Amount::of($t['month_billed']);
            $collected = Amount::of($t['month_settled']);
            $outstanding = Amount::of($t['month_outstanding']);

            $collectionRate = $billed->isPositive()
                ? (float) $collected->getAmount()->toFloat() / (float) $billed->getAmount()->toFloat()
                : 0.0;

            return [
                'ownerCount' => $owners->count(),
                'activeOwnerCount' => $owners->where('status', 'active')->count(),
                'suspendedOwnerCount' => $owners->where('status', 'suspended')->count(),
                'newSignupCount' => $owners->where('created_at', '>=', now()->subMonth())->count(),
                'propertyCount' => $propertyCount,
                'unitCount' => $unitCount,
                'occupiedUnits' => $occupiedUnits,
                'occupancyRate' => $unitCount > 0 ? $occupiedUnits / $unitCount : 0.0,
                'activeRenterCount' => $activeRenterCount,
                'billedTotal' => Amount::toString($billed),
                'collectedTotal' => Amount::toString($collected),
                'outstandingTotal' => Amount::toString($outstanding),
                'collectionRate' => $collectionRate,
                'overdueTotal' => $t['overdue'],
                'arrearsTotal' => $t['arrears'],
                'creditTotal' => $t['credit'],
                'emailFailuresLast24h' => EmailLog::where('status', 'failed')
                    ->where('sent_at', '>=', now()->subDay())
                    ->count(),
                'emailSuccessLast24h' => EmailLog::where('status', 'sent')
                    ->where('sent_at', '>=', now()->subDay())
                    ->count(),
            ];
        });

        return Inertia::render('Admin/Dashboard', $totals);
    }

    /**
     * Get recent system errors/incidents for the error dashboard.
     */
    public function errors(Request $request): Response
    {
        // Get pagination parameters
        $perPage = max(5, min((int) $request->integer('per_page', 20), 100));
        $page = $request->integer('page', 1);

        // Query error/incedent data - customize based on your error tracking setup
        // This is a placeholder - implement based on your error tracking service (Sentry, Bugsnag, etc.)
        $errors = [];

        return Inertia::render('Admin/ErrorDashboard', [
            'errors' => $errors,
            'total' => count($errors),
            'page' => $page,
            'perPage' => $perPage,
            'canPaginate' => false, // Set to true if you implement pagination
        ]);
    }
}

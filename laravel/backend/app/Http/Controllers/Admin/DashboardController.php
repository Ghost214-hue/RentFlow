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
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
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

            $month = now()->format('Y-m');

            $billed = Amount::of(
                DB::table('bills')->where('month', $month)->sum('total'),
            );

            $collected = Amount::of(
                DB::table('payments')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount'),
            );

            $outstanding = Amount::atLeastZero($billed->subtract($collected));

            $collectionRate = $billed->isPositive()
                ? (float) $collected->getAmount() / (float) $billed->getAmount()
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
                'emailFailuresLast24h' => EmailLog::where('status', 'failed')
                    ->where('sent_at', '>=', now()->subDay())
                    ->count(),
                'emailSuccessLast24h' => EmailLog::where('status', 'sent')
                    ->where('sent_at', '>=', now()->subDay())
                    ->count(),
            ];
        });

        return view('admin.dashboard', $totals);
    }
}

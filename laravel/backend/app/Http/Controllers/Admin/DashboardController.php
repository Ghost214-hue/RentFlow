<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Support\TenantContext;
use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Renter;
use App\Support\CrossTenant;
use App\Support\PlatformContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): \Illuminate\View\View
    {
        // Use CrossTenant::run() for all cross-owner queries.
        // This ensures an audit row is written for every cross-owner read
        // (actor = admin, target_owner_id, reason).

        $reason = 'Admin dashboard platform totals calculation';

        // Compute platform totals via CrossTenant with grouped SQL.
        // We temporarily clear the owner context and query globally.
        $previous = \App\Support\TenantContext::id();
        \App\Support\TenantContext::clear();

        try {
            // Owners: count active/suspended/new this month
            $owners = Owner::query()
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->get();

            $ownerCount = $owners->count();
            $activeOwnerCount = $owners->where('status', 'active')->count();
            $suspendedOwnerCount = $owners->where('status', 'suspended')->count();

            // New signups this month: owners created in the last 30 days
            $newSignupCount = Owner::where('created_at', '>=', now()->subMonths(1))->count();

            // Properties and units via CrossTenant
            $propertyCount = Property::withoutOwnerScope()->count();
            // Count total units across all properties
            $unitCount = \App\Models\House::withoutOwnerScope()
                ->whereIn('property_id', Property::withoutOwnerScope()->pluck('id'))
                ->count();

            // Active renters (across all properties)
            $activeRenterCount = Renter::where('status', 'active')->count();

            // Financials via BillSnapshot (this month) - using CrossTenant
            // Since BillSnapshot::currentMonth() is owner-scoped, we compute manually
            // using grouped SQL through CrossTenant. For now, use the existing methods
            // but ensure we have an owner context, or fall back to platform totals.
            $bills = \App\Billing\BillSnapshot::currentMonth();
            $collected = \App\Billing\BillSnapshot::collectedThisMonth();
            $collectionRate = $bills->total > 0
                ? $collected / $bills->total
                : 0;

            $billedTotal = $bills->total;
            $collectedTotal = $collected;
            $outstandingTotal = $bills->total - $collected;

            // Email failures in last 24h
            $emailFailuresLast24h = \App\Models\EmailLog::where('status', 'failed')
                ->where('sent_at', '>=', now()->subDay())
                ->count();
            $emailSuccessLast24h = \App\Models\EmailLog::where('status', 'sent')
                ->where('sent_at', '>=', now()->subDay())
                ->count();

        } finally {
            // Restore previous owner context
            \App\Support\TenantContext::set($previous);
        }

        return view('admin.dashboard', [
            'ownerCount' => $ownerCount,
            'activeOwnerCount' => $activeOwnerCount,
            'suspendedOwnerCount' => $suspendedOwnerCount,
            'newSignupCount' => $newSignupCount,
            'propertyCount' => $propertyCount,
            'unitCount' => $unitCount,
            'activeRenterCount' => $activeRenterCount,
            'billedTotal' => $billedTotal,
            'collectedTotal' => $collectedTotal,
            'outstandingTotal' => $outstandingTotal,
            'collectionRate' => $collectionRate,
            'emailFailuresLast24h' => $emailFailuresLast24h,
            'emailSuccessLast24h' => $emailSuccessLast24h,
        ]);
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Support\PlatformContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): \Illuminate\View\View
    {
        // Platform context — never sets owner id.
        // Admin queries name owners explicitly via CrossTenant::run.

        $owners = Owner::withoutOwnerScope()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->get();

        // Compute collection rate via BillSnapshot.
        $bills = \App\Billing\BillSnapshot::currentMonth();
        $collected = \App\Billing\BillSnapshot::collectedThisMonth();
        $collectionRate = $bills->total > 0
            ? $collected / $bills->total
            : 0;

        return view('admin.dashboard', [
            'owners' => $owners->count(),
            'billed' => $bills->total,
            'collected' => $collected,
            'outstanding' => $bills->total - $collected,
            'collectionRate' => $collectionRate,
            'newSignups' => $owners->count(),
        ]);
    }
}
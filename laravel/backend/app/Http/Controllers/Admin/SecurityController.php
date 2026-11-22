<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // Audit: view security metrics
        \App\Models\AuditLog::log('view', 'security_metrics', 0, 'Admin viewed security metrics', request()->ip(), request()->header('User-Agent'));

        $owners = Owner::withoutOwnerScope()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->get();

        $failedLogins = 0;

        return Inertia::render('Admin/Security', [
            'ownersCount' => $owners->count(),
            'failedLogins' => $failedLogins,
        ]);
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    public function __invoke(Request $request): \Illuminate\View\View
    {
        // Audit: view security metrics
        \App\Models\AuditLog::log('view', 'security_metrics', 0, 'Admin viewed security metrics', request()->ip(), request()->header('User-Agent'));

        $owners = Owner::withoutOwnerScope()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->get();

        $failedLogins = 0; // Would query actual failed login attempts from logs

        return view('admin.security', [
            'owners' => $owners->count(),
            'failedLogins' => $failedLogins,
        ]);
    }
}
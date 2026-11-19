<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin as AdminModel;
use App\Models\Owner;
use App\Models\SupportAccessGrant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportAccessController extends Controller
{
    public function index(Request $request): Response
    {
        // Audit: list support access grants
        \App\Models\AuditLog::log('view', 'support_access_list', 0, 'Admin viewed support access grants', request()->ip(), request()->header('User-Agent'));

        $grants = SupportAccessGrant::with(['owner:id,name', 'admin:id,name'])
            ->whereNotNull('owner_id')
            ->when($request->filled('owner_id'), fn ($q) => $q->where('owner_id', $request->integer('owner_id')))
            ->orderByDesc('id')
            ->get();

        $owners = Owner::withoutOwnerScope()->orderBy('name')->get(['id', 'name']);
        $admins = AdminModel::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Support/Index', [
            'grants' => $grants,
            'owners' => $owners,
            'admins' => $admins,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Audit: create support access grant
        \App\Models\AuditLog::log('create', 'support_access_create', 0, 'Admin created support access grant', request()->ip(), request()->header('User-Agent'));

        $request->validate([
            'owner_id' => ['required', 'integer', 'exists:owners,id'],
            'admin_id' => ['required', 'integer', 'exists:admins,id'],
            'scope' => ['required', 'string'],
            'reason' => ['required', 'string'],
        ]);

        // Check for existing active grant.
        $existing = SupportAccessGrant::where('owner_id', $request->owner_id)
            ->where('admin_id', $request->admin_id)
            ->whereNull('revoked_at')
            ->first();

        if ($existing) {
            return back()->with('error', 'An active support grant already exists for this owner.');
        }

        SupportAccessGrant::create([
            'owner_id' => $request->owner_id,
            'admin_id' => $request->admin_id,
            'scope' => $request->scope,
            'reason' => $request->reason,
            'expires_at' => $request->filled('expires_at') ? now()->addDays($request->integer('expires_at')) : null,
        ]);

        return back()->with('success', 'Support access grant created.');
    }

    public function destroy(SupportAccessGrant $grant): RedirectResponse
    {
        // Audit: revoke support access grant
        \App\Models\AuditLog::log('delete', 'support_access_revoke', $grant->id, 'Admin revoked support access grant', request()->ip(), request()->header('User-Agent'));

        $grant->update(['revoked_at' => now()]);

        return back()->with('success', 'Support access grant revoked.');
    }
}
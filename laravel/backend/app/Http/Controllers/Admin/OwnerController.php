<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Services\PasswordResetService;
use App\Support\PlatformContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController extends Controller
{
    public function index(Request $request): Response
    {
        // Audit: list all owners
        \App\Models\AuditLog::log('view', 'owner_list', 0, 'Admin viewed owner list', request()->ip(), request()->header('User-Agent'));

        $owners = Owner::withoutOwnerScope()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'status', 'created_at']);

        return Inertia::render('Admin/Owners/Index', [
            'owners' => $owners,
        ]);
    }

    public function show(Owner $owner): Response
    {
        // Audit: view specific owner
        \App\Models\AuditLog::log('view', 'owner_show', $owner->id, 'Admin viewed owner detail', request()->ip(), request()->header('User-Agent'));

        $owner->load(['properties.houses']);

        return Inertia::render('Admin/Owners/Show', [
            'owner' => $owner,
        ]);
    }

    public function activate(Owner $owner): RedirectResponse
    {
        // Audit: activate owner
        \App\Models\AuditLog::log('update', 'owner_activate', $owner->id, 'Admin activated owner', request()->ip(), request()->header('User-Agent'));

        $owner->status = 'active';
        $owner->save();

        return back()->with('success', "Owner {$owner->name} activated.");
    }

    public function suspend(Owner $owner): RedirectResponse
    {
        // Audit: suspend owner
        \App\Models\AuditLog::log('update', 'owner_suspend', $owner->id, 'Admin suspended owner', request()->ip(), request()->header('User-Agent'));

        $owner->status = 'suspended';
        $owner->save();

        return back()->with('warning', "Owner {$owner->name} suspended.");
    }

    public function sendReset(Owner $owner, PasswordResetService $resets): RedirectResponse
    {
        // Audit: reset owner password
        \App\Models\AuditLog::log('update', 'owner_reset', $owner->id, 'Admin sent password reset for owner', request()->ip(), request()->header('User-Agent'));

        $resets->request((string) $owner->email);

        return back()->with('success', "Password reset email sent to {$owner->email}.");
    }
}
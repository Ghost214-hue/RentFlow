<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Support\PlatformContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        // Audit: list all owners
        \App\Models\AuditLog::log('view', 'owner_list', 0, 'Admin viewed owner list', request()->ip(), request()->header('User-Agent'));

        $owners = Owner::withoutOwnerScope()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->get();

        return view('admin.owners.index', [
            'owners' => $owners,
        ]);
    }

    public function show(Owner $owner): \Illuminate\View\View
    {
        // Audit: view specific owner
        \App\Models\AuditLog::log('view', 'owner_show', $owner->id, 'Admin viewed owner detail', request()->ip(), request()->header('User-Agent'));

        return view('admin.owners.show', [
            'owner' => $owner,
        ]);
    }

    public function activate(Owner $owner): \Illuminate\Http\RedirectResponse
    {
        // Audit: activate owner
        \App\Models\AuditLog::log('update', 'owner_activate', $owner->id, 'Admin activated owner', request()->ip(), request()->header('User-Agent'));

        $owner->status = 'active';
        $owner->save();

        return back()->with('success', "Owner {$owner->name} activated.");
    }

    public function suspend(Owner $owner): \Illuminate\Http\RedirectResponse
    {
        // Audit: suspend owner
        \App\Models\AuditLog::log('update', 'owner_suspend', $owner->id, 'Admin suspended owner', request()->ip(), request()->header('User-Agent'));

        $owner->status = 'suspended';
        $owner->save();

        return back()->with('warning', "Owner {$owner->name} suspended.");
    }
}
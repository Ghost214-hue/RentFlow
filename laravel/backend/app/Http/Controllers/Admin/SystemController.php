<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    public function __invoke(Request $request): \Illuminate\View\View
    {
        // Audit: view system status
        \App\Models\AuditLog::log('view', 'system_status', 0, 'Admin viewed system status', request()->ip(), request()->header('User-Agent'));

        // Version from composer or app constant
        $version = '1.0.0'; // Would come from composer.json or app version

        return view('admin.system', [
            'version' => $version,
        ]);
    }
}
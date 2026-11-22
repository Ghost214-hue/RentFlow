<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // Audit: view system status
        \App\Models\AuditLog::log('view', 'system_status', 0, 'Admin viewed system status', request()->ip(), request()->header('User-Agent'));

        return Inertia::render('Admin/System', [
            'version' => '1.0.0',
            'env' => config('app.env', 'local'),
            'debug' => (bool) config('app.debug', false),
            'sessionDriver' => (string) config('session.driver', 'database'),
            'queueConnection' => (string) config('queue.default', 'database'),
            'phpVersion' => PHP_VERSION,
            'dbConnection' => (string) config('database.default', 'mysql'),
        ]);
    }
}
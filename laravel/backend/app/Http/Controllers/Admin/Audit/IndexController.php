<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // Build query
        $query = AuditLog::query();
        
        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }
        
        // Filter by actor type
        if ($request->filled('actor')) {
            $query->where('actor_type', $request->string('actor'));
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->string('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->string('to_date') . ' 23:59:59');
        }
        
        // Count total
        $total = $query->count();
        
        // Paginate
        $logs = $query->latest('id')
            ->paginate($request->integer('per_page', 25))
            ->withQueryString();
        
        return Inertia::render('Admin/Audit/Index', [
            'logs' => $logs,
            'total' => $total,
            'filters' => $request->only(['action', 'actor', 'from_date', 'to_date']),
        ]);
    }
}
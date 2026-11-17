<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Audit;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Routing\ControllerManager;
use Illuminate\Validation\Rule;

class IndexController extends Controller
{
    public function __invoke(Request $request)
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
            ->paginate($request->integer('per_page', 25));
        
        return response()->json([
            'html' => view('admin.audit.partial', [
                'logs' => $logs,
                'total' => $total,
            ])->render(),
            'total' => $total,
        ]);
    }
}
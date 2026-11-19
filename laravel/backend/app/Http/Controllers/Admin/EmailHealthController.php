<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailHealthController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // Audit: view email health
        \App\Models\AuditLog::log('view', 'email_health', 0, 'Admin viewed email health', request()->ip(), request()->header('User-Agent'));

        $query = EmailLog::query();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('to_email', 'like', $term)->orWhere('subject', 'like', $term);
            });
        }

        $logs = $query->orderByDesc('sent_at')->orderByDesc('id')->paginate(25)->withQueryString();

        $summary = EmailLog::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Admin/EmailHealth', [
            'logs' => $logs,
            'summary' => $summary,
            'filters' => $request->only(['status', 'search']),
        ]);
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EmailLogStatus;
use App\Models\Caretaker;
use App\Models\EmailLog;
use App\Models\Owner;
use App\Models\Renter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Email delivery log.
 *
 * Ported from EmailLogController and frontend/pages/email-logs.php.
 *
 * READ ONLY. The log is written by the mail queue as mail is sent; nothing in
 * the UI may edit or delete a delivery record, because that would destroy the
 * evidence of what was actually sent to a renter.
 */
class EmailLogController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = max(10, min((int) $request->integer('per_page', 25), 100));
        $actor = $request->user();

        $query = EmailLog::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($q) => $q
                ->where(function ($w) use ($request): void {
                    $term = '%'.$request->string('search').'%';
                    $w->where('to_email', 'like', $term)->orWhere('subject', 'like', $term);
                }))
            // A renter sees only mail addressed to them. Staff see the whole
            // portfolio, already scoped by the owner tenancy.
            ->when(
                $actor instanceof Renter,
                fn ($q) => $q->where('to_email', $actor->email),
            );

        $logs = $query->orderByDesc('sent_at')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();

        $summary = EmailLog::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('EmailLogs/Index', [
            'logs' => [
                'data' => $logs->map(fn (EmailLog $l) => [
                    'id' => (int) $l->id,
                    'to_email' => (string) $l->to_email,
                    'to_name' => $l->to_name,
                    'subject' => (string) $l->subject,
                    'status' => $l->status instanceof \BackedEnum ? $l->status->value : (string) $l->status,
                    'error' => $l->error,
                    'sent_at' => $l->sent_at?->toIso8601String(),
                ])->all(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                ],
            ],
            'summary' => [
                'sent' => (int) ($summary[EmailLogStatus::Sent->value] ?? 0),
                'failed' => (int) ($summary[EmailLogStatus::Failed->value] ?? 0),
                'pending' => (int) ($summary[EmailLogStatus::Pending->value] ?? 0),
            ],
            'filters' => [
                'status' => $request->string('status')->toString() ?: null,
                'search' => $request->string('search')->toString() ?: null,
            ],
        ]);
    }
}
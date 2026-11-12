import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import type { EmailLogEntry } from '@/types';

interface Props {
    logs: {
        data: EmailLogEntry[];
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    summary: { sent: number; failed: number; pending: number };
    filters: { status: string | null; search: string | null };
}

const STATUS_TONE: Record<string, string> = {
    sent: 'bg-emerald-100 text-emerald-700',
    failed: 'bg-red-100 text-red-700',
    pending: 'bg-amber-100 text-amber-700',
};

/**
 * Email delivery log.
 *
 * Ported from frontend/pages/email-logs.php. Read only: the mail queue writes
 * these rows, so there is deliberately no create, edit or delete control.
 */
export default function EmailLogsIndex({ logs, summary, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    function apply(status: string) {
        router.get(
            '/email-logs',
            {
                status: status || undefined,
                search: search || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    }

    const tiles = [
        { label: 'Sent', value: summary.sent, tone: 'bg-emerald-50 text-emerald-700' },
        { label: 'Failed', value: summary.failed, tone: 'bg-red-50 text-red-700' },
        { label: 'Pending', value: summary.pending, tone: 'bg-amber-50 text-amber-700' },
    ];

    return (
        <>
            <Head title="Email Delivery" />

            <AppLayout
                title="Email Delivery"
                actions={
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">Email Delivery</h1>
                        <p className="text-slate-500 mt-1">What was sent, and whether it arrived</p>
                    </div>
                }
            >
                <div className="grid grid-cols-3 gap-4 mb-6">
                    {tiles.map((tile) => (
                        <div key={tile.label} className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                            <div className={`inline-flex px-2.5 py-1 rounded-full text-xs font-medium ${tile.tone}`}>
                                {tile.label}
                            </div>
                            <p className="mt-3 text-2xl font-bold text-slate-900">{tile.value}</p>
                        </div>
                    ))}
                </div>

                <div className="mb-4 flex flex-col sm:flex-row gap-3">
                    <label htmlFor="logSearch" className="sr-only">Search email log</label>
                    <input
                        id="logSearch"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => { if (e.key === 'Enter') apply(filters.status ?? ''); }}
                        placeholder="Search recipient or subject..."
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm max-w-sm"
                    />

                    <select value={filters.status ?? ''} onChange={(e) => apply(e.target.value)}
                        aria-label="Filter by status"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                        <option value="">All statuses</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    <th className="px-6 py-4">Recipient</th>
                                    <th className="px-6 py-4">Subject</th>
                                    <th className="px-6 py-4">Status</th>
                                    <th className="px-6 py-4">Sent</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {logs.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={4} className="px-6 py-12 text-center text-slate-400">
                                            No emails recorded yet.
                                        </td>
                                    </tr>
                                ) : (
                                    logs.data.map((log) => (
                                        <tr key={log.id} className="hover:bg-blue-50/30 transition-colors">
                                            <td className="px-6 py-4">
                                                <p className="text-sm font-medium text-slate-900">{log.to_name || log.to_email}</p>
                                                {log.to_name && (
                                                    <p className="text-xs text-slate-500">{log.to_email}</p>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {log.subject}
                                                {log.error && log.status === 'failed' && (
                                                    <p className="text-xs text-red-600 mt-1">{log.error}</p>
                                                )}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${STATUS_TONE[log.status] ?? STATUS_TONE.pending}`}>
                                                    {log.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-500">
                                                {log.sent_at ? new Date(log.sent_at).toLocaleString() : '-'}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {logs.meta.last_page > 1 && (
                        <nav className="p-4 flex items-center justify-between border-t border-blue-50" aria-label="Pagination">
                            <p className="text-sm text-slate-500">
                                Page {logs.meta.current_page} of {logs.meta.last_page} ({logs.meta.total})
                            </p>
                            <div className="flex gap-2">
                                {filters.status && (
                                    <button type="button" onClick={() => router.get('/email-logs', { per_page: logs.meta.per_page })}
                                        className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">
                                        Clear filter
                                    </button>
                                )}
                            </div>
                        </nav>
                    )}
                </div>
            </AppLayout>
        </>
    );
}
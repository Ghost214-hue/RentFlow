import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import type { Paginated } from '@/types';

interface EmailLogItem {
    id: number;
    to_email: string;
    to_name: string | null;
    subject: string;
    status: 'sent' | 'failed' | 'pending' | string;
    error: string | null;
    sent_at: string | null;
}

interface Props {
    logs: Paginated<EmailLogItem>;
    summary: Record<string, number>;
    filters: { status?: string; search?: string };
}

export default function EmailHealth({ logs, summary, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    function applyFilters() {
        router.get(
            '/admin/email-health',
            {
                search: search || undefined,
                status: status || undefined,
            },
            { preserveState: true },
        );
    }

    const totalSent = summary['sent'] ?? 0;
    const totalFailed = summary['failed'] ?? 0;
    const totalPending = summary['pending'] ?? 0;

    return (
        <AppLayout
            title="Email & Queue Health"
            subtitle="Platform-wide monitoring of automated outgoing messages, deliverability, and failure alerts"
            actions={
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        applyFilters();
                    }}
                    className="flex flex-wrap items-center gap-3"
                >
                    <input
                        type="search"
                        placeholder="Search recipient or subject…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="rounded-xl border border-border bg-surface px-4 py-2 text-sm text-text placeholder-text-subtle focus:border-primary focus:outline-none"
                    />
                    <select
                        value={status}
                        onChange={(e) => {
                            setStatus(e.target.value);
                            router.get(
                                '/admin/email-health',
                                { search: search || undefined, status: e.target.value || undefined },
                                { preserveState: true },
                            );
                        }}
                        className="rounded-xl border border-border bg-surface px-4 py-2 text-sm text-text focus:border-primary focus:outline-none"
                    >
                        <option value="">All Statuses</option>
                        <option value="sent">Sent ({totalSent})</option>
                        <option value="failed">Failed ({totalFailed})</option>
                        <option value="pending">Pending ({totalPending})</option>
                    </select>
                    <button
                        type="submit"
                        className="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-hover transition"
                    >
                        Filter
                    </button>
                </form>
            }
        >
            <Head title="Email Health - Admin" />

            {/* Summary KPI Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div className="rounded-2xl border border-border bg-surface p-5 shadow-card flex items-center justify-between">
                    <div>
                        <span className="text-xs font-semibold uppercase text-text-subtle">Successfully Delivered</span>
                        <div className="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {totalSent}
                        </div>
                    </div>
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
                        <i className="fas fa-check text-lg" aria-hidden="true" />
                    </span>
                </div>

                <div className="rounded-2xl border border-border bg-surface p-5 shadow-card flex items-center justify-between">
                    <div>
                        <span className="text-xs font-semibold uppercase text-text-subtle">Delivery Failures</span>
                        <div className="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                            {totalFailed}
                        </div>
                    </div>
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/10 text-red-500">
                        <i className="fas fa-triangle-exclamation text-lg" aria-hidden="true" />
                    </span>
                </div>

                <div className="rounded-2xl border border-border bg-surface p-5 shadow-card flex items-center justify-between">
                    <div>
                        <span className="text-xs font-semibold uppercase text-text-subtle">Queue / In-Flight</span>
                        <div className="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {totalPending}
                        </div>
                    </div>
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                        <i className="fas fa-clock text-lg" aria-hidden="true" />
                    </span>
                </div>
            </div>

            {/* Email Logs Table */}
            <div className="rounded-2xl border border-border bg-surface shadow-card overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm text-text">
                        <thead className="border-b border-border bg-surface-raised/50 text-xs font-semibold uppercase text-text-subtle">
                            <tr>
                                <th className="px-6 py-4">Recipient</th>
                                <th className="px-6 py-4">Subject</th>
                                <th className="px-6 py-4">Status</th>
                                <th className="px-6 py-4">Timestamp</th>
                                <th className="px-6 py-4">Error Diagnostics</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {logs.data.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-6 py-12 text-center text-text-subtle">
                                        No email logs found.
                                    </td>
                                </tr>
                            ) : (
                                logs.data.map((log) => (
                                    <tr key={log.id} className="hover:bg-page/40 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="font-medium text-text">{log.to_email}</div>
                                            {log.to_name && <div className="text-xs text-text-subtle">{log.to_name}</div>}
                                        </td>
                                        <td className="px-6 py-4 max-w-xs truncate text-text font-medium" title={log.subject}>
                                            {log.subject}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span
                                                className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    log.status === 'sent'
                                                        ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20'
                                                        : log.status === 'failed'
                                                        ? 'bg-red-500/10 text-red-600 border border-red-500/20'
                                                        : 'bg-amber-500/10 text-amber-600 border border-amber-500/20'
                                                }`}
                                            >
                                                <span
                                                    className={`h-1.5 w-1.5 rounded-full ${
                                                        log.status === 'sent'
                                                            ? 'bg-emerald-500'
                                                            : log.status === 'failed'
                                                            ? 'bg-red-500'
                                                            : 'bg-amber-500'
                                                    }`}
                                                />
                                                {log.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-xs text-text-subtle whitespace-nowrap">
                                            {log.sent_at ? new Date(log.sent_at).toLocaleString() : 'Pending'}
                                        </td>
                                        <td className="px-6 py-4 text-xs text-danger font-mono max-w-sm truncate" title={log.error ?? ''}>
                                            {log.error ?? <span className="text-text-subtle font-sans">None</span>}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {logs.meta.last_page > 1 && (
                    <div className="p-4 border-t border-border flex items-center justify-between text-xs text-text-subtle">
                        <span>
                            Showing page {logs.meta.current_page} of {logs.meta.last_page} ({logs.meta.total} total)
                        </span>
                        <div className="flex gap-2">
                            {logs.links.prev && (
                                <Link
                                    href={logs.links.prev}
                                    preserveScroll
                                    className="rounded-lg border border-border px-3 py-1.5 text-text hover:bg-surface-raised"
                                >
                                    Previous
                                </Link>
                            )}
                            {logs.links.next && (
                                <Link
                                    href={logs.links.next}
                                    preserveScroll
                                    className="rounded-lg border border-border px-3 py-1.5 text-text hover:bg-surface-raised"
                                >
                                    Next
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

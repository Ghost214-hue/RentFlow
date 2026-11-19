import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import type { Paginated } from '@/types';

interface AuditItem {
    id: number;
    action: string;
    actor_type: string;
    actor_id?: number | null;
    subject_type?: string | null;
    subject_id?: number | null;
    ip_address?: string | null;
    description: string;
    created_at: string;
}

interface Props {
    logs: Paginated<AuditItem>;
    total: number;
    filters: {
        action?: string;
        actor?: string;
        from_date?: string;
        to_date?: string;
    };
}

export default function AuditLogIndex({ logs, total, filters }: Props) {
    const [action, setAction] = useState(filters.action ?? '');
    const [actor, setActor] = useState(filters.actor ?? '');
    const [fromDate, setFromDate] = useState(filters.from_date ?? '');
    const [toDate, setToDate] = useState(filters.to_date ?? '');

    function applyFilters() {
        router.get(
            '/admin/audit',
            {
                action: action || undefined,
                actor: actor || undefined,
                from_date: fromDate || undefined,
                to_date: toDate || undefined,
            },
            { preserveState: true },
        );
    }

    return (
        <AppLayout
            title="Platform Audit Logs"
            subtitle="Immutable activity trails tracking administrative actions, access events, and data modifications"
            actions={
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        applyFilters();
                    }}
                    className="flex flex-wrap items-center gap-2.5 text-sm"
                >
                    <select
                        value={action}
                        onChange={(e) => setAction(e.target.value)}
                        className="rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                    >
                        <option value="">All Actions</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="view">View</option>
                    </select>

                    <select
                        value={actor}
                        onChange={(e) => setActor(e.target.value)}
                        className="rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                    >
                        <option value="">All Actors</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                        <option value="caretaker">Caretaker</option>
                        <option value="renter">Renter</option>
                    </select>

                    <input
                        type="date"
                        value={fromDate}
                        onChange={(e) => setFromDate(e.target.value)}
                        className="rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                        title="From Date"
                    />

                    <input
                        type="date"
                        value={toDate}
                        onChange={(e) => setToDate(e.target.value)}
                        className="rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                        title="To Date"
                    />

                    <button
                        type="submit"
                        className="rounded-xl bg-primary px-4 py-2 font-semibold text-white shadow-sm hover:bg-primary-hover transition"
                    >
                        Filter
                    </button>
                </form>
            }
        >
            <Head title="Audit Logs - Admin" />

            <div className="rounded-2xl border border-border bg-surface shadow-card overflow-hidden">
                <div className="p-4 border-b border-border bg-surface-raised/40 flex items-center justify-between">
                    <span className="text-xs font-semibold text-text-subtle uppercase">
                        Recorded Activity Events ({total} total entries)
                    </span>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm text-text">
                        <thead className="border-b border-border bg-surface-raised/50 text-xs font-semibold uppercase text-text-subtle">
                            <tr>
                                <th className="px-5 py-3">Timestamp</th>
                                <th className="px-5 py-3">Action</th>
                                <th className="px-5 py-3">Actor</th>
                                <th className="px-5 py-3">Description</th>
                                <th className="px-5 py-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {logs.data.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-6 py-12 text-center text-text-subtle">
                                        No audit entries found matching the filter criteria.
                                    </td>
                                </tr>
                            ) : (
                                logs.data.map((log) => (
                                    <tr key={log.id} className="hover:bg-page/40 transition-colors">
                                        <td className="px-5 py-3 text-xs text-text-subtle whitespace-nowrap">
                                            {new Date(log.created_at).toLocaleString()}
                                        </td>
                                        <td className="px-5 py-3">
                                            <span
                                                className={`rounded px-2 py-0.5 text-xs font-bold uppercase font-mono ${
                                                    log.action === 'delete'
                                                        ? 'bg-red-500/10 text-red-500'
                                                        : log.action === 'create'
                                                        ? 'bg-emerald-500/10 text-emerald-500'
                                                        : log.action === 'update'
                                                        ? 'bg-blue-500/10 text-blue-500'
                                                        : 'bg-slate-500/10 text-slate-500'
                                                }`}
                                            >
                                                {log.action}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3 text-xs font-medium text-text">
                                            {log.actor_type}
                                            {log.actor_id ? ` #${log.actor_id}` : ''}
                                        </td>
                                        <td className="px-5 py-3 text-sm text-text max-w-md truncate" title={log.description}>
                                            {log.description}
                                        </td>
                                        <td className="px-5 py-3 font-mono text-xs text-text-subtle">
                                            {log.ip_address ?? '—'}
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
                            Page {logs.meta.current_page} of {logs.meta.last_page}
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

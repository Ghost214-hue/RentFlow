import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import type { AuthUser, MaintenanceRecord, Paginated, RenterOption } from '@/types';
import MaintenanceModal from './MaintenanceModal';

interface Props {
    records: Paginated<MaintenanceRecord>;
    renters: RenterOption[];
    filters: { status: string | null; priority: string | null };
    canBroadcast: boolean;
    flash?: { success?: string | null };
}

const PRIORITY_TONE: Record<string, string> = {
    urgent: 'bg-red-100 text-red-700',
    high: 'bg-orange-100 text-orange-700',
    medium: 'bg-amber-100 text-amber-700',
    low: 'bg-slate-100 text-slate-600',
};

const STATUS_TONE: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700',
    'in-progress': 'bg-blue-100 text-blue-700',
    completed: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-slate-100 text-slate-500',
};

const NEXT_LABEL: Record<string, string> = {
    pending: 'Start',
    'in-progress': 'Complete',
};

/**
 * Maintenance requests.
 *
 * Ported from frontend/pages/maintenance.php. A renter raises a request and
 * can follow its progress; only staff advance it or record a cost.
 */
export default function MaintenanceIndex({ records, renters, filters, canBroadcast, flash }: Props) {
    const user = (usePage().props.auth as { user: AuthUser | null }).user;
    const isRenter = user?.role === 'tenant';

    const [showModal, setShowModal] = useState(false);

    function applyFilter(status: string, priority: string) {
        router.get('/maintenance', {
            status: status || undefined,
            priority: priority || undefined,
        }, { preserveState: true });
    }

    function advance(record: MaintenanceRecord) {
        router.post(`/maintenance/${record.id}/advance`, { preserveScroll: true });
    }

    function remove(record: MaintenanceRecord) {
        if (!window.confirm(`Delete "${record.title}"?`)) return;
        router.delete(`/maintenance/${record.id}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Maintenance" />

            <AppLayout
                title="Maintenance"
                actions={
                    <>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">
                                {isRenter ? 'My Requests' : 'Maintenance'}
                            </h1>
                            <p className="text-slate-500 mt-1">
                                {isRenter ? 'Report a problem with your unit' : 'Repairs and upkeep'}
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={() => setShowModal(true)}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"
                        >
                            <i className="fas fa-plus" aria-hidden="true" />
                            {isRenter ? 'New Request' : 'New Notice'}
                        </button>
                    </>
                }
            >
                {flash?.success && (
                    <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">
                        {flash.success}
                    </div>
                )}

                <div className="mb-4 flex flex-col sm:flex-row gap-3">
                    <select value={filters.status ?? ''} onChange={(e) => applyFilter(e.target.value, filters.priority ?? '')}
                        aria-label="Filter by status"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <select value={filters.priority ?? ''} onChange={(e) => applyFilter(filters.status ?? '', e.target.value)}
                        aria-label="Filter by priority"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                        <option value="">All priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    <th className="px-6 py-4">Request</th>
                                    <th className="px-6 py-4">Unit</th>
                                    <th className="px-6 py-4">Priority</th>
                                    <th className="px-6 py-4">Status</th>
                                    {!isRenter && <th className="px-6 py-4">Cost</th>}
                                    <th className="px-6 py-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {records.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={isRenter ? 5 : 6} className="px-6 py-12 text-center text-slate-400">
                                            No maintenance requests found.
                                        </td>
                                    </tr>
                                ) : (
                                    records.data.map((record) => (
                                        <tr key={record.id} className="hover:bg-blue-50/30 transition-colors">
                                            <td className="px-6 py-4">
                                                <p className="text-sm font-medium text-slate-900">{record.title}</p>
                                                <p className="text-xs text-slate-500 mt-0.5">
                                                    {record.category ?? 'General'}
                                                    {record.assigned_to ? ` · ${record.assigned_to}` : ''}
                                                </p>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {record.house_unit ?? '-'}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${PRIORITY_TONE[record.priority] ?? PRIORITY_TONE.low}`}>
                                                    {record.priority}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${STATUS_TONE[record.status] ?? STATUS_TONE.pending}`}>
                                                    {record.status}
                                                </span>
                                            </td>
                                            {!isRenter && (
                                                <td className="px-6 py-4 text-sm text-slate-600">
                                                    {record.cost === null ? '-' : formatMoney(record.cost)}
                                                </td>
                                            )}
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-1">
                                                    {NEXT_LABEL[record.status] && !isRenter && (
                                                        <button type="button" onClick={() => advance(record)}
                                                            className="px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-all"
                                                            aria-label={`Advance ${record.title}`}>
                                                            {NEXT_LABEL[record.status]}
                                                        </button>
                                                    )}
                                                    {!isRenter && (
                                                        <button type="button" onClick={() => remove(record)}
                                                            className="p-1.5 text-slate-400 hover:text-red-600 transition-colors"
                                                            aria-label={`Delete ${record.title}`}>
                                                            <i className="fas fa-trash" aria-hidden="true" />
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {records.meta.last_page > 1 && (
                        <nav className="p-4 flex items-center justify-between border-t border-blue-50" aria-label="Pagination">
                            <p className="text-sm text-slate-500">
                                Page {records.meta.current_page} of {records.meta.last_page} ({records.meta.total} requests)
                            </p>
                            <div className="flex gap-2">
                                {records.links.prev && (
                                    <Link href={records.links.prev} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Previous</Link>
                                )}
                                {records.links.next && (
                                    <Link href={records.links.next} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next</Link>
                                )}
                            </div>
                        </nav>
                    )}
                </div>

                {showModal && (
                    <MaintenanceModal
                        renters={isRenter ? [] : renters}
                        canBroadcast={canBroadcast}
                        onClose={() => setShowModal(false)}
                    />
                )}
            </AppLayout>
        </>
    );
}
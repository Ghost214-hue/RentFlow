import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { statusTone } from '@/lib/status';
import type { AuthUser, Complaint, Paginated, RenterOption } from '@/types';
import ComplaintModal from './ComplaintModal';

interface Props {
    complaints: Paginated<Complaint>;
    renters: RenterOption[];
    filters: { status: string | null; direction: string | null };
    canBroadcast: boolean;
    flash?: { success?: string | null };
}

const PRIORITY_TONE: Record<string, string> = {
    high: 'bg-red-100 text-red-700',
    medium: 'bg-amber-100 text-amber-700',
    low: 'bg-slate-100 text-slate-600',
};

/**
 * Complaints.
 *
 * Ported from frontend/pages/complaints.php. Status and priority are shown
 * as the server labels them; the browser never derives either.
 */
export default function ComplaintsIndex({
    complaints,
    renters,
    filters,
    canBroadcast,
    flash,
}: Props) {
    const user = (usePage().props.auth as { user: AuthUser | null }).user;
    const isRenter = user?.role === 'tenant';

    const [showModal, setShowModal] = useState(false);

    function applyFilter(status: string, direction: string) {
        router.get('/complaints', { status: status || undefined, direction: direction || undefined }, { preserveState: true });
    }

    function advance(complaint: Complaint) {
        router.post(`/complaints/${complaint.id}/advance`, { preserveScroll: true });
    }

    function remove(complaint: Complaint) {
        if (!window.confirm(`Delete "${complaint.title}"?`)) return;
        router.delete(`/complaints/${complaint.id}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Complaints" />

            <AppLayout
                title="Complaints"
                actions={
                    <>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">
                                {isRenter ? 'My Complaints' : 'Complaints'}
                            </h1>
                            <p className="text-slate-500 mt-1">
                                {isRenter ? 'Raise an issue with your landlord' : 'Two-way communication'}
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={() => setShowModal(true)}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"
                        >
                            <i className="fas fa-plus" aria-hidden="true" />
                            {isRenter ? 'Raise Complaint' : 'New Notice'}
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
                    <select value={filters.status ?? ''} onChange={(e) => applyFilter(e.target.value, filters.direction ?? '')}
                        aria-label="Filter by status"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                        <option value="">All statuses</option>
                        <option value="open">Open</option>
                        <option value="in-progress">In progress</option>
                        <option value="resolved">Resolved</option>
                    </select>

                    {canBroadcast && (
                        <select value={filters.direction ?? ''} onChange={(e) => applyFilter(filters.status ?? '', e.target.value)}
                            aria-label="Filter by direction"
                            className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                            <option value="">All</option>
                            <option value="received">Raised by renters</option>
                            <option value="sent">Sent by staff</option>
                        </select>
                    )}
                </div>

                <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    <th className="px-6 py-4">Complaint</th>
                                    <th className="px-6 py-4">From</th>
                                    <th className="px-6 py-4">Priority</th>
                                    <th className="px-6 py-4">Status</th>
                                    <th className="px-6 py-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {complaints.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center text-slate-400">
                                            No complaints found.
                                        </td>
                                    </tr>
                                ) : (
                                    complaints.data.map((c) => {
                                        const status = statusTone(c.status);

                                        return (
                                            <tr key={c.id} className="hover:bg-blue-50/30 transition-colors">
                                                <td className="px-6 py-4">
                                                    <p className="text-sm font-medium text-slate-900">{c.title}</p>
                                                    <p className="text-xs text-slate-500 mt-0.5">
                                                        {c.category ?? 'General'}
                                                        {c.house_unit ? ` · Unit ${c.house_unit}` : ''}
                                                    </p>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-600">
                                                    {c.tenant_name ?? (c.sender_role ?? 'Staff')}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${PRIORITY_TONE[c.priority] ?? PRIORITY_TONE.low}`}>
                                                        {c.priority}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${status.className}`}>
                                                        {status.label}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center gap-1">
                                                        {c.status !== 'resolved' && !isRenter && (
                                                            <button type="button" onClick={() => advance(c)}
                                                                className="px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-all"
                                                                aria-label={`Advance ${c.title}`}>
                                                                {c.status === 'open' ? 'Start' : 'Resolve'}
                                                            </button>
                                                        )}
                                                        {!isRenter && (
                                                            <button type="button" onClick={() => remove(c)}
                                                                className="p-1.5 text-slate-400 hover:text-red-600 transition-colors"
                                                                aria-label={`Delete ${c.title}`}>
                                                                <i className="fas fa-trash" aria-hidden="true" />
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {complaints.meta.last_page > 1 && (
                        <nav className="p-4 flex items-center justify-between border-t border-blue-50" aria-label="Pagination">
                            <p className="text-sm text-slate-500">
                                Page {complaints.meta.current_page} of {complaints.meta.last_page} ({complaints.meta.total} complaints)
                            </p>
                            <div className="flex gap-2">
                                {complaints.links.prev && (
                                    <Link href={complaints.links.prev} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Previous</Link>
                                )}
                                {complaints.links.next && (
                                    <Link href={complaints.links.next} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next</Link>
                                )}
                            </div>
                        </nav>
                    )}
                </div>

                {showModal && (
                    <ComplaintModal
                        renters={isRenter ? [] : renters}
                        canBroadcast={canBroadcast}
                        onClose={() => setShowModal(false)}
                    />
                )}
            </AppLayout>
        </>
    );
}
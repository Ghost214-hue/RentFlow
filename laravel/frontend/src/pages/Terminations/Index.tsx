import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Termination {
    id: number;
    renter_name: string | null;
    property_name: string | null;
    house_unit: string | null;
    initiated_by: string;
    reason: string | null;
    effective_date: string | null;
    status: string;
    created_at: string | null;
}

interface Props {
    terminations: {
        data: Termination[];
        meta: { current_page: number; last_page: number; total: number };
    };
    canAct: boolean;
    filters: { status: string | null };
}

const STATUS_TONE: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-blue-100 text-blue-700',
    completed: 'bg-emerald-100 text-emerald-700',
};

const INITIATOR: Record<string, string> = {
    tenant: 'Renter',
    owner: 'Owner',
    caretaker: 'Caretaker',
};

/**
 * Tenancy termination history.
 *
 * Ported from the audit view in TenantController::listTerminations(). A renter
 * may read their own history but cannot act on it; canAct is false for them.
 */
export default function TerminationsIndex({ terminations, canAct, filters }: Props) {
    function filterByStatus(status: string) {
        router.get('/terminations', { status: status || undefined }, { preserveState: true });
    }

    return (
        <>
            <Head title="Terminations" />

            <AppLayout
                title="Terminations"
                actions={
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">Tenancy Terminations</h1>
                        <p className="text-slate-500 mt-1">Requests, approvals and completions</p>
                    </div>
                }
            >
                <div className="mb-4">
                    <select value={filters.status ?? ''} onChange={(e) => filterByStatus(e.target.value)}
                        aria-label="Filter by status"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    <th className="px-6 py-4">Renter</th>
                                    <th className="px-6 py-4">Unit</th>
                                    <th className="px-6 py-4">Initiated by</th>
                                    <th className="px-6 py-4">Reason</th>
                                    <th className="px-6 py-4">Effective</th>
                                    <th className="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {terminations.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-12 text-center text-slate-400">
                                            No tenancy terminations recorded.
                                        </td>
                                    </tr>
                                ) : (
                                    terminations.data.map((t) => (
                                        <tr key={t.id} className="hover:bg-blue-50/30 transition-colors">
                                            <td className="px-6 py-4 text-sm font-medium text-slate-900">
                                                {t.renter_name ?? '-'}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {t.property_name ?? '-'}
                                                {t.house_unit ? ` · ${t.house_unit}` : ''}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {INITIATOR[t.initiated_by] ?? t.initiated_by}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">
                                                {t.reason ?? '-'}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-500">
                                                {t.effective_date ?? '-'}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`px-2 py-1 rounded-full text-xs font-medium capitalize ${STATUS_TONE[t.status] ?? STATUS_TONE.pending}`}>
                                                    {t.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {!canAct && (
                    <p className="mt-4 text-xs text-slate-400">
                        Terminations are requested from your tenancy page and actioned by your landlord.
                    </p>
                )}
            </AppLayout>
        </>
    );
}
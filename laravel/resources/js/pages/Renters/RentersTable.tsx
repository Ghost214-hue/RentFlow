import { Link } from '@inertiajs/react';
import { formatMoney } from '@/lib/money';
import type { Paginated, Renter } from '@/types';
import { initials } from '@/pages/Houses/helpers';

interface TableProps {
    renters: Paginated<Renter>;
    canManage: boolean;
    onEdit: (renter: Renter) => void;
    /** Open the terminate modal, or approve a pending request. */
    onTerminate: (renter: Renter, mode: 'terminate' | 'approve') => void;
}

/**
 * Renters table, ported from frontend/pages/tenants.php.
 *
 * The legacy page built money strings inline with toLocaleString(); here
 * formatMoney() owns KES formatting so it lives in exactly one place.
 */
export default function RentersTable({
    renters,
    canManage,
    onEdit,
    onTerminate,
}: TableProps) {
    return (
        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead>
                        <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th className="px-6 py-4">Renter</th>
                            <th className="px-6 py-4">Property</th>
                            <th className="px-6 py-4">Unit</th>
                            <th className="px-6 py-4">Rent</th>
                            <th className="px-6 py-4">Balance</th>
                            <th className="px-6 py-4">Status</th>
                            <th className="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-blue-50">
                        {renters.data.length === 0 ? (
                            <tr>
                                <td colSpan={7} className="px-6 py-12 text-center text-slate-400">
                                    No renters found.
                                </td>
                            </tr>
                        ) : (
                            renters.data.map((r) => (
                                <tr key={r.id} className="hover:bg-blue-50/30 transition-colors">
                                    <td className="px-6 py-4">
                                        <Link href={`/renters/${r.id}`} className="flex items-center gap-2 hover:underline">
                                            <span className="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                                                {initials(r.name)}
                                            </span>
                                            <span className="text-sm text-slate-700 font-medium">{r.name}</span>
                                        </Link>
                                    </td>
                                    <td className="px-6 py-4 text-sm text-slate-600">{r.property_name ?? 'N/A'}</td>
                                    <td className="px-6 py-4 text-sm text-slate-600">{r.house_unit ?? '-'}</td>
                                    <td className="px-6 py-4 text-sm text-slate-900 font-medium">{formatMoney(r.rent)}</td>
                                    <td className={`px-6 py-4 text-sm font-medium ${Number(r.balance) > 0 ? 'text-red-600' : 'text-emerald-600'}`}>
                                        {formatMoney(r.balance)}
                                    </td>
                                    <td className="px-6 py-4">
                                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${r.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                                            {r.status}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4">
                                        <div className="flex items-center gap-1">
                                            <Link href={`/renters/${r.id}`} className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors" aria-label={`View ${r.name}`}>
                                                <i className="fas fa-eye" aria-hidden="true" />
                                            </Link>
                                            {canManage && (
                                                <>
                                                    <button type="button" onClick={() => onEdit(r)} className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors" aria-label={`Edit ${r.name}`}>
                                                        <i className="fas fa-pen" aria-hidden="true" />
                                                    </button>
                                                    {/*
                                                        A tenancy is ENDED, never deleted: the
                                                        unit is freed, the personal data is
                                                        scrubbed and the financial history is
                                                        kept. There is no remove action.
                                                    */}
                                                    {r.status === 'pending_termination' ? (
                                                        <button
                                                            type="button"
                                                            onClick={() => onTerminate(r, 'approve')}
                                                            className="p-1.5 text-slate-400 hover:text-emerald-600 transition-colors"
                                                            aria-label={`Approve termination for ${r.name}`}>
                                                            <i className="fas fa-check" aria-hidden="true" />
                                                        </button>
                                                    ) : r.status === 'active' ? (
                                                        <button
                                                            type="button"
                                                            onClick={() => onTerminate(r, 'terminate')}
                                                            className="p-1.5 text-slate-400 hover:text-red-600 transition-colors"
                                                            aria-label={`Terminate tenancy for ${r.name}`}>
                                                            <i className="fas fa-door-open" aria-hidden="true" />
                                                        </button>
                                                    ) : null}
                                                </>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {renters.meta.last_page > 1 && (
                <nav className="p-4 flex items-center justify-between border-t border-blue-50" aria-label="Pagination">
                    <p className="text-sm text-slate-500">
                        Page {renters.meta.current_page} of {renters.meta.last_page} ({renters.meta.total} renters)
                    </p>
                    <div className="flex gap-2">
                        {renters.links.prev && (
                            <Link href={renters.links.prev} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Previous</Link>
                        )}
                        {renters.links.next && (
                            <Link href={renters.links.next} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next</Link>
                        )}
                    </div>
                </nav>
            )}
        </div>
    );
}
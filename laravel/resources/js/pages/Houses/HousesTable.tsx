import { Link } from '@inertiajs/react';
import { formatAmount } from '@/lib/money';
import type { House, Paginated } from '@/types';
import { initials } from './helpers';

interface TableProps {
    houses: Paginated<House>;
    canManage: boolean;
    onEdit: (house: House) => void;
    onDelete: (house: House) => void;
}

function HouseRow({ house, canManage, onEdit, onDelete }: {
    house: House; canManage: boolean; onEdit: (h: House) => void; onDelete: (h: House) => void;
}) {
    return (
        <tr className="hover:bg-blue-50/30 transition-colors">
            <td className="px-6 py-4 font-medium text-slate-900">{house.unit}</td>
            <td className="px-6 py-4 text-sm text-slate-600">{house.property_name ?? 'N/A'}</td>
            <td className="px-6 py-4 text-sm text-slate-500">{house.type}</td>
            <td className="px-6 py-4">
                {house.tenant_name ? (
                    <div className="flex items-center gap-2">
                        <div className="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                            {initials(house.tenant_name)}
                        </div>
                        <span className="text-sm text-slate-700">{house.tenant_name}</span>
                    </div>
                ) : (
                    <span className="text-sm text-slate-400">-</span>
                )}
            </td>
            <td className="px-6 py-4 text-sm font-medium text-slate-900">KES {formatAmount(house.rent)}</td>
            <td className="px-6 py-4">
                <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                    house.status === 'occupied' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'
                }`}>{house.status}</span>
            </td>
            <td className="px-6 py-4">
                <div className="flex items-center gap-1">
                    {/* View the unit profile. Available to every staff role,
                        including a caretaker who cannot edit the unit. */}
                    <Link href={`/houses/${house.id}`}
                        className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                        aria-label={`View unit ${house.unit}`}>
                        <i className="fas fa-eye" aria-hidden="true" />
                    </Link>
                    {canManage && (
                        <>
                            <button type="button" onClick={() => onEdit(house)}
                                className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                                aria-label={`Edit unit ${house.unit}`}>
                                <i className="fas fa-pen" aria-hidden="true" />
                            </button>
                            <button type="button" onClick={() => onDelete(house)}
                                className="p-1.5 text-slate-400 hover:text-red-600 transition-colors"
                                aria-label={`Delete unit ${house.unit}`}>
                                <i className="fas fa-trash" aria-hidden="true" />
                            </button>
                        </>
                    )}
                </div>
            </td>
        </tr>
    );
}

/** The units table, ported from the legacy houses.php markup. */
export default function HousesTable({ houses, canManage, onEdit, onDelete }: TableProps) {
    return (
        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead>
                        <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th className="px-6 py-4">Unit</th>
                            <th className="px-6 py-4">Property</th>
                            <th className="px-6 py-4">Type</th>
                            <th className="px-6 py-4">Tenant</th>
                            <th className="px-6 py-4">Rent</th>
                            <th className="px-6 py-4">Status</th>
                            <th className="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-blue-50">
                        {houses.data.length === 0 ? (
                            <tr><td colSpan={7} className="px-6 py-12 text-center text-slate-400">No units found.</td></tr>
                        ) : (
                            houses.data.map((house) => (
                                <HouseRow key={house.id} house={house} canManage={canManage}
                                    onEdit={onEdit} onDelete={onDelete} />
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {houses.meta.last_page > 1 && (
                <nav className="p-4 flex items-center justify-between border-t border-blue-50" aria-label="Pagination">
                    <p className="text-sm text-slate-500">
                        Page {houses.meta.current_page} of {houses.meta.last_page} ({houses.meta.total} units)
                    </p>
                    <div className="flex gap-2">
                        {houses.links.prev && (
                            <Link href={houses.links.prev} preserveScroll
                                className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Previous</Link>
                        )}
                        {houses.links.next && (
                            <Link href={houses.links.next} preserveScroll
                                className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next</Link>
                        )}
                    </div>
                </nav>
            )}
        </div>
    );
}
import { Link } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { formatMoney } from '@/lib/money';
import type { Paginated, Renter } from '@/types';
import { initials } from '@/pages/Houses/helpers';

interface CallOption {
    label: string;
    value: string;
}

function callOptions(r: Renter): CallOption[] {
    const options: CallOption[] = [];
    if (r.phone) {
        options.push({ label: `Call ${r.name}`, value: r.phone });
    }
    if (r.next_of_kin_phone) {
        options.push({
            label: r.next_of_kin_name
                ? `Call ${r.next_of_kin_name} (Next of Kin)`
                : 'Call Next of Kin',
            value: r.next_of_kin_phone,
        });
    }
    return options;
}

/**
 * One-click contact for a renter row.
 *
 * Clicking the phone icon shows who can be called (the renter and/or their
 * next of kin). Selecting an option opens a `tel:` link so the device can
 * place the call immediately - the mobile dialler on phones, or a linked
 * calling app on a capable laptop - without opening the renter's profile
 * first.
 */
function ContactButton({ renter }: { renter: Renter }) {
    const [open, setOpen] = useState(false);
    const buttonRef = useRef<HTMLDivElement>(null);
    const options = callOptions(renter);

    useEffect(() => {
        if (!open) return;
        function onClickOutside(e: MouseEvent) {
            if (buttonRef.current && !buttonRef.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', onClickOutside);
        return () => document.removeEventListener('mousedown', onClickOutside);
    }, [open]);

    // Always render a phone button. If no number is on file, show it greyed-out
    // and disabled so the user can see at a glance that the field is empty.
    const hasOptions = options.length > 0;

    return (
        <div ref={buttonRef} className="relative inline-block">
            <button
                type="button"
                disabled={!hasOptions}
                onClick={(e) => {
                    e.stopPropagation();
                    if (hasOptions) setOpen((o) => !o);
                }}
                aria-label={hasOptions ? `Contact ${renter.name}` : `No phone on file for ${renter.name}`}
                title={
                    !hasOptions
                        ? 'No phone number on file'
                        : options.length === 1
                        ? options[0]!.label
                        : `Call ${renter.name} or next of kin`
                }
                className={`p-1.5 transition-colors rounded ${
                    hasOptions
                        ? 'text-slate-400 hover:text-blue-600 cursor-pointer'
                        : 'text-slate-200 cursor-not-allowed'
                }`}
            >
                <i className="fas fa-phone text-sm" aria-hidden="true" />
            </button>
            {open && hasOptions && (
                <div className="absolute right-0 z-20 mt-1 flex flex-col gap-0.5 bg-white border border-slate-200 rounded-xl shadow-xl p-1.5 min-w-[190px]">
                    {options.map((o, i) => (
                        <a
                            key={i}
                            href={`tel:${o.value}`}
                            onClick={() => setOpen(false)}
                            className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors"
                        >
                            <i
                                className={`fas ${i === 0 ? 'fa-phone' : 'fa-user-shield'} w-4 text-center text-slate-400`}
                                aria-hidden="true"
                            />
                            <span className="flex flex-col leading-tight">
                                <span className="font-medium">{o.label}</span>
                                <span className="text-xs text-slate-400">{o.value}</span>
                            </span>
                        </a>
                    ))}
                </div>
            )}
        </div>
    );
}


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
                                            <ContactButton renter={r} />
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
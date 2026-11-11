import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import type { Caretaker, PropertyOption } from '@/types';
import CaretakerModal from './CaretakerModal';

interface Props {
    caretakers: Caretaker[];
    properties: PropertyOption[];
    flash?: { success?: string | null };
}

/**
 * Caretakers.
 *
 * Ported from frontend/pages/caretakers.php. The assigned_properties CSV is
 * presented as checkboxes, so no client code parses a delimited string.
 */
export default function CaretakersIndex({ caretakers, properties, flash }: Props) {
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Caretaker | null>(null);

    function remove(caretaker: Caretaker) {
        if (!window.confirm(`Remove ${caretaker.name}?`)) return;
        router.delete(`/caretakers/${caretaker.id}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Caretakers" />

            <AppLayout
                title="Caretakers"
                actions={
                    <>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">Caretakers</h1>
                            <p className="text-slate-500 mt-1">Staff managing your properties</p>
                        </div>

                        <button
                            type="button"
                            onClick={() => { setEditing(null); setShowModal(true); }}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"
                        >
                            <i className="fas fa-plus" aria-hidden="true" />
                            Add Caretaker
                        </button>
                    </>
                }
            >
                {flash?.success && (
                    <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">
                        {flash.success}
                    </div>
                )}

                <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    <th className="px-6 py-4">Name</th>
                                    <th className="px-6 py-4">Email</th>
                                    <th className="px-6 py-4">Phone</th>
                                    <th className="px-6 py-4">Properties</th>
                                    <th className="px-6 py-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {caretakers.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center text-slate-400">
                                            No caretakers yet.
                                        </td>
                                    </tr>
                                ) : (
                                    caretakers.map((c) => (
                                        <tr key={c.id} className="hover:bg-blue-50/30 transition-colors">
                                            <td className="px-6 py-4 text-sm font-medium text-slate-900">{c.name}</td>
                                            <td className="px-6 py-4 text-sm text-slate-600">{c.email}</td>
                                            <td className="px-6 py-4 text-sm text-slate-600">{c.phone ?? '-'}</td>
                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {c.assigned_count === 0 ? (
                                                    <span className="text-slate-400">None assigned</span>
                                                ) : (
                                                    <span className="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                        {c.assigned_count} propert{c.assigned_count === 1 ? 'y' : 'ies'}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-1">
                                                    <button type="button" onClick={() => { setEditing(c); setShowModal(true); }}
                                                        className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                                                        aria-label={`Edit ${c.name}`}>
                                                        <i className="fas fa-pen" aria-hidden="true" />
                                                    </button>
                                                    <button type="button" onClick={() => remove(c)}
                                                        className="p-1.5 text-slate-400 hover:text-red-600 transition-colors"
                                                        aria-label={`Remove ${c.name}`}>
                                                        <i className="fas fa-trash" aria-hidden="true" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {showModal && (
                    <CaretakerModal
                        editing={editing}
                        properties={properties}
                        onClose={() => { setShowModal(false); setEditing(null); }}
                    />
                )}
            </AppLayout>
        </>
    );
}
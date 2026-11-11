import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import type { Caretaker, PropertyOption } from '@/types';

const fieldClass = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

interface Props {
    editing: Caretaker | null;
    properties: PropertyOption[];
    onClose: () => void;
}

function Field({ id, label, error, children }: {
    id: string; label: string; error?: string; children: React.ReactNode;
}) {
    return (
        <div>
            <label htmlFor={id} className="block text-sm font-medium text-slate-700 mb-1">{label}</label>
            {children}
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

/** Add / edit a caretaker and the properties they are assigned to. */
export default function CaretakerModal({ editing, properties, onClose }: Props) {
    const { data, setData, post, put, processing, errors, clearErrors } = useForm({
        name: '',
        email: '',
        phone: '',
        id_number: '',
        password: '',
        assigned_properties: [] as number[],
    });

    useEffect(() => {
        if (editing === null) return;

        setData({
            name: editing.name,
            email: editing.email,
            phone: editing.phone ?? '',
            id_number: editing.id_number ?? '',
            password: '',
            assigned_properties: editing.assigned_properties,
        });
    }, [editing, setData]);

    function toggleProperty(id: number) {
        setData(
            'assigned_properties',
            data.assigned_properties.includes(id)
                ? data.assigned_properties.filter((p) => p !== id)
                : [...data.assigned_properties, id],
        );
    }

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        clearErrors();

        const options = { preserveScroll: true, onSuccess: () => onClose() };

        if (editing) {
            put(`/caretakers/${editing.id}`, options);
            return;
        }

        post('/caretakers', options);
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
            role="dialog" aria-modal="true" aria-labelledby="caretaker-modal-title">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6"
                onClick={(e) => e.stopPropagation()}>
                <div className="flex items-center justify-between mb-4">
                    <h3 id="caretaker-modal-title" className="text-lg font-bold text-slate-900">
                        {editing ? `Edit ${editing.name}` : 'Add Caretaker'}
                    </h3>
                    <button type="button" onClick={onClose} className="text-slate-400 hover:text-slate-600" aria-label="Close">
                        <i className="fas fa-times text-xl" aria-hidden="true" />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <Field id="caretakerName" label="Full Name" error={errors.name}>
                        <input id="caretakerName" value={data.name}
                            onChange={(e) => setData('name', e.target.value)} className={fieldClass} required />
                    </Field>

                    <Field id="caretakerEmail" label="Email" error={errors.email}>
                        <input id="caretakerEmail" type="email" value={data.email}
                            onChange={(e) => setData('email', e.target.value)} className={fieldClass} required />
                    </Field>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Field id="caretakerPhone" label="Phone" error={errors.phone}>
                            <input id="caretakerPhone" value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)} className={fieldClass} />
                        </Field>
                        <Field id="caretakerIdNumber" label="ID Number" error={errors.id_number}>
                            <input id="caretakerIdNumber" value={data.id_number}
                                onChange={(e) => setData('id_number', e.target.value)} className={fieldClass} />
                        </Field>
                    </div>

                    {!editing && (
                        <Field id="caretakerPassword" label="Password (optional)" error={errors.password}>
                            <input id="caretakerPassword" type="password" value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className={fieldClass}
                                placeholder="Leave blank to send a setup link" />
                        </Field>
                    )}

                    <fieldset>
                        <legend className="block text-sm font-medium text-slate-700 mb-2">
                            Assigned properties
                        </legend>

                        {properties.length === 0 ? (
                            <p className="text-sm text-slate-400">No properties available.</p>
                        ) : (
                            <div className="space-y-2 max-h-40 overflow-y-auto">
                                {properties.map((p) => (
                                    <label key={p.id} className="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={data.assigned_properties.includes(p.id)}
                                            onChange={() => toggleProperty(p.id)}
                                            className="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span className="text-sm text-slate-700">{p.name}</span>
                                    </label>
                                ))}
                            </div>
                        )}
                    </fieldset>

                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose}
                            className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" disabled={processing}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60">
                            {processing ? 'Saving...' : editing ? 'Save Changes' : 'Add Caretaker'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
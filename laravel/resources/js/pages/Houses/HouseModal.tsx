import { useForm } from '@inertiajs/react';
import { isValidMoney, normaliseMoneyInput } from '@/lib/money';
import type { House, PropertyOption } from '@/types';

const TYPES = ['flat', 'bedsitter', 'studio', 'apartment', 'house'];
const fieldClass = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

interface ModalProps {
    editing: House | null;
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

/**
 * Add / Edit unit modal.
 *
 * Validation lives on the server (StoreHouseRequest); these field checks only
 * give immediate feedback. Non-owners never reach this: the policy rejects
 * them on the controller too.
 */
export default function HouseModal({ editing, properties, onClose }: ModalProps) {
    const { data, setData, post, put, processing, errors, clearErrors } = useForm({
        property_id: '', unit: '', type: 'flat', rent: '', water_meter: '', elec_meter: '',
    });

    // Seed the form when editing an existing unit.
    if (editing !== null && data.unit !== editing.unit) {
        clearErrors();
        setData({
            property_id: String(editing.property_id),
            unit: editing.unit,
            type: editing.type,
            rent: editing.rent,
            water_meter: editing.water_meter ?? '',
            elec_meter: editing.elec_meter ?? '',
        });
    }

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (!isValidMoney(data.rent)) return;

        const options = { preserveScroll: true, onSuccess: () => onClose() };
        if (editing) { put(`/houses/${editing.id}`, options); return; }
        post('/houses', options);
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
            role="dialog" aria-modal="true" aria-labelledby="house-modal-title">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6"
                onClick={(e) => e.stopPropagation()}>
                <div className="flex items-center justify-between mb-4">
                    <h3 id="house-modal-title" className="text-lg font-bold text-slate-900">
                        {editing ? `Edit Unit ${editing.unit}` : 'Add Unit'}
                    </h3>
                    <button type="button" onClick={onClose}
                        className="text-slate-400 hover:text-slate-600" aria-label="Close">
                        <i className="fas fa-times text-xl" aria-hidden="true" />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <Field id="houseProperty" label="Property" error={errors.property_id}>
                        <select id="houseProperty" value={data.property_id}
                            onChange={(e) => setData('property_id', e.target.value)} className={fieldClass}>
                            <option value="">Select property</option>
                            {properties.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                        </select>
                    </Field>

                    <Field id="houseUnit" label="Unit" error={errors.unit}>
                        <input id="houseUnit" value={data.unit}
                            onChange={(e) => setData('unit', e.target.value)}
                            className={fieldClass} placeholder="e.g. A1" required />
                    </Field>

                    <Field id="houseType" label="Type" error={errors.type}>
                        <select id="houseType" value={data.type}
                            onChange={(e) => setData('type', e.target.value)} className={fieldClass}>
                            {TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
                        </select>
                    </Field>

                    <Field id="houseRent" label="Monthly Rent (KES)" error={errors.rent}>
                        <input id="houseRent" inputMode="decimal" value={data.rent}
                            onChange={(e) => setData('rent', normaliseMoneyInput(e.target.value))}
                            className={fieldClass} placeholder="6500.00" required />
                    </Field>

                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose}
                            className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" disabled={processing}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60">
                            {processing ? 'Saving...' : editing ? 'Save Changes' : 'Add Unit'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
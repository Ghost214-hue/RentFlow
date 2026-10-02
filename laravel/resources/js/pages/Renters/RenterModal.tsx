import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { isValidMoney, normaliseMoneyInput } from '@/lib/money';
import type { PropertyOption, Renter, VacantHouse } from '@/types';

const ID_TYPES = ['National ID', 'Passport', 'Birth Certificate'];
const fieldClass = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

interface ModalProps {
    editing: Renter | null;
    properties: PropertyOption[];
    vacantHouses: VacantHouse[];
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
 * Onboard / edit a renter.
 *
 * Mirrors the legacy tenants.php onboarding form. Validation is enforced on
 * the server (StoreRenterRequest); these checks only give fast feedback.
 */
export default function RenterModal({
    editing,
    properties,
    vacantHouses,
    onClose,
}: ModalProps) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: '',
        email: '',
        phone: '',
        id_type: 'National ID',
        id_number: '',
        property_id: '',
        house_id: '',
        rent: '',
        deposit: '',
        opening_balance: '',
        lease_start: '',
        lease_end: '',
        next_of_kin_name: '',
        next_of_kin_phone: '',
        next_of_kin_email: '',
    });

    // Seed the form when editing.
    useEffect(() => {
        if (editing === null) return;

        setData({
            name: editing.name,
            email: editing.email ?? '',
            phone: editing.phone,
            id_type: editing.id_type,
            id_number: editing.id_number ?? '',
            property_id: String(editing.property_id ?? ''),
            house_id: String(editing.house_id ?? ''),
            rent: editing.rent,
            deposit: editing.deposit,
            opening_balance: editing.balance,
            lease_start: editing.lease_start ?? '',
            lease_end: editing.lease_end ?? '',
            next_of_kin_name: editing.next_of_kin_name ?? '',
            next_of_kin_phone: editing.next_of_kin_phone ?? '',
            next_of_kin_email: editing.next_of_kin_email ?? '',
        });
    }, [editing, setData]);

    // Only units in the selected property are offered.
    const housesForProperty = data.property_id === ''
        ? []
        : vacantHouses.filter((h) => h.property_id === Number(data.property_id));

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!isValidMoney(data.rent)) return;

        const options = { preserveScroll: true, onSuccess: () => onClose() };

        if (editing) {
            put(`/renters/${editing.id}`, options);
            return;
        }

        post('/renters', options);
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
            role="dialog" aria-modal="true" aria-labelledby="renter-modal-title">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6"
                onClick={(e) => e.stopPropagation()}>
                <div className="flex items-center justify-between mb-4">
                    <h3 id="renter-modal-title" className="text-lg font-bold text-slate-900">
                        {editing ? `Edit ${editing.name}` : 'Add Renter'}
                    </h3>
                    <button type="button" onClick={onClose} className="text-slate-400 hover:text-slate-600" aria-label="Close">
                        <i className="fas fa-times text-xl" aria-hidden="true" />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Field id="renterName" label="Full Name" error={errors.name}>
                            <input id="renterName" value={data.name} onChange={(e) => setData('name', e.target.value)} className={fieldClass} required />
                        </Field>
                        <Field id="renterPhone" label="Phone" error={errors.phone}>
                            <input id="renterPhone" value={data.phone} onChange={(e) => setData('phone', e.target.value)} className={fieldClass} required />
                        </Field>
                        <Field id="renterEmail" label="Email (optional)" error={errors.email}>
                            <input id="renterEmail" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className={fieldClass} />
                        </Field>
                        <Field id="renterIdType" label="ID Type" error={errors.id_type}>
                            <select id="renterIdType" value={data.id_type} onChange={(e) => setData('id_type', e.target.value)} className={fieldClass}>
                                {ID_TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
                            </select>
                        </Field>
                        <Field id="renterIdNumber" label="ID Number" error={errors.id_number}>
                            <input id="renterIdNumber" value={data.id_number} onChange={(e) => setData('id_number', e.target.value)} className={fieldClass} />
                        </Field>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Field id="renterProperty" label="Property" error={errors.property_id}>
                            <select id="renterProperty" value={data.property_id}
                                onChange={(e) => { setData('property_id', e.target.value); setData('house_id', ''); }}
                                className={fieldClass}>
                                <option value="">Select property</option>
                                {properties.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                            </select>
                        </Field>
                        <Field id="renterHouse" label="Unit" error={errors.house_id}>
                            <select id="renterHouse" value={data.house_id}
                                onChange={(e) => {
                                    setData('house_id', e.target.value);
                                    const house = vacantHouses.find((h) => h.id === Number(e.target.value));
                                    // Prefill rent from the unit. The string is
                                    // shown as-is; nothing is computed here.
                                    if (house) setData('rent', house.rent);
                                }}
                                className={fieldClass}>
                                <option value="">Select vacant unit</option>
                                {housesForProperty.map((h) => (
                                    <option key={h.id} value={h.id}>{h.unit}</option>
                                ))}
                            </select>
                        </Field>
                        <Field id="renterRent" label="Monthly Rent (KES)" error={errors.rent}>
                            <input id="renterRent" inputMode="decimal" value={data.rent}
                                onChange={(e) => setData('rent', normaliseMoneyInput(e.target.value))}
                                className={fieldClass} placeholder="6500.00" required />
                        </Field>
                        <Field id="renterDeposit" label="Security Deposit (KES)" error={errors.deposit}>
                            <input id="renterDeposit" inputMode="decimal" value={data.deposit}
                                onChange={(e) => setData('deposit', normaliseMoneyInput(e.target.value))}
                                className={fieldClass} placeholder="0.00" />
                        </Field>
                        <Field id="renterOpening" label="Opening Balance (KES)" error={errors.opening_balance}>
                            <input id="renterOpening" inputMode="decimal" value={data.opening_balance}
                                onChange={(e) => setData('opening_balance', normaliseMoneyInput(e.target.value))}
                                className={fieldClass} placeholder="0.00" />
                        </Field>
                        <Field id="renterLeaseStart" label="Lease Start" error={errors.lease_start}>
                            <input id="renterLeaseStart" type="date" value={data.lease_start}
                                onChange={(e) => setData('lease_start', e.target.value)} className={fieldClass} />
                        </Field>
                        <Field id="renterLeaseEnd" label="Lease End" error={errors.lease_end}>
                            <input id="renterLeaseEnd" type="date" value={data.lease_end}
                                onChange={(e) => setData('lease_end', e.target.value)} className={fieldClass} />
                        </Field>
                    </div>

                    <div className="border-t border-blue-50 pt-4">
                        <h4 className="text-sm font-semibold text-slate-700 mb-3">Next of Kin</h4>
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <Field id="nokName" label="Name" error={errors.next_of_kin_name}>
                                <input id="nokName" value={data.next_of_kin_name} onChange={(e) => setData('next_of_kin_name', e.target.value)} className={fieldClass} />
                            </Field>
                            <Field id="nokPhone" label="Phone" error={errors.next_of_kin_phone}>
                                <input id="nokPhone" value={data.next_of_kin_phone} onChange={(e) => setData('next_of_kin_phone', e.target.value)} className={fieldClass} />
                            </Field>
                            <Field id="nokEmail" label="Email" error={errors.next_of_kin_email}>
                                <input id="nokEmail" type="email" value={data.next_of_kin_email} onChange={(e) => setData('next_of_kin_email', e.target.value)} className={fieldClass} />
                            </Field>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose} className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" disabled={processing}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60">
                            {processing ? 'Saving...' : editing ? 'Save Changes' : 'Add Renter'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
import { useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { isValidMoney, normaliseMoneyInput } from '@/lib/money';
import type { PropertyOption, Renter, VacantHouse } from '@/types';

/*
 * Renter onboarding wizard.
 *
 * Ported from the 5-step wizard in frontend/pages/tenants.php:
 *
 *   1 Personal    name, email, phone
 *   2 Identity    ID type, ID number
 *   3 Next of kin name, phone, email
 *   4 Lease       property, unit, lease period
 *   5 Finance     rent, deposit, opening balance
 *
 * The legacy version crammed fifteen fields onto one scrollable page, which
 * made the deposit toggle and the unit assignment easy to miss. Splitting it
 * means each screen asks for one kind of answer, and the progress indicator
 * shows how much is left.
 *
 * Per-step checks are for fast feedback only. The server (StoreRenterRequest)
 * is the authority.
 */

const STEPS = [
    { label: 'Personal', icon: 'fa-user', tone: 'blue' },
    { label: 'ID', icon: 'fa-id-card', tone: 'emerald' },
    { label: 'Next of Kin', icon: 'fa-users', tone: 'teal' },
    { label: 'Lease', icon: 'fa-file-contract', tone: 'amber' },
    { label: 'Finance', icon: 'fa-money-bill-wave', tone: 'purple' },
] as const;

const ID_TYPES = ['National ID', 'Passport', 'Driving License', 'Alien ID', 'Birth Certificate'];

const PANEL: Record<string, string> = {
    blue: 'from-blue-50 to-blue-100/50 border-blue-100/80',
    emerald: 'from-emerald-50 to-emerald-100/50 border-emerald-100/80',
    teal: 'from-teal-50 to-teal-100/50 border-teal-100/80',
    amber: 'from-amber-50 to-amber-100/50 border-amber-100/80',
    purple: 'from-purple-50 to-purple-100/50 border-purple-100/80',
};

const ICON_BG: Record<string, string> = {
    blue: 'from-blue-500 to-blue-600',
    emerald: 'from-emerald-500 to-emerald-600',
    teal: 'from-teal-500 to-teal-600',
    amber: 'from-amber-500 to-amber-600',
    purple: 'from-purple-500 to-purple-600',
};

interface Form {
    name: string;
    email: string;
    phone: string;
    id_type: string;
    id_number: string;
    next_of_kin_name: string;
    next_of_kin_phone: string;
    next_of_kin_email: string;
    property_id: string;
    house_id: string;
    lease_start: string;
    lease_end: string;
    rent: string;
    deposit: string;
    opening_balance: string;
}

const EMPTY: Form = {
    name: '', email: '', phone: '',
    id_type: 'National ID', id_number: '',
    next_of_kin_name: '', next_of_kin_phone: '', next_of_kin_email: '',
    property_id: '', house_id: '', lease_start: '', lease_end: '',
    rent: '', deposit: '', opening_balance: '',
};

const fieldClass = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

interface ModalProps {
    editing: Renter | null;
    properties: PropertyOption[];
    vacantHouses: VacantHouse[];
    onClose: () => void;
}

function Field({ id, label, error, hint, children }: {
    id: string; label: string; error?: string; hint?: string; children: React.ReactNode;
}) {
    return (
        <div>
            <label htmlFor={id} className="block text-sm font-medium text-slate-700 mb-1">
                {label}
            </label>
            {children}
            {hint && !error && <p className="text-xs text-slate-400 mt-1">{hint}</p>}
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

export default function RenterWizardModal({
    editing,
    properties,
    vacantHouses,
    onClose,
}: ModalProps) {
    const [step, setStep] = useState(0);
    const [depositOn, setDepositOn] = useState(false);
    const [stepError, setStepError] = useState<string | null>(null);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<Form>(EMPTY);

    useEffect(() => {
        if (editing === null) {
            setData(EMPTY);
            setStep(0);
            setDepositOn(false);
            return;
        }

        const form: Form = {
            name: editing.name,
            email: editing.email ?? '',
            phone: editing.phone,
            id_type: editing.id_type || 'National ID',
            id_number: editing.id_number ?? '',
            next_of_kin_name: editing.next_of_kin_name ?? '',
            next_of_kin_phone: editing.next_of_kin_phone ?? '',
            next_of_kin_email: editing.next_of_kin_email ?? '',
            property_id: String(editing.property_id ?? ''),
            house_id: String(editing.house_id ?? ''),
            lease_start: editing.lease_start ?? '',
            lease_end: editing.lease_end ?? '',
            rent: editing.rent,
            deposit: editing.deposit,
            opening_balance: editing.balance,
        };

        setData(form);
        setDepositOn(Number(editing.deposit) > 0);
        setStep(0);
    }, [editing, setData]);

    const housesForProperty = data.property_id === ''
        ? []
        : vacantHouses.filter((h) => h.property_id === Number(data.property_id));

    /** Validate the CURRENT step only. Returns true when it may advance. */
    function validateStep(index: number): boolean {
        setStepError(null);

        switch (index) {
            case 0:
                if (!data.name.trim()) return fail('Please enter the renter\'s name');
                if (!data.phone.trim()) return fail('Please enter a phone number');
                return true;
            case 1:
                if (!data.id_number.trim()) return fail('Please enter the ID number');
                return true;
            case 2:
                if (!data.next_of_kin_name.trim()) return fail('Please enter a next-of-kin name');
                if (!data.next_of_kin_phone.trim()) return fail('Please enter a next-of-kin phone');
                return true;
            case 3:
                if (!data.house_id) return fail('Please assign a unit');
                return true;
            case 4:
                if (!isValidMoney(data.rent) || Number(data.rent) <= 0) {
                    return fail('Please enter a valid monthly rent');
                }
                return true;
            default:
                return true;
        }

        function fail(message: string): boolean {
            setStepError(message);
            return false;
        }
    }

    function goTo(target: number) {
        // May only move FORWARD by validating the step being left.
        if (target > step && !validateStep(step)) return;
        setStepError(null);
        setStep(target);
    }

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        // Re-check every step, not just the visible one, so a value edited
        // earlier cannot slip through unchecked.
        for (let i = 0; i < STEPS.length; i++) {
            if (!validateStep(i)) {
                setStep(i);
                return;
            }
        }

        clearErrors();

        const options = { preserveScroll: true, onSuccess: () => onClose() };

        if (editing) {
            put(`/renters/${editing.id}`, options);
            return;
        }

        post('/renters', options);
    }

    // step is always within range, but the array index is still typed as
    // possibly undefined under strict mode.
    const current = STEPS[step] ?? STEPS[0];
    const tone = current.tone;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
            role="dialog" aria-modal="true" aria-labelledby="wizard-title">

            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-5 sm:p-6 lg:p-8"
                onClick={(e) => e.stopPropagation()}>

                <div className="flex items-start justify-between mb-6">
                    <div>
                        <h3 id="wizard-title" className="text-xl font-bold text-slate-900">
                            {editing ? `Edit ${editing.name}` : 'Add Renter'}
                        </h3>
                        <p className="text-sm text-slate-500 mt-0.5">
                            Step {step + 1} of {STEPS.length}
                        </p>
                    </div>
                    <button type="button" onClick={onClose}
                        className="text-slate-400 hover:text-slate-600 transition-colors"
                        aria-label="Close">
                        <i className="fas fa-times text-xl" aria-hidden="true" />
                    </button>
                </div>

                {/* Progress */}
                <ol className="flex items-center mb-8 px-2">
                    {STEPS.map((s, i) => (
                        <li key={s.label} className="flex items-center flex-1 last:flex-none">
                            <button type="button" onClick={() => goTo(i)}
                                className="flex flex-col items-center group"
                                aria-current={i === step ? 'step' : undefined}>
                                <span className={`w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all ${
                                    i < step
                                        ? 'bg-emerald-500 text-white'
                                        : i === step
                                            ? `bg-gradient-to-br ${ICON_BG[s.tone]} text-white shadow-lg`
                                            : 'bg-slate-200 text-slate-500 group-hover:bg-slate-300'
                                }`}>
                                    {i < step
                                        ? <i className="fas fa-check text-xs" aria-hidden="true" />
                                        : i + 1}
                                </span>
                                <span className={`text-xs font-medium mt-1.5 whitespace-nowrap ${
                                    i === step ? `text-${s.tone}-600` : i < step ? 'text-emerald-600' : 'text-slate-400'
                                }`}>
                                    {s.label}
                                </span>
                            </button>
                            {i < STEPS.length - 1 && (
                                <span className={`flex-1 h-0.5 mx-2 -mt-6 rounded ${
                                    i < step ? 'bg-emerald-400' : 'bg-slate-200'
                                }`} />
                            )}
                        </li>
                    ))}
                </ol>

                <form onSubmit={submit} noValidate>
                    {/* STEP 1 - Personal */}
                    {step === 0 && (
                        <div className={`bg-gradient-to-br ${PANEL.blue} rounded-xl p-5 border shadow-sm`}>
                            <div className="flex items-center gap-3 mb-5">
                                <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${ICON_BG.blue} text-white flex items-center justify-center text-lg`}>
                                    <i className={`fas ${STEPS[0].icon}`} aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 className="font-bold text-slate-900">Personal Information</h4>
                                    <p className="text-xs text-slate-500">Basic contact details</p>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <Field id="wzName" label="Full Name *" error={errors.name}>
                                    <input id="wzName" value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className={fieldClass}
                                        placeholder="e.g. John Mwangi Kiprop" required />
                                </Field>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Field id="wzEmail" label="Email Address" error={errors.email}>
                                        <input id="wzEmail" type="email" value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            className={fieldClass}
                                            placeholder="renter@example.com" />
                                    </Field>
                                    <Field id="wzPhone" label="Phone Number *" error={errors.phone}>
                                        <input id="wzPhone" type="tel" value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            className={fieldClass}
                                            placeholder="+254 712 345 678" required />
                                    </Field>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 2 - Identification */}
                    {step === 1 && (
                        <div className={`bg-gradient-to-br ${PANEL.emerald} rounded-xl p-5 border shadow-sm`}>
                            <div className="flex items-center gap-3 mb-5">
                                <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${ICON_BG.emerald} text-white flex items-center justify-center text-lg`}>
                                    <i className={`fas ${STEPS[1].icon}`} aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 className="font-bold text-slate-900">Identification</h4>
                                    <p className="text-xs text-slate-500">ID details</p>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Field id="wzIdType" label="ID Type" error={errors.id_type}>
                                        <select id="wzIdType" value={data.id_type}
                                            onChange={(e) => setData('id_type', e.target.value)}
                                            className={fieldClass}>
                                            {ID_TYPES.map((t) => (
                                                <option key={t} value={t}>{t}</option>
                                            ))}
                                        </select>
                                    </Field>
                                    <Field id="wzIdNumber" label="ID Number *" error={errors.id_number}>
                                        <input id="wzIdNumber" value={data.id_number}
                                            onChange={(e) => setData('id_number', e.target.value)}
                                            className={fieldClass}
                                            placeholder="ID / passport number" required />
                                    </Field>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 3 - Next of kin */}
                    {step === 2 && (
                        <div className={`bg-gradient-to-br ${PANEL.teal} rounded-xl p-5 border shadow-sm`}>
                            <div className="flex items-center gap-3 mb-5">
                                <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${ICON_BG.teal} text-white flex items-center justify-center text-lg`}>
                                    <i className={`fas ${STEPS[2].icon}`} aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 className="font-bold text-slate-900">Next of Kin</h4>
                                    <p className="text-xs text-slate-500">Emergency contact</p>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <Field id="wzKinName" label="Full Name *" error={errors.next_of_kin_name}>
                                    <input id="wzKinName" value={data.next_of_kin_name}
                                        onChange={(e) => setData('next_of_kin_name', e.target.value)}
                                        className={fieldClass}
                                        placeholder="e.g. Jane Mwangi" required />
                                </Field>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Field id="wzKinPhone" label="Phone Number *" error={errors.next_of_kin_phone}>
                                        <input id="wzKinPhone" type="tel" value={data.next_of_kin_phone}
                                            onChange={(e) => setData('next_of_kin_phone', e.target.value)}
                                            className={fieldClass}
                                            placeholder="+254 712 345 678" required />
                                    </Field>
                                    <Field id="wzKinEmail" label="Email Address" error={errors.next_of_kin_email}>
                                        <input id="wzKinEmail" type="email" value={data.next_of_kin_email}
                                            onChange={(e) => setData('next_of_kin_email', e.target.value)}
                                            className={fieldClass}
                                            placeholder="nextofkin@example.com" />
                                    </Field>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 4 - Lease */}
                    {step === 3 && (
                        <div className={`bg-gradient-to-br ${PANEL.amber} rounded-xl p-5 border shadow-sm`}>
                            <div className="flex items-center gap-3 mb-5">
                                <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${ICON_BG.amber} text-white flex items-center justify-center text-lg`}>
                                    <i className={`fas ${STEPS[3].icon}`} aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 className="font-bold text-slate-900">Lease &amp; Unit Assignment</h4>
                                    <p className="text-xs text-slate-500">Assign a property, unit and lease period</p>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Field id="wzProperty" label="Property" error={errors.property_id}>
                                        <select id="wzProperty" value={data.property_id}
                                            onChange={(e) => {
                                                setData('property_id', e.target.value);
                                                // Changing property invalidates the unit.
                                                setData('house_id', '');
                                            }}
                                            className={fieldClass}>
                                            <option value="">Select property...</option>
                                            {properties.map((p) => (
                                                <option key={p.id} value={p.id}>
                                                    {p.name}
                                                </option>
                                            ))}
                                        </select>
                                    </Field>
                                    <Field id="wzHouse" label="Unit *" error={errors.house_id}>
                                        <select id="wzHouse" value={data.house_id}
                                            onChange={(e) => {
                                                const houseId = e.target.value;
                                                setData('house_id', houseId);

                                                // Carry the unit's rent across, as the
                                                // legacy form did.
                                                const house = housesForProperty.find((h) => h.id === Number(houseId));
                                                if (house) setData('rent', house.rent);
                                            }}
                                            className={fieldClass}
                                            disabled={data.property_id === ''}>
                                            <option value="">
                                                {data.property_id === '' ? 'Select a property first' : 'Select unit...'}
                                            </option>
                                            {housesForProperty.map((h) => (
                                                <option key={h.id} value={h.id}>
                                                    {h.unit} (KES {h.rent})
                                                </option>
                                            ))}
                                        </select>
                                    </Field>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <Field id="wzLeaseStart" label="Lease Start" error={errors.lease_start}>
                                        <input id="wzLeaseStart" type="date" value={data.lease_start}
                                            onChange={(e) => setData('lease_start', e.target.value)}
                                            className={fieldClass} />
                                    </Field>
                                    <Field id="wzLeaseEnd" label="Lease End" error={errors.lease_end}>
                                        <input id="wzLeaseEnd" type="date" value={data.lease_end}
                                            onChange={(e) => setData('lease_end', e.target.value)}
                                            className={fieldClass} />
                                    </Field>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 5 - Finance */}
                    {step === 4 && (
                        <div className={`bg-gradient-to-br ${PANEL.purple} rounded-xl p-5 border shadow-sm`}>
                            <div className="flex items-center gap-3 mb-5">
                                <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${ICON_BG.purple} text-white flex items-center justify-center text-lg`}>
                                    <i className={`fas ${STEPS[4].icon}`} aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 className="font-bold text-slate-900">Financial Details</h4>
                                    <p className="text-xs text-slate-500">Rent, deposit and opening balance</p>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                                    <Field id="wzRent" label="Monthly Rent (KES) *" error={errors.rent}>
                                        {/* Kept as a string end to end; never parsed to a float. */}
                                        <input id="wzRent" inputMode="decimal" value={data.rent}
                                            onChange={(e) => setData('rent', normaliseMoneyInput(e.target.value))}
                                            className={fieldClass}
                                            placeholder="45000" required />
                                    </Field>

                                    <div>
                                        <span className="block text-sm font-medium text-slate-700 mb-2">
                                            Require Security Deposit?
                                        </span>
                                        <div className="flex items-center">
                                            <button type="button"
                                                onClick={() => {
                                                    const next = !depositOn;
                                                    setDepositOn(next);
                                                    // Default the deposit to one month's rent.
                                                    setData('deposit', next ? data.rent : '');
                                                }}
                                                role="switch"
                                                aria-checked={depositOn}
                                                aria-label="Require security deposit"
                                                className={`relative inline-flex h-9 w-16 items-center rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 ${
                                                    depositOn ? 'bg-blue-600' : 'bg-slate-300'
                                                }`}>
                                                <span className={`inline-block h-7 w-7 transform rounded-full bg-white shadow transition-all duration-200 ${
                                                    depositOn ? 'translate-x-9' : 'translate-x-1'
                                                }`} />
                                            </button>
                                            <span className={`ml-3 text-xs font-semibold uppercase tracking-wide ${
                                                depositOn ? 'text-blue-600' : 'text-slate-500'
                                            }`}>
                                                {depositOn ? 'On' : 'Off'}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {depositOn && (
                                    <Field id="wzDeposit" label="Security Deposit (KES)" error={errors.deposit}>
                                        <input id="wzDeposit" inputMode="decimal" value={data.deposit}
                                            onChange={(e) => setData('deposit', normaliseMoneyInput(e.target.value))}
                                            className={fieldClass}
                                            placeholder="Same as rent" />
                                    </Field>
                                )}

                                <Field id="wzBalance" label="Opening Balance (KES)" error={errors.opening_balance}
                                    hint="Only if the renter owes something from a previous period.">
                                    <input id="wzBalance" inputMode="decimal" value={data.opening_balance}
                                        onChange={(e) => setData('opening_balance', normaliseMoneyInput(e.target.value))}
                                        className={fieldClass}
                                        placeholder="0.00" />
                                </Field>
                            </div>
                        </div>
                    )}

                    {stepError && (
                        <p role="alert" className="mt-4 text-sm text-red-600 text-center">{stepError}</p>
                    )}

                    <div className="flex justify-between gap-3 mt-6 pt-4 border-t border-slate-100">
                        {step === 0 ? (
                            <button type="button" onClick={onClose}
                                className="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                        ) : (
                            <button type="button" onClick={() => goTo(step - 1)}
                                className="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all inline-flex items-center gap-2">
                                <i className="fas fa-arrow-left text-sm" aria-hidden="true" />
                                Back
                            </button>
                        )}

                        {step < STEPS.length - 1 ? (
                            <button type="button" onClick={() => goTo(step + 1)}
                                className={`px-6 py-2.5 bg-gradient-to-r ${ICON_BG[tone]} text-white font-semibold rounded-xl shadow-lg transition-all inline-flex items-center gap-2`}>
                                Next
                                <i className="fas fa-arrow-right text-sm" aria-hidden="true" />
                            </button>
                        ) : (
                            <button type="submit" disabled={processing}
                                className="px-8 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all inline-flex items-center gap-2 disabled:opacity-60">
                                <i className="fas fa-check-circle text-sm" aria-hidden="true" />
                                {processing
                                    ? 'Saving...'
                                    : editing ? 'Update Renter' : 'Register Renter'}
                            </button>
                        )}
                    </div>
                </form>
            </div>
        </div>
    );
}
import { useForm } from '@inertiajs/react';
import { isValidMoney, normaliseMoneyInput } from '@/lib/money';
import type { RenterOption } from '@/types';

const TYPES = ['Rent', 'Deposit', 'Water', 'Electricity', 'Mixed'];
const fieldClass = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

interface Props {
    renters: RenterOption[];
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
 * Record a payment.
 *
 * The server allocates it across the renter's arrears (chosen month first,
 * then oldest, remainder to credit), so this form never asks where the money
 * should go — that decision belongs to the allocation rule, not the user.
 */
export default function RecordPaymentModal({ renters, onClose }: Props) {
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        renter_id: '',
        amount: '',
        month: '',
        type: 'Rent',
        method: 'M-Pesa',
        date: '',
        description: '',
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (!isValidMoney(data.amount)) return;

        post('/payments', { preserveScroll: true, onSuccess: () => onClose() });
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
            role="dialog" aria-modal="true" aria-labelledby="payment-modal-title">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6"
                onClick={(e) => e.stopPropagation()}>
                <div className="flex items-center justify-between mb-4">
                    <h3 id="payment-modal-title" className="text-lg font-bold text-slate-900">Record Payment</h3>
                    <button type="button" onClick={onClose} className="text-slate-400 hover:text-slate-600" aria-label="Close">
                        <i className="fas fa-times text-xl" aria-hidden="true" />
                    </button>
                </div>

                <p className="text-xs text-slate-500 mb-4">
                    Applied to the selected month first, then to any older unpaid
                    bills. Anything left over becomes renter credit.
                </p>

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <Field id="paymentRenter" label="Renter" error={errors.renter_id}>
                        <select id="paymentRenter" value={data.renter_id}
                            onChange={(e) => { clearErrors(); setData('renter_id', e.target.value); }}
                            className={fieldClass} required>
                            <option value="">Select renter</option>
                            {renters.map((r) => <option key={r.id} value={r.id}>{r.name}</option>)}
                        </select>
                    </Field>

                    <Field id="paymentAmount" label="Amount (KES)" error={errors.amount}>
                        <input id="paymentAmount" inputMode="decimal" value={data.amount}
                            onChange={(e) => setData('amount', normaliseMoneyInput(e.target.value))}
                            className={fieldClass} placeholder="6500.00" required />
                    </Field>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Field id="paymentMonth" label="Apply to month (optional)" error={errors.month}>
                            <input id="paymentMonth" type="month" value={data.month}
                                onChange={(e) => setData('month', e.target.value)} className={fieldClass} />
                        </Field>
                        <Field id="paymentDate" label="Payment date" error={errors.date}>
                            <input id="paymentDate" type="date" value={data.date}
                                onChange={(e) => setData('date', e.target.value)} className={fieldClass} />
                        </Field>
                        <Field id="paymentType" label="Category" error={errors.type}>
                            <select id="paymentType" value={data.type}
                                onChange={(e) => setData('type', e.target.value)} className={fieldClass}>
                                {TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
                            </select>
                        </Field>
                        <Field id="paymentMethod" label="Method" error={errors.method}>
                            <input id="paymentMethod" value={data.method}
                                onChange={(e) => setData('method', e.target.value)} className={fieldClass} />
                        </Field>
                    </div>

                    <Field id="paymentDescription" label="Description (optional)" error={errors.description}>
                        <input id="paymentDescription" value={data.description}
                            onChange={(e) => setData('description', e.target.value)} className={fieldClass} />
                    </Field>

                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose}
                            className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" disabled={processing}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60">
                            {processing ? 'Recording...' : 'Record Payment'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
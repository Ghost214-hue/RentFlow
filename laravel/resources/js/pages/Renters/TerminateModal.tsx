import { useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { normaliseMoneyInput } from '@/lib/money';
import type { Renter } from '@/types';

/*
 * End a tenancy.
 *
 * Replaces the old hard DELETE. Nothing is removed: the unit is freed, the
 * renter's personal data is scrubbed and the bill/payment history is kept, so
 * the portfolio still shows who occupied which unit and for how long.
 *
 * `mode` is:
 *   terminate  the owner ends it directly
 *   approve    the renter asked to leave and the owner is approving
 *
 * Damages become completed maintenance rows on the unit, so the repair cost
 * stays visible in its history rather than disappearing at termination.
 */

interface Damage {
    title: string;
    description: string;
    cost: string;
    vendor_name: string;
}

const EMPTY_DAMAGE: Damage = { title: '', description: '', cost: '', vendor_name: '' };

const field = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

interface Props {
    renter: Renter;
    mode: 'terminate' | 'approve';
    onClose: () => void;
}

export default function TerminateModal({ renter, mode, onClose }: Props) {
    const isApprove = mode === 'approve';

    const { data, setData, post, processing, errors, clearErrors } = useForm({
        reason: '',
        effective_date: new Date().toISOString().slice(0, 10),
        damages: [] as Damage[],
    });

    const [damages, setDamages] = useState<Damage[]>(isApprove ? [] : [EMPTY_DAMAGE]);

    useEffect(() => {
        clearErrors();
        setData('reason', '');
        setData('damages', []);
        setDamages(isApprove ? [] : [EMPTY_DAMAGE]);
    }, [renter.id, mode, clearErrors, isApprove, setData]);

    function updateDamage(index: number, patch: Partial<Damage>) {
        const next = damages.map((d, i) => (i === index ? { ...d, ...patch } : d));
        setDamages(next);
        setData('damages', next);
    }

    function removeDamage(index: number) {
        const next = damages.filter((_, i) => i !== index);
        setDamages(next);
        setData('damages', next);
    }

    function addDamage() {
        const next = [...damages, EMPTY_DAMAGE];
        setDamages(next);
        setData('damages', next);
    }

    // Only send rows that actually have a title; blank rows are dropped by
    // validation anyway but this keeps the payload honest.
    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setData('damages', damages.filter((d) => d.title.trim() !== ''));

        post(
            isApprove
                ? `/renters/${renter.id}/termination/approve`
                : `/renters/${renter.id}/terminate`,
            { preserveScroll: true, onSuccess: () => onClose() },
        );
    }

    const damageErrors = errors['damages'] as string[] | undefined;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
            role="dialog" aria-modal="true" aria-labelledby="terminate-modal-title">

            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6"
                onClick={(e) => e.stopPropagation()}>

                <div className="flex items-start justify-between mb-4">
                    <div>
                        <h3 id="terminate-modal-title" className="text-lg font-bold text-slate-900">
                            {isApprove ? 'Approve Termination' : 'Terminate Tenancy'}
                        </h3>
                        <p className="text-sm text-slate-500 mt-0.5">
                            {renter.name}
                            {renter.house_unit ? ` · Unit ${renter.house_unit}` : ''}
                        </p>
                    </div>
                    <button type="button" onClick={onClose}
                        className="text-slate-400 hover:text-slate-600 transition-colors" aria-label="Close">
                        <i className="fas fa-times text-xl" aria-hidden="true" />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <div>
                        <label htmlFor="termReason" className="block text-sm font-medium text-slate-700 mb-1">
                            Reason
                        </label>
                        <textarea id="termReason" rows={2} value={data.reason}
                            onChange={(e) => setData('reason', e.target.value)}
                            className={field}
                            placeholder="Optional reason" />
                    </div>

                    <div>
                        <label htmlFor="termDate" className="block text-sm font-medium text-slate-700 mb-1">
                            Effective Date
                        </label>
                        <input id="termDate" type="date" value={data.effective_date}
                            onChange={(e) => setData('effective_date', e.target.value)}
                            className={`${field} max-w-xs`} />
                    </div>

                    {!isApprove && (
                        <div className="border-t border-amber-100 pt-4">
                            <div className="flex items-center justify-between mb-2">
                                <h4 className="text-sm font-semibold text-slate-700">
                                    <i className="fas fa-tools mr-1 text-amber-600" aria-hidden="true" />
                                    Damages &amp; Costs
                                </h4>
                                <button type="button" onClick={addDamage}
                                    className="text-xs px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition-all">
                                    <i className="fas fa-plus mr-1" aria-hidden="true" />
                                    Add damage
                                </button>
                            </div>

                            <p className="text-xs text-slate-400 mb-3">
                                Recorded against the unit as completed repairs, so the cost stays in its history.
                            </p>

                            {damages.map((damage, index) => (
                                <div key={index} className="grid grid-cols-1 sm:grid-cols-12 gap-2 mb-2 p-3 rounded-lg border border-amber-100 bg-amber-50/30">
                                    <input
                                        value={damage.title}
                                        onChange={(e) => updateDamage(index, { title: e.target.value })}
                                        className="sm:col-span-4 px-3 py-2 rounded-lg border border-slate-200 text-sm"
                                        placeholder="What was damaged"
                                        aria-label={`Damage ${index + 1} title`}
                                    />
                                    <input
                                        value={damage.description}
                                        onChange={(e) => updateDamage(index, { description: e.target.value })}
                                        className="sm:col-span-3 px-3 py-2 rounded-lg border border-slate-200 text-sm"
                                        placeholder="Details"
                                        aria-label={`Damage ${index + 1} description`}
                                    />
                                    <input
                                        inputMode="decimal"
                                        value={damage.cost}
                                        onChange={(e) => updateDamage(index, { cost: normaliseMoneyInput(e.target.value) })}
                                        className="sm:col-span-2 px-3 py-2 rounded-lg border border-slate-200 text-sm"
                                        placeholder="Cost"
                                        aria-label={`Damage ${index + 1} cost`}
                                    />
                                    <div className="sm:col-span-3 flex items-center gap-1">
                                        <input
                                            value={damage.vendor_name}
                                            onChange={(e) => updateDamage(index, { vendor_name: e.target.value })}
                                            className="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-sm"
                                            placeholder="Vendor"
                                            aria-label={`Damage ${index + 1} vendor`}
                                        />
                                        <button type="button" onClick={() => removeDamage(index)}
                                            className="p-2 text-slate-400 hover:text-red-600 transition-colors"
                                            aria-label={`Remove damage ${index + 1}`}>
                                            <i className="fas fa-times" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                            ))}

                            {damageErrors && (
                                <p className="mt-1 text-sm text-red-600">{damageErrors.join(', ')}</p>
                            )}
                        </div>
                    )}

                    <p className="text-xs text-red-500">
                        The unit will be marked vacant and the renter&apos;s personal details removed.
                        Their bills and payment history are kept. This cannot be undone.
                    </p>

                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose}
                            className="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <button type="submit" disabled={processing}
                            className={`px-6 py-2.5 text-white font-semibold rounded-xl shadow-lg transition-all inline-flex items-center gap-2 disabled:opacity-60 ${
                                isApprove
                                    ? 'bg-gradient-to-r from-emerald-500 to-emerald-600'
                                    : 'bg-gradient-to-r from-red-500 to-red-600'
                            }`}>
                            <i className={`fas ${isApprove ? 'fa-check-circle' : 'fa-door-open'} text-sm`} aria-hidden="true" />
                            {processing
                                ? 'Working...'
                                : isApprove ? 'Approve & Terminate' : 'Terminate Tenancy'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
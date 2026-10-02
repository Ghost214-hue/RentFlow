import { useForm } from '@inertiajs/react';
import type { RenterOption } from '@/types';

const PRIORITIES = ['low', 'medium', 'high'];
const fieldClass = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

interface Props {
    renters: RenterOption[];
    canBroadcast: boolean;
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
 * Raise a complaint, or broadcast a notice to several renters.
 *
 * A renter only fills in title/description: their own tenant_id and
 * sender_role are derived server-side and cannot be forged.
 */
export default function ComplaintModal({ renters, canBroadcast, onClose }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        category: '',
        priority: 'medium',
        tenant_id: '',
        recipient_type: 'individual',
        recipient_ids: [] as number[],
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/complaints', { preserveScroll: true, onSuccess: () => onClose() });
    }

    function toggleRecipient(id: number) {
        setData(
            'recipient_ids',
            data.recipient_ids.includes(id)
                ? data.recipient_ids.filter((r) => r !== id)
                : [...data.recipient_ids, id],
        );
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
            role="dialog" aria-modal="true" aria-labelledby="complaint-modal-title">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6"
                onClick={(e) => e.stopPropagation()}>
                <div className="flex items-center justify-between mb-4">
                    <h3 id="complaint-modal-title" className="text-lg font-bold text-slate-900">
                        {canBroadcast ? 'New Notice' : 'Raise a Complaint'}
                    </h3>
                    <button type="button" onClick={onClose} className="text-slate-400 hover:text-slate-600" aria-label="Close">
                        <i className="fas fa-times text-xl" aria-hidden="true" />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <Field id="complaintTitle" label="Title" error={errors.title}>
                        <input id="complaintTitle" value={data.title}
                            onChange={(e) => setData('title', e.target.value)} className={fieldClass} required />
                    </Field>

                    <Field id="complaintDescription" label="Description" error={errors.description}>
                        <textarea id="complaintDescription" rows={4} value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            className={fieldClass} required />
                    </Field>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Field id="complaintCategory" label="Category" error={errors.category}>
                            <input id="complaintCategory" value={data.category}
                                onChange={(e) => setData('category', e.target.value)}
                                className={fieldClass} placeholder="e.g. Plumbing" />
                        </Field>

                        <Field id="complaintPriority" label="Priority" error={errors.priority}>
                            <select id="complaintPriority" value={data.priority}
                                onChange={(e) => setData('priority', e.target.value)} className={fieldClass}>
                                {PRIORITIES.map((p) => (
                                    <option key={p} value={p}>{p}</option>
                                ))}
                            </select>
                        </Field>
                    </div>

                    {canBroadcast && (
                        <>
                            <Field id="complaintRecipientType" label="Send to" error={errors.recipient_type}>
                                <select id="complaintRecipientType" value={data.recipient_type}
                                    onChange={(e) => setData('recipient_type', e.target.value)} className={fieldClass}>
                                    <option value="individual">One renter</option>
                                    <option value="property">Everyone in a property</option>
                                    <option value="all">All renters</option>
                                </select>
                            </Field>

                            {data.recipient_type === 'individual' && (
                                <Field id="complaintTenant" label="Renter" error={errors.tenant_id}>
                                    <select id="complaintTenant" value={data.tenant_id}
                                        onChange={(e) => setData('tenant_id', e.target.value)} className={fieldClass}>
                                        <option value="">Select renter</option>
                                        {renters.map((r) => (
                                            <option key={r.id} value={r.id}>{r.name}</option>
                                        ))}
                                    </select>
                                </Field>
                            )}

                            {data.recipient_type === 'property' && (
                                <fieldset>
                                    <legend className="block text-sm font-medium text-slate-700 mb-2">
                                        Renters
                                    </legend>
                                    <div className="space-y-2 max-h-32 overflow-y-auto">
                                        {renters.map((r) => (
                                            <label key={r.id} className="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" checked={data.recipient_ids.includes(r.id)}
                                                    onChange={() => toggleRecipient(r.id)}
                                                    className="w-4 h-4 rounded border-slate-300 text-blue-600" />
                                                <span className="text-sm text-slate-700">{r.name}</span>
                                            </label>
                                        ))}
                                    </div>
                                </fieldset>
                            )}
                        </>
                    )}

                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose}
                            className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" disabled={processing}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60">
                            {processing ? 'Saving...' : canBroadcast ? 'Send Notice' : 'Submit Complaint'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
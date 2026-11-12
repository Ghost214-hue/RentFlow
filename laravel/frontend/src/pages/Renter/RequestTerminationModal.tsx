import { useForm } from '@inertiajs/react';

/*
 * A renter asks to leave.
 *
 * This does NOT terminate anything: it creates a PENDING request that the
 * landlord approves. The renter's own request is the only one they can make --
 * the controller rejects a request for anyone else.
 */

const field = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

interface Props {
    renterId: number;
    onClose: () => void;
}

export default function RequestTerminationModal({ renterId, onClose }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        reason: '',
        // A month by default, matching the legacy default of +30 days.
        effective_date: new Date(Date.now() + 30 * 86400000)
            .toISOString()
            .slice(0, 10),
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post(`/renters/${renterId}/termination`, {
            preserveScroll: true,
            onSuccess: () => onClose(),
        });
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
            role="dialog" aria-modal="true" aria-labelledby="request-termination-title">

            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6"
                onClick={(e) => e.stopPropagation()}>

                <div className="flex items-start justify-between mb-4">
                    <div>
                        <h3 id="request-termination-title" className="text-lg font-bold text-slate-900">
                            Request to Leave
                        </h3>
                        <p className="text-sm text-slate-500 mt-0.5">
                            Your landlord will review this request.
                        </p>
                    </div>
                    <button type="button" onClick={onClose}
                        className="text-slate-400 hover:text-slate-600 transition-colors" aria-label="Close">
                        <i className="fas fa-times text-xl" aria-hidden="true" />
                    </button>
                </div>

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <div>
                        <label htmlFor="reqReason" className="block text-sm font-medium text-slate-700 mb-1">
                            Reason
                        </label>
                        <textarea id="reqReason" rows={3} value={data.reason}
                            onChange={(e) => setData('reason', e.target.value)}
                            className={field}
                            placeholder="Optional — helps your landlord understand" />
                    </div>

                    <div>
                        <label htmlFor="reqDate" className="block text-sm font-medium text-slate-700 mb-1">
                            Preferred move-out date
                        </label>
                        <input id="reqDate" type="date" value={data.effective_date}
                            onChange={(e) => setData('effective_date', e.target.value)}
                            className={`${field} max-w-xs`} />
                    </div>

                    <p className="text-xs text-slate-500">
                        This does not end your tenancy on its own. Your landlord approves it,
                        and any outstanding bills are settled up to that date.
                    </p>

                    {(errors.reason || errors.effective_date) && (
                        <p role="alert" className="text-sm text-red-600">
                            {errors.reason ?? errors.effective_date}
                        </p>
                    )}

                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose}
                            className="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <button type="submit" disabled={processing}
                            className="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-semibold rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/40 transition-all inline-flex items-center gap-2 disabled:opacity-60">
                            <i className="fas fa-door-open text-sm" aria-hidden="true" />
                            {processing ? 'Submitting...' : 'Submit Request'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
import { Head, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';

interface Props {
    secret: string;
    qrUrl: string;
}

export default function TwoFactorEnroll({ secret, qrUrl }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/admin/2fa/enroll');
    }

    return (
        <>
            <Head title="Set Up Two-Factor Authentication" />

            <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-4">
                <div className="w-full max-w-lg rounded-3xl bg-slate-900/90 p-8 shadow-2xl border border-slate-800 text-white">
                    <div className="flex items-center gap-3 mb-6">
                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/20 border border-indigo-500/30">
                            <i className="fas fa-qrcode text-indigo-400" aria-hidden="true" />
                        </span>
                        <div>
                            <h1 className="text-xl font-bold">Set Up Two-Factor Authentication</h1>
                            <p className="text-xs text-slate-400">Mandatory security requirement for all administrators</p>
                        </div>
                    </div>

                    <p className="text-sm text-slate-300 mb-4 leading-relaxed">
                        Add this secret key to your authenticator app (e.g. Google Authenticator, 1Password, Authy), then enter the 6-digit confirmation code below.
                    </p>

                    <div className="bg-slate-950 p-4 rounded-xl border border-slate-800 mb-4 space-y-2">
                        <div className="text-xs font-semibold uppercase tracking-wider text-slate-400">Secret Key</div>
                        <code className="block select-all font-mono text-sm text-indigo-300 break-all bg-slate-900/80 p-2.5 rounded-lg border border-slate-800">
                            {secret}
                        </code>
                        <div className="text-xs text-slate-500 pt-1">
                            Or configure via URI: <span className="text-slate-400 break-all select-all font-mono">{qrUrl}</span>
                        </div>
                    </div>

                    {errors.code && (
                        <div className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-400">
                            {errors.code}
                        </div>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label htmlFor="code" className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                                6-Digit Authenticator Code
                            </label>
                            <input
                                id="code"
                                type="text"
                                inputMode="numeric"
                                autoComplete="one-time-code"
                                placeholder="123456"
                                required
                                autoFocus
                                value={data.code}
                                onChange={(e) => setData('code', e.target.value)}
                                className="block w-full rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-3 text-center text-xl tracking-[0.3em] font-mono text-white placeholder-slate-600 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:from-indigo-500 hover:to-blue-500 disabled:opacity-60 transition"
                        >
                            {processing ? 'Enrolling…' : 'Confirm and Enable 2FA'}
                        </button>
                    </form>
                </div>
            </div>
        </>
    );
}

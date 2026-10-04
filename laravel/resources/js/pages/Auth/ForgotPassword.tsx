import { Head, Link, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';

/**
 * Password reset, step 1: ask for the address and email a six-digit code.
 *
 * The confirmation message is deliberately identical whether or not the address
 * has an account. Saying "no such user" here would let anyone find out which
 * addresses are registered here and target them with a phishing mail, so the
 * server never tells this page which case it was.
 */

interface Props {
    flash?: { success?: string | null };
}

const inputClass =
    'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all';

function Field({ id, label, error, children }: { id: string; label: string; error?: string; children: ReactNode }) {
    return (
        <div>
            <label htmlFor={id} className="block text-sm font-medium text-slate-700 mb-1">{label}</label>
            {children}
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

export default function ForgotPassword({ flash }: Props) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });
    const [sent, setSent] = useState(false);

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/forgot-password', {
            preserveScroll: true,
            onSuccess: () => setSent(true),
        });
    }

    return (
        <>
            <Head title="Reset your password" />

            <div className="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 relative overflow-hidden">
                <div className="absolute inset-0 opacity-10" aria-hidden="true">
                    <svg className="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
                        <path d="M0 100 C 40 20 60 20 100 100 Z" fill="white" opacity="0.5" />
                    </svg>
                </div>

                <div className="relative z-10 w-full max-w-md bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-6 sm:p-8">
                    <div className="flex items-center gap-3 mb-6">
                        <div className="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-lg text-white">RF</div>
                        <span className="font-bold text-xl text-slate-900">RentalFlow</span>
                    </div>

                    <h1 className="text-2xl font-bold text-slate-900 mb-1">Reset your password</h1>
                    <p className="text-slate-500 mb-6">
                        Enter the email address on your account and we will send you a verification code.
                    </p>

                    {(flash?.success || sent) && (
                        <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-100">
                            {flash?.success ??
                                'If that address belongs to a RentFlow account, we have sent it a verification code. Check your inbox (and spam folder).'}
                        </div>
                    )}

                    <form onSubmit={submit} className="space-y-4" noValidate>
                        <Field id="resetEmail" label="Email Address" error={errors.email}>
                            <input
                                id="resetEmail"
                                type="email"
                                autoComplete="email"
                                autoFocus
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className={inputClass}
                                placeholder="Enter email"
                                required
                            />
                        </Field>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            {processing ? 'Sending...' : 'Send verification code'}
                        </button>
                    </form>

                    {/*
                        An Inertia <Link>, not <a href>. A plain anchor forces a
                        full page reload, which throws away the SPA shell -- the
                        same reason the "Sign up" link beside it was broken.
                    */}
                    <div className="mt-6 text-center">
                        <Link href="/login" className="text-sm font-medium text-blue-600 hover:text-blue-700">
                            Back to sign in
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
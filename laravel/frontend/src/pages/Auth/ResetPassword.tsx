import { Head, Link, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';

/**
 * Password reset, step 2: enter the emailed code and choose a new password.
 *
 * The server answers a wrong code, an expired code and an unknown address
 * identically, and says so here too -- the form must not imply which one it was.
 */

interface Props {
    email: string;
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

export default function ResetPassword({ email }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        email,
        code: '',
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/reset-password');
    }

    return (
        <>
            <Head title="Choose a new password" />

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

                    <h1 className="text-2xl font-bold text-slate-900 mb-1">Choose a new password</h1>
                    <p className="text-slate-500 mb-6">Enter the six-digit code we emailed you, then pick a new password.</p>

                    <form onSubmit={submit} className="space-y-4" noValidate>
                        <Field id="email" label="Email Address" error={errors.email}>
                            <input
                                id="email"
                                type="email"
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className={inputClass}
                                required
                            />
                        </Field>

                        <Field id="code" label="Verification Code" error={errors.code}>
                            <input
                                id="code"
                                type="text"
                                inputMode="numeric"
                                autoComplete="one-time-code"
                                maxLength={6}
                                value={data.code}
                                onChange={(e) => setData('code', e.target.value.replace(/\D/g, ''))}
                                className={`${inputClass} tracking-[0.4em] font-mono`}
                                placeholder="000000"
                                required
                            />
                        </Field>

                        <Field id="password" label="New Password" error={errors.password}>
                            <div className="relative">
                                <input
                                    id="password"
                                    type={showPassword ? 'text' : 'password'}
                                    autoComplete="new-password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className={`${inputClass} pr-11`}
                                    placeholder="At least 8 characters"
                                    required
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword((v) => !v)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                    aria-label={showPassword ? 'Hide password' : 'Show password'}
                                >
                                    <i className={`fas ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`} aria-hidden="true" />
                                </button>
                            </div>
                        </Field>

                        <Field id="password_confirmation" label="Confirm New Password" error={errors.password_confirmation}>
                            <input
                                id="password_confirmation"
                                type={showPassword ? 'text' : 'password'}
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                className={inputClass}
                                placeholder="Repeat your new password"
                                required
                            />
                        </Field>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            {processing ? 'Saving...' : 'Set new password'}
                        </button>
                    </form>

                    <div className="mt-6 text-center">
                        <Link href="/forgot-password" className="text-sm font-medium text-blue-600 hover:text-blue-700">
                            Request a new code
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
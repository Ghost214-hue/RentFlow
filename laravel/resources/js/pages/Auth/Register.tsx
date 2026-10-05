import { Head, Link, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';

/**
 * Self-serve owner registration.
 *
 * This page DID NOT EXIST -- the login screen linked to /signup and got a 404,
 * the same broken link as /forgot-password. Only owners register here; renters
 * and caretakers are invited by a landlord instead.
 *
 * Password rules mirror the server exactly, so the user is told what is wrong
 * before a round trip rather than after.
 */

interface Props {
    flash?: { success?: string | null; error?: string | null };
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

export default function Register({ flash }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/register');
    }

    return (
        <>
            <Head title="Create your account" />

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

                    <h1 className="text-2xl font-bold text-slate-900 mb-1">Create your account</h1>
                    <p className="text-slate-500 mb-6">Set up a landlord account to manage your properties.</p>

                    {flash?.success && (
                        <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-100">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div role="alert" className="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm border border-red-100">
                            {flash.error}
                        </div>
                    )}

                    <form onSubmit={submit} className="space-y-4" noValidate>
                        <Field id="name" label="Full Name" error={errors.name}>
                            <input id="name" type="text" autoComplete="name" value={data.name} autoFocus
                                onChange={(e) => setData('name', e.target.value)} className={inputClass}
                                placeholder="e.g. Grace Wanjiru" required />
                        </Field>

                        <Field id="email" label="Email Address" error={errors.email}>
                            <input id="email" type="email" autoComplete="email" value={data.email}
                                onChange={(e) => setData('email', e.target.value)} className={inputClass}
                                placeholder="you@example.com" required />
                        </Field>

                        <Field id="phone" label="Phone (optional)" error={errors.phone}>
                            <input id="phone" type="tel" autoComplete="tel" value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)} className={inputClass}
                                placeholder="07xx xxx xxx" />
                        </Field>

                        <Field id="password" label="Password" error={errors.password}>
                            <div className="relative">
                                <input id="password" type={showPassword ? 'text' : 'password'}
                                    autoComplete="new-password" value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className={`${inputClass} pr-11`} placeholder="At least 8 characters"
                                    required />
                                <button type="button" onClick={() => setShowPassword((v) => !v)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                    aria-label={showPassword ? 'Hide password' : 'Show password'}>
                                    <i className={`fas ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`} aria-hidden="true" />
                                </button>
                            </div>
                            <p className="mt-1 text-xs text-slate-500">At least 8 characters, including a number.</p>
                        </Field>

                        <Field id="password_confirmation" label="Confirm Password" error={errors.password_confirmation}>
                            <input id="password_confirmation" type={showPassword ? 'text' : 'password'}
                                autoComplete="new-password" value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                className={inputClass} placeholder="Repeat your password" required />
                        </Field>

                        <button type="submit" disabled={processing}
                            className="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                            {processing ? 'Creating account...' : 'Create account'}
                        </button>
                    </form>

                    <div className="mt-6 text-center">
                        <p className="text-sm text-slate-500">Already have an account?{' '}
                            <Link href="/login" className="font-medium text-blue-600 hover:text-blue-700">Sign in</Link></p>
                    </div>
                </div>
            </div>
        </>
    );
}
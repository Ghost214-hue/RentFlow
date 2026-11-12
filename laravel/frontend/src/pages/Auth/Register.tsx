import { Head, Link, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';

/**
 * Self-serve owner registration.
 * Layout is the MIRROR of Login: brand panel LEFT, form panel RIGHT.
 * The same card animation plays, so navigating login ↔ register
 * feels like a smooth, continuous transition.
 */

interface Props {
    flash?: { success?: string | null; error?: string | null };
}

function Field({
    id,
    label,
    error,
    hint,
    children,
}: {
    id: string;
    label: string;
    error?: string;
    hint?: string;
    children: ReactNode;
}) {
    return (
        <div>
            <label htmlFor={id} className="block text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">
                {label}
            </label>
            {children}
            {hint && !error && <p className="mt-1.5 text-xs text-slate-400">{hint}</p>}
            {error && <p className="mt-1.5 text-xs text-red-600">{error}</p>}
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

    const inputClass = (hasError: boolean) =>
        `block w-full rounded-xl border px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 ${
            hasError
                ? 'border-red-400 bg-red-50'
                : 'border-slate-200 bg-slate-50 hover:border-slate-300'
        }`;

    return (
        <>
            <Head title="Create account" />

            <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 p-4">
                {/*
                 * REGISTER layout: [Brand panel] [Form panel]
                 * This is the exact reverse of login, so the brand "crosses over"
                 * from right → left when the user navigates between pages,
                 * giving a natural flip feel.
                 */}
                <div
                    className="w-full max-w-4xl min-h-[680px] overflow-hidden rounded-3xl shadow-2xl shadow-blue-900/50 flex"
                    style={{ animation: 'authFadeSlide 0.45s cubic-bezier(0.22,1,0.36,1) both' }}
                >
                    {/* ── Brand panel (blue) – LEFT on register ── */}
                    <div className="relative hidden md:flex md:w-5/12 flex-col justify-between bg-gradient-to-b from-blue-900 to-blue-700 p-12 text-white overflow-hidden">
                        {/* Logo */}
                        <div className="flex items-center gap-3 relative z-10">
                            <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm border border-white/20">
                                <i className="fas fa-building text-white text-lg" aria-hidden="true" />
                            </span>
                            <span className="text-xl font-bold tracking-tight">RentFlow</span>
                        </div>

                        {/* Main copy */}
                        <div className="relative z-10">
                            <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-medium text-blue-100">
                                <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                                Free to get started
                            </div>
                            <h1 className="text-4xl font-extrabold leading-tight">
                                Start managing<br />your properties<br />
                                <span className="text-blue-300">today</span>
                            </h1>
                            <p className="mt-4 text-sm text-blue-200 leading-relaxed max-w-xs">
                                Create a landlord account in seconds and get a full view of your rental portfolio from day one.
                            </p>

                            {/* Feature pills */}
                            <div className="mt-6 flex flex-col gap-2">
                                {[
                                    { icon: 'fa-bolt', text: 'Set up in under 2 minutes' },
                                    { icon: 'fa-shield-halved', text: 'Your data stays private' },
                                    { icon: 'fa-chart-line', text: 'Real-time rent analytics' },
                                ].map(f => (
                                    <div key={f.icon} className="flex items-center gap-2.5 text-sm text-blue-100">
                                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15">
                                            <i className={`fas ${f.icon} text-xs`} aria-hidden="true" />
                                        </span>
                                        {f.text}
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Decorative blobs */}
                        <div aria-hidden="true">
                            <div className="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-blue-500/25 blur-3xl" />
                            <div className="absolute top-20 -right-10 h-40 w-40 rounded-full bg-white/8 blur-2xl" />
                            <div className="absolute bottom-32 left-10 h-24 w-24 rounded-full bg-blue-400/20 blur-xl" />
                        </div>
                    </div>

                    {/* ── Form panel (white) – RIGHT on register ── */}
                    <div className="flex-1 flex flex-col justify-center bg-white px-8 py-12 md:px-12">

                        {/* Mobile logo */}
                        <div className="mb-7 flex items-center gap-2 md:hidden">
                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600">
                                <i className="fas fa-building text-white" aria-hidden="true" />
                            </span>
                            <span className="text-base font-bold text-blue-700">RentFlow</span>
                        </div>

                        <h2 className="text-3xl font-extrabold text-slate-800">Create your account</h2>
                        <p className="mt-1 mb-8 text-sm text-slate-500">Set up a landlord account to manage your properties.</p>

                        {/* Flash */}
                        {flash?.success && (
                            <div className="mb-5 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                <i className="fas fa-circle-check mt-0.5 shrink-0" aria-hidden="true" />
                                {flash.success}
                            </div>
                        )}
                        {flash?.error && (
                            <div className="mb-5 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <i className="fas fa-circle-exclamation mt-0.5 shrink-0" aria-hidden="true" />
                                {flash.error}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-5" noValidate>
                            {/* Name + Phone side by side */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field id="name" label="Full Name" error={errors.name}>
                                    <input
                                        id="name"
                                        type="text"
                                        autoComplete="name"
                                        autoFocus
                                        required
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                        placeholder="Grace Wanjiru"
                                        className={inputClass(!!errors.name)}
                                    />
                                </Field>

                                <Field id="phone" label="Phone (optional)" error={errors.phone}>
                                    <input
                                        id="phone"
                                        type="tel"
                                        autoComplete="tel"
                                        value={data.phone}
                                        onChange={e => setData('phone', e.target.value)}
                                        placeholder="07xx xxx xxx"
                                        className={inputClass(!!errors.phone)}
                                    />
                                </Field>
                            </div>

                            <Field id="email" label="Email Address" error={errors.email}>
                                <input
                                    id="email"
                                    type="email"
                                    autoComplete="email"
                                    required
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    placeholder="you@example.com"
                                    className={inputClass(!!errors.email)}
                                />
                            </Field>

                            {/* Password + Confirm side by side */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field
                                    id="password"
                                    label="Password"
                                    error={errors.password}
                                    hint="At least 8 characters, including a number."
                                >
                                    <div className="relative">
                                        <input
                                            id="password"
                                            type={showPassword ? 'text' : 'password'}
                                            autoComplete="new-password"
                                            required
                                            value={data.password}
                                            onChange={e => setData('password', e.target.value)}
                                            placeholder="••••••••"
                                            className={`${inputClass(!!errors.password)} pr-12`}
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword(v => !v)}
                                            aria-label={showPassword ? 'Hide password' : 'Show password'}
                                            className="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-slate-600 transition-colors"
                                        >
                                            <i className={`fas ${showPassword ? 'fa-eye-slash' : 'fa-eye'} text-sm`} aria-hidden="true" />
                                        </button>
                                    </div>
                                </Field>

                                <Field id="password_confirmation" label="Confirm Password" error={errors.password_confirmation}>
                                    <input
                                        id="password_confirmation"
                                        type={showPassword ? 'text' : 'password'}
                                        autoComplete="new-password"
                                        required
                                        value={data.password_confirmation}
                                        onChange={e => setData('password_confirmation', e.target.value)}
                                        placeholder="••••••••"
                                        className={inputClass(!!errors.password_confirmation)}
                                    />
                                </Field>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-xl bg-blue-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-500/40 focus:outline-none focus:ring-2 focus:ring-blue-500/40 disabled:opacity-60 transition-all mt-1"
                            >
                                {processing ? (
                                    <span className="inline-flex items-center gap-2 justify-center">
                                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-r-transparent" />
                                        Creating account…
                                    </span>
                                ) : 'Create account'}
                            </button>
                        </form>

                        <p className="mt-8 text-center text-sm text-slate-500">
                            Already have an account?{' '}
                            <Link
                                href="/login"
                                className="font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                            >
                                Sign in
                            </Link>
                        </p>
                    </div>
                </div>
            </div>

            <style>{`
                @keyframes authFadeSlide {
                    from { opacity: 0; transform: translateY(18px) scale(0.98); }
                    to   { opacity: 1; transform: translateY(0)   scale(1);    }
                }
            `}</style>
        </>
    );
}

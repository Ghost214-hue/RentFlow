import { Head, Link, useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';

interface Props {
    flash?: { success?: string | null; error?: string | null };
}

export default function Login({ flash }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });
    const [showPassword, setShowPassword] = useState(false);

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/login');
    }

    return (
        <>
            <Head title="Sign In" />

            <div className="auth-page flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 p-4">
                {/*
                 * LOGIN layout: [Form panel] [Brand panel]
                 * REGISTER is the reverse: [Brand panel] [Form panel]
                 * The card uses flex-row-reverse so the brand slides to the right
                 * when navigating to register — a natural left↔right swap.
                 */}
                <div
                    className="auth-card w-full max-w-4xl min-h-[680px] overflow-hidden rounded-3xl shadow-2xl shadow-blue-900/50 flex"
                    style={{ animation: 'authFadeSlide 0.45s cubic-bezier(0.22,1,0.36,1) both' }}
                >
                    {/* ── Form panel (white) – left on login ── */}
                    <div className="flex-1 flex flex-col justify-center bg-white px-10 py-14 md:px-14 order-1">

                        {/* Mobile logo */}
                        <div className="mb-7 flex items-center gap-2 md:hidden">
                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600">
                                <i className="fas fa-building text-white" aria-hidden="true" />
                            </span>
                            <span className="text-base font-bold text-blue-700">RentFlow</span>
                        </div>

                        <h2 className="text-3xl font-extrabold text-slate-800">Welcome back</h2>
                        <p className="mt-2 mb-10 text-sm text-slate-500">Sign in to your account to continue</p>

                        {/* Flash */}
                        {flash?.error && (
                            <div className="mb-5 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <i className="fas fa-circle-exclamation mt-0.5 shrink-0" aria-hidden="true" />
                                {flash.error}
                            </div>
                        )}
                        {flash?.success && (
                            <div className="mb-5 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                <i className="fas fa-circle-check mt-0.5 shrink-0" aria-hidden="true" />
                                {flash.success}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-6" noValidate>
                            {/* Email */}
                            <div>
                                <label htmlFor="email" className="block text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">
                                    Email Address
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    autoComplete="email"
                                    required
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    placeholder="you@example.com"
                                    className={`block w-full rounded-xl border px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 ${
                                        errors.email ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'
                                    }`}
                                />
                                {errors.email && <p className="mt-1.5 text-xs text-red-600">{errors.email}</p>}
                            </div>

                            {/* Password */}
                            <div>
                                <label htmlFor="password" className="block text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">
                                    Password
                                </label>
                                <div className="relative">
                                    <input
                                        id="password"
                                        type={showPassword ? 'text' : 'password'}
                                        autoComplete="current-password"
                                        required
                                        value={data.password}
                                        onChange={e => setData('password', e.target.value)}
                                        placeholder="••••••••"
                                        className={`block w-full rounded-xl border px-4 py-3 pr-12 text-sm text-slate-800 placeholder-slate-400 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 ${
                                            errors.password ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'
                                        }`}
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
                                {errors.password && <p className="mt-1.5 text-xs text-red-600">{errors.password}</p>}
                            </div>

                            {/* Remember + Forgot */}
                            <div className="flex items-center justify-between pt-1">
                                <label className="inline-flex cursor-pointer items-center gap-2 select-none">
                                    <input
                                        type="checkbox"
                                        checked={data.remember}
                                        onChange={e => setData('remember', e.target.checked)}
                                        className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span className="text-sm text-slate-600">Remember me</span>
                                </label>
                                <Link
                                    href="/forgot-password"
                                    className="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors"
                                >
                                    Forgot password?
                                </Link>
                            </div>

                            {/* Submit */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-xl bg-blue-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-500/40 focus:outline-none focus:ring-2 focus:ring-blue-500/40 disabled:opacity-60 transition-all mt-2"
                            >
                                {processing ? (
                                    <span className="inline-flex items-center gap-2 justify-center">
                                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-r-transparent" />
                                        Signing in…
                                    </span>
                                ) : 'Sign In'}
                            </button>
                        </form>

                        <p className="mt-10 text-center text-sm text-slate-500">
                            Don't have an account?{' '}
                            <Link
                                href="/register"
                                className="font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                            >
                                Sign up
                            </Link>
                        </p>
                    </div>

                    {/* ── Brand panel (blue) – right on login ── */}
                    <div className="relative hidden md:flex md:w-5/12 flex-col justify-between bg-gradient-to-b from-blue-900 to-blue-700 p-14 text-white overflow-hidden order-2">
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
                                Trusted by landlords across Kenya
                            </div>
                            <h1 className="text-4xl font-extrabold leading-tight">
                                Property<br />Management<br />
                                <span className="text-blue-300">Simplified</span>
                            </h1>
                            <p className="mt-4 text-sm text-blue-200 leading-relaxed max-w-xs">
                                Manage rentals, track payments, handle complaints and stay on top of every unit — all in one place.
                            </p>

                            {/* Feature pills */}
                            <div className="mt-6 flex flex-col gap-2">
                                {[
                                    { icon: 'fa-house', text: 'Multi-property support' },
                                    { icon: 'fa-money-bill-wave', text: 'Automated rent tracking' },
                                    { icon: 'fa-users', text: 'Tenant & caretaker portals' },
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
                            <div className="absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-blue-500/25 blur-3xl" />
                            <div className="absolute top-20 -left-10 h-40 w-40 rounded-full bg-white/8 blur-2xl" />
                            <div className="absolute bottom-32 right-10 h-24 w-24 rounded-full bg-blue-400/20 blur-xl" />
                        </div>
                    </div>
                </div>
            </div>

            {/* Page-enter animation (self-contained, no extra CSS file needed) */}
            <style>{`
                @keyframes authFadeSlide {
                    from { opacity: 0; transform: translateY(18px) scale(0.98); }
                    to   { opacity: 1; transform: translateY(0)   scale(1);    }
                }
            `}</style>
        </>
    );
}

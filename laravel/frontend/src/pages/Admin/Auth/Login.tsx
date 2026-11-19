import { Head, Link, useForm } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';

interface Props {
    flash?: { success?: string | null; error?: string | null };
}

export default function AdminLogin({ flash }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });
    const [showPassword, setShowPassword] = useState(false);

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/admin/login');
    }

    return (
        <>
            <Head title="Platform Admin Sign In" />

            <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-4">
                <div
                    className="w-full max-w-4xl min-h-[580px] overflow-hidden rounded-3xl shadow-2xl shadow-black/80 flex border border-slate-800"
                    style={{ animation: 'authFadeSlide 0.45s cubic-bezier(0.22,1,0.36,1) both' }}
                >
                    {/* Brand / Platform Panel */}
                    <div className="relative hidden md:flex md:w-5/12 flex-col justify-between bg-gradient-to-b from-slate-900 to-indigo-950 p-12 text-white overflow-hidden border-r border-slate-800">
                        <div className="flex items-center gap-3 relative z-10">
                            <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/20 backdrop-blur-sm border border-indigo-500/30">
                                <i className="fas fa-shield-halved text-indigo-400 text-lg" aria-hidden="true" />
                            </span>
                            <div>
                                <span className="text-xl font-bold tracking-tight block">RentFlow</span>
                                <span className="text-xs uppercase tracking-widest text-indigo-400 font-semibold">Super Admin</span>
                            </div>
                        </div>

                        <div className="relative z-10">
                            <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-indigo-500/30 bg-indigo-500/10 px-4 py-1.5 text-xs font-medium text-indigo-300">
                                <span className="h-1.5 w-1.5 rounded-full bg-indigo-400 animate-pulse" />
                                Protected Management Console
                            </div>
                            <h1 className="text-3xl font-extrabold leading-tight text-white">
                                Platform<br />Administration<br />
                                <span className="text-indigo-400">Portal</span>
                            </h1>
                            <p className="mt-4 text-sm text-slate-400 leading-relaxed max-w-xs">
                                Manage landlord accounts, audit security, review system metrics, and inspect platform activity.
                            </p>
                        </div>

                        <div className="text-xs text-slate-500 relative z-10">
                            Mandatory 2FA enforced on all administrative access.
                        </div>

                        <div aria-hidden="true" className="pointer-events-none">
                            <div className="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-indigo-600/15 blur-3xl" />
                            <div className="absolute top-20 -right-10 h-40 w-40 rounded-full bg-blue-600/10 blur-2xl" />
                        </div>
                    </div>

                    {/* Form Panel */}
                    <div className="flex-1 flex flex-col justify-center bg-slate-900/90 px-8 py-12 md:px-12 text-white">
                        <div className="mb-7 flex items-center gap-2 md:hidden">
                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600">
                                <i className="fas fa-shield-halved text-white" aria-hidden="true" />
                            </span>
                            <span className="text-base font-bold text-indigo-400">RentFlow Admin</span>
                        </div>

                        <h2 className="text-3xl font-extrabold text-white">Admin Sign In</h2>
                        <p className="mt-1 mb-8 text-sm text-slate-400">Sign in with authorized administrator credentials</p>

                        {flash?.error && (
                            <div className="mb-5 flex items-start gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                                <i className="fas fa-circle-exclamation mt-0.5 shrink-0" aria-hidden="true" />
                                {flash.error}
                            </div>
                        )}
                        {flash?.success && (
                            <div className="mb-5 flex items-start gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
                                <i className="fas fa-circle-check mt-0.5 shrink-0" aria-hidden="true" />
                                {flash.success}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-5" noValidate>
                            <div>
                                <label htmlFor="email" className="block text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">
                                    Admin Email
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    autoComplete="email"
                                    required
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="admin@rentflow.internal"
                                    className={`block w-full rounded-xl border px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 bg-slate-950/80 ${
                                        errors.email ? 'border-red-500' : 'border-slate-800 hover:border-slate-700'
                                    }`}
                                />
                                {errors.email && <p className="mt-1.5 text-xs text-red-400">{errors.email}</p>}
                            </div>

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
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="••••••••••••"
                                        className={`block w-full rounded-xl border px-4 py-3 pr-12 text-sm text-white placeholder-slate-500 outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 bg-slate-950/80 ${
                                            errors.password ? 'border-red-500' : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword((v) => !v)}
                                        aria-label={showPassword ? 'Hide password' : 'Show password'}
                                        className="absolute inset-y-0 right-0 flex items-center px-4 text-slate-500 hover:text-slate-300 transition-colors"
                                    >
                                        <i className={`fas ${showPassword ? 'fa-eye-slash' : 'fa-eye'} text-sm`} aria-hidden="true" />
                                    </button>
                                </div>
                                {errors.password && <p className="mt-1.5 text-xs text-red-400">{errors.password}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:from-indigo-500 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 disabled:opacity-60 transition-all mt-4"
                            >
                                {processing ? (
                                    <span className="inline-flex items-center gap-2 justify-center">
                                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-r-transparent" />
                                        Authenticating…
                                    </span>
                                ) : (
                                    'Access Admin Console'
                                )}
                            </button>
                        </form>

                        <div className="mt-8 text-center">
                            <Link href="/login" className="text-xs text-slate-400 hover:text-indigo-400 transition-colors">
                                <i className="fas fa-arrow-left mr-1.5" aria-hidden="true" /> Back to Landlord & Staff Login
                            </Link>
                        </div>
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

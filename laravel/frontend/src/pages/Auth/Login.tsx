import { Head, Link, useForm } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';

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

function BrandPanel() {
    return (
        <div className="lg:w-5/12 bg-gradient-to-br from-blue-600 to-blue-800 p-6 sm:p-8 lg:p-12 flex flex-col justify-between text-white relative">
            <div className="absolute inset-0 opacity-5" aria-hidden="true">
                <svg viewBox="0 0 200 200" className="w-full h-full">
                    <path d="M0 200 C 50 0 150 0 200 200 Z" fill="white" />
                </svg>
            </div>
            <div className="relative">
                <div className="flex items-center gap-3 mb-10">
                    <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg backdrop-blur">RF</div>
                    <span className="font-bold text-xl">RentalFlow</span>
                </div>
                <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold mb-3 sm:mb-4 leading-tight">
                    Property Management<br />
                    <span className="text-blue-200">Made Simple</span>
                </h1>
                <p className="text-blue-100/80 text-sm sm:text-base lg:text-lg mb-6 sm:mb-10">
                    Streamline your rental operations with our all-in-one platform.
                </p>
            </div>
        </div>
    );
}

export default function Login({ flash }: Props) {
    const { data, setData, post, processing, errors } = useForm({ email: '', password: '', remember: false });
    const [showPassword, setShowPassword] = useState(false);

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/login');
    }

    return (
        <>
            <Head title="Sign In" />
            <div className="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 relative overflow-hidden">
            <div className="absolute inset-0 opacity-10" aria-hidden="true">
                <svg className="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
                    <path d="M0 100 C 40 20 60 20 100 100 Z" fill="white" opacity="0.5" />
                </svg>
            </div>
            <div className="relative z-10 w-full max-w-5xl bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden flex flex-col-reverse lg:flex-row">
                <BrandPanel />
                <div className="lg:w-7/12 p-6 sm:p-8 lg:p-12">
                    <h2 className="text-2xl font-bold text-slate-900 mb-1">Welcome back</h2>
                    <p className="text-slate-500 mb-8">Sign in to your account</p>
                    {flash?.error && (
                        <div role="alert" className="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm border border-red-100">{flash.error}</div>
                    )}
                    <form onSubmit={submit} className="space-y-4" noValidate>
                        <Field id="loginEmail" label="Email Address" error={errors.email}>
                            <input id="loginEmail" type="email" autoComplete="email" value={data.email}
                                onChange={(e) => setData('email', e.target.value)} className={inputClass}
                                placeholder="Enter email" required />
                        </Field>
                        <Field id="loginPassword" label="Password" error={errors.password}>
                            <div className="relative">
                                <input id="loginPassword" type={showPassword ? 'text' : 'password'}
                                    autoComplete="current-password" value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className={`${inputClass} pr-11`} placeholder="Enter password" required />
                                <button type="button" onClick={() => setShowPassword((v) => !v)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                    aria-label={showPassword ? 'Hide password' : 'Show password'}>
                                    <i className={`fas ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`} aria-hidden="true" />
                                </button>
                            </div>
                        </Field>
                        <div className="flex items-center justify-between">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                <span className="text-sm text-slate-600">Remember me</span>
                            </label>
                            {/*
                                An Inertia <Link>, NOT <a href>.
                                A plain anchor forces a full page reload, which
                                throws away the SPA shell -- and /forgot-password
                                had no route at all, so this link 404'd. That was
                                the whole of the reported "forgot password does
                                not work" bug.
                            */}
                            <Link href="/forgot-password" className="text-sm font-medium text-blue-600 hover:text-blue-700">
                                Forgot password?
                            </Link>
                        </div>
                        <button type="submit" disabled={processing}
                            className="w-full py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                            {processing ? 'Signing in...' : 'Sign In'}
                        </button>
                    </form>
                    <div className="mt-6 text-center">
                        {/*
                            Was <a href="/signup">, which had NO route at all --
                            a second dead link on this screen, same as
                            /forgot-password. Registration now exists, and this is
                            an Inertia <Link> so the SPA shell survives.
                        */}
                        <p className="text-sm text-slate-500">Don't have an account?{' '}
                            <Link href="/register" className="font-medium text-blue-600 hover:text-blue-700">Sign up</Link></p>
                    </div>
                </div>
            </div>
            </div>
        </>
    );
}
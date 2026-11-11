import { Link, usePage, router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import type { AuthUser } from '@/types';
import { NAV, PAGE_TITLES } from './AppLayout';

interface ShellProps {
    children: ReactNode;
    title?: string;
    subtitle?: string;
}

/** Sidebar + header chrome shared by every authenticated page. */
export function Shell({ children, title, subtitle }: ShellProps) {
    const page = usePage();
    const user = (page.props.auth as { user: AuthUser | null } | undefined)
        ?.user;
    const pathname = page.url.split('?')[0] ?? '/';
    const items = user ? (NAV[user.role] ?? []) : [];

    function isActive(href: string): boolean {
        return href === '/' ? pathname === '/' : pathname.startsWith(href);
    }

    return (
        <div className="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
            {/* Sidebar — blue gradient, matching the legacy component */}
            <aside className="hidden lg:flex lg:w-64 flex-col bg-gradient-to-b from-blue-700 to-blue-900 text-white shadow-2xl min-h-screen">
                <div className="h-16 flex items-center px-5 border-b border-white/10">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center font-bold text-sm">
                            RF
                        </div>
                        <span className="font-bold text-lg">RentalFlow</span>
                    </div>
                </div>

                <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    {items.map((item) => {
                        // A module that has not been ported yet is shown for
                        // context but is NOT a link: clicking it would 404,
                        // which reads as a broken app.
                        if (item.ready === false) {
                            return (
                                <span
                                    key={item.href}
                                    aria-disabled="true"
                                    title="Not yet available"
                                    className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-blue-100/35 cursor-not-allowed"
                                >
                                    <i
                                        className={`fas ${item.icon} w-5`}
                                        aria-hidden="true"
                                    />
                                    {item.label}
                                    <span className="ml-auto text-[10px] uppercase tracking-wide bg-white/10 rounded px-1.5 py-0.5">
                                        Soon
                                    </span>
                                </span>
                            );
                        }

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
                                    isActive(item.href)
                                        ? 'bg-white/15 text-white'
                                        : 'text-blue-100/80 hover:bg-white/10 hover:text-white'
                                }`}
                                aria-current={
                                    isActive(item.href) ? 'page' : undefined
                                }
                            >
                                <i
                                    className={`fas ${item.icon} w-5`}
                                    aria-hidden="true"
                                />
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>
            </aside>

            <div className="flex-1 flex flex-col min-h-screen">
                <header className="h-16 bg-white/80 backdrop-blur border-b border-blue-100/50 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30">
                    <div>
                        <h1 className="text-lg font-bold text-slate-900">
                            {title ?? PAGE_TITLES[pathname] ?? 'RentFlow'}
                        </h1>
                        {subtitle && (
                            <p className="text-sm text-slate-500">{subtitle}</p>
                        )}
                    </div>

                    <div className="flex items-center gap-4">
                        {user && (
                            <div className="hidden sm:flex items-center gap-3">
                                <div className="text-right">
                                    <p className="text-sm font-medium text-slate-900">
                                        {user.name}
                                    </p>
                                    <p className="text-xs text-slate-500">
                                        {user.roleLabel}
                                    </p>
                                </div>
                                <div className="w-9 h-9 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                                    {user.name
                                        .split(' ')
                                        .map((s) => s.charAt(0))
                                        .join('')
                                        .slice(0, 2)
                                        .toUpperCase()}
                                </div>
                            </div>
                        )}

                        <button
                            type="button"
                            onClick={() => router.post('/logout')}
                            className="p-2 text-slate-400 hover:text-red-600 transition-colors"
                            aria-label="Sign out"
                        >
                            <i
                                className="fas fa-right-from-bracket"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto p-4 lg:p-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
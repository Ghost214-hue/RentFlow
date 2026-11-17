import { Link, usePage, router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import type { AuthUser } from '@/types';
import { ThemeToggle } from '@/components/ui';
import { NAV, PAGE_TITLES } from './AppLayout';
import { useState } from 'react';

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
    const [sidebarOpen, setSidebarOpen] = useState(false);

    function isActive(href: string): boolean {
        return href === '/' ? pathname === '/' : pathname.startsWith(href);
    }

    return (
        <div className="min-h-screen flex flex-col bg-page text-text lg:flex-row">
            {/* Mobile hamburger */}
            <button
                type="button"
                onClick={() => setSidebarOpen(!sidebarOpen)}
                className="lg:hidden p-3 text-text-muted hover:text-primary rounded-[var(--radius-control)] hover:bg-surface-raised transition-colors"
                aria-label={sidebarOpen ? 'Close sidebar' : 'Open sidebar'}
                aria-expanded={sidebarOpen}
            >
                <i className={`fas ${sidebarOpen ? 'fa-times' : 'fa-bars'}`} aria-hidden="true" />
            </button>

            <aside
                className={`glow-border glow-sidebar fixed inset-y-0 left-0 z-40 min-h-screen w-64 flex-col border-r border-border bg-surface text-text shadow-raised transform transition-colors duration-300 lg:static lg:flex lg:translate-x-0 ${
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="flex h-16 items-center justify-between border-b border-border bg-surface-raised px-5">
                    <div className="flex items-center gap-3">
                        <div className="flex h-9 w-9 items-center justify-center rounded-[var(--radius-control)] bg-primary text-sm font-bold text-white shadow-lg shadow-blue-950/30">
                            RF
                        </div>
                        <span className="text-lg font-bold tracking-tight text-text">RentalFlow</span>
                    </div>
                    <button
                        type="button"
                        onClick={() => setSidebarOpen(false)}
                        className="rounded-[var(--radius-control)] p-2 text-text-muted hover:bg-page hover:text-text lg:hidden"
                        aria-label="Close sidebar"
                    >
                        <i className="fas fa-times" aria-hidden="true" />
                    </button>
                </div>

                <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    {items.map((item) => {
                        if (item.ready === false) {
                            return (
                                <span
                                    key={item.href}
                                    aria-disabled="true"
                                    title="Not yet available"
                                    className="flex cursor-not-allowed items-center gap-3 rounded-[var(--radius-control)] px-3 py-2.5 text-sm font-medium text-text-subtle opacity-60"
                                >
                                    <i className={`fas ${item.icon} w-5`} aria-hidden="true" />
                                    {item.label}
                                    <span className="ml-auto rounded border border-border bg-page px-1.5 py-0.5 text-[10px] uppercase tracking-[0.14em] text-text-subtle">
                                        Soon
                                    </span>
                                </span>
                            );
                        }

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all ${
                                    isActive(item.href)
                                        ? 'border-l-2 border-primary bg-primary/10 font-semibold text-primary'
                                        : 'text-text-muted hover:bg-page hover:text-text'
                                }`}
                                aria-current={isActive(item.href) ? 'page' : undefined}
                            >
                                <i className={`fas ${item.icon} w-5`} aria-hidden="true" />
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>
            </aside>

            <div className="workspace-frame glow-border flex min-h-screen min-w-0 flex-1 flex-col">
                <header className="glow-border glow-header sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-surface/80 px-4 backdrop-blur-xl lg:px-8">
                    <div>
                        <h1 className="text-lg font-bold text-text">
                            {title ?? PAGE_TITLES[pathname] ?? 'RentFlow'}
                        </h1>
                        {subtitle && <p className="text-sm text-text-muted">{subtitle}</p>}
                    </div>

                    <div className="flex items-center gap-3">
                        <ThemeToggle />
                        {user && (
                            <div className="hidden items-center gap-3 sm:flex">
                                <div className="text-right">
                                    <p className="text-sm font-medium text-text">{user.name}</p>
                                    <p className="text-xs text-text-muted">{user.roleLabel}</p>
                                </div>
                                <div className="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-primary/15 to-primary/5 text-sm font-bold text-primary">
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
                            className="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-surface-raised text-text-muted transition-colors hover:text-danger"
                            aria-label="Sign out"
                        >
                            <i className="fas fa-right-from-bracket" aria-hidden="true" />
                        </button>
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto p-4 lg:p-8">
                    <div className="mx-auto max-w-7xl">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
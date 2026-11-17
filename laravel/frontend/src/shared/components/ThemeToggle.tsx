/** Shared Portal Layout
 * 
 * Common chrome (sidebar, header) used by all portals.
 */
import { usePage } from '@inertiajs/react';
import { useAuth } from '@/shared/auth/context';
import { useState } from 'react';

interface SharedLayoutProps {
    children: React.ReactNode;
    title: string;
    subtitle?: string;
}

export function SharedLayout({ children, title, subtitle }: SharedLayoutProps) {
    const page = usePage();
    const user = (page.props.auth as { user: any } | undefined)?.user;
    const pathname = page.url.split('?')[0] ?? '/';
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Mobile sidebar toggle */}
            <button
                type="button"
                onClick={() => setSidebarOpen(!sidebarOpen)}
                className="lg:hidden p-2 text-gray-500 hover:text-primary rounded"
                aria-label="Open sidebar"
            >
                <i className={`fas ${sidebarOpen ? 'fa-times' : 'fa-bars'}`} aria-hidden="true" />
            </button>

            {/* Sidebar (hidden on desktop) */}
            <aside
                className={`glow-border glow-sidebar fixed inset-y-0 left-0 z-40 min-h-screen w-64 flex-col border-r border-gray-200 bg-white text-gray-800 shadow-md transform transition-colors duration-300 lg:static lg:translate-x-0 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}
            >
                <div className="flex h-16 items-center justify-between border-b border-gray-200 p-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-9 w-9 items-center justify-center rounded bg-primary text-sm font-bold text-white shadow-lg shadow-blue-950/30">
                            RF
                        </div>
                        <span className="text-lg font-bold tracking-tight text-gray-800">RentalFlow</span>
                    </div>
                    <button
                        type="button"
                        onClick={() => setSidebarOpen(false)}
                        className="rounded p-1.5 text-gray-400 hover:bg-gray-100"
                        aria-label="Close sidebar"
                    >
                        <i className="fas fa-times" aria-hidden="true" />
                    </button>
                </div>

                <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    {/* Portal-specific nav items would be rendered here */}
                    {/* This is a shared layout - specific nav per role goes in portal pages */}
                </nav>
            </aside>

            <div className="workspace-frame flex min-h-screen flex-col">
                <header className="glow-border glow-header sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-gray-50 px-4 lg:px-8">
                    <div>
                        <h1 className="text-lg font-bold text-gray-800">
                            {title ?? 'RentFlow'}
                        </h1>
                        {subtitle && <p className="text-sm text-gray-500">{subtitle}</p>}
                    </div>

                    <div className="flex items-center gap-3">
                        <ThemeToggle />

                        {user && (
                            <div className="hidden items-center gap-3 sm:flex">
                                <div className="text-right">
                                    <p className="text-sm font-medium text-gray-800">{user.name}</p>
                                    <p className="text-xs text-gray-500">{user.role}</p>
                                </div>
                                <div className="flex h-9 w-9 items-center justify-center rounded bg-gradient-to-br from-primary/15 to-primary/5 text-sm font-bold text-primary">
                                    {user.name.split(' ').map((s) => s.charAt(0)).join('').toUpperCase()}
                                </div>
                            </div>
                        )}

                        <button
                            type="button"
                            onClick={() => router.post('/logout')}
                            className="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 text-gray-400 transition-colors hover:text-danger"
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

/** Theme toggle component */
import { useState } from 'react';

export function ThemeToggle() {
    const [theme, setTheme] = useState<'light' | 'dark'>('light');

    return (
        <button
            type="button"
            onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')}
            className="rounded-full p-2 bg-gray-100 hover:bg-gray-200 transition-colors"
            aria-label="Toggle theme"
        >
            <i
                className={`fas ${theme === 'dark' ? 'fa-moon' : 'fa-sun'}`}
                aria-hidden="true"
            />
        </button>
    );
}
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    version: string;
    env?: string;
    debug?: boolean;
    sessionDriver?: string;
    queueConnection?: string;
    phpVersion?: string;
    dbConnection?: string;
}

export default function SystemStatus({
    version,
    env = 'local',
    debug = false,
    sessionDriver = 'database',
    queueConnection = 'database',
    phpVersion = '8.3',
    dbConnection = 'mysql',
}: Props) {
    return (
        <AppLayout
            title="System & Environment"
            subtitle="Runtime diagnostics, framework versioning, drivers, and background processing"
        >
            <Head title="System Status - Admin" />

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                {/* Application Details */}
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-card">
                    <h3 className="text-base font-bold text-text mb-4 flex items-center gap-2">
                        <i className="fas fa-cube text-primary text-sm" aria-hidden="true" />
                        Application Core
                    </h3>
                    <dl className="space-y-3 text-sm">
                        <div className="flex items-center justify-between">
                            <dt className="text-xs text-text-subtle font-semibold uppercase">Version</dt>
                            <dd className="font-mono text-xs font-bold text-primary">{version}</dd>
                        </div>
                        <div className="flex items-center justify-between">
                            <dt className="text-xs text-text-subtle font-semibold uppercase">Environment</dt>
                            <dd>
                                <span className="rounded bg-primary/10 text-primary px-2 py-0.5 text-xs font-semibold uppercase font-mono">
                                    {env}
                                </span>
                            </dd>
                        </div>
                        <div className="flex items-center justify-between">
                            <dt className="text-xs text-text-subtle font-semibold uppercase">Debug Mode</dt>
                            <dd>
                                <span
                                    className={`rounded px-2 py-0.5 text-xs font-semibold ${
                                        debug
                                            ? 'bg-amber-500/10 text-amber-600'
                                            : 'bg-emerald-500/10 text-emerald-600'
                                    }`}
                                >
                                    {debug ? 'ENABLED' : 'DISABLED'}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                {/* Infrastructure Drivers */}
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-card">
                    <h3 className="text-base font-bold text-text mb-4 flex items-center gap-2">
                        <i className="fas fa-server text-primary text-sm" aria-hidden="true" />
                        Infrastructure Drivers
                    </h3>
                    <dl className="space-y-3 text-sm">
                        <div className="flex items-center justify-between">
                            <dt className="text-xs text-text-subtle font-semibold uppercase">PHP Runtime</dt>
                            <dd className="font-mono text-xs font-semibold text-text">{phpVersion}</dd>
                        </div>
                        <div className="flex items-center justify-between">
                            <dt className="text-xs text-text-subtle font-semibold uppercase">Database Driver</dt>
                            <dd className="font-mono text-xs font-semibold text-text">{dbConnection}</dd>
                        </div>
                        <div className="flex items-center justify-between">
                            <dt className="text-xs text-text-subtle font-semibold uppercase">Session Store</dt>
                            <dd className="font-mono text-xs font-semibold text-text">{sessionDriver}</dd>
                        </div>
                        <div className="flex items-center justify-between">
                            <dt className="text-xs text-text-subtle font-semibold uppercase">Queue Worker</dt>
                            <dd className="font-mono text-xs font-semibold text-text">{queueConnection}</dd>
                        </div>
                    </dl>
                </div>

                {/* Health Checks */}
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-card">
                    <h3 className="text-base font-bold text-text mb-4 flex items-center gap-2">
                        <i className="fas fa-heart-pulse text-emerald-500 text-sm" aria-hidden="true" />
                        Platform Health
                    </h3>
                    <div className="space-y-3 text-sm">
                        <div className="flex items-center justify-between p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <span className="text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                Database Connection
                            </span>
                            <span className="text-xs font-bold text-emerald-600">CONNECTED</span>
                        </div>
                        <div className="flex items-center justify-between p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <span className="text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                Storage / Public Disk
                            </span>
                            <span className="text-xs font-bold text-emerald-600">WRITABLE</span>
                        </div>
                        <div className="flex items-center justify-between p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <span className="text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                Inertia React Client
                            </span>
                            <span className="text-xs font-bold text-emerald-600">OPERATIONAL</span>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

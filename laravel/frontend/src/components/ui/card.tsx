import type { HTMLAttributes, ReactNode } from 'react';

function cn(...classes: Array<string | false | null | undefined>) {
    return classes.filter(Boolean).join(' ');
}

export function Card({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={cn(
                'rounded-[var(--radius-card)] border border-border bg-surface shadow-card backdrop-blur-sm',
                className,
            )}
            {...props}
        />
    );
}

export function CardHeader({
    className,
    children,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('border-b border-border px-5 py-4 sm:px-6', className)} {...props}>
            {children}
        </div>
    );
}

export function CardBody({
    className,
    children,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('px-5 py-5 sm:px-6', className)} {...props}>
            {children}
        </div>
    );
}

interface StatCardProps extends HTMLAttributes<HTMLDivElement> {
    label: string;
    value: string;
    icon: string;
    tone?: 'primary' | 'success' | 'warning' | 'danger' | 'info';
}

const toneMap: Record<NonNullable<StatCardProps['tone']>, string> = {
    primary: 'from-blue-500 to-blue-600',
    success: 'from-emerald-500 to-emerald-600',
    warning: 'from-amber-500 to-amber-600',
    danger: 'from-red-500 to-red-600',
    info: 'from-violet-500 to-violet-600',
};

export function StatCard({
    label,
    value,
    icon,
    tone = 'primary',
    className,
    ...props
}: StatCardProps) {
    return (
        <Card
            className={cn('glow-border p-5 transition-transform duration-200 hover:-translate-y-0.5', className)}
            {...props}
        >
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-medium text-text-muted">{label}</p>
                    <p className="mt-2 text-2xl font-bold tracking-tight text-text">{value}</p>
                </div>
                <div className={cn('flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-lg', toneMap[tone])}>
                    <i className={`fas ${icon}`} aria-hidden="true" />
                </div>
            </div>
        </Card>
    );
}

export function PageHeader({
    title,
    subtitle,
    actions,
    className,
}: {
    title: string;
    subtitle?: string;
    actions?: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between', className)}>
            <div>
                <h1 className="text-2xl font-bold tracking-tight text-text">{title}</h1>
                {subtitle && <p className="mt-1 text-sm text-text-muted">{subtitle}</p>}
            </div>
            {actions && <div className="flex items-center gap-2">{actions}</div>}
        </div>
    );
}

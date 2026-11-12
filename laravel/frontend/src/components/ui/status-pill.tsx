import type { ReactNode } from 'react';

function cn(...classes: Array<string | false | null | undefined>) {
    return classes.filter(Boolean).join(' ');
}

export type StatusVariant = 'success' | 'warning' | 'danger' | 'info' | 'neutral';

const variantClasses: Record<StatusVariant, string> = {
    success: 'bg-[color:var(--color-success-bg)] text-[color:var(--color-success-fg)] border-[color:var(--color-success-border)]',
    warning: 'bg-[color:var(--color-warning-bg)] text-[color:var(--color-warning-fg)] border-[color:var(--color-warning-border)]',
    danger: 'bg-[color:var(--color-danger-bg)] text-[color:var(--color-danger-fg)] border-[color:var(--color-danger-border)]',
    info: 'bg-[color:var(--color-info-bg)] text-[color:var(--color-info-fg)] border-[color:var(--color-info-border)]',
    neutral: 'bg-[color:var(--color-neutral-bg)] text-[color:var(--color-neutral-fg)] border-[color:var(--color-neutral-border)]',
};

export function StatusPill({ children, variant = 'neutral' }: { children: ReactNode; variant?: StatusVariant }) {
    return (
        <span className={cn('inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-medium shadow-sm', variantClasses[variant])}>
            {children}
        </span>
    );
}

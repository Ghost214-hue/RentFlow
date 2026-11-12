import type { ButtonHTMLAttributes, ReactNode } from 'react';

export type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger';
export type ButtonSize = 'sm' | 'md' | 'lg';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: ButtonVariant;
    size?: ButtonSize;
    loading?: boolean;
    children: ReactNode;
}

const variantClasses: Record<ButtonVariant, string> = {
    primary:
        'bg-gradient-to-r from-primary to-primary-hover text-white shadow-[0_10px_30px_rgba(37,99,235,0.30)] hover:shadow-[0_12px_32px_rgba(37,99,235,0.42)] border border-transparent',
    secondary:
        'bg-surface-raised text-text border border-border hover:border-border-strong shadow-card',
    ghost:
        'bg-transparent text-text-muted hover:bg-surface hover:text-text border border-transparent',
    danger:
        'bg-[color:var(--color-danger)] text-[color:var(--color-danger-fg)] border border-[color:var(--color-danger-border)] shadow-[0_10px_30px_rgba(239,68,68,0.18)] hover:brightness-105',
};

const sizeClasses: Record<ButtonSize, string> = {
    sm: 'h-9 px-3 text-sm',
    md: 'h-11 px-4 text-sm',
    lg: 'h-12 px-5 text-base',
};

export function Button({
    variant = 'primary',
    size = 'md',
    loading = false,
    disabled,
    children,
    className = '',
    type = 'button',
    ...props
}: ButtonProps) {
    const isDisabled = disabled || loading;

    return (
        <button
            type={type}
            disabled={isDisabled}
            className={[
                'inline-flex items-center justify-center gap-2 rounded-[var(--radius-control)] font-medium transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/35 disabled:cursor-not-allowed disabled:opacity-60',
                variantClasses[variant],
                sizeClasses[size],
                className,
            ].join(' ')}
            {...props}
        >
            {loading && (
                <span className="inline-flex h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent" aria-hidden="true" />
            )}
            {children}
        </button>
    );
}

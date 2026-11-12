import type { InputHTMLAttributes, ReactNode, SelectHTMLAttributes, TextareaHTMLAttributes } from 'react';

function cn(...classes: Array<string | false | null | undefined>) {
    return classes.filter(Boolean).join(' ');
}

const baseInputClass =
    'w-full rounded-[var(--radius-control)] border border-border bg-surface-raised px-3.5 py-2.5 text-sm text-text placeholder:text-text-subtle shadow-[0_1px_0_rgba(15,23,42,0.02)] transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary';

export interface FieldProps {
    id: string;
    label?: string;
    error?: string;
    children: ReactNode;
    className?: string;
}

export function Field({ id, label, error, children, className }: FieldProps) {
    return (
        <div className={cn('space-y-1.5', className)}>
            {label && (
                <label htmlFor={id} className="block text-sm font-medium text-text">
                    {label}
                </label>
            )}
            {children}
            {error && <p className="text-sm text-danger">{error}</p>}
        </div>
    );
}

export function Input({ className, error, ...props }: InputHTMLAttributes<HTMLInputElement> & { error?: string }) {
    return (
        <input
            {...props}
            className={cn(baseInputClass, error && 'border-danger/60 focus:border-danger focus:ring-danger/20', className)}
        />
    );
}

export function Select({ className, error, ...props }: SelectHTMLAttributes<HTMLSelectElement> & { error?: string }) {
    return (
        <select
            {...props}
            className={cn(baseInputClass, error && 'border-danger/60 focus:border-danger focus:ring-danger/20', className)}
        />
    );
}

export function Textarea({ className, error, ...props }: TextareaHTMLAttributes<HTMLTextAreaElement> & { error?: string }) {
    return (
        <textarea
            {...props}
            className={cn(baseInputClass, 'min-h-[120px] resize-y', error && 'border-danger/60 focus:border-danger focus:ring-danger/20', className)}
        />
    );
}

export function Checkbox({ className, ...props }: InputHTMLAttributes<HTMLInputElement>) {
    return (
        <input
            {...props}
            type="checkbox"
            className={cn('h-4 w-4 rounded border-border text-primary shadow-sm focus:ring-primary/30 focus:ring-2', className)}
        />
    );
}

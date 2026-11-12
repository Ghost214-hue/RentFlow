import { useTheme } from '@/lib/theme';

export function ThemeToggle({ className = '' }: { className?: string }) {
    const { theme, resolvedTheme, setTheme } = useTheme();

    const currentLabel =
        theme === 'system' ? `System theme (${resolvedTheme})` : `${theme} theme`;

    return (
        <button
            type="button"
            aria-label={`Switch theme. Current: ${currentLabel}`}
            title={`Current theme: ${currentLabel}`}
            onClick={() => {
                if (theme === 'light') {
                    setTheme('dark');
                    return;
                }
                if (theme === 'dark') {
                    setTheme('system');
                    return;
                }
                setTheme('light');
            }}
            className={[
                'inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-surface-raised text-text-muted shadow-card transition-colors hover:text-text',
                className,
            ].join(' ')}
        >
            <i
                className={[
                    'fas',
                    resolvedTheme === 'dark' ? 'fa-moon' : 'fa-sun',
                    theme === 'system' ? 'opacity-80' : '',
                ].join(' ')}
                aria-hidden="true"
            />
        </button>
    );
}

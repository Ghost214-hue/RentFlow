import { createInertiaApp } from '@inertiajs/react';
import type { ResolvedComponent } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import './bootstrap';

const appName = import.meta.env.VITE_APP_NAME || 'RentFlow';

createInertiaApp({
    title: (title: string) => (title ? `${title} - ${appName}` : appName),

    resolve: async (name: string) => {
        // Every page under resources/js/pages is addressable by its path,
        // e.g. 'Houses/Index' -> ./pages/Houses/Index.tsx
        const pages = import.meta.glob<{ default: ResolvedComponent }>(
            './pages/**/*.tsx',
            { eager: true },
        );

        const page = pages[`./pages/${name}.tsx`];

        if (!page) {
            throw new Error(`Inertia page not found: ${name}`);
        }

        return page.default;
    },

    setup({ el, App, props }) {
        // Inertia types el as HTMLElement | null; it is always the #app root.
        const container = el as HTMLElement;
        createRoot(container).render(<App {...props} />);
    },

    progress: {
        color: '#2563eb',
    },
});
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'node:path';

// Vite dev server port. Must match VITE_PORT in ../backend/.env so the CSP
// (SecurityHeaders.php) allows font/assets served from the dev server.
const vitePort = process.env.VITE_PORT ? parseInt(process.env.VITE_PORT, 10) : 5173;

export default defineConfig({
    plugins: [
        laravel({
            input: ['src/css/app.css', 'src/app.tsx'],
            publicDirectory: '../backend/public',
            buildDirectory: 'build',
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        react({ jsxRuntime: 'automatic' }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(import.meta.dirname, 'src'),
        },
    },
    build: {
        emptyOutDir: true,
    },
    server: {
        host: '127.0.0.1',
        port: vitePort,
        watch: {
            ignored: ['../backend/storage/framework/views/**'],
        },
    },
    // Vite 8 enables experimental bundled dev mode by default, which triggers
    // "@vitejs/plugin-react can't detect preamble" in dev. Disable it until the
    // plugin/Vite combination is stable.
    experimental: {
        bundledDev: false,
    },
});

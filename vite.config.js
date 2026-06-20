import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const vitePort = Number(env.VITE_PORT || 5173);

    return {
        server: {
            host: '127.0.0.1',
            port: vitePort,
            strictPort: true,
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.jsx'],
                refresh: true,
            }),
            react(),
            VitePWA({
                registerType: 'autoUpdate',
                includeAssets: [
                    'favicon.ico',
                    'icons/kicap-event-logo.svg',
                    'icons/kicap-event-apple-touch.png',
                ],
                manifest: {
                    name: 'Kicap Event',
                    short_name: 'Kicap Event',
                    description: 'Aplikasi PWA mobile-first untuk pengelolaan Event/Kegiatan dan output LPJ.',
                    start_url: '/app',
                    scope: '/',
                    display: 'standalone',
                    background_color: '#f3f7f4',
                    theme_color: '#0f766e',
                    icons: [
                        {
                            src: '/icons/kicap-event-logo.svg',
                            sizes: 'any',
                            type: 'image/svg+xml',
                            purpose: 'any maskable',
                        },
                        {
                            src: '/icons/kicap-event-192.png',
                            sizes: '192x192',
                            type: 'image/png',
                            purpose: 'any maskable',
                        },
                        {
                            src: '/icons/kicap-event-512.png',
                            sizes: '512x512',
                            type: 'image/png',
                            purpose: 'any maskable',
                        },
                    ],
                },
            }),
        ],
    };
});

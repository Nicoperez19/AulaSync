import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build',
        manifest: true,
        sourcemap: false,
        chunkSizeWarningLimit: 500,
    },
    optimizeDeps: {
        include: ['@tailwindcss/forms'],
    },
    server: {
        watch: {
            usePolling: true,
        },
    },
});

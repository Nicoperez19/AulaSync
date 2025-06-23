import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/data-upload.js'
            ],
            refresh: true,
        }),
    ],
<<<<<<< HEAD
    build: {
        outDir: 'public/build',
        manifest: true,
        sourcemap: false,
        chunkSizeWarningLimit: 500,
    },
    optimizeDeps: {
        include: [
            '@tailwindcss/forms',
            'alpinejs',
            '@alpinejs/collapse',
            '@alpinejs/focus',
            '@alpinejs/mask'
        ]
    }
});
=======
}); 
>>>>>>> Nperez

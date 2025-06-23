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
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
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

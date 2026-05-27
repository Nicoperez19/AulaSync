import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { readFileSync, writeFileSync, existsSync } from 'fs';
import { resolve } from 'path';

/**
 * Plugin que procesa docs/MANUAL.md en cada build y genera
 * public/manual-meta.json con la fecha de actualización y lista
 * de capítulos, garantizando que la vista siempre muestre el
 * contenido más reciente tras un `pnpm run build`.
 */
function manualProcessorPlugin() {
    return {
        name: 'manual-processor',
        buildStart() {
            const manualPath = resolve(__dirname, 'docs/MANUAL.md');
            if (!existsSync(manualPath)) return;

            const content = readFileSync(manualPath, 'utf8');
            const chapters = [];

            content.split('\n').forEach(line => {
                const match = line.match(/^## (.+)$/);
                if (match) {
                    const title = match[1].trim();
                    const slug = title
                        .toLowerCase()
                        .replace(/[áéíóú]/g, c => ({ á:'a',é:'e',í:'i',ó:'o',ú:'u' }[c] || c))
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/[\s-]+/g, '-')
                        .trim();
                    chapters.push({ title, slug });
                }
            });

            const meta = {
                updatedAt: new Date().toISOString(),
                chaptersCount: chapters.length,
                chapters,
            };

            const outputPath = resolve(__dirname, 'public/manual-meta.json');
            writeFileSync(outputPath, JSON.stringify(meta, null, 2), 'utf8');
            console.log(`[manual-processor] Procesados ${chapters.length} capítulos → public/manual-meta.json`);
        },
    };
}

export default defineConfig({
    plugins: [
        manualProcessorPlugin(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/dashboard.js',
            ],
            refresh: true,
        }),
    ],
});
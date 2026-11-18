import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { readFileSync, writeFileSync, existsSync } from 'fs';
import { resolve } from 'path';

/**
 * Plugin que procesa docs/MANUAL.md en cada build y genera
 * public/manual-meta.json con la fecha de actualización y lista
 * de capítulos, garantizando que la vista siempre muestre el
 * contenido más reciente tras un `npm run build`.
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
    esbuild: {
        drop: ['console', 'debugger'],
    },
    plugins: [
        manualProcessorPlugin(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/dashboard.js',
                'resources/js/tablero.js',
                'resources/js/views/acciones-rapidas/gestionar-reservas.js',
                'resources/js/views/acciones-rapidas/crear-reserva.js',
                'resources/js/views/acciones-rapidas/gestionar-salas-estudio.js',
                'resources/js/views/reportes/tipo-espacio.js',
                'resources/js/views/clases-temporales/create.js',
                'resources/js/views/reportes/salas-estudio.js',
                'resources/js/views/reportes/espacios.js',
                'resources/js/views/mapas/create.js',
                'resources/js/views/mapas/edit.js',
                'resources/js/views/livewire/clases-no-realizadas-table.js',
                'resources/js/views/reservas/index.js',
                'resources/js/views/clases-temporales/edit.js',
                'resources/js/views/horarios/espacios.js',
                'resources/js/views/data/index.js',
                'resources/js/views/acciones-rapidas/gestionar-espacios.js',
                'resources/js/views/horarios/profesores.js',
                'resources/js/views/components/navbar.js',
                'resources/js/views/plano-digital/show.js',
                'resources/js/views/tenant/initialization/steps/step4-bulk.js',
                'resources/js/views/espacios/index.js',
                'resources/js/views/livewire/correos-masivos-manager.js',
                'resources/js/views/components/asistencia/lista-estudiantes.js',
                'resources/js/views/espacios/edit.js',
                'resources/js/views/auth/forgot-password.js',
                'resources/js/views/livewire/partials/plantillas-correos-tab.js',
                'resources/js/views/reservas/solicitudes-reservas.js',
                'resources/js/views/livewire/permissions-table.js',
                'resources/js/views/acciones-rapidas/editar-reserva.js',
                'resources/js/views/configuracion/index.js',
                'resources/js/views/docente/asistencia/tomar.js',
                'resources/js/views/reportes/cumplimiento-escuelas.js',
                'resources/js/views/manual/index.js',
                'resources/js/views/auth/reset-password.js',
                'resources/js/views/livewire/data-load-table.js',
                'resources/js/views/components/layouts/app.js',
                'resources/js/views/layouts/dashboard.js',
                'resources/js/views/components/modal.js',
                'resources/js/views/usuarios/edit.js',
                'resources/js/views/partials/dashboard/status_clases_chart.js',
                'resources/js/views/universidades/edit.js',
                'resources/js/views/livewire/modulos-actuales-table.js',
                'resources/js/views/livewire/reservations-table.js',
                'resources/js/views/plano-digital/index.js',
                'resources/js/views/tenant/initialization/steps/step2-logo.js',
                'resources/js/views/profesores/edit.js',
                'resources/js/views/acciones-rapidas/index.js',
                'resources/js/views/tenant/initialization/success.js',
                'resources/js/views/livewire/mapas-table.js',
                'resources/js/views/reportes/accesos.js',
                'resources/js/views/auth/login.js',
                'resources/js/views/tenant/initialization/steps/step0-password.js',
                'resources/js/views/reportes/ocupacion-semanal.js',
                'resources/js/views/perfil/edit.js',
                'resources/js/views/estudiantes/carga-masiva.js',
                'resources/js/views/livewire/notificaciones-dropdown.js',
            ],
            refresh: true,
        }),
    ],
});
@props([
    'estudiantesData' => collect(),
    'bloqueado' => false,
    'mensajeBloqueo' => null
])

<div class="bg-white dark:bg-dark-eval-1 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden" x-data="asistenciaListHandler()">
    {{-- Barra superior de acciones y contadores --}}
    <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-dark-eval-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-user text-light-cloud-blue"></i>
                <span>Listado de Estudiantes</span>
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Marca la casilla correspondiente para registrar la presencia del alumno en la sesión.</p>
        </div>

        {{-- Contadores dinámicos --}}
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                <i class="fa-solid fa-user-check"></i>
                <span x-text="totalPresentes"></span> Presentes
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-rose-100 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 text-xs font-semibold">
                <i class="fa-solid fa-user-xmark"></i>
                <span x-text="totalAusentes"></span> Ausentes
            </div>
        </div>
    </div>

    {{-- Buscador y botón para agregar alumno no listado --}}
    <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="relative w-full sm:w-72">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" x-model="filtro" placeholder="Buscar por nombre o RUN..." 
                   class="w-full pl-9 pr-3 py-1.5 text-xs rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-light-cloud-blue outline-none transition">
        </div>

        @if(!$bloqueado)
            <button type="button" @click="mostrarModalNuevo = true" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-md bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 text-light-cloud-blue text-xs font-semibold border border-blue-200 dark:border-blue-800 transition">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>Agregar Estudiante no Listado</span>
            </button>
        @endif
    </div>

    @if($bloqueado)
        <div class="p-6 text-center bg-amber-50/50 dark:bg-dark-eval-2">
            <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-lock text-xl"></i>
            </div>
            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100">Toma de asistencia bloqueada</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 max-w-md mx-auto mt-1">{{ $mensajeBloqueo ?? 'Debes escanear el código QR del espacio para habilitar la toma de asistencia.' }}</p>
        </div>
    @endif

    {{-- Tabla de asistencia --}}
    <div class="overflow-x-auto">
        <table class="w-full text-center border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-dark-eval-2 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                    <th class="py-3 px-4 w-24 text-center">
                        <label class="inline-flex items-center justify-center gap-1.5 cursor-pointer select-none" title="Marcar o desmarcar todos">
                            <input type="checkbox" 
                                   @change="toggleTodos($el.checked)" 
                                   :checked="todosMarcados" 
                                   :disabled="{{ $bloqueado ? 'true' : 'false' }}"
                                   class="w-4 h-4 rounded text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:ring-offset-0 transition cursor-pointer">
                            <span>Estado</span>
                        </label>
                    </th>
                    <th class="py-3 px-4 w-36 text-center">RUN</th>
                    <th class="py-3 px-4 text-center">Apellidos y Nombres</th>
                    <th class="py-3 px-4 w-32 text-center">Inscripción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                {{-- Filas dinámicas existentes --}}
                <template x-for="(est, index) in estudiantesFiltrados" :key="est.id || ('nuevo_' + index)">
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-dark-eval-2 transition" :class="est.presente ? 'bg-emerald-50/20 dark:bg-emerald-950/10' : ''">
                        <td class="py-3 px-4 text-center">
                            <input type="hidden" :name="'asistencia[' + est.id + ']'" :value="est.presente ? '1' : '0'" :disabled="{{ $bloqueado ? 'true' : 'false' }}">
                            <label class="inline-flex items-center justify-center cursor-pointer">
                                <input type="checkbox" x-model="est.presente" @change="recalcular()" :disabled="{{ $bloqueado ? 'true' : 'false' }}"
                                       class="w-5 h-5 rounded text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:ring-offset-0 transition cursor-pointer">
                            </label>
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-gray-300 text-center" x-text="est.run"></td>
                        <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-100 text-center" x-text="est.nombre"></td>
                        <td class="py-3 px-4 text-center">
                            <template x-if="est.inscrito">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300">Inscrito</span>
                            </template>
                            <template x-if="!est.inscrito">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-300">Manual</span>
                            </template>
                        </td>
                    </tr>
                </template>

                {{-- Nuevos estudiantes agregados en caliente --}}
                <template x-for="(nuevo, nIndex) in nuevosEstudiantes" :key="'nuevo_item_' + nIndex">
                    <tr class="bg-amber-50/40 dark:bg-amber-950/20 hover:bg-amber-50/80 transition border-l-4 border-amber-500">
                        <td class="py-3 px-4 text-center">
                            <input type="hidden" name="nuevo_estudiante_run[]" :value="nuevo.run">
                            <input type="hidden" name="nuevo_estudiante_nombre[]" :value="nuevo.nombre">
                            <span class="w-5 h-5 inline-flex items-center justify-center rounded bg-emerald-600 text-white text-xs">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-xs font-bold text-gray-700 dark:text-gray-200 text-center" x-text="nuevo.run"></td>
                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-gray-100 text-center" x-text="nuevo.nombre"></td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center justify-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300">
                                    Agregado en clase
                                </span>
                                <button type="button" @click="eliminarNuevo(nIndex)" class="text-rose-500 hover:text-rose-700 text-xs p-1" title="Quitar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr x-show="estudiantesFiltrados.length === 0 && nuevosEstudiantes.length === 0">
                    <td colspan="4" class="py-8 text-center text-xs text-gray-400">
                        No se encontraron estudiantes para mostrar.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Modal / Panel para agregar nuevo estudiante no listado --}}
    <div x-show="mostrarModalNuevo" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-dark-eval-1 rounded-lg shadow-2xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-700" @click.away="mostrarModalNuevo = false">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-light-cloud-blue"></i>
                    <span>Agregar Estudiante no Listado</span>
                </h4>
                <button type="button" @click="mostrarModalNuevo = false" class="text-gray-400 hover:text-gray-600 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Si un estudiante asiste a la clase pero no figura en la lista oficial (ej. oyente, clase temporal o cambio de sección), puedes ingresarlo con su RUN.
            </p>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">RUN del Estudiante (sin puntos ni guión)</label>
                    <input type="text" x-model="nuevoRun" placeholder="Ej: 201234567" 
                           class="w-full px-3 py-2 text-xs rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-light-cloud-blue outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Apellidos y Nombres</label>
                    <input type="text" x-model="nuevoNombre" placeholder="Ej: PÉREZ GONZÁLEZ, Juan Carlos" 
                           class="w-full px-3 py-2 text-xs rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-light-cloud-blue outline-none">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="mostrarModalNuevo = false" class="px-3 py-2 text-xs font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-eval-2 transition">
                    Cancelar
                </button>
                <button type="button" @click="agregarNuevoEstudiante()" class="px-4 py-2 text-xs font-semibold rounded-md bg-light-cloud-blue hover:bg-blue-600 text-white shadow-sm transition">
                    Agregar y Marcar Presente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function asistenciaListHandler() {
    return {
        filtro: '',
        mostrarModalNuevo: false,
        nuevoRun: '',
        nuevoNombre: '',
        estudiantes: @json($estudiantesData),
        nuevosEstudiantes: [],

        get estudiantesFiltrados() {
            if (!this.filtro.trim()) {
                return this.estudiantes;
            }
            const q = this.filtro.toLowerCase();
            return this.estudiantes.filter(e => 
                (e.nombre && e.nombre.toLowerCase().includes(q)) || 
                (e.run && e.run.toLowerCase().includes(q))
            );
        },

        get totalEstudiantes() {
            return this.estudiantes.length + this.nuevosEstudiantes.length;
        },

        get totalPresentes() {
            const pres = this.estudiantes.filter(e => e.presente).length;
            return pres + this.nuevosEstudiantes.length;
        },

        get totalAusentes() {
            return this.estudiantes.filter(e => !e.presente).length;
        },

        get todosMarcados() {
            if (this.estudiantesFiltrados.length === 0) return false;
            return this.estudiantesFiltrados.every(e => e.presente);
        },

        toggleTodos(estado) {
            this.estudiantesFiltrados.forEach(e => e.presente = estado);
        },

        marcarTodos(estado) {
            this.estudiantes.forEach(e => e.presente = estado);
        },

        recalcular() {
            // Forzar reactividad de Alpine
        },

        agregarNuevoEstudiante() {
            const run = this.nuevoRun.trim().toUpperCase().replace(/[^0-9K]/g, '');
            const nombre = this.nuevoNombre.trim();

            if (!run || !nombre) {
                alert('Por favor ingresa tanto el RUN como el Nombre del estudiante.');
                return;
            }

            // Verificar si ya existe en la lista
            const yaExiste = this.estudiantes.some(e => e.run === run) || this.nuevosEstudiantes.some(e => e.run === run);
            if (yaExiste) {
                alert('Este RUN ya se encuentra en la lista de asistencia.');
                return;
            }

            this.nuevosEstudiantes.push({
                run: run,
                nombre: nombre,
                presente: true,
                inscrito: false
            });

            this.nuevoRun = '';
            this.nuevoNombre = '';
            this.mostrarModalNuevo = false;
        },

        eliminarNuevo(index) {
            this.nuevosEstudiantes.splice(index, 1);
        }
    }
}
</script>

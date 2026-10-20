<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue text-white shadow-md">
                    <i class="text-2xl fa-solid fa-clipboard-user"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                        {{ $tituloClase }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span>{{ $codigoClase }} @if($seccionClase !== 'N/A') &bull; Sección {{ $seccionClase }} @endif</span>
                        <span>&bull;</span>
                        <span>Espacio: <strong class="text-gray-700 dark:text-gray-200">{{ $espacioActualNombre }}</strong></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('docente.asistencia.historial', $id) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 dark:bg-dark-eval-1 dark:hover:bg-dark-eval-2 border border-slate-200 hover:border-slate-300 dark:border-gray-600 text-slate-700 dark:text-gray-200 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    <i class="fa-solid fa-clock-rotate-left text-xs text-slate-500 dark:text-gray-400"></i>
                    <span>Ver Historial</span>
                </a>
                <a href="{{ route('docente.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 dark:bg-dark-eval-1 dark:hover:bg-dark-eval-2 border border-slate-200 hover:border-slate-300 dark:border-gray-600 text-slate-700 dark:text-gray-200 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    <svg class="w-4 h-4 shrink-0 text-slate-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Volver a Mis Clases</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Banner de Superadmin Simulando --}}
        @if($esSuperadmin)
            <div class="p-4 rounded-lg bg-amber-50 border-l-4 border-amber-500 text-amber-900 shadow-sm flex items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-flask text-amber-600 text-base"></i>
                    <span><strong>Modo Prueba Superadministrador:</strong> Puedes guardar o modificar la asistencia de esta clase en nombre del docente.</span>
                </div>
                <span class="px-2.5 py-1 rounded bg-amber-200 text-amber-900 font-bold text-xs">
                    MODO PRUEBA
                </span>
            </div>
        @endif

        {{-- Barra de Información y Selector de Fecha de la Sesión --}}
        <div class="p-4 rounded-lg bg-white dark:bg-dark-eval-1 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg {{ $esHoy ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400' : 'bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400' }}">
                    <i class="fa-solid fa-calendar-day text-lg"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium uppercase tracking-wider">Fecha de la Sesión</div>
                    <div class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <span>{{ $fechaConsulta->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                        @if($esHoy)
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                Hoy
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                                Histórico
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                {{-- Selector de Fecha con Recarga Automática al Elegir Fecha --}}
                <input type="date" 
                       id="selector_fecha_sesion" 
                       value="{{ $fechaConsulta->format('Y-m-d') }}" 
                       max="{{ now()->format('Y-m-d') }}"
                       onchange="cambiarFechaSesion(this.value)"
                       class="text-xs px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 font-medium focus:ring-2 focus:ring-light-cloud-blue outline-none cursor-pointer shadow-xs">
            </div>
        </div>

        {{-- Formulario de Asistencia --}}
        <form method="POST" action="{{ route('docente.asistencia.store', $id) }}" class="space-y-6">
            @csrf
            <input type="hidden" id="fecha_asistencia_hidden" name="fecha" value="{{ $fechaConsulta->format('Y-m-d') }}">

            {{-- Campo de Actividad (obligatorio para clases temporales o editable) --}}
            @if($esColaboracion && !$asignatura)
                <div class="p-5 bg-amber-50/70 dark:bg-dark-eval-1 border border-amber-200 dark:border-amber-800 rounded-lg shadow-sm space-y-2">
                    <label for="actividad" class="block text-xs font-bold text-amber-950 dark:text-amber-200 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-clipboard-list text-amber-600"></i>
                        Actividad realizada en la clase temporal <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-amber-800 dark:text-amber-300">
                        Indica brevemente el tema, contenido o actividad desarrollada durante esta clase temporal para el registro institucional.
                    </p>
                    <textarea id="actividad" name="actividad" rows="2" required placeholder="Ej: Taller práctico de resolución de ejercicios sobre..."
                              class="w-full text-sm p-3 rounded-md border border-amber-300 dark:border-amber-700 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 outline-none">{{ old('actividad', $sesion?->actividad ?? $colaborador?->descripcion) }}</textarea>
                </div>
            @endif

            {{-- Componente Reutilizable de Lista de Estudiantes --}}
            <x-asistencia.lista-estudiantes 
                :estudiantesData="$estudiantesData" 
                :bloqueado="$bloqueadoPorIngreso" 
                :mensajeBloqueo="$mensajeBloqueo" 
            />

            {{-- Barra Inferior de Guardado Fija / Sticky --}}
            @if(!$bloqueadoPorIngreso)
                <div class="sticky bottom-4 z-20 bg-white/95 dark:bg-dark-eval-1/95 backdrop-blur-md p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center sm:text-left flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-light-cloud-blue"></i>
                        <span>Puedes volver a editar y actualizar la asistencia durante el transcurso del día.</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ route('docente.dashboard') }}" 
                           class="flex-1 sm:flex-initial text-center px-4 py-2.5 text-xs font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-eval-2 transition">
                            Descartar cambios
                        </a>
                        <x-button type="submit" 
                                  variant="ucsc"
                                  class="flex-1 sm:flex-initial justify-center gap-2 px-6 py-2.5 text-xs font-bold shadow-md">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>{{ $sesion ? 'Guardar Modificaciones' : 'Confirmar y Guardar Asistencia' }}</span>
                        </x-button>
                    </div>
                </div>
            @endif
        </form>
    </div>

    @push('scripts')
    <script>
        function cambiarFechaSesion(nuevaFecha) {
            if (!nuevaFecha) return;
            const hidden = document.getElementById('fecha_asistencia_hidden');
            if (hidden) {
                hidden.value = nuevaFecha;
            }
            const baseUrl = "{{ route('docente.asistencia.show', $id) }}";
            window.location.href = baseUrl + '?fecha=' + encodeURIComponent(nuevaFecha);
        }
    </script>
    @endpush
</x-app-layout>

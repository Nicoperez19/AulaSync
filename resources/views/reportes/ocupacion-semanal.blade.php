<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Histórico de Ocupación Semanal</h2>
                    <p class="text-sm text-gray-500">Almacenamiento por semanas, análisis de demanda y exportaciones en Excel y PDF</p>
                </div>
            </div>

            <!-- Botones con tamaño homogéneo y estilo idéntico a Control Docente -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 gap-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Dashboard</span>
                </a>
                <button type="button" onclick="document.getElementById('modal-generar-snapshot').classList.remove('hidden')"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 gap-2">
                    <i class="fa-solid fa-camera"></i>
                    <span>Registrar Semana Manual</span>
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Contenedor responsive al 100% (igual a Control Docente) -->
    <div class="space-y-6 pb-8">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-semibold flex items-center gap-3">
                <i class="fas fa-check-circle text-lg text-green-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filtros y Selector de Tipo -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <!-- Nav Pills por Tipo -->
                <div class="flex flex-wrap items-center gap-1.5 bg-gray-100 p-1.5 rounded-xl border border-gray-200">
                    @foreach(['todos' => 'Todos', 'laboratorios' => 'Laboratorios', 'salas_clases' => 'Salas de Clases', 'salas_estudio' => 'Salas de Estudio'] as $tKey => $tLabel)
                        <a href="{{ route('reportes.ocupacion-semanal.index', ['tipo' => $tKey, 'anio' => $anio, 'periodo' => $periodo]) }}"
                           class="inline-flex items-center px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $tipoFilter === $tKey ? 'bg-light-cloud-blue text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white' }}">
                            {{ $tLabel }}
                        </a>
                    @endforeach
                </div>

                <!-- Filtros de Año y Período -->
                <form method="GET" action="{{ route('reportes.ocupacion-semanal.index') }}" class="flex items-center gap-3 w-full lg:w-auto">
                    <input type="hidden" name="tipo" value="{{ $tipoFilter }}">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-gray-700">Año:</label>
                        <select name="anio" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 py-1.5 px-3">
                            @foreach($aniosDisponibles as $a)
                                <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Semanas Almacenadas: Responsive 100% y Tipografía idéntica a Control Docente -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semana / Período</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Filtro</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Espacios</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio Semanal</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pico Máximo</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Día Más Concurrido</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Descargas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse ($snapshots as $snap)
                            @php
                                $prom = $snap->resumen['promedio_general'] ?? 0;
                                $pico = $snap->resumen['pico_maximo'] ?? 0;
                                $diaPico = ucfirst($snap->resumen['dia_pico'] ?? 'N/A');
                                $modPico = $snap->resumen['modulo_pico'] ?? 'N/A';
                                
                                $badgeClass = $prom <= 35 
                                    ? 'bg-green-100 text-green-800' 
                                    : ($prom <= 75 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">
                                        {{ $snap->semana_inicio->format('d/m/Y') }} &mdash; {{ $snap->semana_fin->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Semana {{ $snap->semana_iso }} &bull; Período {{ $snap->periodo }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ str_replace('_', ' ', $snap->tipo_filtro) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-medium text-gray-900">
                                    {{ $snap->total_espacios }}
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                        {{ $prom }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-semibold text-gray-900">
                                    {{ $pico }}%
                                </td>
                                <td class="px-4 py-3.5 text-center text-gray-600 whitespace-nowrap">
                                    <span class="font-medium text-gray-900">{{ $diaPico }}</span>
                                    <span class="text-xs text-gray-500">(Mód. {{ $modPico }})</span>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('reportes.ocupacion-semanal.export', ['id' => $snap->id, 'format' => 'excel']) }}"
                                           title="Descargar en Excel"
                                           class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                            <i class="fas fa-file-excel"></i>
                                            <span class="hidden sm:inline">Excel</span>
                                        </a>
                                        <a href="{{ route('reportes.ocupacion-semanal.export', ['id' => $snap->id, 'format' => 'pdf']) }}"
                                           title="Descargar en PDF" target="_blank"
                                           class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                            <i class="fas fa-file-pdf"></i>
                                            <span class="hidden sm:inline">PDF</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-calendar-times text-4xl mb-3 text-gray-300"></i>
                                    <p class="font-semibold text-base text-gray-700">No hay semanas registradas para los filtros seleccionados.</p>
                                    <p class="text-xs text-gray-500 mt-1">Los snapshots se guardan automáticamente cada domingo o puedes generar uno con el botón superior.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($snapshots->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $snapshots->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Registrar Semana Manual (Diseño Institucional Unificado) -->
    <div id="modal-generar-snapshot"
         onclick="if (event.target === this) this.classList.add('hidden')"
         class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs overflow-y-auto h-full w-full z-[10000] flex items-center justify-center p-4 hidden">
        <div class="flex flex-col w-full max-w-lg overflow-hidden bg-white rounded-2xl shadow-2xl border border-gray-100 transform transition-all">

            <!-- Encabezado institucional tipo banner con círculos decorativos -->
            <div class="relative flex items-center justify-between p-6 bg-light-cloud-blue overflow-hidden">
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>

                <div class="flex items-center min-w-0 gap-4 z-10">
                    <div class="flex items-center justify-center flex-shrink-0">
                        <div class="p-3 bg-white rounded-full bg-opacity-20 shadow-inner">
                            <i class="text-2xl text-white fa-solid fa-camera"></i>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h2 class="text-xl font-bold text-white truncate">Registrar Snapshot de Semana</h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs truncate text-white/80">Histórico de Ocupación Semanal</span>
                            <span class="text-xs text-white/80">•</span>
                            <span class="text-xs font-semibold text-white/80">{{ now()->format('Y') }}</span>
                        </div>
                    </div>
                </div>

                <button type="button"
                        onclick="document.getElementById('modal-generar-snapshot').classList.add('hidden')"
                        class="text-3xl font-bold text-white/80 hover:text-white transition-colors duration-150 cursor-pointer z-10 leading-none"
                        title="Cerrar modal (Esc)"
                        aria-label="Cerrar modal">&times;</button>
            </div>

            <!-- Contenido del modal -->
            <form method="POST" action="{{ route('reportes.ocupacion-semanal.generar') }}">
                @csrf
                <div class="p-6 bg-white space-y-5">
                    <!-- Nota informativa -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-start gap-3">
                        <div class="p-2 bg-red-50 text-light-cloud-blue rounded-lg flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-calendar-check text-base"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900">Registro automático y actualización manual</h4>
                            <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">
                                El sistema registra automáticamente cada semana al finalizar el sábado. Usa esta opción si deseas recalcular o actualizar una semana específica.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block mb-1.5 text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Fecha dentro de la semana a registrar <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="date" name="fecha_semana" required value="{{ now()->format('Y-m-d') }}"
                                       class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 px-3.5 py-2.5 shadow-2xs">
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info text-gray-400"></i>
                                <span>Se calculará automáticamente el rango de lunes a sábado de la semana seleccionada.</span>
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1.5 text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Tipo de Espacio <span class="text-red-500">*</span>
                            </label>
                            <select name="tipo_filtro"
                                    class="w-full text-sm rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 px-3.5 py-2.5 shadow-2xs">
                                <option value="todos_los_tipos">Todos los tipos (Generar los 4 snapshots)</option>
                                <option value="todos">Sólo General (Todos)</option>
                                <option value="laboratorios">Laboratorios</option>
                                <option value="salas_clases">Salas de Clases</option>
                                <option value="salas_estudio">Salas de Estudio</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer con botones de acción -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-2xl">
                    <button type="button"
                            onclick="document.getElementById('modal-generar-snapshot').classList.add('hidden')"
                            class="border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 active:bg-gray-200 rounded-lg font-medium text-sm gap-2 inline-flex items-center justify-center shadow-xs px-4 py-2 cursor-pointer transition-all duration-150">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit"
                            class="bg-light-cloud-blue hover:bg-cloud-blue active:bg-red-800 text-white rounded-lg font-semibold text-sm gap-2 inline-flex items-center justify-center shadow-sm px-5 py-2 cursor-pointer transition-all duration-150">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Calcular y Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('modal-generar-snapshot');
                if (modal && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            }
        });
    </script>
</x-app-layout>

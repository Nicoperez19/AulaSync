<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue text-white shadow-md">
                    <i class="text-2xl fa-solid fa-chart-column"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                        Reportes de Asistencia
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Estadísticas, cumplimiento y control histórico de asistencia de tus estudiantes
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                @if($asignaturaSeleccionada && $asignaturaSeleccionada !== 'todas')
                    <a href="{{ route('docente.reportes-asistencia.export', ['asignatura_id' => $asignaturaSeleccionada, 'fecha_inicio' => $fechaInicio->format('Y-m-d'), 'fecha_fin' => $fechaFin->format('Y-m-d')]) }}" 
                       class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition duration-150">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Exportar Excel / CSV</span>
                    </a>
                @endif

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

    <div class="space-y-6" x-data="{ tabActiva: 'estudiantes' }">
        {{-- Tarjetas de Estadísticas Globales --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Sesiones en el Período</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $totalSesiones }}</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg {{ $promedioAsistencia >= 75 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40' : 'bg-amber-50 text-amber-600 dark:bg-amber-950/40' }} flex items-center justify-center text-xl">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Asistencia Promedio</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $promedioAsistencia }}%</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Total de Asistencias</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $totalPresentes }}</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Total de Inasistencias</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $totalAusentes }}</div>
                </div>
            </div>
        </div>

        {{-- Barra de Filtros --}}
        <div class="p-5 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <form method="GET" action="{{ route('docente.reportes-asistencia.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="asignatura_id" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                        Asignatura / Clase:
                    </label>
                    <select name="asignatura_id" id="asignatura_id" 
                            class="w-full text-xs py-2 px-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 outline-none focus:ring-2 focus:ring-light-cloud-blue">
                        <option value="todas" {{ $asignaturaSeleccionada === 'todas' ? 'selected' : '' }}>Todas mis asignaturas</option>
                        @foreach($clases as $c)
                            <option value="{{ $c['id'] }}" {{ $asignaturaSeleccionada == $c['id'] ? 'selected' : '' }}>
                                {{ $c['codigo'] }} - {{ $c['nombre'] }} @if($c['seccion'] !== 'N/A') (Sec {{ $c['seccion'] }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fecha_inicio" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                        Desde:
                    </label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio->format('Y-m-d') }}"
                           class="w-full text-xs py-2 px-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 outline-none focus:ring-2 focus:ring-light-cloud-blue">
                </div>

                <div>
                    <label for="fecha_fin" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                        Hasta:
                    </label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin->format('Y-m-d') }}"
                           class="w-full text-xs py-2 px-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 outline-none focus:ring-2 focus:ring-light-cloud-blue">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-md bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold shadow transition">
                        <i class="fa-solid fa-filter text-xs"></i>
                        <span>Aplicar Filtros</span>
                    </button>
                    <a href="{{ route('docente.reportes-asistencia.index') }}" 
                       class="py-2 px-3 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-dark-eval-2 text-gray-600 dark:text-gray-300 text-xs font-semibold transition" title="Limpiar">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Contenedor Principal con Pestañas --}}
        <div class="bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-md overflow-hidden">
            {{-- Encabezado de Pestañas --}}
            <div class="flex items-center border-b border-gray-200 dark:border-gray-700 px-4 pt-3 bg-gray-50/50 dark:bg-dark-eval-2 gap-4">
                <button type="button" @click="tabActiva = 'estudiantes'" 
                        :class="tabActiva === 'estudiantes' ? 'border-b-2 border-[#D2091E] text-[#D2091E] font-bold' : 'text-gray-500 hover:text-gray-700 font-medium border-b-2 border-transparent'"
                        class="pb-3 text-xs sm:text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-users"></i>
                    <span>Desglose por Estudiantes</span>
                    @if($reporteEstudiantes->isNotEmpty())
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-gray-100 dark:bg-dark-eval-1 text-gray-600 dark:text-gray-300 font-bold">
                            {{ $reporteEstudiantes->count() }}
                        </span>
                    @endif
                </button>

                <button type="button" @click="tabActiva = 'sesiones'" 
                        :class="tabActiva === 'sesiones' ? 'border-b-2 border-[#D2091E] text-[#D2091E] font-bold' : 'text-gray-500 hover:text-gray-700 font-medium border-b-2 border-transparent'"
                        class="pb-3 text-xs sm:text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Listado de Sesiones Realizadas</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-gray-100 dark:bg-dark-eval-1 text-gray-600 dark:text-gray-300 font-bold">
                        {{ $sesiones->count() }}
                    </span>
                </button>
            </div>

            {{-- Contenido Pestaña 1: Estudiantes --}}
            <div x-show="tabActiva === 'estudiantes'" class="p-0">
                @if($reporteEstudiantes->isEmpty())
                    <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-user-slash text-3xl mb-3 text-gray-300"></i>
                        <p class="text-sm font-medium">No hay registros de estudiantes disponibles para los filtros seleccionados.</p>
                        <p class="text-xs mt-1">Selecciona una asignatura específica en el filtro superior para ver el detalle de cada alumno.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-dark-eval-2 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Apellidos y Nombres</th>
                                    <th class="py-3 px-4">RUN</th>
                                    <th class="py-3 px-4">Correo</th>
                                    <th class="py-3 px-4 text-center">Clases</th>
                                    <th class="py-3 px-4 text-center">Asistencias</th>
                                    <th class="py-3 px-4 text-center">Inasistencias</th>
                                    <th class="py-3 px-4 text-center w-40">% Cumplimiento</th>
                                    <th class="py-3 px-4 text-center">Condición</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                @foreach($reporteEstudiantes as $index => $est)
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-dark-eval-2 transition">
                                        <td class="py-3 px-4 text-center text-xs font-mono text-gray-400">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-100">{{ $est['nombre'] }}</td>
                                        <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $est['run'] }}</td>
                                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400">{{ $est['email'] }}</td>
                                        <td class="py-3 px-4 text-center font-bold text-gray-700 dark:text-gray-200">{{ $est['total_clases'] }}</td>
                                        <td class="py-3 px-4 text-center font-bold text-emerald-600">{{ $est['presentes'] }}</td>
                                        <td class="py-3 px-4 text-center font-bold text-rose-600">{{ $est['ausentes'] }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 w-24 bg-gray-200 dark:bg-dark-eval-3 rounded-full h-2 overflow-hidden">
                                                    <div class="h-full rounded-full {{ $est['porcentaje'] >= 75 ? 'bg-emerald-500' : ($est['porcentaje'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" 
                                                         style="width: {{ min(100, $est['porcentaje']) }}%"></div>
                                                </div>
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-200 min-w-[36px] text-right">{{ $est['porcentaje'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($est['total_clases'] == 0)
                                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-600 dark:bg-dark-eval-2 dark:text-gray-300">
                                                    Sin Clases
                                                </span>
                                            @elseif($est['estado'] === 'normal')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Normal
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300">
                                                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> En Riesgo
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Contenido Pestaña 2: Sesiones --}}
            <div x-show="tabActiva === 'sesiones'" class="p-0" style="display: none;">
                @if($sesiones->isEmpty())
                    <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-calendar-xmark text-3xl mb-3 text-gray-300"></i>
                        <p class="text-sm font-medium">No se registran sesiones en el rango de fechas seleccionado.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-dark-eval-2 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <th class="py-3 px-4">Fecha</th>
                                    <th class="py-3 px-4">Asignatura / Clase</th>
                                    <th class="py-3 px-4">Espacio</th>
                                    <th class="py-3 px-4">Actividad Registrada</th>
                                    <th class="py-3 px-4 text-center">Asistencia</th>
                                    <th class="py-3 px-4 text-center">% Cumplimiento</th>
                                    <th class="py-3 px-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                @foreach($sesiones as $s)
                                    @php
                                        $p = $s->totalPresentes();
                                        $t = $s->totalRegistrados();
                                        $pct = $s->porcentajeAsistencia();
                                        $nombreClase = $s->asignatura?->nombre_asignatura ?? ($s->profesorColaborador?->nombre_asignatura ?? 'Clase');
                                        $codigo = $s->asignatura?->codigo_asignatura ?? 'TEMP';
                                        $seccion = $s->asignatura?->seccion ?? 'N/A';
                                        $asigId = $s->id_asignatura ?? ('colab_' . $s->id_profesor_colaborador);
                                    @endphp
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-dark-eval-2 transition">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-gray-800 dark:text-gray-100">
                                                {{ $s->fecha->translatedFormat('d/m/Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 capitalize">
                                                {{ $s->fecha->translatedFormat('l') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $nombreClase }}</div>
                                            <div class="text-xs text-gray-500">{{ $codigo }} &bull; Sec {{ $seccion }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-xs font-medium text-gray-600 dark:text-gray-300">
                                            {{ $s->espacio?->nombre_espacio ?? 'No asignado' }}
                                        </td>
                                        <td class="py-3 px-4 max-w-xs truncate text-xs text-gray-600 dark:text-gray-300">
                                            {{ $s->actividad ?? 'Clase regular' }}
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold">
                                            <span class="text-emerald-600">{{ $p }}</span>
                                            <span class="text-gray-400 text-xs">/ {{ $t }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $pct >= 75 ? 'bg-emerald-100 text-emerald-800' : ($pct >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                                {{ $pct }}%
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ route('docente.reportes-asistencia.sesion', $s->id) }}" 
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-dark-eval-2 dark:hover:bg-dark-eval-3 dark:text-gray-300 transition">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                    <span>Ver Ficha</span>
                                                </a>
                                                <a href="{{ route('docente.asistencia.show', [$asigId, 'fecha' => $s->fecha->format('Y-m-d')]) }}" 
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 transition">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                    <span>Editar</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

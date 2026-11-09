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
        {{-- KPIs / Estadísticas Globales (Estilo Control Docente) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Sesiones en el Período -->
            <div class="stat-card bg-slate-50 border border-[#C0C0C0] rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-700 text-xs font-semibold uppercase tracking-wider">Sesiones en el Período</p>
                        <p class="text-3xl font-black text-[#191919] mt-1">{{ number_format($totalSesiones) }}</p>
                    </div>
                    <div class="p-3 bg-slate-100 rounded-lg text-[#D12420]">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Asistencia Promedio -->
            <div class="stat-card {{ $promedioAsistencia >= 75 ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }} rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="{{ $promedioAsistencia >= 75 ? 'text-emerald-700' : 'text-amber-700' }} text-xs font-semibold uppercase tracking-wider">Asistencia Promedio</p>
                        <p class="text-3xl font-black {{ $promedioAsistencia >= 75 ? 'text-emerald-950' : 'text-amber-950' }} mt-1">{{ $promedioAsistencia }}%</p>
                    </div>
                    <div class="p-3 {{ $promedioAsistencia >= 75 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }} rounded-lg">
                        <i class="fa-solid fa-chart-pie text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total de Asistencias -->
            <div class="stat-card bg-indigo-50 border border-indigo-200 rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-indigo-700 text-xs font-semibold uppercase tracking-wider">Total de Asistencias</p>
                        <p class="text-3xl font-black text-indigo-950 mt-1">{{ number_format($totalPresentes) }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                        <i class="fa-solid fa-user-check text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total de Inasistencias -->
            <div class="stat-card bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-700 text-xs font-semibold uppercase tracking-wider">Total de Inasistencias</p>
                        <p class="text-3xl font-black text-red-950 mt-1">{{ number_format($totalAusentes) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-lg text-red-600">
                        <i class="fa-solid fa-user-xmark text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección FILTROS (Estilo Control Docente) --}}
        <div class="bg-white dark:bg-dark-eval-1 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-filter text-[#950101]"></i> FILTROS
                </h3>
            </div>

            <form method="GET" action="{{ route('docente.reportes-asistencia.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Asignatura / Clase -->
                <div>
                    <label for="asignatura_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">
                        Asignatura / Clase
                    </label>
                    <select name="asignatura_id" id="asignatura_id" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100">
                        <option value="todas" {{ $asignaturaSeleccionada === 'todas' ? 'selected' : '' }}>Todas mis asignaturas</option>
                        @foreach($clases as $c)
                            <option value="{{ $c['id'] }}" {{ $asignaturaSeleccionada == $c['id'] ? 'selected' : '' }}>
                                {{ $c['codigo'] }} - {{ $c['nombre'] }} @if($c['seccion'] !== 'N/A') (Sec {{ $c['seccion'] }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Desde -->
                <div>
                    <label for="fecha_inicio" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">
                        Desde
                    </label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio->format('Y-m-d') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100">
                </div>

                <!-- Hasta -->
                <div>
                    <label for="fecha_fin" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">
                        Hasta
                    </label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin->format('Y-m-d') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100">
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 gap-2 cursor-pointer">
                        <i class="fas fa-filter text-xs"></i>
                        <span>Aplicar Filtros</span>
                    </button>
                    <a href="{{ route('docente.reportes-asistencia.index') }}" 
                       class="border border-gray-300 bg-white dark:bg-dark-eval-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 rounded-lg font-medium text-sm gap-2 inline-flex items-center justify-center shadow-sm px-4 py-2 cursor-pointer transition-all duration-150" 
                       title="Limpiar filtros">
                        <i class="fas fa-rotate-left"></i>
                        <span>Limpiar</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- Navpills de Navegación (Estilo Control Docente) --}}
        <div class="flex flex-wrap sm:inline-flex items-center gap-2 bg-gray-200/80 p-1.5 rounded-2xl border border-gray-300/70 shadow-sm mb-6">
            <button 
                type="button"
                @click="tabActiva = 'estudiantes'"
                :class="tabActiva === 'estudiantes' ? 'bg-white shadow-md text-gray-900 font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-medium'"
                class="px-6 py-3 rounded-xl text-base sm:text-lg transition-all duration-200 flex items-center gap-3 cursor-pointer"
            >
                <i class="fa-solid fa-users text-xl" :class="tabActiva === 'estudiantes' ? 'text-[#D12420]' : 'text-gray-400'"></i>
                <span>Desglose por Estudiantes</span>
                @if($reporteEstudiantes->isNotEmpty())
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold shadow-sm transition-all"
                          :class="tabActiva === 'estudiantes' ? 'bg-[#D12420] text-white' : 'bg-gray-300 text-gray-700'">
                        {{ $reporteEstudiantes->count() }}
                    </span>
                @endif
            </button>

            <button 
                type="button"
                @click="tabActiva = 'sesiones'"
                :class="tabActiva === 'sesiones' ? 'bg-white shadow-md text-gray-900 font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-medium'"
                class="px-6 py-3 rounded-xl text-base sm:text-lg transition-all duration-200 flex items-center gap-3 cursor-pointer"
            >
                <i class="fa-solid fa-calendar-days text-xl" :class="tabActiva === 'sesiones' ? 'text-[#D12420]' : 'text-gray-400'"></i>
                <span>Listado de Sesiones Realizadas</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold shadow-sm transition-all"
                      :class="tabActiva === 'sesiones' ? 'bg-[#D12420] text-white' : 'bg-gray-300 text-gray-700'">
                    {{ $sesiones->count() }}
                </span>
            </button>
        </div>

        {{-- Contenedor de Tablas con Transición Suave --}}
        <div>
            {{-- Pestaña 1: Desglose por Estudiantes --}}
            <div x-show="tabActiva === 'estudiantes'" 
                 x-transition:enter="transition ease-out duration-200" 
                 x-transition:enter-start="opacity-0 scale-[0.99]" 
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                    @if($reporteEstudiantes->isEmpty())
                        <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-user-slash text-4xl mb-3 text-gray-300"></i>
                            <p class="text-base font-bold text-gray-700 dark:text-gray-200">No hay registros de estudiantes disponibles para los filtros seleccionados.</p>
                            <p class="text-xs text-gray-500 mt-1">Selecciona una asignatura específica en el filtro superior para ver el detalle de cada alumno.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                                    <tr>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider w-12">#</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Estudiante / RUN</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Correo Electrónico</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Clases</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Asistencias</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Inasistencias</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider w-44">% Cumplimiento</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Condición</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-dark-eval-1 dark:divide-gray-700">
                                    @foreach($reporteEstudiantes as $index => $est)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-eval-2 transition">
                                            <td class="px-3 py-4 text-center text-xs font-mono text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                <div class="flex flex-col items-center justify-center">
                                                    <span class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $est['nombre'] }}</span>
                                                    <span class="text-xs text-gray-500 font-mono mt-0.5">RUN: {{ $est['run'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-center whitespace-nowrap text-xs font-mono text-gray-600 dark:text-gray-300">
                                                {{ $est['email'] }}
                                            </td>
                                            <td class="px-3 py-4 text-center whitespace-nowrap font-bold text-gray-800 dark:text-gray-200 text-sm">
                                                {{ $est['total_clases'] }}
                                            </td>
                                            <td class="px-3 py-4 text-center whitespace-nowrap font-bold text-emerald-600 text-base">
                                                {{ $est['presentes'] }}
                                            </td>
                                            <td class="px-3 py-4 text-center whitespace-nowrap font-bold text-rose-600 text-base">
                                                {{ $est['ausentes'] }}
                                            </td>
                                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                                <div class="flex items-center justify-center gap-3">
                                                    <div class="w-24 bg-gray-200 dark:bg-dark-eval-3 rounded-full h-2.5 overflow-hidden">
                                                        <div class="h-full rounded-full {{ $est['porcentaje'] >= 75 ? 'bg-emerald-500' : ($est['porcentaje'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" 
                                                             style="width: {{ min(100, $est['porcentaje']) }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 min-w-[36px] text-right">{{ $est['porcentaje'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                                @if($est['total_clases'] == 0)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-dark-eval-2 dark:text-gray-300">
                                                        Sin Clases
                                                    </span>
                                                @elseif($est['estado'] === 'normal')
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/70 dark:text-emerald-300">
                                                        <i class="fa-solid fa-circle-check text-[11px]"></i> Normal
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 dark:bg-rose-950/70 dark:text-rose-300">
                                                        <i class="fa-solid fa-triangle-exclamation text-[11px]"></i> En Riesgo
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
            </div>

            {{-- Pestaña 2: Sesiones Realizadas --}}
            <div x-show="tabActiva === 'sesiones'" 
                 x-transition:enter="transition ease-out duration-200" 
                 x-transition:enter-start="opacity-0 scale-[0.99]" 
                 x-transition:enter-end="opacity-100 scale-100" 
                 style="display: none;">
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                    @if($sesiones->isEmpty())
                        <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-calendar-xmark text-4xl mb-3 text-gray-300"></i>
                            <p class="text-base font-bold text-gray-700 dark:text-gray-200">No se registran sesiones en el rango de fechas seleccionado.</p>
                            <p class="text-xs text-gray-500 mt-1">Prueba seleccionando otro rango de fechas o una asignatura distinta.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Asignatura / Clase</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Espacio</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Actividad Registrada</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Presentes / Total</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">% Asistencia</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider min-w-[180px]">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-dark-eval-1 dark:divide-gray-700">
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
                                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-eval-2 transition">
                                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $s->fecha->translatedFormat('d/m/Y') }}</div>
                                                    <div class="text-xs text-gray-500 capitalize">{{ $s->fecha->translatedFormat('l') }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $nombreClase }}</div>
                                                    <div class="text-xs text-gray-500">{{ $codigo }} &bull; Sec {{ $seccion }}</div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200 dark:bg-dark-eval-2 dark:text-gray-200">
                                                    {{ $s->espacio?->nombre_espacio ?? 'No asignado' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center max-w-xs truncate text-xs text-gray-700 dark:text-gray-300 font-medium">
                                                {{ $s->actividad ?? 'Clase regular' }}
                                            </td>
                                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                                <span class="font-extrabold text-emerald-600 text-sm">{{ $p }}</span>
                                                <span class="text-gray-400 text-xs">/ {{ $t }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $pct >= 75 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($pct >= 50 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-rose-100 text-rose-800 border border-rose-200') }}">
                                                    {{ $pct }}%
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="{{ route('docente.reportes-asistencia.sesion', $s->id) }}" 
                                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 shadow-xs transition dark:bg-dark-eval-2 dark:hover:bg-dark-eval-3 dark:text-gray-300 dark:border-gray-600">
                                                        <i class="fa-solid fa-eye text-xs"></i>
                                                        <span>Ver Ficha</span>
                                                    </a>
                                                    <a href="{{ route('docente.asistencia.show', [$asigId, 'fecha' => $s->fecha->format('Y-m-d')]) }}" 
                                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 shadow-xs transition dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800">
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
    </div>
</x-app-layout>

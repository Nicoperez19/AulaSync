<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue text-white shadow-md">
                    <i class="text-2xl fa-solid fa-clock-rotate-left"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                        Historial de Asistencias &bull; {{ $tituloClase }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span>{{ $codigoClase }} @if($seccionClase !== 'N/A') &bull; Sección {{ $seccionClase }} @endif</span>
                        <span>&bull;</span>
                        <span>{{ $carreraNombre }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <x-button href="{{ route('docente.asistencia.show', $id) }}" 
                          variant="ucsc" 
                          class="shadow-sm font-semibold gap-2">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Pasar Asistencia de Hoy</span>
                </x-button>

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

    <div class="space-y-6" x-data="{ modalDetalle: false, sesionSeleccionada: null }">
        {{-- Tarjetas de Métricas Acumuladas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Sesiones Registradas</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $totalSesiones }}</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">% Asistencia Promedio</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $porcentajeGlobal }}%</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Asistencias Totales</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $totalPresentesAcumulado }} / {{ $totalRegistrosAcumulado }}</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Estudiantes Inscritos</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $totalInscritos }}</div>
                </div>
            </div>
        </div>

        {{-- Tabla de Sesiones Registradas --}}
        <div class="bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-md overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-gray-50/50 dark:bg-dark-eval-2">
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-light-cloud-blue"></i>
                        <span>Registro Histórico de Sesiones</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Consulta el detalle de presentes y ausentes de cualquier sesión pasada.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('docente.reportes-asistencia.asignatura', $id) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md bg-white dark:bg-dark-eval-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 shadow-xs transition">
                        <i class="fa-solid fa-chart-pie text-xs"></i>
                        <span>Ver Reporte Completo</span>
                    </a>
                </div>
            </div>

            @if($sesiones->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-50 dark:bg-dark-eval-2 text-light-cloud-blue flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                    <h4 class="text-base font-bold text-gray-800 dark:text-gray-100">Aún no hay asistencias registradas</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1 mb-5">
                        Cuando registres la primera asistencia de esta clase, podrás consultarla y revisarla históricamente aquí.
                    </p>
                    <x-button href="{{ route('docente.asistencia.show', $id) }}" variant="ucsc" class="shadow-md">
                        <i class="fa-solid fa-clipboard-check mr-2"></i>
                        Tomar Asistencia Hoy
                    </x-button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-dark-eval-2 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <th class="py-3 px-4">Fecha</th>
                                <th class="py-3 px-4">Espacio / Sala</th>
                                <th class="py-3 px-4">Actividad / Observación</th>
                                <th class="py-3 px-4 text-center">Asistencia</th>
                                <th class="py-3 px-4 text-center">% Cumplimiento</th>
                                <th class="py-3 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            @foreach($sesiones as $sesion)
                                @php
                                    $presentes = $sesion->totalPresentes();
                                    $total = $sesion->totalRegistrados();
                                    $porcentaje = $sesion->porcentajeAsistencia();
                                    $esHoy = $sesion->fecha->isToday();
                                @endphp
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-dark-eval-2 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-gray-800 dark:text-gray-100">
                                            {{ $sesion->fecha->translatedFormat('d \d\e M, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                            {{ $sesion->fecha->translatedFormat('l') }}
                                            @if($esHoy)
                                                <span class="ml-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950 px-1.5 py-0.2 rounded">Hoy</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <i class="fa-solid fa-door-open text-gray-400"></i>
                                            {{ $sesion->espacio ? ($sesion->espacio->id_espacio && $sesion->espacio->nombre_espacio && $sesion->espacio->id_espacio !== $sesion->espacio->nombre_espacio ? ($sesion->espacio->id_espacio . ' (' . $sesion->espacio->nombre_espacio . ')') : ($sesion->espacio->nombre_espacio ?? $sesion->espacio->id_espacio)) : 'No asignado' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate text-xs text-gray-600 dark:text-gray-300">
                                        {{ $sesion->actividad ?? 'Clase regular sin observaciones adicionales' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-bold text-gray-800 dark:text-gray-100">{{ $presentes }}</span>
                                        <span class="text-gray-400 text-xs">/ {{ $total }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($porcentaje >= 75)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                                {{ $porcentaje }}%
                                            </span>
                                        @elseif($porcentaje >= 50)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                                {{ $porcentaje }}%
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300">
                                                {{ $porcentaje }}%
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('docente.asistencia.show', [$id, 'fecha' => $sesion->fecha->format('Y-m-d')]) }}" 
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:hover:bg-blue-900 dark:text-blue-300 transition">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                <span>Consultar / Editar</span>
                                            </a>
                                            <a href="{{ route('docente.reportes-asistencia.sesion', $sesion->id) }}" 
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-dark-eval-2 dark:hover:bg-dark-eval-3 dark:text-gray-300 transition">
                                                <i class="fa-solid fa-file-lines text-xs"></i>
                                                <span>Ficha</span>
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
</x-app-layout>

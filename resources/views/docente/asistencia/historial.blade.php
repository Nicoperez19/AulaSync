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
        {{-- KPIs / Estadísticas Acumuladas (Estilo Control Docente) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-card bg-slate-50 border border-[#C0C0C0] rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-700 text-xs font-semibold uppercase tracking-wider">Sesiones Registradas</p>
                        <p class="text-3xl font-black text-[#191919] mt-1">{{ number_format($totalSesiones) }}</p>
                    </div>
                    <div class="p-3 bg-slate-100 rounded-lg text-[#D12420]">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card {{ $porcentajeGlobal >= 75 ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }} rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="{{ $porcentajeGlobal >= 75 ? 'text-emerald-700' : 'text-amber-700' }} text-xs font-semibold uppercase tracking-wider">% Asistencia Promedio</p>
                        <p class="text-3xl font-black {{ $porcentajeGlobal >= 75 ? 'text-emerald-950' : 'text-amber-950' }} mt-1">{{ $porcentajeGlobal }}%</p>
                    </div>
                    <div class="p-3 {{ $porcentajeGlobal >= 75 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }} rounded-lg">
                        <i class="fa-solid fa-chart-line text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-indigo-50 border border-indigo-200 rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-indigo-700 text-xs font-semibold uppercase tracking-wider">Asistencias Totales</p>
                        <p class="text-3xl font-black text-indigo-950 mt-1">{{ number_format($totalPresentesAcumulado) }} <span class="text-base text-gray-500 font-normal">/ {{ number_format($totalRegistrosAcumulado) }}</span></p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                        <i class="fa-solid fa-user-check text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-700 text-xs font-semibold uppercase tracking-wider">Estudiantes Inscritos</p>
                        <p class="text-3xl font-black text-[#191919] mt-1">{{ number_format($totalInscritos) }}</p>
                    </div>
                    <div class="p-3 bg-slate-100 rounded-lg text-slate-700">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de Sesiones Registradas (Estilo Control Docente) --}}
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-gray-50/50 dark:bg-dark-eval-2">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-[#950101]"></i>
                        <span>Registro Histórico de Sesiones</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Consulta el detalle de presentes y ausentes de cualquier sesión pasada.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('docente.reportes-asistencia.asignatura', $id) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-dark-eval-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 shadow-xs transition">
                        <i class="fa-solid fa-chart-pie text-xs"></i>
                        <span>Ver Reporte Completo</span>
                    </a>
                </div>
            </div>

            @if($sesiones->isEmpty())
                <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-calendar-xmark text-4xl mb-3 text-gray-300"></i>
                    <p class="text-base font-bold text-gray-700 dark:text-gray-200">Aún no hay asistencias registradas</p>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1 mb-5">
                        Cuando registres la primera asistencia de esta clase, podrás consultarla y revisarla históricamente aquí.
                    </p>
                    <x-button href="{{ route('docente.asistencia.show', $id) }}" variant="ucsc" class="shadow-sm">
                        <i class="fa-solid fa-clipboard-check mr-2"></i>
                        Tomar Asistencia Hoy
                    </x-button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Espacio / Sala</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Actividad / Observación</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Asistencia</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">% Cumplimiento</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider min-w-[180px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-dark-eval-1 dark:divide-gray-700">
                            @foreach($sesiones as $sesion)
                                @php
                                    $presentes = $sesion->totalPresentes();
                                    $total = $sesion->totalRegistrados();
                                    $porcentaje = $sesion->porcentajeAsistencia();
                                    $esHoy = $sesion->fecha->isToday();
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-eval-2 transition">
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">
                                                {{ $sesion->fecha->translatedFormat('d \d\e M, Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 capitalize">
                                                {{ $sesion->fecha->translatedFormat('l') }}
                                                @if($esHoy)
                                                    <span class="ml-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded-full border border-emerald-200">Hoy</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200 dark:bg-dark-eval-2 dark:text-gray-200">
                                            {{ $sesion->espacio ? ($sesion->espacio->id_espacio && $sesion->espacio->nombre_espacio && $sesion->espacio->id_espacio !== $sesion->espacio->nombre_espacio ? ($sesion->espacio->id_espacio . ' (' . $sesion->espacio->nombre_espacio . ')') : ($sesion->espacio->nombre_espacio ?? $sesion->espacio->id_espacio)) : 'No asignado' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center max-w-xs truncate text-xs text-gray-700 dark:text-gray-300 font-medium">
                                        {{ $sesion->actividad ?? 'Clase regular sin observaciones adicionales' }}
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <span class="font-extrabold text-emerald-600 text-sm">{{ $presentes }}</span>
                                        <span class="text-gray-400 text-xs">/ {{ $total }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $porcentaje >= 75 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($porcentaje >= 50 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-rose-100 text-rose-800 border border-rose-200') }}">
                                            {{ $porcentaje }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('docente.asistencia.show', [$id, 'fecha' => $sesion->fecha->format('Y-m-d')]) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 shadow-xs transition dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                <span>Consultar / Editar</span>
                                            </a>
                                            <a href="{{ route('docente.reportes-asistencia.sesion', $sesion->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 shadow-xs transition dark:bg-dark-eval-2 dark:hover:bg-dark-eval-3 dark:text-gray-300 dark:border-gray-600">
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

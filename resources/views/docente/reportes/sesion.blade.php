<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue text-white shadow-md">
                    <i class="text-2xl fa-solid fa-file-invoice"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                        Ficha de Asistencia &bull; {{ $sesion->fecha->translatedFormat('d \d\e F, Y') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span>{{ $tituloClase }} ({{ $codigoClase }} &bull; Sec {{ $seccionClase }})</span>
                        <span>&bull;</span>
                        <span>Espacio: <strong>{{ $sesion->espacio?->nombre_espacio ?? 'No asignado' }}</strong></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" onclick="window.print()" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-xl shadow-xs transition">
                    <i class="fa-solid fa-print"></i>
                    <span>Imprimir Ficha</span>
                </button>

                @php
                    $claseId = $sesion->id_asignatura ?? ('colab_' . $sesion->id_profesor_colaborador);
                @endphp
                <x-button href="{{ route('docente.asistencia.show', [$claseId, 'fecha' => $sesion->fecha->format('Y-m-d')]) }}" 
                          variant="ucsc" 
                          class="shadow-sm font-semibold gap-1.5">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Modificar Asistencia</span>
                </x-button>

                <a href="{{ route('docente.reportes-asistencia.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 dark:bg-dark-eval-1 dark:hover:bg-dark-eval-2 border border-slate-200 hover:border-slate-300 dark:border-gray-600 text-slate-700 dark:text-gray-200 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    <svg class="w-4 h-4 shrink-0 text-slate-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Volver a Reportes</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- KPIs / Estadísticas de la Sesión (Estilo Control Docente) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-card bg-slate-50 border border-[#C0C0C0] rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-700 text-xs font-semibold uppercase tracking-wider">Estudiantes Registrados</p>
                        <p class="text-3xl font-black text-[#191919] mt-1">{{ number_format($totalRegistrados) }}</p>
                    </div>
                    <div class="p-3 bg-slate-100 rounded-lg text-[#D12420]">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-emerald-50 border border-emerald-200 rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-700 text-xs font-semibold uppercase tracking-wider">Presentes</p>
                        <p class="text-3xl font-black text-emerald-950 mt-1">{{ number_format($totalPresentes) }}</p>
                    </div>
                    <div class="p-3 bg-emerald-100 rounded-lg text-emerald-600">
                        <i class="fa-solid fa-user-check text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-700 text-xs font-semibold uppercase tracking-wider">Ausentes</p>
                        <p class="text-3xl font-black text-red-950 mt-1">{{ number_format($totalAusentes) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-lg text-red-600">
                        <i class="fa-solid fa-user-xmark text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card {{ $porcentaje >= 75 ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }} rounded-xl p-4 shadow-sm transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="{{ $porcentaje >= 75 ? 'text-emerald-700' : 'text-amber-700' }} text-xs font-semibold uppercase tracking-wider">% Asistencia</p>
                        <p class="text-3xl font-black {{ $porcentaje >= 75 ? 'text-emerald-950' : 'text-amber-950' }} mt-1">{{ $porcentaje }}%</p>
                    </div>
                    <div class="p-3 {{ $porcentaje >= 75 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }} rounded-lg">
                        <i class="fa-solid fa-percent text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Observaciones de la Sesión --}}
        @if(!empty($sesion->actividad))
            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-start gap-3">
                <div class="p-2.5 rounded-lg bg-red-50 text-[#D12420] shrink-0">
                    <i class="fa-solid fa-clipboard-list text-lg"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Actividad / Observaciones de la Clase:</div>
                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-1">{{ $sesion->actividad }}</div>
                </div>
            </div>
        @endif

        {{-- Tabla Detallada de Estudiantes (Estilo Control Docente) --}}
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-dark-eval-2 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-[#950101]"></i>
                    <span>Nómina de Estudiantes de la Sesión</span>
                </h3>
                <span class="text-xs px-3 py-1 rounded-full bg-gray-200 dark:bg-dark-eval-1 text-gray-700 dark:text-gray-300 font-bold">
                    {{ $asistencias->count() }} alumnos
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                        <tr>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider w-16">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Estudiante / RUN</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Correo Electrónico</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Inscripción</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Observación</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-dark-eval-1 dark:divide-gray-700">
                        @foreach($asistencias as $asist)
                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-eval-2 transition {{ $asist->presente ? 'bg-emerald-50/20' : 'bg-rose-50/20' }}">
                                <td class="px-3 py-4 text-center whitespace-nowrap">
                                    @if($asist->presente)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/70 dark:text-emerald-300">
                                            <i class="fa-solid fa-circle-check text-[11px]"></i> Presente
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 dark:bg-rose-950/70 dark:text-rose-300">
                                            <i class="fa-solid fa-circle-xmark text-[11px]"></i> Ausente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="font-bold text-gray-900 dark:text-gray-100 text-sm">
                                            {{ $asist->estudiante?->nombre ?? 'Estudiante no registrado' }}
                                        </span>
                                        <span class="text-xs text-gray-500 font-mono mt-0.5">
                                            RUN: {{ $asist->estudiante?->run ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center whitespace-nowrap text-xs font-mono text-gray-600 dark:text-gray-300">
                                    {{ $asist->estudiante?->email ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    @if($asist->inscrito)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                            Inscrito
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                                            Manual
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center text-xs text-gray-500 italic max-w-xs truncate">
                                    {{ $asist->observacion ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

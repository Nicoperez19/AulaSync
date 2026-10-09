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
        {{-- Tarjetas de Métricas de la Sesión --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Estudiantes Registrados</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $totalRegistrados }}</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Presentes</div>
                    <div class="text-xl font-bold text-emerald-600">{{ $totalPresentes }}</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">Ausentes</div>
                    <div class="text-xl font-bold text-rose-600">{{ $totalAusentes }}</div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg {{ $porcentaje >= 75 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center text-xl">
                    <i class="fa-solid fa-percent"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-medium">% Asistencia</div>
                    <div class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ $porcentaje }}%</div>
                </div>
            </div>
        </div>

        {{-- Observaciones de la Sesión --}}
        @if(!empty($sesion->actividad))
            <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-clipboard-list text-light-cloud-blue text-lg mt-0.5"></i>
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Actividad / Observaciones de la Clase:</div>
                    <div class="text-sm text-gray-800 dark:text-gray-200 mt-0.5">{{ $sesion->actividad }}</div>
                </div>
            </div>
        @endif

        {{-- Tabla Detallada de Estudiantes --}}
        <div class="bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-md overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-dark-eval-2 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-light-cloud-blue"></i>
                    <span>Nómina de Estudiantes de la Sesión</span>
                </h3>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-dark-eval-1 text-gray-700 dark:text-gray-300 font-semibold">
                    {{ $asistencias->count() }} alumnos
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-dark-eval-2 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <th class="py-3 px-4 w-16 text-center">Estado</th>
                            <th class="py-3 px-4">RUN</th>
                            <th class="py-3 px-4">Apellidos y Nombres</th>
                            <th class="py-3 px-4">Correo</th>
                            <th class="py-3 px-4 text-center">Inscripción</th>
                            <th class="py-3 px-4">Observación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @foreach($asistencias as $asist)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-dark-eval-2 transition {{ $asist->presente ? 'bg-emerald-50/20' : 'bg-rose-50/20' }}">
                                <td class="py-3 px-4 text-center">
                                    @if($asist->presente)
                                        <span class="w-6 h-6 inline-flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold" title="Presente">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    @else
                                        <span class="w-6 h-6 inline-flex items-center justify-center rounded-full bg-rose-100 text-rose-700 text-xs font-bold" title="Ausente">
                                            <i class="fa-solid fa-xmark"></i>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-gray-300">
                                    {{ $asist->estudiante?->run ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $asist->estudiante?->nombre ?? 'Estudiante no registrado' }}
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $asist->estudiante?->email ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($asist->inscrito)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800">
                                            Inscrito
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 text-purple-800">
                                            Manual
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-500 italic">
                                    {{ $asist->observacion ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

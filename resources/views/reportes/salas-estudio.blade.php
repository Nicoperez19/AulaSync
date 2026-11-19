<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-book"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Reporte de Salas de Estudio</h2>
                    <p class="text-sm text-gray-500">Accesos registrados agrupados por sesión</p>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('reservas.export-excel', array_filter(['tipo_espacio' => 'Sala de Estudio', 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin])) }}"
                   class="btn btn-excel px-4 py-2 text-sm font-semibold">
                    <i class="mr-2 fas fa-file-excel"></i> Exportar Excel
                </a>
                <a href="{{ route('reportes.salas-estudio.export', ['format' => 'pdf']) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}{{ $salaId ? '&sala_id=' . $salaId : '' }}"
                   class="btn btn-pdf px-4 py-2 text-sm font-semibold">
                    <i class="mr-2 fas fa-file-pdf"></i> Exportar PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="px-4 min-h-[80vh]">
        <!-- Nav Pills -->
        <div class="mb-4">
            <nav class="flex gap-2" aria-label="Tabs">
                <button onclick="cambiarVista('registros')" id="pill-registros"
                        class="pill-button active px-4 py-2 text-sm font-medium text-white bg-light-cloud-blue rounded-lg">
                    <i class="mr-2 fas fa-book"></i>
                    Salas de Estudio
                </button>
                <button onclick="cambiarVista('vetados')" id="pill-vetados"
                        class="pill-button px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                    <i class="mr-2 fas fa-user-slash"></i>
                    Usuarios Vetados
                </button>
            </nav>
        </div>

        <!-- Vista Registros -->
        <div id="vista-registros" class="vista-content">
        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-4">
            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <i class="text-blue-600 fas fa-users dark:text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total accesos</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalAccesos }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
                        <i class="text-green-600 fas fa-layer-group dark:text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total grupos</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalGrupos }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                        <i class="text-yellow-600 fas fa-door-open dark:text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Salas utilizadas</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $salasUsadas }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-900">
                        <i class="text-purple-600 fas fa-user-friends dark:text-purple-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Promedio personas/grupo</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $promedioPersonasPorGrupo }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-4 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-700 dark:text-gray-300">
                <i class="fas fa-filter"></i> Filtros de búsqueda
            </h3>
            <form method="GET" action="{{ route('reportes.salas-estudio') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="fecha_inicio" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha inicio
                    </label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label for="fecha_fin" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha fin
                    </label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label for="sala_id" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sala
                    </label>
                    <select id="sala_id" name="sala_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Todas las salas</option>
                        @foreach($salasEstudio as $sala)
                            <option value="{{ $sala->id_espacio }}" {{ $salaId == $sala->id_espacio ? 'selected' : '' }}>
                                {{ $sala->nombre_espacio }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 text-white transition-colors bg-[#191919] rounded-lg hover:bg-black font-semibold text-sm shadow-xs">
                        <i class="mr-2 fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('reportes.salas-estudio') }}"
                       class="px-4 py-2 text-gray-700 transition-colors bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 font-semibold text-sm shadow-xs">
                        <i class="mr-2 fas fa-undo"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
        <!-- Tabla única de registros -->
        <div class="overflow-hidden bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-800 dark:text-gray-200">
                    <i class="fas fa-list text-[#950101]"></i>
                    Registros de Accesos a Salas de Estudio
                </h3>
                <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full w-fit">
                    Total: {{ count($reservas ?? []) }} accesos
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs bg-white rounded-lg dark:bg-gray-800 table-auto">
                    <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                        <tr>
                            <th class="px-3 py-3 text-xs font-semibold text-center uppercase tracking-wider text-white">#</th>
                            <th class="px-3 py-3 text-xs font-semibold text-center uppercase tracking-wider text-white">Sala</th>
                            <th class="px-3 py-3 text-xs font-semibold text-center uppercase tracking-wider text-white">RUN</th>
                            <th class="px-3 py-3 text-xs font-semibold text-left uppercase tracking-wider text-white">Estudiante / Usuario</th>
                            <th class="px-3 py-3 text-xs font-semibold text-center uppercase tracking-wider text-white">Fecha</th>
                            <th class="px-3 py-3 text-xs font-semibold text-center uppercase tracking-wider text-white">Entrada</th>
                            <th class="px-3 py-3 text-xs font-semibold text-center uppercase tracking-wider text-white">Salida</th>
                            <th class="px-3 py-3 text-xs font-semibold text-center uppercase tracking-wider text-white">Duración</th>
                            <th class="px-3 py-3 text-xs font-semibold text-left uppercase tracking-wider text-white">Observaciones</th>
                            <th class="px-3 py-3 text-xs font-semibold text-center uppercase tracking-wider text-white">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse(($reservas ?? []) as $index => $reserva)
                            @php
                                $nombreEspacio = $reserva->espacio->nombre_espacio ?? 'Sala ' . $reserva->id_espacio;
                                $run = $reserva->run_solicitante ?: ($reserva->run_profesor ?? 'N/A');
                                $nombreUsuario = $reserva->solicitante->nombre ?? $reserva->profesor->name ?? 'N/A';
                                $fechaObj = $reserva->fecha_reserva instanceof \Carbon\Carbon
                                    ? $reserva->fecha_reserva
                                    : \Carbon\Carbon::parse($reserva->fecha_reserva);
                                $horaEntrada = \Carbon\Carbon::parse($reserva->hora)->format('H:i');
                                $horaSalida = $reserva->hora_salida ? \Carbon\Carbon::parse($reserva->hora_salida)->format('H:i') : null;
                                
                                $duracion = null;
                                if ($reserva->hora_salida) {
                                    $diffMins = \Carbon\Carbon::parse($reserva->hora)->diffInMinutes(\Carbon\Carbon::parse($reserva->hora_salida));
                                    $duracion = round($diffMins / 60, 1) . ' hrs';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-3 py-2.5 text-center font-medium text-gray-500 dark:text-gray-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-50 text-red-800 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800">
                                        <i class="mr-1 fas fa-door-open"></i> {{ $nombreEspacio }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center font-mono whitespace-nowrap text-gray-800 dark:text-gray-200">
                                    {{ $run }}
                                </td>
                                <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-white">
                                    {{ $nombreUsuario }}
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    {{ $fechaObj->format('d/m/Y') }}
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap font-medium text-gray-800 dark:text-gray-200">
                                    {{ $horaEntrada }}
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    @if($horaSalida)
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $horaSalida }}</span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">--:--</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    @if($duracion)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            <i class="mr-1 fas fa-clock text-gray-400"></i> {{ $duracion }}
                                        </span>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                                            <i class="mr-1 fas fa-spinner fa-spin"></i> En curso
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-left text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $reserva->observaciones }}">
                                    {{ $reserva->observaciones ?: '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <button onclick="vetarIndividual('{{ $run }}', '{{ addslashes($nombreUsuario) }}', '{{ $reserva->id_reserva }}')"
                                            class="px-2.5 py-1 text-xs text-white transition-colors bg-[#D12420] rounded hover:bg-[#950101] shadow-xs inline-flex items-center gap-1">
                                        <i class="fas fa-ban"></i> Vetar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="mb-3 text-4xl text-gray-400 fas fa-inbox block"></i>
                                    <p class="text-base font-medium">No hay registros de salas de estudio para el período seleccionado</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        <!-- Vista Usuarios Vetados -->
        <div id="vista-vetados" class="hidden vista-content">
            <div class="p-4 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">
                        <i class="mr-2 fas fa-user-slash"></i>
                        Lista de Usuarios Vetados
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="exportarVetados()" id="btn-exportar-vetados"
                                class="btn btn-excel px-4 py-2 text-sm font-semibold">
                            <i class="mr-2 fas fa-file-excel"></i> Exportar Excel
                        </button>
                        <button onclick="filtrarVetados('activo')" id="filter-activo"
                                class="filter-button active px-4 py-2 text-sm text-white bg-[#D12420] rounded-lg hover:bg-[#950101]">
                            Activos
                        </button>
                        <button onclick="filtrarVetados('liberado')" id="filter-liberado"
                                class="filter-button px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                            Liberados
                        </button>
                        <button onclick="filtrarVetados('')" id="filter-todos"
                                class="filter-button px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                            Todos
                        </button>
                    </div>
                </div>

                <div id="listaVetados" class="space-y-3">
                    <!-- Se llenará con JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para vetar individual -->
    <div id="modalVetarIndividual" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    <i class="mr-2 text-red-600 fas fa-ban"></i> Vetar Usuario
                </h3>
                <button onclick="cerrarModalVetar()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">RUN: <span id="vetoRun" class="font-semibold"></span></p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Nombre: <span id="vetoNombre" class="font-semibold"></span></p>
            </div>

            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Motivo del veto <span class="text-red-500">*</span>
                </label>
                <textarea id="vetoObservacion" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          placeholder="Describe el motivo del veto..."></textarea>
            </div>

            <div class="flex gap-2">
                <button onclick="confirmarVetoIndividual()" 
                        class="flex-1 px-4 py-2 text-white transition-colors bg-[#D12420] rounded-lg hover:bg-[#950101]">
                    <i class="mr-2 fas fa-check"></i> Confirmar Veto
                </button>
                <button onclick="cerrarModalVetar()" 
                        class="flex-1 px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                    <i class="mr-2 fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para vetar grupo -->
    <div id="modalVetarGrupo" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    <i class="mr-2 text-red-600 fas fa-ban"></i> Vetar Grupo Completo
                </h3>
                <button onclick="cerrarModalVetarGrupo()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-3 mb-4 border-l-4 border-amber-500 bg-amber-50 dark:bg-amber-900/20">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    <i class="mr-1 fas fa-exclamation-triangle"></i> Se vetará a todos los miembros del <span id="grupoInfo" class="font-bold"></span>
                </p>
            </div>

            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Motivo del veto grupal <span class="text-red-500">*</span>
                </label>
                <textarea id="vetoGrupoObservacion" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          placeholder="Describe el motivo del veto grupal..."></textarea>
            </div>

            <div class="flex gap-2">
                <button onclick="confirmarVetoGrupo()" 
                        class="flex-1 px-4 py-2 text-white transition-colors bg-[#D12420] rounded-lg hover:bg-[#950101]">
                    <i class="mr-2 fas fa-check"></i> Confirmar Veto Grupal
                </button>
                <button onclick="cerrarModalVetarGrupo()" 
                        class="flex-1 px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                    <i class="mr-2 fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para editar veto -->
    <div id="modalEditarVeto" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    <i class="mr-2 text-blue-600 fas fa-edit"></i> Editar Observación
                </h3>
                <button onclick="cerrarModalEditar()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nueva observación <span class="text-red-500">*</span>
                </label>
                <textarea id="editarObservacion" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>

            <div class="flex gap-2">
                <button onclick="confirmarEdicion()" 
                        class="flex-1 px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                    <i class="mr-2 fas fa-save"></i> Guardar
                </button>
                <button onclick="cerrarModalEditar()" 
                        class="flex-1 px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                    <i class="mr-2 fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

@vite(['resources/js/views/reportes/salas-estudio.js'])
@endpush
</x-app-layout>

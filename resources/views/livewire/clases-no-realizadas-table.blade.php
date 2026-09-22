<div wire:poll.60s>

    @if($periodoNoIniciado)
        <!-- Mensaje cuando el periodo no ha iniciado -->
        <div class="flex items-center justify-center min-h-96 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg border-2 border-dashed border-yellow-300 p-8">
            <div class="text-center max-w-md">
                <div class="mb-6 flex justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-20 h-20 text-yellow-500">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $nombrePeriodo }}</h2>
                <p class="text-gray-600 mb-4">El periodo académico aún no ha comenzado</p>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <p class="text-yellow-800 text-sm">
                        No existen registros en el Control de Clases porque el periodo académico oficial no ha iniciado. 
                        Los registros se generarán automáticamente una vez que comiencen las actividades académicas.
                    </p>
                </div>
            </div>
        </div>
    @else

    <!-- Título Clases Realizadas -->
    <div class="mb-4">
        <span class="inline-flex items-center gap-3 px-5 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-base sm:text-lg font-bold text-gray-900">
            <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
            <span>Clases Realizadas</span>
        </span>
    </div>

    <!-- Navpills / Estadísticas (por fuera) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total realizadas -->
        <div class="stat-card bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-700 text-xs font-semibold uppercase tracking-wider">Total realizadas</p>
                    <p class="text-3xl font-black text-blue-950 mt-1">{{ $estadisticas['total'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
            </div>
        </div>

        <!-- No registradas -->
        <div class="stat-card bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-700 text-xs font-semibold uppercase tracking-wider">No registradas</p>
                    <p class="text-3xl font-black text-red-950 mt-1">{{ $estadisticas['no_realizadas'] }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg text-red-600">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Registradas -->
        <div class="stat-card bg-indigo-50 border border-indigo-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-700 text-xs font-semibold uppercase tracking-wider">Registradas</p>
                    <p class="text-3xl font-black text-indigo-950 mt-1">{{ $estadisticas['realizadas'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Justificadas -->
        <div class="stat-card bg-amber-50 border border-amber-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-700 text-xs font-semibold uppercase tracking-wider">Justificadas</p>
                    <p class="text-3xl font-black text-amber-950 mt-1">{{ $estadisticas['justificados'] }}</p>
                </div>
                <div class="p-3 bg-amber-100 rounded-lg text-amber-600">
                    <i class="fas fa-shield-alt text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección FILTROS -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-filter text-blue-600"></i> FILTROS
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Semestre -->
            <div>
                <label for="periodo_filtro" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Semestre</label>
                <select wire:model.live="periodo" 
                        id="periodo_filtro"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    @foreach($periodosDisponibles as $p)
                        <option value="{{ $p->codigo }}">
                            {{ $p->display_label }}
                        </option>
                    @endforeach
                    <option value="">Todos los semestres</option>
                </select>
            </div>

            <!-- Buscar -->
            <div>
                <label for="search" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Buscar</label>
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.800ms="search" 
                           id="search"
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Profesor, asignatura, RUN o espacio...">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            <!-- Estado -->
            <div>
                <label for="estado" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Estado</label>
                <select wire:model.live.debounce.800ms="estado" 
                        id="estado"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Todos</option>
                    <option value="no_realizada">No registrada</option>
                    <option value="realizada">Registrada (Clase realizada)</option>
                    <option value="pendiente">Pendiente de recuperación</option>
                    <option value="justificado">Justificado</option>
                </select>
            </div>

            <!-- Desde -->
            <div>
                <label for="fecha_inicio" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Desde</label>
                <input type="date" 
                       wire:model.live.debounce.800ms="fecha_inicio" 
                       id="fecha_inicio"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Hasta -->
            <div>
                <label for="fecha_fin" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Hasta</label>
                <input type="date" 
                       wire:model.live.debounce.800ms="fecha_fin" 
                       id="fecha_fin"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
            <button type="button" wire:click="limpiarFiltros" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2">
                <i class="fas fa-undo text-xs"></i> Limpiar
            </button>
            <button type="button" wire:click="aplicarFiltros" 
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                <i class="fas fa-check text-xs"></i> Aplicar filtros
            </button>
        </div>
    </div>

    <!-- Botón Exportar -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('clases-no-realizadas.export-all-excel', array_filter(['periodo' => $periodo, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'estado' => $estado, 'search' => $search])) }}"
           class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 gap-2">
            <i class="fas fa-file-excel"></i>
            <span>Exportar todas las clases</span>
        </a>
    </div>

            
            <!-- Mensaje Flash -->
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            {{-- Barra de Acciones Masivas --}}
            @if(count($selectedClases) > 0 || $selectAllFiltered)
                <div class="mb-4 bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 text-white px-5 py-3.5 rounded-xl shadow-lg flex flex-wrap items-center justify-between gap-3 border border-blue-700/60 transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-blue-600/40 rounded-lg text-amber-400 border border-amber-400/30">
                            <i class="fas fa-check-double text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-base text-white flex items-center gap-2">
                                @if($selectAllFiltered)
                                    <span>{{ $totalNoRealizadasFiltradas }} clases seleccionadas</span>
                                    <span class="text-xs px-2.5 py-0.5 bg-amber-400 text-gray-950 rounded-full font-extrabold uppercase tracking-wide">Filtro completo</span>
                                @else
                                    <span>{{ count($selectedClases) }} {{ count($selectedClases) === 1 ? 'clase seleccionada' : 'clases seleccionadas' }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-blue-200">
                                Aplica una justificación en lote a todas las clases seleccionadas con un único motivo y detalle.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" wire:click="limpiarSeleccion" 
                                class="px-3.5 py-2 text-xs font-semibold text-gray-300 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" wire:click="abrirModalJustificarMasivo" 
                                class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-950 text-xs sm:text-sm font-black rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer transform hover:-translate-y-0.5">
                            <i class="fas fa-shield-alt text-base"></i>
                            <span>Justificar Seleccionadas</span>
                        </button>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                {{-- Banner de Selección Global (Estilo Gmail) --}}
                @if($selectAllPage && $totalNoRealizadasFiltradas > count($currentPageNoRealizadasKeys))
                    <div class="bg-blue-50 border-b border-blue-200 text-blue-900 px-4 py-2.5 text-xs sm:text-sm flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-600 text-base"></i>
                            @if($selectAllFiltered)
                                <span>Están seleccionadas <strong>todas las {{ $totalNoRealizadasFiltradas }} clases no registradas</strong> de esta búsqueda.</span>
                            @else
                                <span>Has seleccionado las <strong>{{ count($currentPageNoRealizadasKeys) }}</strong> clases no registradas de esta página.</span>
                                <button type="button" wire:click="seleccionarTodoElFiltro" class="font-bold underline text-blue-700 hover:text-blue-950 cursor-pointer ml-1">
                                    Seleccionar las {{ $totalNoRealizadasFiltradas }} clases encontradas en esta búsqueda
                                </button>
                            @endif
                        </div>
                        @if($selectAllFiltered)
                            <button type="button" wire:click="limpiarSeleccion" class="text-xs font-semibold text-blue-700 hover:text-blue-900 underline cursor-pointer">
                                Deshacer selección global
                            </button>
                        @endif
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-center w-10">
                                    <input type="checkbox" 
                                           wire:model.live="selectAllPage" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 cursor-pointer"
                                           title="Seleccionar todas las clases no registradas de esta página"
                                           @if(empty($currentPageNoRealizadasKeys)) disabled @endif>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer w-24" 
                                    wire:click="sortBy('fecha_clase')">
                                    Fecha
                                    @if($sortField === 'fecha_clase')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Profesor</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Asignatura</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espacio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mód. Inicio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mód. Fin</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                    wire:click="sortBy('estado')">
                                    Estado
                                    @if($sortField === 'estado')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Detección</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32 sticky right-0 bg-gray-50">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($clasesNoRealizadas as $clase)
                                @php
                                    // Parsear módulos inicio y fin
                                    $modulos = explode(',', $clase['modulo']);
                                    $moduloInicio = preg_replace('/^[A-Z]{2}\./', '', $modulos[0]);
                                    $moduloFin = count($modulos) > 1 ? preg_replace('/^[A-Z]{2}\./', '', end($modulos)) : $moduloInicio;
                                @endphp
                                <tr class="table-row hover:bg-gray-50 {{ $clase['estado'] === 'Pendiente de Recuperación' ? 'bg-yellow-50' : '' }}">
                                    <td class="px-3 py-4 text-center w-10">
                                        @if($clase['estado'] === 'No Registrada')
                                            <input type="checkbox" 
                                                   wire:model.live="selectedClases" 
                                                   value="{{ $clase['unique_key'] }}" 
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 cursor-pointer">
                                        @else
                                            <input type="checkbox" disabled class="rounded border-gray-200 text-gray-300 cursor-not-allowed opacity-30" title="Solo se pueden justificar clases no registradas">
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-900 w-24">
                                        <div class="flex items-center gap-1">
                                            {{ \Carbon\Carbon::parse($clase['fecha'])->format('d/m/Y') }}
                                            @if($clase['estado'] === 'Pendiente de Recuperación')
                                                <i class="fas fa-clock text-yellow-600 text-xs cursor-help" 
                                                   title="Clase reagendada - Pendiente de recuperación"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 w-32">
                                        <div class="break-words">{{ $clase['profesor'] ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-900 w-40">
                                        <div class="break-words">
                                            <div class="font-medium">{{ $clase['asignatura'] ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $clase['codigo_asignatura'] ?? '' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $clase['espacio'] }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $moduloInicio }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $moduloFin }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            @if($clase['estado'] === 'No Registrada')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    No Registrada
                                                </span>
                                            @elseif($clase['estado'] === 'Realizada' || $clase['estado'] === 'Registrada')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 flex items-center gap-1">
                                                    <i class="fas fa-check-circle text-[10px]"></i>
                                                    Registrada
                                                </span>
                                            @elseif($clase['estado'] === 'Pendiente de Recuperación')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 flex items-center gap-1">
                                                    <i class="fas fa-clock text-[10px]"></i>
                                                    Pendiente Recuperación
                                                </span>
                                            @elseif($clase['estado'] === 'Justificada' || $clase['estado'] === 'Feriado/Justificado')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $clase['estado'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 w-28">
                                        <div class="break-words">
                                            @if(isset($clase['hora_deteccion']))
                                                <div>{{ \Carbon\Carbon::parse($clase['hora_deteccion'])->format('d/m/Y') }}</div>
                                                <div class="text-xs">{{ \Carbon\Carbon::parse($clase['hora_deteccion'])->format('H:i') }}</div>
                                            @else
                                                <span class="text-xs text-gray-400">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-medium w-32 sticky right-0 bg-white">
                                        @if(!empty($clase['id']) || !in_array($clase['estado'], ['Realizada', 'Feriado/Justificado']))
                                            <div class="flex space-x-1">
                                                @if($clase['estado'] === 'No Registrada')
                                                    <div class="custom-tooltip">
                                                        <button wire:click="prepararAccion('reagendar', {{ json_encode($clase) }})" 
                                                                class="action-button reagendar p-1"
                                                                title="Reagendar Clase">
                                                            <i class="fas fa-calendar-plus icon-animate text-xs"></i>
                                                        </button>
                                                        <span class="tooltip-text">Reagendar</span>
                                                    </div>
                                                @endif
                                                @if($clase['estado'] === 'Pendiente de Recuperación')
                                                    <div class="custom-tooltip">
                                                        <button wire:click="prepararAccion('recuperada', {{ json_encode($clase) }})" 
                                                                class="p-1 bg-green-100 hover:bg-green-200 text-green-700 rounded transition-colors duration-200"
                                                                title="Marcar como recuperada">
                                                            <i class="fas fa-check-circle icon-animate text-xs"></i>
                                                        </button>
                                                        <span class="tooltip-text">Recuperada</span>
                                                    </div>
                                                @endif
                                                <div class="custom-tooltip">
                                                    <button wire:click="prepararAccion('editar', {{ json_encode($clase) }})" 
                                                            class="action-button editar p-1"
                                                            title="Editar">
                                                        <i class="fas fa-edit icon-animate text-xs"></i>
                                                    </button>
                                                    <span class="tooltip-text">Editar</span>
                                                </div>
                                                <div class="custom-tooltip">
                                                    <button wire:click="prepararAccion('eliminar', {{ json_encode($clase) }})" 
                                                            class="action-button eliminar p-1"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash icon-animate text-xs"></i>
                                                    </button>
                                                    <span class="tooltip-text">Eliminar</span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium">No se encontraron registros</p>
                                            <p class="text-sm">No hay registros con los filtros aplicados en el Control de Clases.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($clasesNoRealizadas->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $clasesNoRealizadas->links() }}
                    </div>
                @endif
            </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listener para abrir modal automáticamente desde URL
    Livewire.on('auto-open-reagendar', (data) => {
        const id = data[0]?.id || data.id;
        if (id) {
            // Pequeño delay para asegurar que la página esté lista
            setTimeout(() => {
                window.Livewire.find('{{ $this->getId() }}').call('showReagendarModal', id);
            }, 500);
        }
    });

    // Listener para abrir modal de justificación masiva
    Livewire.on('show-bulk-justify-modal', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const cantidad = payload?.cantidad || 0;

        Swal.fire({
            title: '<strong><i class="fas fa-shield-alt text-amber-500"></i> Justificación Masiva</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-amber-50 border border-amber-200 p-3.5 rounded-lg flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg text-amber-700">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-amber-950">Vas a justificar ${cantidad} ${cantidad === 1 ? 'clase no registrada' : 'clases no registradas'}</p>
                            <p class="text-xs text-amber-800">Todas pasarán al estado <strong>Justificada</strong> y se reflejarán inmediatamente en las estadísticas y reportes.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Motivo Principal</label>
                        <select id="swal-bulk-motivo-select" class="w-full p-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <option value="Supervisión de Prácticas / Terreno" selected>Supervisión de Prácticas / Terreno (Campos clínicos, visitas, prácticas)</option>
                            <option value="Licencia Médica / Permiso Administrativo">Licencia Médica / Permiso Administrativo</option>
                            <option value="Comisión de Servicio / Actividad Institucional">Comisión de Servicio / Actividad Institucional</option>
                            <option value="Suspensión de Actividades Académicas">Suspensión de Actividades Académicas</option>
                            <option value="Problema Técnico / Ajuste de Registro">Problema Técnico / Ajuste de Registro</option>
                            <option value="__OTRO__">Otro motivo (especificar)...</option>
                        </select>
                        <input type="text" id="swal-bulk-motivo-custom" class="w-full p-2 text-sm border border-gray-300 rounded-lg mt-2 hidden" placeholder="Escribe el motivo personalizado...">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Observaciones / Detalle</label>
                        <textarea id="swal-bulk-observaciones" rows="3" class="w-full p-2.5 text-sm border border-gray-300 rounded-lg resize-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" placeholder="Ej: Docente asignado a supervisión de prácticas clínicas según programación del semestre..."></textarea>
                        <p class="text-[11px] text-gray-500 mt-1">Este texto quedará registrado en las observaciones de cada una de las clases seleccionadas.</p>
                    </div>

                    <div class="pt-2 border-t border-gray-200">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="swal-bulk-sobrescribir" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-xs text-gray-700">Reemplazar observaciones previas (si no se marca, se añadirá al final)</span>
                        </label>
                    </div>
                </div>
            `,
            width: 580,
            showCancelButton: true,
            confirmButtonText: `<i class="fas fa-check-circle mr-1"></i> Justificar ${cantidad} ${cantidad === 1 ? 'Clase' : 'Clases'}`,
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Cancelar',
            confirmButtonColor: '#F59E0B',
            cancelButtonColor: '#6B7280',
            didOpen: () => {
                const select = document.getElementById('swal-bulk-motivo-select');
                const customInput = document.getElementById('swal-bulk-motivo-custom');
                select.addEventListener('change', () => {
                    if (select.value === '__OTRO__') {
                        customInput.classList.remove('hidden');
                        customInput.focus();
                    } else {
                        customInput.classList.add('hidden');
                    }
                });
            },
            preConfirm: () => {
                const select = document.getElementById('swal-bulk-motivo-select');
                const customInput = document.getElementById('swal-bulk-motivo-custom');
                let motivo = select.value === '__OTRO__' ? customInput.value.trim() : select.value;
                const observaciones = document.getElementById('swal-bulk-observaciones').value.trim();
                const sobrescribir = document.getElementById('swal-bulk-sobrescribir').checked;

                if (!motivo) {
                    Swal.showValidationMessage('Por favor especifica un motivo');
                    return false;
                }

                if (observaciones.length > 1000) {
                    Swal.showValidationMessage('Las observaciones no pueden exceder 1000 caracteres');
                    return false;
                }

                return { motivo, observaciones, sobrescribir };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { motivo, observaciones, sobrescribir } = result.value;

                Swal.fire({
                    title: 'Aplicando justificación masiva...',
                    text: `Actualizando ${cantidad} clases, por favor espera.`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                window.Livewire.find('{{ $this->getId() }}').call('ejecutarJustificacionMasiva', motivo, observaciones, sobrescribir);
            }
        });
    });

    Livewire.on('show-edit-modal', (data) => {
        const clase = data[0];
        
        Swal.fire({
            title: '<strong>Editar Clase No Registrada</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}</p>
                        <p><strong>Fecha:</strong> ${clase.fecha}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select id="swal-estado" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="no_realizada" ${clase.estado === 'no_realizada' ? 'selected' : ''}>No registrada</option>
                            <option value="realizada" ${clase.estado === 'realizada' || clase.estado === 'registrada' ? 'selected' : ''}>Registrada (Docente realizó la clase)</option>
                            <option value="pendiente" ${clase.estado === 'pendiente' ? 'selected' : ''}>Pendiente de recuperación</option>
                            <option value="justificado" ${clase.estado === 'justificado' ? 'selected' : ''}>Justificado (sin recuperación)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <strong>Registrada:</strong> Docente dictó la clase (asistencia regularizada)<br>
                            <strong>Pendiente:</strong> Clase reagendada esperando recuperación<br>
                            <strong>Justificado:</strong> Clase justificada sin necesidad de recuperación
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea id="swal-observaciones" rows="4" class="w-full p-2 border border-gray-300 rounded-md resize-none" placeholder="Ingrese observaciones...">${clase.observaciones || ''}</textarea>
                    </div>
                </div>
            `,
            width: 600,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Guardar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#3B82F6',
            cancelButtonColor: '#6B7280',
            didOpen: () => {
                document.getElementById('swal-estado').focus();
            },
            preConfirm: () => {
                const estado = document.getElementById('swal-estado').value;
                const observaciones = document.getElementById('swal-observaciones').value;
                
                if (!estado) {
                    Swal.showValidationMessage('Por favor selecciona un estado');
                    return false;
                }
                
                if (observaciones.length > 1000) {
                    Swal.showValidationMessage('Las observaciones no pueden exceder 1000 caracteres');
                    return false;
                }
                
                return { estado, observaciones };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { estado, observaciones } = result.value;
                window.Livewire.find('{{ $this->getId() }}').call('updateClase', clase.id, estado, observaciones);
            }
        });
    });

    Livewire.on('show-reagendar-modal', (data) => {
        const clase = data[0];
        
        console.log('Datos completos recibidos:', clase);
        
        // Variable global para almacenar módulos
        let modulosDisponibles = [];
        const fechaOriginal = clase.fecha_original; // formato: "dd/mm/yyyy"

        Swal.fire({
            title: '<strong><i class="fas fa-calendar-plus"></i> Reagendar Clase</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Importante:</strong> Al reagendar esta clase, se marcará como <strong>justificada</strong> 
                                    pero quedará <strong>pendiente de recuperación</strong>. La clase permanecerá en el listado 
                                    hasta que se confirme su realización.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Clase Original</h4>
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}</p>
                        <p><strong>Fecha:</strong> ${clase.fecha_original}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio_original}</p>
                        <p><strong>Módulo:</strong> ${clase.modulo_original}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Fecha <span class="text-red-500">*</span></label>
                            <input type="date" id="swal-nueva-fecha" class="w-full p-2 border border-gray-300 rounded-md" min="${new Date().toISOString().split('T')[0]}">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad de Módulos <span class="text-red-500">*</span></label>
                            <input type="number" id="swal-cantidad-modulos" class="w-full p-2 border border-gray-300 rounded-md" min="1" max="8" value="${clase.totalModulosProgramados}">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Inicio <span class="text-red-500">*</span></label>
                            <select id="swal-nuevo-modulo-inicio" class="w-full p-2 border border-gray-300 rounded-md">
                                <option value="">Cargando módulos...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Final (Automático)</label>
                        <div class="p-3 bg-gray-50 border border-gray-300 rounded-md">
                            <span id="swal-modulo-final-display" class="text-gray-700">Selecciona un módulo inicial</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Espacio <span class="text-red-500">*</span></label>
                        <select id="swal-nuevo-espacio" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="">Selecciona fecha y módulo primero</option>
                        </select>
                        <p id="espacios-disponibles-info" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Motivo del reagendamiento</label>
                        <textarea id="swal-motivo-reagendamiento" rows="3" class="w-full p-2 border border-gray-300 rounded-md resize-none" placeholder="Explique el motivo del reagendamiento..."></textarea>
                    </div>
                </div>
            `,
            width: 700,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-calendar-check"></i> Reagendar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#6B7280',
            didOpen: async () => {
                const dateInput = document.getElementById('swal-nueva-fecha');
                const hoy = new Date().toISOString().split('T')[0];
                dateInput.min = hoy;
                dateInput.focus();
                
                // Cargar módulos desde la API
                try {
                    const response = await fetch('/api/modulos');
                    modulosDisponibles = await response.json();
                    console.log('Módulos cargados:', modulosDisponibles);
                    
                    // Llenar select de módulos iniciales inicialmente vacío
                    const selectModuloInicio = document.getElementById('swal-nuevo-modulo-inicio');
                    selectModuloInicio.innerHTML = '<option value="">Selecciona fecha primero</option>';
                    selectModuloInicio.disabled = true;

                    // Función para filtrar módulos por día
                    const filtrarModulosPorDia = (fechaStr) => {
                        if (!fechaStr) {
                            selectModuloInicio.innerHTML = '<option value="">Selecciona fecha primero</option>';
                            selectModuloInicio.disabled = true;
                            return;
                        }

                        const fecha = new Date(fechaStr + 'T12:00:00');
                        const prefijos = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
                        const prefijo = prefijos[fecha.getDay()];

                        // Filtrar y ordenar módulos
                        const modulosFiltrados = modulosDisponibles
                            .filter(m => m.id_modulo.startsWith(prefijo + '.'))
                            .sort((a, b) => {
                                const numA = parseInt(a.id_modulo.split('.')[1]);
                                const numB = parseInt(b.id_modulo.split('.')[1]);
                                return numA - numB;
                            });

                        if (modulosFiltrados.length === 0) {
                            selectModuloInicio.innerHTML = '<option value="">No hay módulos para este día</option>';
                            selectModuloInicio.disabled = true;
                        } else {
                            selectModuloInicio.innerHTML = '<option value="">Seleccionar módulo</option>';
                            modulosFiltrados.forEach(modulo => {
                                const option = document.createElement('option');
                                option.value = modulo.id_modulo;
                                option.textContent = `${modulo.id_modulo} (${modulo.hora_inicio} - ${modulo.hora_termino})`;
                                selectModuloInicio.appendChild(option);
                            });
                            selectModuloInicio.disabled = false;
                        }
                    };

                    // Listener para cambio de fecha
                    dateInput.addEventListener('change', (e) => {
                        filtrarModulosPorDia(e.target.value);
                        // Limpiar espacio si cambia la fecha
                        document.getElementById('swal-nuevo-espacio').innerHTML = '<option value="">Selecciona fecha y módulo primero</option>';
                    });
                } catch (error) {
                    console.error('Error cargando módulos:', error);
                }
                
                // Variables para controlar el cálculo
                const moduloInicio = document.getElementById('swal-nuevo-modulo-inicio');
                const moduloFinalDisplay = document.getElementById('swal-modulo-final-display');
                const cantidadModulos = document.getElementById('swal-cantidad-modulos');
                const dateInput2 = document.getElementById('swal-nueva-fecha');
                const selectEspacios = document.getElementById('swal-nuevo-espacio');
                const infoParagraph = document.getElementById('espacios-disponibles-info');
                
                // Función para cargar espacios disponibles
                const cargarEspaciosDisponibles = async () => {
                    const fecha = dateInput2.value;
                    const modulo = moduloInicio.value;
                    
                    if (!fecha || !modulo) {
                        selectEspacios.innerHTML = '<option value="">Selecciona fecha y módulo primero</option>';
                        infoParagraph.textContent = '';
                        return;
                    }

                    // Validar que no sea el mismo día
                    const [dia, mes, anio] = fechaOriginal.split('/');
                    const fechaOriginalFormato = anio + '-' + mes + '-' + dia;
                    if (fecha === fechaOriginalFormato) {
                        selectEspacios.innerHTML = '<option value="">No puedes reagendar para el mismo día</option>';
                        infoParagraph.textContent = 'El reagendamiento debe ser en una fecha diferente';
                        return;
                    }

                    // Calcular módulo final
                    const partesModulo = modulo.split('.');
                    const inicio = parseInt(partesModulo[1] || partesModulo[0]);
                    const prefijo = partesModulo[0];
                    const cantidad = parseInt(cantidadModulos.value) || clase.totalModulosProgramados;
                    const fin = inicio + cantidad - 1;
                    const moduloFinalId = prefijo + '.' + fin;

                    try {
                        const url = `/api/espacios-disponibles/${fecha}/${modulo}/${moduloFinalId}`;
                        const response = await fetch(url);
                        const data = await response.json();
                        
                        if (data.error) {
                            selectEspacios.innerHTML = '<option value="">Error: ' + data.error + '</option>';
                            infoParagraph.textContent = '';
                            return;
                        }

                        console.log('Espacios disponibles:', data);

                        if (data.espacios.length === 0) {
                            selectEspacios.innerHTML = '<option value="">No hay espacios disponibles para esta fecha y módulos</option>';
                            infoParagraph.textContent = 'Intenta con otra fecha o módulo';
                            return;
                        }

                        selectEspacios.innerHTML = '<option value="">Seleccionar espacio</option>';
                        data.espacios.forEach(espacio => {
                            const option = document.createElement('option');
                            option.value = espacio.id_espacio;
                            option.textContent = espacio.display_name;
                            selectEspacios.appendChild(option);
                        });

                        infoParagraph.textContent = 'Disponibles: ' + data.total_disponibles + ' espacio(s) • Módulo: ' + data.hora_inicio + ' - ' + data.hora_fin;
                    } catch (error) {
                        console.error('Error cargando espacios:', error);
                        selectEspacios.innerHTML = '<option value="">Error al cargar espacios</option>';
                    }
                };
                
                // Función para recalcular el módulo final y mostrar horarios
                const recalcularModuloFinal = () => {
                    if (moduloInicio.value && modulosDisponibles.length > 0) {
                        const partesModulo = moduloInicio.value.split('.');
                        const inicio = parseInt(partesModulo[1] || partesModulo[0]);
                        const prefijo = partesModulo[0];
                        const cantidad = parseInt(cantidadModulos.value) || clase.totalModulosProgramados;
                        const fin = inicio + cantidad - 1;
                        const moduloFinalId = prefijo + '.' + fin;
                        
                        // Buscar horarios de inicio y fin
                        const moduloInicial = modulosDisponibles.find(m => m.id_modulo == moduloInicio.value);
                        const moduloFinal = modulosDisponibles.find(m => m.id_modulo == moduloFinalId);
                        
                        if (moduloInicial && moduloFinal) {
                            const texto = 'Módulos ' + inicio + ' - ' + fin + ' (' + moduloInicial.hora_inicio + ' - ' + moduloFinal.hora_termino + ')';
                            moduloFinalDisplay.textContent = texto;
                        }
                        
                        // Cargar espacios disponibles cuando se selecciona módulo
                        cargarEspaciosDisponibles();
                    } else {
                        moduloFinalDisplay.textContent = 'Selecciona un módulo inicial';
                    }
                };
                
                dateInput2.addEventListener('change', cargarEspaciosDisponibles);
                moduloInicio.addEventListener('change', recalcularModuloFinal);
                cantidadModulos.addEventListener('change', recalcularModuloFinal);
                cantidadModulos.addEventListener('input', recalcularModuloFinal);
            },
            preConfirm: () => {
                const nuevaFecha = document.getElementById('swal-nueva-fecha').value;
                const nuevoEspacio = document.getElementById('swal-nuevo-espacio').value;
                const nuevoModulo = document.getElementById('swal-nuevo-modulo-inicio').value;
                const cantidadModulos = document.getElementById('swal-cantidad-modulos').value;
                const motivo = document.getElementById('swal-motivo-reagendamiento').value;
                
                if (!nuevaFecha) {
                    Swal.showValidationMessage('Por favor selecciona una fecha');
                    return false;
                }
                
                // Validar que no sea el mismo día
                const [dia, mes, anio] = fechaOriginal.split('/');
                const fechaOriginalFormato = anio + '-' + mes + '-' + dia;
                if (nuevaFecha === fechaOriginalFormato) {
                    Swal.showValidationMessage('No puedes reagendar para el mismo día');
                    return false;
                }
                
                if (!nuevoEspacio) {
                    Swal.showValidationMessage('Por favor selecciona un espacio');
                    return false;
                }
                
                if (!nuevoModulo) {
                    Swal.showValidationMessage('Por favor selecciona un módulo de inicio');
                    return false;
                }
                
                if (!cantidadModulos || parseInt(cantidadModulos) < 1 || parseInt(cantidadModulos) > 15) {
                    Swal.showValidationMessage('Por favor ingresa una cantidad válida de módulos (1-15)');
                    return false;
                }
                
                const fechaSeleccionada = new Date(nuevaFecha);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                
                if (fechaSeleccionada < hoy) {
                    Swal.showValidationMessage('La fecha no puede ser anterior a hoy');
                    return false;
                }
                
                return { nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo } = result.value;
                window.Livewire.find('{{ $this->getId() }}').call('reagendarClase', clase.id, nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo);
            }
        });
    });

    Livewire.on('confirm-delete', (data) => {
        const clase = data[0];
        
        Swal.fire({
            title: '¿Eliminar registro?',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Profesor:</strong> ${clase.profesor}</p>
                    <p class="mb-2"><strong>Asignatura:</strong> ${clase.asignatura}</p>
                    <p class="mb-2"><strong>Fecha:</strong> ${clase.fecha}</p>
                    <br>
                    <p class="text-red-600"><strong>Esta acción no se puede deshacer</strong></p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280',
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find('{{ $this->getId() }}').call('confirmDelete', clase.id);
            }
        });
    });

    Livewire.on('show-success', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const message = payload?.message || 'Operación realizada exitosamente';
        
        Swal.fire({
            title: '¡Éxito!',
            text: message,
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#10B981',
            timer: 3000,
            timerProgressBar: true
        });
    });

    Livewire.on('show-error', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const message = payload?.message || 'Ha ocurrido un error inesperado';
        
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#DC2626'
        });
    });
});
</script>
@endif

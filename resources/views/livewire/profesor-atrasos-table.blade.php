<div class="p-6" wire:poll.60s>

    <!-- Título Atrasos de Profesores -->
    <div class="mb-4">
        <span class="inline-flex items-center gap-3 px-5 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-base sm:text-lg font-bold text-gray-900">
            <i class="fas fa-clock text-orange-500 text-xl"></i>
            <span>Atrasos de Profesores</span>
        </span>
    </div>

    <!-- Navpills / Estadísticas (por fuera) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <!-- Total Atrasos -->
        <div class="stat-card bg-orange-50 border border-orange-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-700 text-xs font-semibold uppercase tracking-wider">Total Atrasos</p>
                    <p class="text-3xl font-black text-orange-950 mt-1">{{ $estadisticas['total'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-lg text-orange-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Promedio de Atraso -->
        <div class="stat-card bg-purple-50 border border-purple-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-700 text-xs font-semibold uppercase tracking-wider">Promedio de Atraso</p>
                    <p class="text-3xl font-black text-purple-950 mt-1">{{ $estadisticas['promedio'] }} min</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg text-purple-600">
                    <i class="fas fa-hourglass-half text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección FILTROS -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-filter text-orange-500"></i> FILTROS
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Semestre -->
            <div>
                <label for="periodo_atrasos" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Semestre</label>
                <select wire:model.live="periodo" 
                        id="periodo_atrasos"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white">
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
                <label for="search_atrasos" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Buscar</label>
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           id="search_atrasos"
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                           placeholder="Profesor, asignatura o sala...">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            <!-- Desde -->
            <div>
                <label for="fecha_inicio_atrasos" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Desde</label>
                <input type="date" 
                       wire:model.live.debounce.300ms="fecha_inicio" 
                       id="fecha_inicio_atrasos"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            <!-- Hasta -->
            <div>
                <label for="fecha_fin_atrasos" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Hasta</label>
                <input type="date" 
                       wire:model.live.debounce.300ms="fecha_fin" 
                       id="fecha_fin_atrasos"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
            <button type="button" wire:click="limpiarFiltros" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2">
                <i class="fas fa-undo text-xs"></i> Limpiar
            </button>
            <button type="button" wire:click="$refresh" 
                    class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                <i class="fas fa-check text-xs"></i> Aplicar filtros
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th 
                            wire:click="sortBy('fecha')" 
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                        >
                            <div class="flex items-center gap-1">
                                Fecha
                                @if($sortField === 'fecha')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-orange-500"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Profesor
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Asignatura
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Sala
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Módulo
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hora Programada
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hora Llegada
                        </th>
                        <th 
                            wire:click="sortBy('minutos_atraso')" 
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                        >
                            <div class="flex items-center gap-1">
                                Atraso
                                @if($sortField === 'minutos_atraso')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-orange-500"></i>
                                @endif
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($atrasos as $atraso)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($atraso->fecha)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $atraso->profesor->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $atraso->run_profesor }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $atraso->asignatura->nombre_asignatura ?? 'N/A' }}">
                                    {{ $atraso->asignatura->nombre_asignatura ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $atraso->id_espacio }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $atraso->id_modulo }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $atraso->hora_programada_formateada }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $atraso->hora_llegada_formateada }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $minutos = $atraso->minutos_atraso ?? 0;
                                    $colorClass = $minutos <= 15 ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : 
                                                 ($minutos <= 30 ? 'bg-orange-100 text-orange-800 border border-orange-200' : 'bg-red-100 text-red-800 border border-red-200');
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $colorClass }}">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $minutos }} min
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-check-circle text-4xl text-green-500"></i>
                                    </div>
                                    <p class="text-base font-bold text-gray-800 mb-1">No hay atrasos registrados</p>
                                    <p class="text-sm text-gray-500">No se encontraron registros de atrasos para los filtros seleccionados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($atrasos->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $atrasos->links() }}
            </div>
        @endif
    </div>
</div>

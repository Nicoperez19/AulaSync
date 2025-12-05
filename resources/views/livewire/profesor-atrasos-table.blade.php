<div class="space-y-4">
    <!-- Filtros -->
    <div class="p-4 bg-white rounded-lg shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <!-- Búsqueda -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Profesor, asignatura o sala..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                >
            </div>

            <!-- Fecha inicio -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input 
                    type="date" 
                    wire:model.live="fecha_inicio"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                >
            </div>

            <!-- Fecha fin -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input 
                    type="date" 
                    wire:model.live="fecha_fin"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                >
            </div>

            <!-- Botón limpiar -->
            <div class="flex items-end">
                <button 
                    wire:click="limpiarFiltros"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                >
                    <i class="fas fa-eraser mr-2"></i>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
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
                                {{ $atraso->hora_programada ? substr($atraso->hora_programada, 0, 5) : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $atraso->hora_llegada ? substr($atraso->hora_llegada, 0, 5) : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $minutos = $atraso->minutos_atraso ?? 0;
                                    $colorClass = $minutos <= 15 ? 'bg-yellow-100 text-yellow-800' : 
                                                 ($minutos <= 30 ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800');
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $minutos }} min
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-check-circle text-4xl text-green-400 mb-2"></i>
                                    <p class="text-lg font-medium">No hay atrasos registrados</p>
                                    <p class="text-sm">No se encontraron atrasos con los filtros seleccionados</p>
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

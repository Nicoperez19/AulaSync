<div>
    <!-- Barra de herramientas -->
    <div class="p-4 bg-gray-50">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                <!-- Búsqueda -->
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Buscar profesor..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg md:w-64 focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                    <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                </div>

                <!-- Filtro de estado -->
                <select wire:model.live="estado"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="finalizada">Finalizadas</option>
                    <option value="cancelada">Canceladas</option>
                </select>
            </div>

            <!-- Botón agregar -->
            <button wire:click="openCreateModal"
                class="px-4 py-2 text-white transition-colors rounded-lg bg-light-cloud-blue hover:bg-cloud-blue">
                <i class="mr-2 fa-solid fa-plus"></i>
                Nueva Ausencia
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-white uppercase bg-light-cloud-blue">
                <tr>
                    <th class="px-6 py-3 cursor-pointer" wire:click="sortBy('run_profesor')">
                        Profesor
                        @if ($sortField === 'run_profesor')
                            <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th class="px-6 py-3 cursor-pointer" wire:click="sortBy('fecha_inicio')">
                        Fecha Inicio
                        @if ($sortField === 'fecha_inicio')
                            <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th class="px-6 py-3 cursor-pointer" wire:click="sortBy('fecha_fin')">
                        Fecha Fin
                        @if ($sortField === 'fecha_fin')
                            <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th class="px-6 py-3">Motivo</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3 text-center">Clases a Recuperar</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($licencias as $licencia)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ $licencia->profesor->name }}</div>
                            <div class="text-xs text-gray-500">{{ $licencia->run_profesor }}</div>
                        </td>
                        <td class="px-6 py-4">
                            {{ $licencia->fecha_inicio->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $licencia->fecha_fin->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $licencia->motivo ?? 'Sin especificar' }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $badgeColors = [
                                    'activa' => 'bg-green-100 text-green-800',
                                    'finalizada' => 'bg-gray-100 text-gray-800',
                                    'cancelada' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded {{ $badgeColors[$licencia->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($licencia->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $totalClases = $licencia->recuperaciones->count();
                                $pendientes = $licencia->recuperaciones->where('estado', 'pendiente')->count();
                                $reagendadas = $licencia->recuperaciones->where('estado', 'reagendada')->count();
                                $realizadas = $licencia->recuperaciones->where('estado', 'realizada')->count();
                            @endphp
                            
                            @if ($totalClases > 0)
                                <div class="flex flex-col gap-1">
                                    <div class="font-semibold text-light-cloud-blue">
                                        {{ $totalClases }} clase{{ $totalClases > 1 ? 's' : '' }}
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        @if ($pendientes > 0)
                                            <span class="px-2 py-1 bg-yellow-100 rounded">{{ $pendientes }} pendiente{{ $pendientes > 1 ? 's' : '' }}</span>
                                        @endif
                                        @if ($reagendadas > 0)
                                            <span class="px-2 py-1 bg-blue-100 rounded">{{ $reagendadas }} reagendada{{ $reagendadas > 1 ? 's' : '' }}</span>
                                        @endif
                                        @if ($realizadas > 0)
                                            <span class="px-2 py-1 bg-green-100 rounded">{{ $realizadas }} realizada{{ $realizadas > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400">Sin clases</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Editar -->
                                <button wire:click="openEditModal({{ $licencia->id_licencia }})"
                                    class="px-3 py-1 text-white transition-colors rounded bg-light-cloud-blue hover:bg-cloud-blue"
                                    title="Editar">
                                    <i class="fa-solid fa-edit"></i>
                                </button>

                                <!-- Cambiar estado -->
                                @if ($licencia->estado === 'activa')
                                    <button wire:click="cambiarEstado({{ $licencia->id_licencia }}, 'finalizada')"
                                        class="px-3 py-1 text-white transition-colors bg-gray-500 rounded hover:bg-gray-600"
                                        title="Finalizar">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                @endif

                                <!-- Eliminar -->
                                <button wire:click="delete({{ $licencia->id_licencia }})"
                                    onclick="return confirm('¿Estás seguro de eliminar esta licencia?')"
                                    class="px-3 py-1 text-white transition-colors bg-red-500 rounded hover:bg-red-600"
                                    title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No se encontraron licencias
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="p-4">
        {{ $licencias->links() }}
    </div>

    <!-- Modal Crear/Editar -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-2xl p-6 mx-4 bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold">
                        {{ $editMode ? 'Editar Ausencia' : 'Nueva Ausencia' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Profesor -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Profesor *</label>
                            <div class="relative">
                                @if ($profesorNombre)
                                    <div class="flex items-center justify-between w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                        <span>{{ $profesorNombre }} ({{ $run_profesor }})</span>
                                        <button type="button" wire:click="$set('profesorNombre', '')" class="text-gray-400 hover:text-gray-600">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                @else
                                    <input type="text" 
                                        wire:model.live="run_profesor" 
                                        placeholder="Buscar por nombre o RUT del profesor..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                                    <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                                @endif
                            </div>

                            @if ($this->profesoresFiltrados->count() > 0)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                    @foreach ($this->profesoresFiltrados as $profesor)
                                        <button type="button"
                                            wire:click="selectProfesor('{{ $profesor->run_profesor }}', '{{ $profesor->name }}')"
                                            class="block w-full px-4 py-2 text-left hover:bg-gray-100">
                                            {{ $profesor->name }} ({{ $profesor->run_profesor }})
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @error('run_profesor') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha Inicio -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Fecha Inicio *</label>
                            <input type="date" wire:model="fecha_inicio" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            @error('fecha_inicio') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha Fin -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Fecha Fin *</label>
                            <input type="date" wire:model="fecha_fin" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            @error('fecha_fin') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Motivo -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Motivo</label>
                            <input type="text" wire:model="motivo" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Ej: Licencia médica, Ausencia anticipada, etc.">
                            @error('motivo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Observaciones -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea wire:model="observaciones" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                            @error('observaciones') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Genera Recuperación -->
                        <div class="md:col-span-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="genera_recuperacion" class="w-5 h-5 rounded text-light-cloud-blue focus:ring-light-cloud-blue">
                                <span class="text-sm font-medium text-gray-700">Genera clases a recuperar</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-white transition-colors rounded-lg bg-light-cloud-blue hover:bg-cloud-blue">
                            {{ $editMode ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

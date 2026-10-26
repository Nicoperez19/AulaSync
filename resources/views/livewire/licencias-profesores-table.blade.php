<div>
    <!-- Card Filtros -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                <!-- Búsqueda -->
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Buscar profesor..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg md:w-64 text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                </div>

                <!-- Filtro de estado -->
                <select wire:model.live="estado"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="finalizada">Finalizadas</option>
                    <option value="cancelada">Canceladas</option>
                </select>
            </div>

            <!-- Botón agregar -->
            <x-button wire:click="openCreateModal" variant="add">
                <i class="mr-2 fa-solid fa-plus"></i>
                Nueva Ausencia
            </x-button>
        </div>
    </div>

    <!-- Card Tabla -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center cursor-pointer select-none hover:text-red-100" wire:click="sortBy('run_profesor')">
                            <div class="flex items-center gap-1.5 justify-center">
                                <span>Profesor</span>
                                @if ($sortField === 'run_profesor')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center cursor-pointer select-none hover:text-red-100" wire:click="sortBy('fecha_inicio')">
                            <div class="flex items-center gap-1.5 justify-center">
                                <span>Fecha Inicio</span>
                                @if ($sortField === 'fecha_inicio')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center cursor-pointer select-none hover:text-red-100" wire:click="sortBy('fecha_fin')">
                            <div class="flex items-center gap-1.5 justify-center">
                                <span>Fecha Fin</span>
                                @if ($sortField === 'fecha_fin')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-white text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs text-white/50"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Motivo</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Estado</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Clases a Recuperar</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center min-w-[200px]">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse ($licencias as $licencia)
                        <tr wire:key="licencia-row-{{ $licencia->id_licencia }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 align-middle">
                                <div class="font-medium">{{ $licencia->profesor->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $licencia->run_profesor }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 align-middle whitespace-nowrap">
                                {{ $licencia->fecha_inicio->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 align-middle whitespace-nowrap">
                                {{ $licencia->fecha_fin->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 align-middle">
                                {{ $licencia->motivo ?? 'Sin especificar' }}
                            </td>
                            <td class="px-4 py-3 text-sm align-middle whitespace-nowrap">
                                @php
                                    $badgeColors = [
                                        'activa' => 'bg-green-100 text-green-800',
                                        'finalizada' => 'bg-gray-100 text-gray-800',
                                        'cancelada' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $badgeColors[$licencia->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($licencia->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                @php
                                    $totalClases = $licencia->recuperaciones->count();
                                    $pendientes = $licencia->recuperaciones->where('estado', 'pendiente')->count();
                                    $reagendadas = $licencia->recuperaciones->where('estado', 'reagendada')->count();
                                    $realizadas = $licencia->recuperaciones->where('estado', 'realizada')->count();
                                @endphp
                                
                                @if ($totalClases > 0)
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="font-semibold text-[#950101] dark:text-red-400 text-xs">
                                            {{ $totalClases }} clase{{ $totalClases > 1 ? 's' : '' }}
                                        </div>
                                        <div class="text-xs flex flex-wrap gap-1 justify-center">
                                            @if ($pendientes > 0)
                                                <span class="px-1.5 py-0.5 bg-yellow-100 text-yellow-800 rounded">{{ $pendientes }} pend.</span>
                                            @endif
                                            @if ($reagendadas > 0)
                                                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded">{{ $reagendadas }} reag.</span>
                                            @endif
                                            @if ($realizadas > 0)
                                                <span class="px-1.5 py-0.5 bg-green-100 text-green-800 rounded">{{ $realizadas }} real.</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">Sin clases</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <!-- Editar -->
                                    <button wire:click="openEditModal({{ $licencia->id_licencia }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs"
                                        title="Editar">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Editar</span>
                                    </button>

                                    <!-- Cambiar estado -->
                                    @if ($licencia->estado === 'activa')
                                        <button wire:click="cambiarEstado({{ $licencia->id_licencia }}, 'finalizada')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-lg border border-emerald-300 transition-colors shadow-xs"
                                            title="Finalizar">
                                            <i class="fa-solid fa-check text-xs"></i>
                                            <span>Finalizar</span>
                                        </button>
                                    @endif

                                    <!-- Eliminar -->
                                    <button wire:click="delete({{ $licencia->id_licencia }})"
                                        wire:confirm="¿Estás seguro de eliminar esta licencia?"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                        <span>Eliminar</span>
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
        @if($licencias->hasPages())
            <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200 dark:bg-gray-900/40 dark:border-gray-700">
                {{ $licencias->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Crear/Editar -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-2xl p-6 mx-4 bg-white rounded-xl shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between pb-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $editMode ? 'Editar Ausencia' : 'Nueva Ausencia' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl font-bold">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="py-4 grid gap-4 md:grid-cols-2">
                        <!-- Profesor -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Profesor *</label>
                            <div class="relative">
                                @if ($profesorNombre)
                                    <div class="flex items-center justify-between w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <span>{{ $profesorNombre }} ({{ $run_profesor }})</span>
                                        <button type="button" wire:click="$set('profesorNombre', '')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                @else
                                    <input type="text" 
                                        wire:model.live="run_profesor" 
                                        placeholder="Buscar por nombre o RUT del profesor..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                                @endif
                            </div>

                            @if ($this->profesoresFiltrados->count() > 0)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto dark:bg-gray-700 dark:border-gray-600">
                                    @foreach ($this->profesoresFiltrados as $profesor)
                                        <button type="button"
                                            wire:click="selectProfesor('{{ $profesor->run_profesor }}', '{{ $profesor->name }}')"
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-white">
                                            {{ $profesor->name }} ({{ $profesor->run_profesor }})
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @error('run_profesor') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha Inicio -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Inicio *</label>
                            <input type="date" wire:model="fecha_inicio" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            @error('fecha_inicio') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha Fin -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Fin *</label>
                            <input type="date" wire:model="fecha_fin" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            @error('fecha_fin') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Motivo -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Motivo</label>
                            <input type="text" wire:model="motivo" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Ej: Licencia médica, Ausencia anticipada, etc.">
                            @error('motivo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Observaciones -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                            <textarea wire:model="observaciones" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            @error('observaciones') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Genera Recuperación -->
                        <div class="md:col-span-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="genera_recuperacion" class="w-4 h-4 rounded text-[#D12420] border-gray-300 focus:ring-2 focus:ring-[#950101]">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Genera clases a recuperar</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-700">
                        <x-button type="button" wire:click="closeModal" variant="cancel">
                            Cancelar
                        </x-button>
                        <x-button type="submit" variant="save">
                            {{ $editMode ? 'Actualizar' : 'Crear' }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

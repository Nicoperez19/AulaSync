<div>
    <!-- Card Filtros -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                <!-- Búsqueda con debounce -->
                <div class="relative">
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Buscar feriado..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg md:w-64 text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                </div>

                <!-- Filtro de tipo -->
                <select wire:model.live="tipo"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los tipos</option>
                    <option value="feriado">Feriado</option>
                    <option value="semana_reajuste">Semana de Reajuste</option>
                    <option value="suspension_actividades">Suspensión de Actividades</option>
                </select>
            </div>

            <!-- Botón agregar -->
            <x-button wire:click="openCreateModal" variant="add">
                <i class="mr-2 fa-solid fa-plus"></i>
                Agregar Feriado
            </x-button>
        </div>
    </div>

    <!-- Card Tabla -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center cursor-pointer select-none hover:text-red-100" wire:click="sortBy('nombre')">
                            <div class="flex items-center gap-1.5 justify-center">
                                <span>Nombre</span>
                                @if ($sortField === 'nombre')
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
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Tipo</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center">Estado</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider text-center w-48">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse ($feriados as $feriado)
                        <tr wire:key="feriado-row-{{ $feriado->id_feriado }}" class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 align-middle">
                                <div class="font-medium">{{ $feriado->nombre }}</div>
                                @if ($feriado->descripcion)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($feriado->descripcion, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 align-middle whitespace-nowrap">
                                {{ $feriado->fecha_inicio->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 align-middle whitespace-nowrap">
                                {{ $feriado->fecha_fin->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm align-middle whitespace-nowrap">
                                @php
                                    $tipoLabels = [
                                        'feriado' => 'Feriado',
                                        'semana_reajuste' => 'Semana de Reajuste',
                                        'suspension_actividades' => 'Suspensión de Actividades',
                                    ];
                                    $tipoColors = [
                                        'feriado' => 'bg-blue-100 text-blue-800',
                                        'semana_reajuste' => 'bg-yellow-100 text-yellow-800',
                                        'suspension_actividades' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $tipoColors[$feriado->tipo] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $tipoLabels[$feriado->tipo] ?? ucfirst($feriado->tipo) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm align-middle whitespace-nowrap">
                                <button wire:click="toggleActivo({{ $feriado->id_feriado }})"
                                    class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded transition-colors {{ $feriado->activo ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                                    {{ $feriado->activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button wire:click="openEditModal({{ $feriado->id_feriado }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs"
                                        title="Editar">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Editar</span>
                                    </button>
                                    <button wire:click="delete({{ $feriado->id_feriado }})"
                                        wire:confirm="¿Está seguro de eliminar este registro?"
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
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No se encontraron registros
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($feriados->hasPages())
            <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200 dark:bg-gray-900/40 dark:border-gray-700">
                {{ $feriados->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Crear/Editar -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-2xl p-6 mx-auto bg-white rounded-xl shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between pb-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $editMode ? 'Editar Feriado' : 'Nuevo Feriado' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl font-bold">
                        &times;
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="py-4 space-y-4">
                        <!-- Nombre -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="nombre"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Ej: Día del Trabajador">
                            @error('nombre') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tipo <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="tipo_feriado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="feriado">Feriado</option>
                                <option value="semana_reajuste">Semana de Reajuste</option>
                                <option value="suspension_actividades">Suspensión de Actividades</option>
                            </select>
                            @error('tipo_feriado') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fechas -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Fecha Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model="fecha_inicio"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('fecha_inicio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Fecha Fin <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model="fecha_fin"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('fecha_fin') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Descripción
                            </label>
                            <textarea wire:model="descripcion" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#950101] focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Descripción adicional (opcional)"></textarea>
                            @error('descripcion') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Estado -->
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="activo" id="activo"
                                class="w-4 h-4 text-[#D12420] border-gray-300 rounded focus:ring-2 focus:ring-[#950101]">
                            <label for="activo" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Activo
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-700">
                        <x-button type="button" wire:click="closeModal" variant="cancel">
                            Cancelar
                        </x-button>
                        <x-button type="submit" variant="save">
                            {{ $editMode ? 'Actualizar' : 'Guardar' }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

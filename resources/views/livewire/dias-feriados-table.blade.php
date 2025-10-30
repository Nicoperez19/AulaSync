<div>
    <!-- Barra de herramientas -->
    <div class="p-4 bg-gray-50">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                <!-- Búsqueda con debounce -->
                <div class="relative">
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Buscar feriado..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg md:w-64 focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                    <i class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                </div>

                <!-- Filtro de tipo -->
                <select wire:model.live="tipo"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                    <option value="">Todos los tipos</option>
                    <option value="feriado">Feriado</option>
                    <option value="semana_reajuste">Semana de Reajuste</option>
                    <option value="suspension_actividades">Suspensión de Actividades</option>
                </select>
            </div>

            <!-- Botón agregar -->
            <button wire:click="openCreateModal"
                class="px-4 py-2 text-white transition-colors rounded-lg bg-light-cloud-blue hover:bg-cloud-blue">
                <i class="mr-2 fa-solid fa-plus"></i>
                Agregar Feriado
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-white uppercase bg-light-cloud-blue">
                <tr>
                    <th class="px-6 py-3 cursor-pointer" wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
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
                    <th class="px-6 py-3">Tipo</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($feriados as $feriado)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ $feriado->nombre }}</div>
                            @if ($feriado->descripcion)
                                <div class="text-xs text-gray-500">{{ Str::limit($feriado->descripcion, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{ $feriado->fecha_inicio->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $feriado->fecha_fin->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
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
                            <span class="px-2 py-1 text-xs font-semibold rounded {{ $tipoColors[$feriado->tipo] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $tipoLabels[$feriado->tipo] ?? ucfirst($feriado->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button wire:click="toggleActivo({{ $feriado->id_feriado }})"
                                class="px-2 py-1 text-xs font-semibold rounded {{ $feriado->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $feriado->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button wire:click="openEditModal({{ $feriado->id_feriado }})"
                                    class="text-blue-600 hover:text-blue-900" title="Editar">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button wire:click="delete({{ $feriado->id_feriado }})"
                                    onclick="return confirm('¿Está seguro de eliminar este registro?')"
                                    class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron registros
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="p-4 bg-white border-t">
        {{ $feriados->links() }}
    </div>

    <!-- Modal Crear/Editar -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
            <div class="relative w-full max-w-2xl p-4 mx-auto bg-white rounded-lg shadow-xl">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ $editMode ? 'Editar Feriado' : 'Nuevo Feriado' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="p-4 space-y-4">
                        <!-- Nombre -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="nombre"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent"
                                placeholder="Ej: Día del Trabajador">
                            @error('nombre') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Tipo <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="tipo_feriado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                                <option value="feriado">Feriado</option>
                                <option value="semana_reajuste">Semana de Reajuste</option>
                                <option value="suspension_actividades">Suspensión de Actividades</option>
                            </select>
                            @error('tipo_feriado') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fechas -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Fecha Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model="fecha_inicio"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                                @error('fecha_inicio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Fecha Fin <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model="fecha_fin"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent">
                                @error('fecha_fin') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Descripción
                            </label>
                            <textarea wire:model="descripcion" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-light-cloud-blue focus:border-transparent"
                                placeholder="Descripción adicional (opcional)"></textarea>
                            @error('descripcion') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Estado -->
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="activo" id="activo"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-light-cloud-blue">
                            <label for="activo" class="ml-2 text-sm font-medium text-gray-700">
                                Activo
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 p-4 border-t">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-white rounded-lg bg-light-cloud-blue hover:bg-cloud-blue">
                            {{ $editMode ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

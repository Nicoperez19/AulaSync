<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Formulario (Izquierda) -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-plus-circle mr-2"></i>
                {{ $editingTipoId ? 'Editar' : 'Nuevo' }} Tipo de Correo
            </h3>

            <form wire:submit.prevent="saveTipo">
                <!-- Nombre -->
                <div class="mb-4">
                    <label for="tipo-nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model.live="tipoNombre"
                           wire:change="generarCodigo"
                           id="tipo-nombre"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej: Informe Clases No Realizadas">
                    @error('tipoNombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Código -->
                <div class="mb-4">
                    <label for="tipo-codigo" class="block text-sm font-medium text-gray-700 mb-2">
                        Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model="tipoCodigo"
                           id="tipo-codigo"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                           placeholder="informe_clases_no_realizadas">
                    <p class="mt-1 text-xs text-gray-500">Se genera automáticamente del nombre</p>
                    @error('tipoCodigo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="mb-4">
                    <label for="tipo-descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea
                        wire:model="tipoDescripcion"
                        id="tipo-descripcion"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Describe el propósito de este tipo de correo..."></textarea>
                    @error('tipoDescripcion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Frecuencia -->
                <div class="mb-4">
                    <label for="tipo-frecuencia" class="block text-sm font-medium text-gray-700 mb-2">
                        Frecuencia <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="tipoFrecuencia"
                            id="tipo-frecuencia"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="manual">Manual</option>
                        <option value="diario">Diario</option>
                        <option value="semanal">Semanal</option>
                        <option value="mensual">Mensual</option>
                    </select>
                    @error('tipoFrecuencia')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Activo -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox"
                               wire:model="tipoActivo"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                </div>

                <!-- Botones -->
                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-save mr-2"></i>
                        {{ $editingTipoId ? 'Actualizar' : 'Crear' }}
                    </button>
                    @if($editingTipoId)
                        <button type="button"
                                wire:click="resetTipoForm"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Lista (Derecha) -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">

            <!-- Buscador -->
            <div class="p-4 border-b border-gray-200">
                <div class="relative">
                    <input type="text"
                           wire:model.live="tipoSearch"
                           placeholder="Buscar tipos de correos..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Frecuencia
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tiposCorreos as $tipo)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $tipo->nombre }}
                                    </div>
                                    <div class="text-sm text-gray-500 font-mono">
                                        {{ $tipo->codigo }}
                                    </div>
                                    @if($tipo->descripcion)
                                        <div class="text-xs text-gray-600 mt-1">
                                            {{ Str::limit($tipo->descripcion, 60) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($tipo->frecuencia === 'diario') bg-blue-100 text-blue-800
                                        @elseif($tipo->frecuencia === 'semanal') bg-purple-100 text-purple-800
                                        @elseif($tipo->frecuencia === 'mensual') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($tipo->frecuencia) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($tipo->tipo === 'sistema') bg-indigo-100 text-indigo-800
                                        @else bg-amber-100 text-amber-800
                                        @endif">
                                        {{ $tipo->tipo === 'sistema' ? 'Sistema' : 'Personalizado' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($tipo->activo)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="showAsignaciones({{ $tipo->id }})"
                                                class="text-purple-600 hover:text-purple-900"
                                                title="Asignar destinatarios">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button wire:click="editTipo({{ $tipo->id }})"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($tipo->tipo !== 'sistema')
                                            <button wire:click="deleteTipo({{ $tipo->id }})"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>No hay tipos de correos registrados</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($tiposCorreos->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $tiposCorreos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

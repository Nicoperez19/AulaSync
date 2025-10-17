<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Formulario (Izquierda) -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-user-plus mr-2"></i>
                {{ $editingDestinatarioId ? 'Editar' : 'Nuevo' }} Destinatario
            </h3>

            <form wire:submit.prevent="saveDestinatario">
                <!-- Tipo de Destinatario -->
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox"
                               wire:model.live="destinatarioEsExterno"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ $editingDestinatarioId ? 'disabled' : '' }}>
                        <span class="ml-2 text-sm font-medium text-gray-700">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Destinatario Externo (no registrado)
                        </span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Marca esta opción para agregar un destinatario que no está en el sistema</p>
                </div>

                @if($destinatarioEsExterno)
                    <!-- Email Externo -->
                    <div class="mb-4">
                        <label for="destinatario-email-externo" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               wire:model="destinatarioEmailExterno"
                               id="destinatario-email-externo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="correo@ejemplo.com"
                               {{ $editingDestinatarioId ? 'disabled' : '' }}>
                        @error('destinatarioEmailExterno')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nombre Externo -->
                    <div class="mb-4">
                        <label for="destinatario-nombre-externo" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model="destinatarioNombreExterno"
                               id="destinatario-nombre-externo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Nombre completo del destinatario">
                        @error('destinatarioNombreExterno')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <!-- Usuario -->
                    <div class="mb-4">
                        <label for="destinatario-user" class="block text-sm font-medium text-gray-700 mb-2">
                            Usuario <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="destinatarioUserId"
                                id="destinatario-user"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                {{ $editingDestinatarioId ? 'disabled' : '' }}>
                            <option value="">Seleccionar usuario...</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->run }}">
                                    {{ $usuario->name }} - RUN: {{ $usuario->run }} ({{ $usuario->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('destinatarioUserId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Rol -->
                <div class="mb-4">
                    <label for="destinatario-rol" class="block text-sm font-medium text-gray-700 mb-2">
                        Rol
                    </label>
                    <input type="text"
                           wire:model="destinatarioRol"
                           id="destinatario-rol"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej: Jefe de Carrera, Director, Subdirector">
                    <p class="mt-1 text-xs text-gray-500">Opcional: Define el rol en el contexto de correos</p>
                    @error('destinatarioRol')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cargo -->
                <div class="mb-4">
                    <label for="destinatario-cargo" class="block text-sm font-medium text-gray-700 mb-2">
                        Cargo/Descripción
                    </label>
                    <textarea
                        wire:model="destinatarioCargo"
                        id="destinatario-cargo"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Descripción adicional del cargo o responsabilidades..."></textarea>
                    @error('destinatarioCargo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Activo -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox"
                               wire:model="destinatarioActivo"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                    </label>
                </div>

                <!-- Botones -->
                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-save mr-2"></i>
                        {{ $editingDestinatarioId ? 'Actualizar' : 'Crear' }}
                    </button>
                    @if($editingDestinatarioId)
                        <button type="button"
                                wire:click="resetDestinatarioForm"
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
                           wire:model.live="destinatarioSearch"
                           placeholder="Buscar por nombre, email, RUN..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Busca por nombre, correo electrónico, RUN, rol o cargo
                </p>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol/Cargo
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
                        @forelse($destinatarios as $destinatario)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full {{ $destinatario->es_externo ? 'bg-purple-100' : 'bg-indigo-100' }} flex items-center justify-center">
                                                @if($destinatario->es_externo)
                                                    <i class="fas fa-external-link-alt text-purple-600"></i>
                                                @else
                                                    <span class="text-indigo-600 font-medium text-sm">
                                                        {{ strtoupper(substr($destinatario->user->name ?? 'N', 0, 2)) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                                @if($destinatario->es_externo)
                                                    {{ $destinatario->nombre_externo ?? 'N/A' }}
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                        <i class="fas fa-external-link-alt mr-1"></i>Externo
                                                    </span>
                                                @else
                                                    {{ $destinatario->user->name ?? 'N/A' }}
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                @if($destinatario->es_externo)
                                                    {{ $destinatario->email_externo ?? 'N/A' }}
                                                @else
                                                    {{ $destinatario->user->email ?? 'N/A' }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($destinatario->rol)
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $destinatario->rol }}
                                        </div>
                                    @endif
                                    @if($destinatario->cargo)
                                        <div class="text-sm text-gray-500">
                                            {{ Str::limit($destinatario->cargo, 40) }}
                                        </div>
                                    @endif
                                    @if(!$destinatario->rol && !$destinatario->cargo)
                                        <span class="text-sm text-gray-400 italic">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($destinatario->activo)
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
                                        <button wire:click="editDestinatario({{ $destinatario->id }})"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="deleteDestinatario({{ $destinatario->id }})"
                                                class="text-red-600 hover:text-red-900"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-user-slash text-4xl mb-2"></i>
                                    <p>No hay destinatarios registrados</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($destinatarios->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $destinatarios->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

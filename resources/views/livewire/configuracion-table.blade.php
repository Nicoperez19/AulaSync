<div>
    <div class="flex flex-col gap-4 mb-4 md:flex-row md:items-center md:justify-between">
        <div class="relative w-full md:w-1/2">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por Clave o Descripción..."
                   class="w-full py-2 pl-10 pr-4 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>
        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
            <span>Total: <strong>{{ $configuraciones->total() }}</strong> configuraciones</span>
        </div>
    </div>

    <div class="mt-2 mb-4">
        {{ $configuraciones->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="configuracion-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('clave')">
                        Clave
                        @if($sortField === 'clave')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3"> Valor </th>
                    <th class="p-3 cursor-pointer select-none" wire:click="sortBy('descripcion')">
                        Descripción
                        @if($sortField === 'descripcion')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($configuraciones as $index => $configuracion)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                            @if(str_starts_with($configuracion->clave, 'logo_institucional_'))
                                @php
                                    $idSede = str_replace('logo_institucional_', '', $configuracion->clave);
                                    $sede = \App\Models\Sede::find($idSede);
                                @endphp
                                <span class="block">{{ $configuracion->clave }}</span>
                                <span class="block text-xs text-gray-500">Sede: {{ $sede ? $sede->nombre_sede : $idSede }}</span>
                            @elseif(str_starts_with($configuracion->clave, 'correo_administrativo_') || str_starts_with($configuracion->clave, 'nombre_remitente_'))
                                @php
                                    $idFacultad = str_replace(['correo_administrativo_', 'nombre_remitente_'], '', $configuracion->clave);
                                    $facultad = \App\Models\Facultad::find($idFacultad);
                                    $tipo = str_starts_with($configuracion->clave, 'correo_administrativo_') ? 'Correo' : 'Nombre';
                                @endphp
                                <span class="block">{{ $configuracion->clave }}</span>
                                <span class="block text-xs text-gray-500">{{ $tipo }} - Escuela: {{ $facultad ? $facultad->nombre_facultad : $idFacultad }}</span>
                            @else
                                {{ $configuracion->clave }}
                            @endif
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            @if(str_starts_with($configuracion->clave, 'logo_institucional_') && $configuracion->valor)
                                <img src="{{ asset('storage/images/logo/' . $configuracion->valor) }}" alt="Logo" class="h-10 mx-auto">
                            @else
                                {{ Str::limit($configuracion->valor, 50) }}
                            @endif
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $configuracion->descripcion ?? 'N/A' }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" href="{{ route('configuracion.edit', $configuracion->id) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <form id="delete-form-{{ $configuracion->id }}"
                                    action="{{ route('configuracion.destroy', $configuracion->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button" onclick="deleteConfiguracion('{{ $configuracion->id }}')"
                                        class="px-4 py-2">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <p class="text-lg font-medium">No se encontraron configuraciones</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $configuraciones->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    function deleteConfiguracion(id) {
        if (typeof Swal === 'undefined') {
            if (confirm('¿Estás seguro de que quieres eliminar esta configuración?')) {
                document.getElementById('delete-form-' + id).submit();
            }
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>


<style>
    .sort-icon {
        display: none;
        margin-left: 5px;
        transition: transform 0.2s;
    }

    .asc .sort-icon,
    .desc .sort-icon {
        display: inline-block;
    }

    .asc .sort-icon {
        transform: rotate(180deg);
    }

    .desc .sort-icon {
        transform: rotate(0deg);
    }

    th {
        cursor: pointer;
        user-select: none;
    }

    th:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>

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
                   placeholder="Buscar por ID, Nombre o Dirección..."
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
            <span>Total: <strong>{{ $universidades->total() }}</strong> universidades</span>
        </div>
    </div>

    <div class="mt-2 mb-4">
        {{ $universidades->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 {{ $sortField === 'id_universidad' ? ($sortDirection === 'asc' ? 'asc' : 'desc') : '' }}"
                        wire:click="sortBy('id_universidad')">
                        ID Universidad
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3 {{ $sortField === 'nombre_universidad' ? ($sortDirection === 'asc' ? 'asc' : 'desc') : '' }}"
                        wire:click="sortBy('nombre_universidad')">
                        Nombre
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3 {{ $sortField === 'direccion_universidad' ? ($sortDirection === 'asc' ? 'asc' : 'desc') : '' }}"
                        wire:click="sortBy('direccion_universidad')">
                        Dirección
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3 {{ $sortField === 'telefono_universidad' ? ($sortDirection === 'asc' ? 'asc' : 'desc') : '' }}"
                        wire:click="sortBy('telefono_universidad')">
                        Teléfono
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3">Imagen</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($universidades as $index => $universidad)
                    <tr wire:key="universidad-row-{{ $universidad->id_universidad }}" class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td
                            class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                            {{ $universidad->id_universidad }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $universidad->nombre_universidad }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $universidad->direccion_universidad }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $universidad->telefono_universidad }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <img src="{{ asset('images/logo_universidad/' . $universidad->imagen_logo) }}" alt="Logo"
                                class="object-cover w-16 h-16 rounded">
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view"
                                    href="{{ route('universities.edit', $universidad->id_universidad) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <form id="delete-form-{{ $universidad->id_universidad }}"
                                    action="{{ route('universities.delete', $universidad->id_universidad) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button"
                                        onclick="deleteUniversity('{{ $universidad->id_universidad }}', '{{ $universidad->nombre_universidad }}')"
                                        class="px-4 py-2">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr wire:key="empty-universidades-row">
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">No se encontraron universidades</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $universidades->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    function deleteUniversity(id, name) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Esta acción eliminará la universidad "${name}" y no se puede deshacer`,
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

    // Verifica si hay mensajes de sesión
    const successMessage = @json(session('success'));
    if (successMessage) {
        Swal.fire({
            title: "¡Éxito!",
            text: successMessage,
            icon: "success",
            confirmButtonText: "Aceptar"
        });
    }

    const errorMessage = @json(session('error'));
    if (errorMessage) {
        Swal.fire({
            title: "¡Error!",
            text: errorMessage,
            icon: "error",
            confirmButtonText: "Aceptar"
        });
    }

    const userNotFoundMessage = @json(session('university_not_found'));
    if (userNotFoundMessage) {
        Swal.fire({
            title: "¡Error!",
            text: userNotFoundMessage,
            icon: "error",
            confirmButtonText: "Aceptar"
        });
    }
</script>

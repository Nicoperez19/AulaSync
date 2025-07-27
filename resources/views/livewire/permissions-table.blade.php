<style>
    .sort-icon {
        display: inline-block;
        margin-left: 5px;
        transition: transform 0.2s;
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
    <div>
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
            <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
                <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                    <tr>
                        <th class="p-3 ">ID</th>
                        <th class="p-3 ">Nombre</th>
                        <th class="p-3 ">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $index => $permission)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'  }}">

                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $permission->id }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $permission->name }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <x-button variant="view"
                                    x-on:click.prevent="$dispatch('open-modal', 'edit-permission-{{ $permission->id }}')"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <form action="{{ route('permission.delete', $permission->id) }}" method="POST"
                                    id="delete-form-{{ $permission->id }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')

                                  
                                     <x-button variant="danger" type="button"
                                        onclick="confirmDelete({{ $permission->id }}, '{{ $permission->name }}')"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p class="text-lg font-medium">No se encontraron permisos</p>
                                    <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $permissions->links('vendor.pagination.tailwind') }}
    </div>

    @foreach ($allPermissions as $permission)
        <x-modal name="edit-permission-{{ $permission->id }}" :show="$errors->any()" focusable>
              @slot('title')
            <h1 class="text-lg font-medium text-white dark:text-gray-100">
               Editar Permiso </h1>
            @endslot
            <form method="POST" action="{{ route('permissions.update', $permission->id) }}">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6">
                    <div class="space-y-2">
                        <x-form.label for="name_permission_{{ $permission->id }}" :value="__('Nombre del Permiso')"
                            class="text-left" />
                        <x-form.input id="name_permission_{{ $permission->id }}" class="block w-full" type="text"
                            name="name" value="{{ $permission->name }}" required autofocus
                            placeholder="{{ __('Nombre del permiso') }}" />
                    </div>
                    <div class="flex justify-end">
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-pencil class="w-6 h-6" aria-hidden="true" />
                            {{ __('Actualizar Permiso') }}
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    @endforeach
</div>

<script>
    function confirmDelete(permissionID, permissionName) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Estás seguro de que deseas eliminar el permiso "${permissionName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + permissionID).submit();
            }
        });
    }
</script>

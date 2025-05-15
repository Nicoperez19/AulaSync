<div class="w-full min-h-screen p-4 bg-gray-100 dark:bg-gray-900">
    <div class="relative overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full text-center border-collapse table-auto min-w-max">
            <thead class="hidden lg:table-header-group @class([
                'text-black border-b border-white',
                'bg-gray-50 dark:bg-black',
                'dark:text-white' => config('app.dark_mode'),
            ])">
                <tr>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $index => $role)
                    <tr class="@class([
                        'text-black',
                        'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                        'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                        'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                        'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                    ])">
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="RUN">
                            {{ $role->id }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                            {{ $role->name }}
                        </td>

                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            <div class="flex justify-end space-x-2">
                                <x-button href="{{ route('roles.edit', $role->id) }}" class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                    Editar
                                </x-button>
                                <form action="{{ route('roles.delete', $role->id) }}" method="POST" id="delete-form-{{ $role->id }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="button" onclick="confirmDelete({{ $role->id }})" variant="danger" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        Eliminar
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        // Función para confirmar la eliminación
        function confirmDelete(roleId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¡No podrás revertir esta acción!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si el usuario confirma, enviar el formulario
                    document.getElementById('delete-form-' + roleId).submit();
                }
            });
        }
    </script>
</div>

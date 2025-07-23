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

<div class="w-full min-h-screen p-4 bg-gray-100 dark:bg-gray-900">
    <div class="relative overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full text-center border-collapse table-auto min-w-max">
            <thead class="hidden text-black border-b border-white lg:table-header-group bg-gray-50 dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Universidad</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Facultad</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Piso</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Tipo</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Estado</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Puestos</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($espacios as $index => $espacio)
                    <tr class="@class([
                        'text-black',
                        'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                        'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                        'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                        'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                    ])">
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->piso->facultad->universidad->nombre_universidad ?? 'Sin Universidad' }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->piso->facultad->nombre_facultad ?? 'Sin Facultad' }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->piso->numero_piso ?? 'Sin Piso' }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->tipo_espacio }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->estado }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            {{ $espacio->puestos_disponibles ?? 'N/A' }}
                        </td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            <div class="flex justify-end space-x-2">
                                <x-button href="{{ route('spaces.edit', $espacio->id_espacio) }}"
                                    class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                    Editar
                                </x-button>
                                <form action="{{ route('spaces.delete', $espacio->id_espacio) }}" method="POST"
                                    style="display: inline;" onclick="confirmDelete(this)" >
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDelete(button) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "¡Esta acción no se puede deshacer!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    
        // Mensajes de sesión con SweetAlert2
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
    
    </script>
    
</div>

<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Universidad</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Dirección</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Teléfono</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Imagen</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($universidades as $index => $universidad)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Universidad">
                        {{ $universidad->id_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                        {{ $universidad->nombre_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Dirección">
                        {{ $universidad->direccion_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Teléfono">
                        {{ $universidad->telefono_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Imagen">
                        <img src="{{ asset('images/logo_universidad/' . $universidad->imagen_logo) }}" alt="Logo"
                            class="object-cover w-16 h-16 rounded">
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <x-button href="{{ route('universities.edit', $universidad->id_universidad) }}"
                                class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                Editar
                            </x-button>
                            <form method="POST"
                                action="{{ route('universities.delete', $universidad->id_universidad) }}"
                                enctype="multipart/form-data">

                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete(this)"
                                    class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                    Eliminar
                                </button>


                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmDelete(button) {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "¡Esta acción no se puede deshacer!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('form').submit();
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

</div>

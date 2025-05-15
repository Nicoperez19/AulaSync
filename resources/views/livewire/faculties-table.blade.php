<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Facultad</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Ubicación</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Universidad</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Logo</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facultades as $index => $facultad)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Facultad">
                        {{ $facultad->id_facultad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                        {{ $facultad->nombre_facultad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Ubicación">
                        {{ $facultad->ubicacion_facultad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Universidad">
                        {{ $facultad->universidad->nombre_universidad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Imagen">
                        <img src="{{ asset('images/logo_facultad/' . $facultad->logo_facultad) }}" alt="Logo"
                            class="object-cover w-16 rounded h-15">
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <x-button href="{{ route('faculties.edit', $facultad->id_facultad) }}"
                                class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                Editar
                            </x-button>
                            <form method="POST" action="{{ route('faculties.delete', $facultad->id_facultad) }}">
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
                text: "¡Esta acción eliminará la facultad permanentemente!",
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

        // Mensajes flash (éxito, error, etc.)
        document.addEventListener('DOMContentLoaded', () => {
            const success = @json(session('success'));
            const error = @json(session('error'));

            if (success) {
                Swal.fire({
                    title: "¡Éxito!",
                    text: success,
                    icon: "success",
                    confirmButtonText: "Aceptar"
                });
            }

            if (error) {
                Swal.fire({
                    title: "¡Error!",
                    text: error,
                    icon: "error",
                    confirmButtonText: "Aceptar"
                });
            }
        });
    </script>

</div>

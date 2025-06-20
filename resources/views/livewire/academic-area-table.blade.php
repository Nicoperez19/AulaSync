<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead class="hidden lg:table-header-group @class([
            'text-black border-b border-white',
            'bg-gray-50 dark:bg-black',
            'dark:text-white' => config('app.dark_mode'),
        ])">
            <tr>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">ID Área Académica</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Tipo</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Facultad</th>
                <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($areasAcademicas as $index => $areaAcademica)
                <tr class="@class([
                    'text-black',
                    'bg-gray-200' => $index % 2 === 0 && !config('app.dark_mode'),
                    'bg-gray-600' => $index % 2 === 0 && config('app.dark_mode'),
                    'bg-gray-100' => $index % 2 !== 0 && !config('app.dark_mode'),
                    'bg-gray-700' => $index % 2 !== 0 && config('app.dark_mode'),
                ])">
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="ID Área Académica">
                        {{ $areaAcademica->id_area_academica }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Nombre">
                        {{ $areaAcademica->nombre_area_academica }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Tipo">
                        {{ ucfirst($areaAcademica->tipo_area_academica) }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap" data-label="Facultad">
                        {{ $areaAcademica->facultad->nombre_facultad }}
                    </td>
                    <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                        <div class="flex justify-end space-x-2">
                            <x-button href="{{ route('academic_areas.edit', $areaAcademica->id_area_academica) }}"
                                class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                Editar
                            </x-button>
                            <form id="delete-area-form-{{ $areaAcademica->id_area_academica }}" method="POST" action="{{ route('academic_areas.delete', $areaAcademica->id_area_academica) }}">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Iteramos sobre todas las áreas académicas para agregar un evento de confirmación
        @foreach ($areasAcademicas as $areaAcademica)
            // Obtenemos el formulario correspondiente por su ID único
            document.getElementById('delete-area-form-{{ $areaAcademica->id_area_academica }}').addEventListener('submit', function(e) {
                e.preventDefault(); // Evita el envío inmediato del formulario

                // Mostramos el cuadro de confirmación usando SweetAlert2
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción eliminará el área académica.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit(); // Si se confirma, enviamos el formulario
                    }
                });
            });
        @endforeach
    </script>

</div>

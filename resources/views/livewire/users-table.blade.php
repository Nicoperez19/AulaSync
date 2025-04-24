<div x-data="{ page: @entangle('page') }">
    <!-- Contenedor de la tabla -->
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-black bg-gray-50 dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">RUN</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Nombre</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Correo</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Celular</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Dirección</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Fecha Nacimiento</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Año Ingreso</th>
                    <th class="p-3 border border-black dark:border-white whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                    <tr class="{{ $index % 2 === 0 ? 'bg-gray-200' : 'bg-gray-100' }}">
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->run }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->name }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->email }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->celular }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->direccion }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->fecha_nacimiento }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">{{ $user->anio_ingreso }}</td>
                        <td class="p-3 border border-black dark:border-white whitespace-nowrap">
                            <div class="flex justify-end space-x-2">
                                <x-button href="{{ route('users.edit', $user->id) }}" class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                    Editar
                                </x-button>
                                <form id="delete-form-{{ $user->id }}" action="{{ route('users.delete', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('{{ $user->id }}')" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4 flex justify-between items-center">
        <button x-on:click="page > 1 && (page--)" class="px-4 py-2 bg-gray-200 rounded-l">
            Anterior
        </button>
        <span class="px-4 py-2" x-text="page"></span>
        <button x-on:click="page++" class="px-4 py-2 bg-gray-200 rounded-r">
            Siguiente
        </button>
    </div>
</div>

<!-- Confirmación de eliminación -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(userId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + userId).submit();
            }
        });
    }

    // Mostrar mensajes de sesión
    @if(session('success'))
        Swal.fire({
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: '¡Error!',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    @endif
</script>

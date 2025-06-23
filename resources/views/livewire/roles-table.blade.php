<<<<<<< HEAD
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
=======
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

<div class="w-full p-4">
    <div class="relative overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-800 dark:border-gray-700">
        <table id="role-table" class="w-full text-center border-collapse table-auto min-w-max">
            <thead class="text-white border-b border-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 border dark:border-white whitespace-nowrap" onclick="sortTable(0)">
                        ID <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3 border dark:border-white whitespace-nowrap" onclick="sortTable(1)">
                        Nombre <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3 border dark:border-white whitespace-nowrap">Acciones</th>
>>>>>>> Nperez
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $index => $role)
<<<<<<< HEAD
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
=======
                    <tr class="{{ $index % 2 === 0 ? 'bg-gray-200 dark:bg-gray-600' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <td class="p-3 border dark:border-white whitespace-nowrap">{{ $role->id }}</td>
                        <td class="p-3 border dark:border-white whitespace-nowrap">{{ $role->name }}</td>
                        <td class="p-3 border dark:border-white whitespace-nowrap">
>>>>>>> Nperez
                            <div class="flex justify-end space-x-2">
                                <x-button href="{{ route('roles.edit', $role->id) }}" class="px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                    Editar
                                </x-button>
<<<<<<< HEAD
                                <form action="{{ route('roles.delete', $role->id) }}" method="POST" id="delete-form-{{ $role->id }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="button" onclick="confirmDelete({{ $role->id }})" variant="danger" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
=======
                                <form action="{{ route('roles.delete', $role->id) }}" method="POST" id="delete-form-{{ $role->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="button" onclick="confirmDelete({{ $role->id }})" class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
>>>>>>> Nperez
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
<<<<<<< HEAD
        // Función para confirmar la eliminación
=======
        function sortTable(columnIndex) {
            var table = document.getElementById("role-table");
            var rows = Array.from(table.rows).slice(1); // excluye thead
            var isAscending = table.rows[0].cells[columnIndex].classList.contains("asc");

            // Limpiar clases anteriores
            Array.from(table.rows[0].cells).forEach(cell => {
                cell.classList.remove("asc", "desc");
            });

            rows.sort((rowA, rowB) => {
                var cellA = rowA.cells[columnIndex].textContent.trim().toLowerCase();
                var cellB = rowB.cells[columnIndex].textContent.trim().toLowerCase();

                if (!isNaN(cellA) && !isNaN(cellB)) {
                    cellA = parseFloat(cellA);
                    cellB = parseFloat(cellB);
                }

                if (cellA < cellB) return isAscending ? 1 : -1;
                if (cellA > cellB) return isAscending ? -1 : 1;
                return 0;
            });

            rows.forEach(row => table.tBodies[0].appendChild(row));

            table.rows[0].cells[columnIndex].classList.add(isAscending ? "desc" : "asc");
        }

>>>>>>> Nperez
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
<<<<<<< HEAD
                    // Si el usuario confirma, enviar el formulario
=======
>>>>>>> Nperez
                    document.getElementById('delete-form-' + roleId).submit();
                }
            });
        }
    </script>
</div>

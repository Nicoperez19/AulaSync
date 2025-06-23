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
    <div
        class="relative overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-800 dark:border-gray-700">
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
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $index => $role)
                    <tr class="{{ $index % 2 === 0 ? 'bg-gray-200 dark:bg-gray-600' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <td class="p-3 border dark:border-white whitespace-nowrap">{{ $role->id }}</td>
                        <td class="p-3 border dark:border-white whitespace-nowrap">{{ $role->name }}</td>
                        <td class="p-3 border dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button href="{{ route('roles.edit', $role->id) }}" variant="view" class="px-4 py-2 ">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />

                                </x-button>
                                <form action="{{ route('roles.delete', $role->id) }}" method="POST"
                                    id="delete-form-{{ $role->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="button" onclick="confirmDelete({{ $role->id }})" variant="danger"
                                        class="px-4 py-2 ">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
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
                    document.getElementById('delete-form-' + roleId).submit();
                }
            });
        }
    </script>
</div>
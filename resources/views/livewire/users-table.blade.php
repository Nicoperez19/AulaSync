<div>
   
    <div class="mt-4 mb-4">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="user-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3" onclick="sortTable(0)">RUN</th>
                    <th class="p-3" onclick="sortTable(1)">Nombre</th>
                    <th class="p-3" onclick="sortTable(2)">Correo</th>
                    <th class="p-3" onclick="sortTable(3)">Celular</th>
                    <th class="p-3" onclick="sortTable(4)">Dirección</th>
                    <th class="p-3" onclick="sortTable(5)">Fecha Nacimiento</th>
                    <th class="p-3" onclick="sortTable(6)">Año Ingreso</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                    <tr class="{{ $index % 2 === 0 ? 'bg-gray-200' : 'bg-gray-100' }}">
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $user->run }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $user->name }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $user->email }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $user->celular }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $user->direccion }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $user->fecha_nacimiento }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $user->anio_ingreso }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">
                            <div class="flex justify-end space-x-2">
                                <x-button variant="primary" href="{{ route('users.edit', $user->run) }}"
                                    class="inline-flex items-center px-4 py-2 text-white bg-blue-500 rounded dark:bg-blue-700">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <form id="delete-form-{{ $user->run }}" action="{{ route('users.delete', $user->run) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button" onclick="confirmDelete('{{ $user->run }}')"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700">
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
    <div class="mt-4">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    function sortTable(columnIndex) {
        var table = document.getElementById("user-table");
        var rows = Array.from(table.rows).slice(1); 
        var isAscending = table.rows[0].cells[columnIndex].classList.contains("asc");
        
        rows.sort((rowA, rowB) => {
            var cellA = rowA.cells[columnIndex].textContent.trim();
            var cellB = rowB.cells[columnIndex].textContent.trim();

            if (columnIndex === 5 || columnIndex === 6) {
                cellA = new Date(cellA);
                cellB = new Date(cellB);
            }

            if (cellA < cellB) {
                return isAscending ? -1 : 1;
            }
            if (cellA > cellB) {
                return isAscending ? 1 : -1;
            }
            return 0;
        });

        rows.forEach(row => table.appendChild(row));

        table.rows[0].cells[columnIndex].classList.toggle("asc", !isAscending);
        table.rows[0].cells[columnIndex].classList.toggle("desc", isAscending);
    }
    function searchTable() {
        var input = document.getElementById("searchInput").value.toLowerCase();
        var table = document.getElementById("user-table");
        var rows = table.getElementsByTagName("tr");

        for (var i = 1; i < rows.length; i++) { 
            var cells = rows[i].getElementsByTagName("td");
            var run = cells[0].textContent.toLowerCase();
            var name = cells[1].textContent.toLowerCase();
            
            if (run.includes(input) || name.includes(input)) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
</script>

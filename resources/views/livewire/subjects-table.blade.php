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

<div>
    <!-- Campo de búsqueda -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
                <input type="text" 
                       wire:model.live="search" 
                       placeholder="Buscar asignaturas..." 
                       class="w-full px-4 py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
         
        </div>
    </div>

    <div class="mt-4 mb-4">
        {{ $asignaturas->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="subjects-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3" onclick="sortTable(0)">ID Asignatura <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(1)">Nombre <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(2)">Docente Responsable <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(3)">Carrera <span class="sort-icon">▼</span></th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($asignaturas as $index => $asignatura)
                    <tr class="{{ $index % 2 === 0 ? 'bg-gray-200' : 'bg-gray-100' }}">
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $asignatura->id_asignatura }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ $asignatura->nombre_asignatura }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ optional($asignatura->profesor)->name ?? 'No asignado' }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">{{ optional($asignatura->carrera)->nombre ?? 'No asignada' }}</td>
                        <td class="p-3 dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" href="{{ route('asignaturas.edit', $asignatura->id_asignatura) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <form id="delete-form-{{ $asignatura->id_asignatura }}"
                                    action="{{ route('asignaturas.destroy', $asignatura->id_asignatura) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button"
                                        onclick="deleteSubject('{{ $asignatura->id_asignatura }}')"
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
    
    <div class="mt-4">
        {{ $asignaturas->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    function sortTable(columnIndex) {
        var table = document.getElementById("subjects-table");
        var rows = Array.from(table.rows).slice(1);
        var isAscending = table.rows[0].cells[columnIndex].classList.contains("asc");

        // Remover clases de ordenamiento de todas las columnas
        Array.from(table.rows[0].cells).forEach(cell => {
            cell.classList.remove("asc", "desc");
        });

        rows.sort((rowA, rowB) => {
            var cellA = rowA.cells[columnIndex].textContent.trim();
            var cellB = rowB.cells[columnIndex].textContent.trim();

            if (cellA < cellB) {
                return isAscending ? -1 : 1;
            }
            if (cellA > cellB) {
                return isAscending ? 1 : -1;
            }
            return 0;
        });

        rows.forEach(row => table.appendChild(row));

        table.rows[0].cells[columnIndex].classList.add(isAscending ? "desc" : "asc");
    }

    function searchTable() {
        var input = document.getElementById("searchInput").value.toLowerCase();
        var table = document.getElementById("subjects-table");
        var rows = table.getElementsByTagName("tr");

        for (var i = 1; i < rows.length; i++) {
            var cells = rows[i].getElementsByTagName("td");
            var id = cells[0].textContent.toLowerCase();
            var name = cells[1].textContent.toLowerCase();
            var docente = cells[2].textContent.toLowerCase();
            var carrera = cells[3].textContent.toLowerCase();

            if (id.includes(input) || name.includes(input) || docente.includes(input) || carrera.includes(input)) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }

    function deleteSubject(id) {
        if (confirm('¿Estás seguro de que quieres eliminar esta asignatura?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
    
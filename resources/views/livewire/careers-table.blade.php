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
    <div class="mt-4">
        {{ $carreras->links('vendor.pagination.tailwind') }}
    </div>
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="careers-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3" onclick="sortTable(0)">ID Carrera <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(1)">Nombre Carrera <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(2)">Área Académica <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(3)">Facultad <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(4)">Sede <span class="sort-icon">▼</span></th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($carreras as $index => $carrera)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td
                            class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                            {{ $carrera->id_carrera }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $carrera->nombre }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $carrera->areaAcademica->nombre_area_academica ?? 'Sin Área Académica' }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $carrera->areaAcademica->facultad->nombre_facultad ?? 'Sin Facultad' }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $carrera->areaAcademica->facultad->sede->nombre_sede ?? 'Sin Sede' }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" href="{{ route('careers.edit', $carrera->id_carrera) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                </x-button>

                                <form id="delete-form-{{ $carrera->id_carrera }}"
                                    action="{{ route('careers.delete', $carrera->id_carrera) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button"
                                        onclick="deleteCarrera('{{ $carrera->id_carrera }}', '{{ $carrera->nombre }}')"
                                        class="px-4 py-2">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">No se encontraron carreras</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $carreras->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>

    function sortTable(columnIndex) {
        var table = document.getElementById("careers-table");
        var rows = Array.from(table.rows).slice(1);
        var isAscending = table.rows[0].cells[columnIndex].classList.contains("asc");

        Array.from(table.rows[0].cells).forEach(cell => {
            cell.classList.remove("asc", "desc");
        });

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

        table.rows[0].cells[columnIndex].classList.add(isAscending ? "desc" : "asc");
    }

    function deleteCarrera(id, name) {
        if (typeof Swal === 'undefined') {
            if (confirm(`¿Estás seguro de que quieres eliminar la carrera "${name}"?`)) {
                document.getElementById('delete-form-' + id).submit();
            }
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Esta acción eliminará la carrera "${name}" y no se puede deshacer`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>

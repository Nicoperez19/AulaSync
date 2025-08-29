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
    <div class="mt-4 mb-4">
        {{ $profesores->links('vendor.pagination.tailwind') }}
    </div>
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="professors-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3" onclick="sortTable(0)"> RUN
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3" onclick="sortTable(1)"> Nombre
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3" onclick="sortTable(2)"> Email
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3" onclick="sortTable(3)"> Tipo
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3" onclick="sortTable(4)"> Carrera
                        <span class="sort-icon">▼</span>
                    </th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($profesores as $index => $profesor)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                <td
                                    class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                                    {{ $profesor->run_profesor }}
                                </td>
                                <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                    {{ $profesor->name }}
                                </td>
                                <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                    {{ $profesor->email }}
                                </td>
                                <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $profesor->tipo_profesor === 'Profesor Responsable' ? 'bg-blue-100 text-blue-800' :
                    ($profesor->tipo_profesor === 'Profesor Colaborador' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $profesor->tipo_profesor }}
                                    </span>
                                </td>
                                <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                    {{ $profesor->carrera->nombre ?? 'Sin Carrera' }}
                                </td>
                                <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                    <div class="flex justify-center space-x-2">
                                        <x-button variant="view" href="{{ route('professors.edit', $profesor->run_profesor) }}"
                                            class="inline-flex items-center px-4 py-2">
                                            <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                        </x-button>

                                        <form id="delete-form-{{ $profesor->run_profesor }}"
                                            action="{{ route('professors.delete', $profesor->run_profesor) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <x-button variant="danger" type="button"
                                                onclick="deleteProfessor('{{ $profesor->run_profesor }}', '{{ $profesor->name }}')"
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
                                <p class="text-lg font-medium">No se encontraron profesores</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $profesores->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    function sortTable(columnIndex) {
        var table = document.getElementById("professors-table");
        var rows = Array.from(table.rows).slice(1);
        var isAscending = table.rows[0].cells[columnIndex].classList.contains("asc");

        // Remover clases de ordenamiento de todas las columnas
        Array.from(table.rows[0].cells).forEach(cell => {
            cell.classList.remove("asc", "desc");
        });

        rows.sort((rowA, rowB) => {
            var cellA = rowA.cells[columnIndex].textContent.trim().toLowerCase();
            var cellB = rowB.cells[columnIndex].textContent.trim().toLowerCase();

            // Para columnas numéricas (RUN)
            if (columnIndex === 0) {
                var numA = parseInt(cellA) || 0;
                var numB = parseInt(cellB) || 0;
                return isAscending ? numA - numB : numB - numA;
            }

            // Para columnas de texto
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

    function deleteProfessor(run, name) {
        // Verificar si SweetAlert2 está disponible
        if (typeof Swal === 'undefined') {
            // Fallback si SweetAlert2 no está disponible
            if (confirm(`¿Estás seguro de que quieres eliminar al profesor "${name}"?`)) {
                document.getElementById('delete-form-' + run).submit();
            }
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Esta acción eliminará al profesor "${name}" y no se puede deshacer`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + run).submit();
            }
        });
    }
</script>
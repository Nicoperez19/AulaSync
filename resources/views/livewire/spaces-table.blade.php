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
        {{ $espacios->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="spaces-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
            <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3" onclick="sortTable(0)">ID del Espacio <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(1)">Nombre del Espacio <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(2)">Facultad <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(3)">Piso <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(4)">Tipo <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(5)">Estado <span class="sort-icon">▼</span></th>
                    <th class="p-3" onclick="sortTable(6)">Puestos <span class="sort-icon">▼</span></th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($espacios as $index => $espacio)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'  }}">

                        <td
                            class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                            {{ $espacio->id_espacio }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $espacio->nombre_espacio ?? 'Sin nombre' }}

                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $espacio->piso->facultad->nombre_facultad ?? 'Sin Facultad' }}, Sede
                            {{ $espacio->piso->facultad->sede->nombre_sede ?? 'Sin nombre' }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $espacio->piso->numero_piso ?? 'Sin Piso' }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $espacio->tipo_espacio }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    @if ($espacio->estado === 'Disponible') bg-green-100 text-green-800 
                                    @elseif($espacio->estado === 'Ocupado') bg-red-100 text-red-800 
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $espacio->estado }}
                            </span>
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            {{ $espacio->puestos_disponibles ?? 'N/A' }}
                        </td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                            <div class="flex justify-center space-x-2">
                                <x-button variant="view" href="{{ route('spaces.edit', $espacio->id_espacio) }}"
                                    class="inline-flex items-center px-4 py-2">
                                    <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />

                                </x-button>
                                <a href="{{ route('spaces.download-qr', $espacio->id_espacio) }}"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-400 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                                    title="Descargar QR">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v2m0 5h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </a>
                                <form action="{{ route('spaces.delete', $espacio->id_espacio) }}" method="POST"
                                    style="display: inline;" id="delete-form-{{ $espacio->id_espacio }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" type="button"
                                        class="px-4 py-2 text-white bg-red-500 rounded dark:bg-red-700"
                                        onclick="confirmDelete('delete-form-{{ $espacio->id_espacio }}')">
                                        <x-icons.delete class="w-5 h-5" aria-hidden="true" />
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">No se encontraron espacios</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $espacios->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    function sortTable(columnIndex) {
        var table = document.getElementById("spaces-table");
        var rows = Array.from(table.rows).slice(1);
        var isAscending = table.rows[0].cells[columnIndex].classList.contains("asc");

        // Remover clases de ordenamiento de todas las columnas
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

    function confirmDelete(formId) {
        // Función confirmDelete llamada para formulario

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Usuario confirmó eliminación
                const form = document.getElementById(formId);
                // Formulario encontrado

                if (form) {
                    // Enviando formulario
                    form.submit();
                } else {
                    console.error('No se encontró el formulario con ID:', formId);
                }
            }
        });
    }


</script>
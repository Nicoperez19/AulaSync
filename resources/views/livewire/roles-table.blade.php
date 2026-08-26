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
    <div class="flex flex-col gap-4 mb-4 md:flex-row md:items-center md:justify-between">
        <div class="relative w-full md:w-1/2">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por Nombre de Rol o ID..."
                   class="w-full py-2 pl-10 pr-4 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>
        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
            <span>Total: <strong>{{ $roles->total() }}</strong> roles</span>
        </div>
    </div>

    <div class="mt-2 mb-4">
        {{ $roles->links('vendor.pagination.tailwind') }}
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
        <table id="role-table" class="w-full text-center border-collapse table-auto min-w-max">
            <thead class="text-white border-b border-white bg-light-cloud-blue dark:bg-black dark:text-white">
                <tr>
                    <th class="p-3 border dark:border-white whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('id')">
                        ID
                        @if($sortField === 'id')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3 border dark:border-white whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('name')">
                        Nombre
                        @if($sortField === 'name')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="p-3 border dark:border-white whitespace-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $index => $role)
                    <tr wire:key="role-row-{{ $role->id }}" class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'  }}">
                        <td
                            class="p-3 text-sm font-semibold text-blue-600 border border-white dark:border-white dark:text-blue-400">
                            {{ $role->id }}</td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">{{ $role->name }}</td>
                        <td class="p-3 border border-white dark:border-white whitespace-nowrap">
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
                @empty
                    <tr wire:key="empty-roles-row">
                        <td colspan="3" class="p-6 text-center text-gray-500 dark:text-gray-400">
                            No se encontraron roles que coincidan con la búsqueda "{{ $search }}".
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 mb-4">
        {{ $roles->links('vendor.pagination.tailwind') }}
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

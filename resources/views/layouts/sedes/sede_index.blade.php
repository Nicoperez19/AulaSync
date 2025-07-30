<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-map-marker-alt"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Sedes</h2>
                    <p class="text-sm text-gray-500">Administra las sedes disponibles en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por Nombre o ID"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-sede')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Sede
            </x-button>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
            <table id="sede-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
                <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                    <tr>
                        <th class="p-3" onclick="sortTable(0)">ID Sede <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(1)">Nombre Sede <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(2)">Universidad <span class="sort-icon">▼</span></th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sedes as $index => $sede)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <span class="font-mono text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $sede->id_sede }}
                                </span>
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $sede->nombre_sede }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $sede->universidad->nombre_universidad ?? 'Sin Universidad' }}
                            </td>
                          
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <x-button variant="view" href="{{ route('sedes.edit', $sede->id_sede) }}"
                                        class="inline-flex items-center px-4 py-2">
                                        <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                    </x-button>

                                    <form id="delete-form-{{ $sede->id_sede }}"
                                        action="{{ route('sedes.destroy', $sede->id_sede) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" type="button"
                                            onclick="deleteSede('{{ $sede->id_sede }}')" class="px-4 py-2">
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
    </div>

    <!-- Modal para agregar sede -->
    <x-modal name="add-sede" :show="$errors->any()" focusable>
        @slot('title')
            <div class="relative flex items-center justify-between p-2 bg-red-700">
                <div class="flex items-center gap-3">
                    <div class="p-4 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Sede
                    </h2>
                </div>
                <button @click="show = false" class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
        @endslot

        <form method="POST" action="{{ route('sedes.store') }}" class="p-6">
            @csrf

            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="id_sede" value="ID Sede *" />
                        <x-form.input id="id_sede" name="id_sede" type="text"
                            class="w-full @error('id_sede') border-red-500 @enderror" required maxlength="20"
                            placeholder="Ej: TH" value="{{ old('id_sede') }}" />
                        @error('id_sede')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_sede" value="Nombre Sede *" />
                        <x-form.input id="nombre_sede" name="nombre_sede" type="text"
                            class="w-full @error('nombre_sede') border-red-500 @enderror" required maxlength="100"
                            placeholder="Ej: Talcahuano" value="{{ old('nombre_sede') }}" />
                        @error('nombre_sede')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="id_universidad" value="Universidad *" />
                        <select name="id_universidad" id="id_universidad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_universidad') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Universidad') }}</option>
                            @foreach($universidades as $universidad)
                                <option value="{{ $universidad->id_universidad }}" {{ old('id_universidad') == $universidad->id_universidad ? 'selected' : '' }}>
                                    {{ $universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="comuna_id" value="Comuna *" />
                        <select name="comuna_id" id="comuna_id"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('comuna_id') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Comuna') }}</option>
                            @foreach($comunas as $comuna)
                                <option value="{{ $comuna->id }}" {{ old('comuna_id') == $comuna->id ? 'selected' : '' }}>
                                    {{ $comuna->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('comuna_id')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Crear Sede') }}</x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <script>
        function sortTable(columnIndex) {
            var table = document.getElementById("sede-table");
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
            var table = document.getElementById("sede-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var id = cells[0].textContent.toLowerCase();
                var name = cells[1].textContent.toLowerCase();
                var universidad = cells[2].textContent.toLowerCase();
                var comuna = cells[3].textContent.toLowerCase();

                if (id.includes(input) || name.includes(input) || universidad.includes(input) || comuna.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function deleteSede(id) {
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
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
</x-app-layout> 
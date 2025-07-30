<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-graduation-cap"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Áreas Académicas</h2>
                    <p class="text-sm text-gray-500">Administra las áreas académicas disponibles en el sistema</p>
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
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-academic-area')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Área Académica
            </x-button>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
            <table id="academic-area-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
                <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                    <tr>
                        <th class="p-3" onclick="sortTable(0)">ID Área <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(1)">Nombre Área <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(2)">Tipo <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(3)">Facultad <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(4)">Sede <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(5)">Universidad <span class="sort-icon">▼</span></th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($areasAcademicas as $index => $areaAcademica)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <span class="font-mono text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $areaAcademica->id_area_academica }}
                                </span>
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $areaAcademica->nombre_area_academica }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $areaAcademica->tipo_area_academica === 'departamento' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($areaAcademica->tipo_area_academica) }}
                                </span>
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $areaAcademica->facultad->nombre_facultad ?? 'Sin Facultad' }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $areaAcademica->facultad->sede->nombre_sede ?? 'Sin Sede' }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $areaAcademica->facultad->sede->universidad->nombre_universidad ?? 'Sin Universidad' }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <x-button variant="view" href="{{ route('academic_areas.edit', $areaAcademica->id_area_academica) }}"
                                        class="inline-flex items-center px-4 py-2">
                                        <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                    </x-button>

                                    <form id="delete-form-{{ $areaAcademica->id_area_academica }}"
                                        action="{{ route('academic_areas.delete', $areaAcademica->id_area_academica) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" type="button"
                                            onclick="deleteAreaAcademica('{{ $areaAcademica->id_area_academica }}')" class="px-4 py-2">
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

    <!-- Modal para agregar área académica -->
    <x-modal name="add-academic-area" :show="$errors->any()" focusable>
        @slot('title')
            <div class="relative bg-red-700 p-2 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-red-100 rounded-full p-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Área Académica
                    </h2>
                </div>
                <button @click="show = false" class="text-2xl font-bold text-white hover:text-gray-200 ml-2">&times;</button>
                <!-- Círculos decorativos -->
                <span class="absolute left-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                <span class="absolute right-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
            </div>
        @endslot

        <form method="POST" action="{{ route('academic_areas.add') }}" class="p-6">
            @csrf

            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="id_area_academica" value="ID Área Académica *" />
                        <x-form.input id="id_area_academica" name="id_area_academica" type="text"
                            class="w-full @error('id_area_academica') border-red-500 @enderror" required maxlength="20"
                            placeholder="Ej: ESC_EDUSAL" value="{{ old('id_area_academica') }}" />
                        @error('id_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_area_academica" value="Nombre Área Académica *" />
                        <x-form.input id="nombre_area_academica" name="nombre_area_academica" type="text"
                            class="w-full @error('nombre_area_academica') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: Escuela de Educación y Salud" value="{{ old('nombre_area_academica') }}" />
                        @error('nombre_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="tipo_area_academica" value="Tipo de Área *" />
                        <select name="tipo_area_academica" id="tipo_area_academica"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('tipo_area_academica') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Tipo') }}</option>
                            <option value="departamento" {{ old('tipo_area_academica') == 'departamento' ? 'selected' : '' }}>Departamento</option>
                            <option value="escuela" {{ old('tipo_area_academica') == 'escuela' ? 'selected' : '' }}>Escuela</option>
                        </select>
                        @error('tipo_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_facultad" value="Facultad *" />
                        <select name="id_facultad" id="id_facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_facultad') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Facultad') }}</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}" {{ old('id_facultad') == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }} - {{ $facultad->sede->nombre_sede ?? 'Sin Sede' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_facultad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Crear Área Académica') }}</x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <script>
        function sortTable(columnIndex) {
            var table = document.getElementById("academic-area-table");
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
            var table = document.getElementById("academic-area-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var id = cells[0].textContent.toLowerCase();
                var name = cells[1].textContent.toLowerCase();
                var tipo = cells[2].textContent.toLowerCase();
                var facultad = cells[3].textContent.toLowerCase();
                var sede = cells[4].textContent.toLowerCase();
                var universidad = cells[5].textContent.toLowerCase();

                if (id.includes(input) || name.includes(input) || tipo.includes(input) || facultad.includes(input) || sede.includes(input) || universidad.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function deleteAreaAcademica(id) {
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

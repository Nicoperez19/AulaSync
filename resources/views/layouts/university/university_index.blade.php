<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-university"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Universidades</h2>
                    <p class="text-sm text-gray-500">Administra las universidades registradas en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por ID, Nombre o Dirección"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-university')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Universidad
            </x-button>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
            <table id="university-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
                <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                    <tr>
                        <th class="p-3" onclick="sortTable(0)">ID <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(1)">Nombre <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(2)">Dirección <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(3)">Teléfono <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(4)">Comuna <span class="sort-icon">▼</span></th>
                        <th class="p-3">Logo</th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($universidades as $index => $universidad)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <span class="font-mono text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $universidad->id_universidad }}
                                </span>
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $universidad->nombre_universidad }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $universidad->direccion_universidad }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $universidad->telefono_universidad }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $universidad->comuna->nombre_comuna ?? 'Sin Comuna' }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                @if ($universidad->imagen_logo)
                                    <img src="{{ asset('images/logo_universidad/' . $universidad->imagen_logo) }}" 
                                         alt="Logo {{ $universidad->nombre_universidad }}" 
                                         class="w-12 h-12 object-contain mx-auto rounded">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center mx-auto">
                                        <i class="fa-solid fa-image text-gray-400"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <div class="flex justify-center space-x-2">
                                    <x-button variant="view" href="{{ route('universities.edit', $universidad->id_universidad) }}"
                                        class="inline-flex items-center px-4 py-2">
                                        <x-icons.edit class="w-5 h-5 mr-1" aria-hidden="true" />
                                    </x-button>

                                    <form id="delete-form-{{ $universidad->id_universidad }}"
                                        action="{{ route('universities.delete', $universidad->id_universidad) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" type="button"
                                            onclick="deleteUniversity('{{ $universidad->id_universidad }}', '{{ $universidad->nombre_universidad }}')" class="px-4 py-2">
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

    <!-- Modal para agregar universidad -->
    <x-modal name="add-university" :show="$errors->any()" focusable>
        @slot('title')
            <div class="relative bg-red-700 p-2 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-red-100 rounded-full p-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Universidad
                    </h2>
                </div>
                <button @click="show = false" class="text-2xl font-bold text-white hover:text-gray-200 ml-2">&times;</button>
                <!-- Círculos decorativos -->
                <span class="absolute left-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                <span class="absolute right-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
            </div>
        @endslot

        <form method="POST" action="{{ route('universities.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="id_universidad" value="ID Universidad *" />
                        <x-form.input id="id_universidad" name="id_universidad" type="text"
                            class="w-full @error('id_universidad') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: UCH" value="{{ old('id_universidad') }}" />
                        @error('id_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_universidad" value="Nombre Universidad *" />
                        <x-form.input id="nombre_universidad" name="nombre_universidad" type="text"
                            class="w-full @error('nombre_universidad') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: Universidad de Chile" value="{{ old('nombre_universidad') }}" />
                        @error('nombre_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="direccion_universidad" value="Dirección Universidad *" />
                        <x-form.input id="direccion_universidad" name="direccion_universidad" type="text"
                            class="w-full @error('direccion_universidad') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: Av. Libertador Bernardo O'Higgins 1058" value="{{ old('direccion_universidad') }}" />
                        @error('direccion_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="telefono_universidad" value="Teléfono Universidad *" />
                        <x-form.input id="telefono_universidad" name="telefono_universidad" type="text"
                            class="w-full @error('telefono_universidad') border-red-500 @enderror" required maxlength="15"
                            pattern="[0-9+]+" placeholder="Ej: +56229781234" value="{{ old('telefono_universidad') }}" />
                        @error('telefono_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="comunas_id" value="Comuna *" />
                        <select name="comunas_id" id="comunas_id"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('comunas_id') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Comuna') }}</option>
                            @foreach($comunas as $comuna)
                                <option value="{{ $comuna->id }}" {{ old('comunas_id') == $comuna->id ? 'selected' : '' }}>
                                    {{ $comuna->nombre_comuna }}
                                </option>
                            @endforeach
                        </select>
                        @error('comunas_id')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="imagen_logo" value="Logo Universidad" />
                        <x-form.input id="imagen_logo" name="imagen_logo" type="file"
                            class="w-full @error('imagen_logo') border-red-500 @enderror" accept="image/*" />
                        <p class="text-xs text-gray-500">Formatos: JPG, JPEG, PNG, GIF. Máximo 2MB</p>
                        @error('imagen_logo')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Crear Universidad') }}</x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <script>
        function sortTable(columnIndex) {
            var table = document.getElementById("university-table");
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
            var table = document.getElementById("university-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var id = cells[0].textContent.toLowerCase();
                var name = cells[1].textContent.toLowerCase();
                var address = cells[2].textContent.toLowerCase();
                var phone = cells[3].textContent.toLowerCase();
                var comuna = cells[4].textContent.toLowerCase();

                if (id.includes(input) || name.includes(input) || address.includes(input) || phone.includes(input) || comuna.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function deleteUniversity(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Esta acción eliminará la universidad "${name}" y no se puede deshacer`,
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

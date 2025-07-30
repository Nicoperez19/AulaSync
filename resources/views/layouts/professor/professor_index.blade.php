<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-chalkboard-teacher"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Profesores</h2>
                    <p class="text-sm text-gray-500">Administra los profesores registrados en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por RUN, Nombre o Email"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-professor')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Profesor
            </x-button>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
            <table id="professor-table" class="w-full text-sm text-center border-collapse table-auto min-w-max">
                <thead class="text-white bg-light-cloud-blue dark:bg-black dark:text-white">
                    <tr>
                        <th class="p-3" onclick="sortTable(0)">RUN <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(1)">Nombre <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(2)">Email <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(3)">Tipo <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(4)">Universidad <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(5)">Facultad <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(6)">Carrera <span class="sort-icon">▼</span></th>
                        <th class="p-3" onclick="sortTable(7)">Área Académica <span class="sort-icon">▼</span></th>
                        <th class="p-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($profesores as $index => $profesor)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <span class="font-mono text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $profesor->run_profesor }}
                                </span>
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $profesor->name }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $profesor->email }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $profesor->tipo_profesor === 'Profesor Responsable' ? 'bg-blue-100 text-blue-800' : 
                                       ($profesor->tipo_profesor === 'Profesor Colaborador' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $profesor->tipo_profesor }}
                                </span>
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $profesor->universidad->nombre_universidad ?? 'Sin Universidad' }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $profesor->facultad->nombre_facultad ?? 'Sin Facultad' }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $profesor->carrera->nombre ?? 'Sin Carrera' }}
                            </td>
                            <td class="p-3 border border-white dark:border-white whitespace-nowrap">
                                {{ $profesor->areaAcademica->nombre_area_academica ?? 'Sin Área Académica' }}
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
                                            onclick="deleteProfessor('{{ $profesor->run_profesor }}', '{{ $profesor->name }}')" class="px-4 py-2">
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

    <!-- Modal para agregar profesor -->
    <x-modal name="add-professor" :show="$errors->any()" focusable>
        @slot('title')
            <div class="relative bg-red-700 p-2 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-red-100 rounded-full p-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Profesor
                    </h2>
                </div>
                <button @click="show = false" class="text-2xl font-bold text-white hover:text-gray-200 ml-2">&times;</button>
                <!-- Círculos decorativos -->
                <span class="absolute left-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
                <span class="absolute right-0 top-0 w-32 h-32 bg-white bg-opacity-10 rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></span>
            </div>
        @endslot

        <form method="POST" action="{{ route('professors.add') }}" class="p-6">
            @csrf
            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="run_profesor" value="RUN *" />
                        <x-form.input id="run_profesor" name="run_profesor" type="text"
                            class="w-full @error('run_profesor') border-red-500 @enderror" required maxlength="8" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Ej: 12345678"
                            value="{{ old('run_profesor') }}" />
                        @error('run_profesor')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="name" value="Nombre *" />
                        <x-form.input id="name" name="name" type="text"
                            class="w-full @error('name') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: Juan Pérez González" value="{{ old('name') }}" />
                        @error('name')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="email" value="Email *" />
                        <x-form.input id="email" name="email" type="email"
                            class="w-full @error('email') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: juan.perez@email.com" value="{{ old('email') }}" />
                        @error('email')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="celular" value="Celular" />
                        <x-form.input id="celular" name="celular" type="text"
                            class="w-full @error('celular') border-red-500 @enderror" maxlength="9"
                            pattern="9[0-9]{8}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            placeholder="Ej: 912345678" value="{{ old('celular') }}" />
                        @error('celular')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="fecha_nacimiento" value="Fecha de Nacimiento" />
                        <x-form.input id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                            class="w-full @error('fecha_nacimiento') border-red-500 @enderror"
                            value="{{ old('fecha_nacimiento') }}" />
                        @error('fecha_nacimiento')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="anio_ingreso" value="Año de Ingreso" />
                        <x-form.input id="anio_ingreso" name="anio_ingreso" type="number"
                            class="w-full @error('anio_ingreso') border-red-500 @enderror" min="1900" max="{{ date('Y') + 1 }}"
                            placeholder="Ej: 2020" value="{{ old('anio_ingreso') }}" />
                        @error('anio_ingreso')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <x-form.label for="direccion" value="Dirección" />
                    <x-form.input id="direccion" name="direccion" type="text"
                        class="w-full @error('direccion') border-red-500 @enderror" maxlength="255"
                        placeholder="Ej: Av. Principal 123, Ciudad" value="{{ old('direccion') }}" />
                    @error('direccion')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="tipo_profesor" value="Tipo de Profesor *" />
                        <select name="tipo_profesor" id="tipo_profesor"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('tipo_profesor') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Tipo') }}</option>
                            <option value="Profesor Responsable" {{ old('tipo_profesor') == 'Profesor Responsable' ? 'selected' : '' }}>Profesor Responsable</option>
                            <option value="Profesor Colaborador" {{ old('tipo_profesor') == 'Profesor Colaborador' ? 'selected' : '' }}>Profesor Colaborador</option>
                            <option value="Ayudante" {{ old('tipo_profesor') == 'Ayudante' ? 'selected' : '' }}>Ayudante</option>
                        </select>
                        @error('tipo_profesor')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_universidad" value="Universidad" />
                        <select name="id_universidad" id="id_universidad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_universidad') border-red-500 @enderror">
                            <option value="" selected>{{ __('Seleccionar Universidad') }}</option>
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
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="id_facultad" value="Facultad" />
                        <select name="id_facultad" id="id_facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_facultad') border-red-500 @enderror">
                            <option value="" selected>{{ __('Seleccionar Facultad') }}</option>
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

                    <div class="space-y-2">
                        <x-form.label for="id_carrera" value="Carrera" />
                        <select name="id_carrera" id="id_carrera"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_carrera') border-red-500 @enderror">
                            <option value="" selected>{{ __('Seleccionar Carrera') }}</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}" {{ old('id_carrera') == $carrera->id_carrera ? 'selected' : '' }}>
                                    {{ $carrera->nombre }} - {{ $carrera->areaAcademica->nombre_area_academica ?? 'Sin Área' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_carrera')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <x-form.label for="id_area_academica" value="Área Académica" />
                    <select name="id_area_academica" id="id_area_academica"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_area_academica') border-red-500 @enderror">
                        <option value="" selected>{{ __('Seleccionar Área Académica') }}</option>
                        @foreach($areasAcademicas as $areaAcademica)
                            <option value="{{ $areaAcademica->id_area_academica }}" {{ old('id_area_academica') == $areaAcademica->id_area_academica ? 'selected' : '' }}>
                                {{ $areaAcademica->nombre_area_academica }} - {{ $areaAcademica->facultad->nombre_facultad ?? 'Sin Facultad' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_area_academica')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Crear Profesor') }}</x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <script>
        function sortTable(columnIndex) {
            var table = document.getElementById("professor-table");
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
            var table = document.getElementById("professor-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var run = cells[0].textContent.toLowerCase();
                var name = cells[1].textContent.toLowerCase();
                var email = cells[2].textContent.toLowerCase();
                var tipo = cells[3].textContent.toLowerCase();
                var universidad = cells[4].textContent.toLowerCase();
                var facultad = cells[5].textContent.toLowerCase();
                var carrera = cells[6].textContent.toLowerCase();
                var areaAcademica = cells[7].textContent.toLowerCase();

                if (run.includes(input) || name.includes(input) || email.includes(input) || tipo.includes(input) || universidad.includes(input) || facultad.includes(input) || carrera.includes(input) || areaAcademica.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function deleteProfessor(run, name) {
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
</x-app-layout> 
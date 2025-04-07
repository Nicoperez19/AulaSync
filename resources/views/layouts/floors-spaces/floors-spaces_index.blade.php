<x-app-layout>
    {{-- ENCABEZADO --}}
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl italic font-semibold leading-tight">
                {{ __('Facultades, Pisos y Espacios') }}
            </h2>
        </div>
    </x-slot>

    {{-- CONTENIDO PRINCIPAL --}}
    <div class="grid grid-cols-1 gap-6 p-6 space-y-6 bg-gray-100 rounded-lg shadow-lg md:grid-cols-2">

        {{-- COLUMNA 1: Listado de Facultades y Pisos --}}
        <div class="space-y-6">
            <h3 class="text-lg font-semibold">Listado de Facultades</h3>
            <div class="p-4 bg-white rounded shadow">
                <form method="GET" action="{{ route('floors_spaces.index') }}" class="space-y-4">
                    {{-- Filtro de Universidad --}}
                    <div>
                        <label for="universidad" class="block text-sm font-medium text-gray-700">Universidad</label>
                        <select id="universidad" name="universidad"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
                            onchange="this.form.submit()">
                            <option value="" disabled selected>Selecciona una Universidad</option>
                            @foreach ($universidades as $universidad)
                                <option value="{{ $universidad->id_universidad }}" {{ request('universidad') == $universidad->id_universidad ? 'selected' : '' }}>
                                    {{ $universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Botones de Filtrar y Limpiar --}}
                    <div class="flex justify-end gap-2 pt-4">
                        <a href="{{ route('floors_spaces.index') }}"
                            class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            {{-- Listado de Facultades --}}
            <div class="p-4 bg-white border rounded">
                <h4 class="mb-4 text-lg font-semibold">Facultades</h4>
                <div class="overflow-x-auto">
                    <table class="w-full border border-collapse rounded">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left border">Facultad</th>
                                <th class="px-4 py-2 text-left border">Número de Pisos</th>
                                <th class="px-4 py-2 text-center border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($facultades as $facultad)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $facultad->nombre_facultad }}</td>
                                    <td class="px-4 py-2 border">{{ $facultad->pisos->count() }}</td>
                                    <td class="px-4 py-2 text-center border">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('pisos.create', ['facultad_id' => $facultad->id_facultad]) }}"
                                                class="px-3 py-2 text-white bg-green-500 rounded hover:bg-green-600"
                                                title="Agregar Piso">
                                                +
                                            </a>
                                            <a href="{{ route('pisos.index', ['facultad_id' => $facultad->id_facultad]) }}"
                                                class="px-3 py-2 text-white bg-red-500 rounded hover:bg-red-600"
                                                title="Quitar Piso">
                                                -
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- COLUMNA 2: CRUD de Espacios --}}
        <div class="space-y-6">
            <h3 class="text-lg font-semibold">Gestión de Espacios</h3>
            <div class="p-4 space-y-4 bg-white rounded shadow">
                <form method="GET" action="{{ route('floors_spaces.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="filtro_facultad" class="block text-sm font-medium text-gray-700">Filtrar por Facultad</label>
                        <select id="filtro_facultad" name="facultad"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
                            onchange="fetchPisos(this.value); this.form.submit()">
                            <option value="" selected>Todas las Facultades</option>
                            @foreach ($todasLasFacultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}" {{ request('facultad') == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filtro_piso" class="block text-sm font-medium text-gray-700">Filtrar por Piso</label>
                        <select id="filtro_piso" name="piso"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
                            onchange="this.form.submit()">
                            <option value="" selected>Todos los Pisos</option>
                            @if (isset($pisosDeFacultad))
                                @foreach ($pisosDeFacultad as $piso)
                                    <option value="{{ $piso->id_piso }}" {{ request('piso') == $piso->id_piso ? 'selected' : '' }}>
                                        {{ $piso->nombre }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </form>

                {{-- Tabla de Espacios --}}
                <div class="overflow-x-auto">
                    <table class="w-full border border-collapse rounded">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700">Nombre del Espacio</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700">Piso</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700">Facultad</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700">Universidad</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($espaciosFiltrados as $espacio)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $espacio->nombre }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $espacio->piso->nombre }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $espacio->facultad->nombre_facultad }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $espacio->universidad->nombre_universidad }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <a href="{{ route('espacios.edit', $espacio->id) }}"
                                            class="text-indigo-500 hover:text-indigo-600">
                                            Editar
                                        </a>
                                        <form action="{{ route('espacios.destroy', $espacio->id) }}" method="POST"
                                            class="inline-block ml-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-600">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Botón para abrir el modal de crear espacio --}}
                <div class="flex justify-end pt-4">
                    <button id="openModalButton" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                        Crear Espacio
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal para crear un nuevo espacio --}}
    <div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-800 bg-opacity-50">
        <div class="w-1/3 p-6 bg-white rounded-lg shadow-lg">
            <h3 class="mb-4 text-xl font-semibold">Crear Espacio</h3>

            <form method="POST" action="{{ route('espacios.store') }}" class="space-y-4">
                @csrf
                {{-- Nombre del Espacio --}}
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Espacio</label>
                    <input type="text" id="nombre" name="nombre" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
                </div>

                {{-- Piso --}}
                <div>
                    <label for="id_piso" class="block text-sm font-medium text-gray-700">Piso</label>
                    <select id="id_piso" name="id_piso" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
                        @foreach ($pisos as $piso)
                            <option value="{{ $piso->id_piso }}">{{ $piso->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Facultad --}}
                <div>
                    <label for="id_facultad" class="block text-sm font-medium text-gray-700">Facultad</label>
                    <select id="id_facultad" name="id_facultad" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
                        @foreach ($facultades as $facultad)
                            <option value="{{ $facultad->id_facultad }}">{{ $facultad->nombre_facultad }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Estado --}}
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select id="estado" name="estado" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
                        <option value="Disponible">Disponible</option>
                        <option value="Ocupado">Ocupado</option>
                        <option value="Reservado">Reservado</option>
                    </select>
                </div>

                {{-- Puestos Disponibles --}}
                <div>
                    <label for="puestos_disponibles" class="block text-sm font-medium text-gray-700">Puestos Disponibles</label>
                    <input type="number" id="puestos_disponibles" name="puestos_disponibles" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" id="closeModalButton" class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400">Cerrar</button>
                    <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">Crear Espacio</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mostrar el modal
        document.getElementById('openModalButton').addEventListener('click', function() {
            document.getElementById('createModal').classList.remove('hidden');
        });

        // Cerrar el modal
        document.getElementById('closeModalButton').addEventListener('click', function() {
            document.getElementById('createModal').classList.add('hidden');
        });
    </script>

</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-building"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Espacios</h2>
                    <p class="text-sm text-gray-500">Administra los espacios físicos disponibles en el sistema</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button href="{{ route('spaces_index') }}" 
                   class="inline-flex items-center px-4 py-2 text-m font-medium  border border-gray-300 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>
    <div class="p-6 bg-white rounded-lg shadow-lg">

        <form id="edit-space-form" action="{{ route('spaces.update', $espacio->id_espacio) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Campos hidden con valores por defecto -->
            <input type="hidden" name="estado" value="Disponible">

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_espacio" :value="__('ID del Espacio')" />
                        <x-form.input id="id_espacio" class="block w-full" type="text" name="id_espacio"
                            value="{{ old('id_espacio', $espacio->id_espacio) }}" required />
                    </div>

                    <div>
                        <x-form.label for="nombre_espacio" :value="__('Nombre del Espacio')" />
                        <x-form.input id="nombre_espacio" class="block w-full" type="text" name="nombre_espacio"
                            value="{{ old('nombre_espacio', $espacio->nombre_espacio) }}" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="universidad" :value="__('Universidad')" />
                        <select name="id_universidad" id="universidad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required onchange="loadSedes()">
                            <option value="" disabled>{{ __('Seleccionar Universidad') }}</option>
                            @foreach($universidades as $universidad)
                                <option value="{{ $universidad->id_universidad }}" 
                                    {{ $espacio->piso->facultad->sede->id_universidad == $universidad->id_universidad ? 'selected' : '' }}>
                                    {{ $universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="sede" :value="__('Sede')" />
                        <select name="id_sede" id="sede"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                           required onchange="loadFacultades()">
                            <option value="" disabled>{{ __('Seleccionar Sede') }}</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}" 
                                    {{ $espacio->piso->facultad->id_sede == $sede->id_sede ? 'selected' : '' }}>
                                    {{ $sede->nombre_sede }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="facultad" :value="__('Facultad')" />
                        <select name="id_facultad" id="facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required onchange="loadPisos()">
                            <option value="" disabled>{{ __('Seleccionar Facultad') }}</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}" 
                                    {{ $espacio->piso->id_facultad == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="piso_id" :value="__('Piso')" />
                        <select name="piso_id" id="piso_id"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required>
                            @foreach ($pisos as $piso)
                                <option value="{{ $piso->id }}"
                                    {{ $piso->id == $espacio->piso_id ? 'selected' : '' }}>
                                    Piso {{ $piso->numero_piso }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="tipo_espacio" :value="__('Tipo de Espacio')" />
                        <select name="tipo_espacio" id="tipo_espacio"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required>
                            @foreach (['Aula', 'Laboratorio', 'Biblioteca', 'Sala de Reuniones', 'Oficinas'] as $tipo)
                                <option value="{{ $tipo }}"
                                    {{ $espacio->tipo_espacio == $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="puestos_disponibles" :value="__('Puestos Disponibles')" />
                        <x-form.input id="puestos_disponibles" class="block w-full" type="number"
                            name="puestos_disponibles"
                            value="{{ old('puestos_disponibles', $espacio->puestos_disponibles) }}" min="1"
                            step="1" />
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Guardar Cambios') }}</x-button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-space-form');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Seguro de editar?',
                        text: "Estás a punto de guardar los cambios.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, editar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }

            // Validación de puestos disponibles
            const puestosInput = document.getElementById('puestos_disponibles');
            if (puestosInput) {
                puestosInput.addEventListener('input', function() {
                    const value = parseInt(this.value);
                    if (value < 1) {
                        this.value = 1;
                    }
                });

                puestosInput.addEventListener('blur', function() {
                    const value = parseInt(this.value);
                    if (value < 1 || isNaN(value)) {
                        this.value = 1;
                    }
                });
            }
        });

        function loadSedes() {
            const universidadId = document.getElementById('universidad').value;
            const sedeSelect = document.getElementById('sede');
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('piso_id');

            // Limpiar y deshabilitar selectores dependientes
            sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
            sedeSelect.disabled = true;
            facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
            facultadSelect.disabled = true;
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!universidadId) return;

            fetch(`/sedes/${universidadId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";

                    if (data && data.length > 0) {
                        data.forEach(sede => {
                            const option = document.createElement("option");
                            option.value = sede.id_sede;
                            option.textContent = sede.nombre_sede;
                            sedeSelect.appendChild(option);
                        });
                        sedeSelect.disabled = false;
                    } else {
                        sedeSelect.innerHTML = "<option value=''>No hay sedes disponibles</option>";
                        sedeSelect.disabled = true;
                    }
                })
                .catch(error => {
                    sedeSelect.innerHTML = "<option value=''>Error cargando sedes</option>";
                    sedeSelect.disabled = true;
                });
        }

        function loadFacultades() {
            const sedeId = document.getElementById('sede').value;
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('piso_id');

            // Limpiar y deshabilitar selectores dependientes
            facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
            facultadSelect.disabled = true;
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!sedeId) return;

            fetch(`/facultades-por-sede/${sedeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";

                    if (data && data.length > 0) {
                        data.forEach(facultad => {
                            const option = document.createElement("option");
                            option.value = facultad.id_facultad;
                            option.textContent = facultad.nombre_facultad;
                            facultadSelect.appendChild(option);
                        });
                        facultadSelect.disabled = false;
                    } else {
                        facultadSelect.innerHTML = "<option value=''>No hay facultades disponibles</option>";
                        facultadSelect.disabled = true;
                    }
                })
                .catch(error => {
                    facultadSelect.innerHTML = "<option value=''>Error cargando facultades</option>";
                    facultadSelect.disabled = true;
                });
        }

        function loadPisos() {
            const facultadId = document.getElementById('facultad').value;
            const pisoSelect = document.getElementById('piso_id');

            // Limpiar selector de pisos
            pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
            pisoSelect.disabled = true;

            if (!facultadId) return;

            fetch(`/pisos/${facultadId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";

                    if (data && data.length > 0) {
                        data.forEach(piso => {
                            const option = document.createElement("option");
                            option.value = piso.id;
                            option.textContent = `Piso ${piso.numero_piso}`;
                            pisoSelect.appendChild(option);
                        });
                        pisoSelect.disabled = false;
                    } else {
                        pisoSelect.innerHTML = "<option value=''>No hay pisos disponibles</option>";
                        pisoSelect.disabled = true;
                    }
                })
                .catch(error => {
                    pisoSelect.innerHTML = "<option value=''>Error cargando pisos</option>";
                    pisoSelect.disabled = true;
                });
        }
    </script>
</x-app-layout>

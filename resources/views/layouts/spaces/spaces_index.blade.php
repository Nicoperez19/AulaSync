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

        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mt-4">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por Nombre o ID"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <div class="flex gap-2">
                <x-button variant="add" class="justify-end max-w-xs gap-2"
                    x-on:click.prevent="$dispatch('open-modal', 'add-espacio')">
                    <x-icons.add class="w-6 h-6" aria-hidden="true" />
                    Agregar Espacio
                </x-button>
                <a href="{{ route('spaces.download-all-qr') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-orange-400 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Descargar QRs
                </a>
            </div>
        </div>
        <livewire:spaces-table />
        <x-modal name="add-espacio" :show="$errors->any()" focusable>
            @slot('title')
            <div class="relative flex items-center justify-between p-2 bg-red-700">
                <div class="flex items-center gap-3">
                    <div class="p-4 bg-red-100 rounded-full">
                        <x-icons.building-office class="w-6 h-6 text-red-600" aria-hidden="true" rt />

                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Espacio </h2>
                </div>
                <button @click="show = false"
                    class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                <!-- Círculos decorativos -->
                <span
                    class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span
                    class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
            @endslot

            <form method="POST" action="{{ route('spaces.store') }}">
                @csrf

                <!-- Campos hidden con valores por defecto -->
                <input type="hidden" name="estado" value="Disponible">

                <div class="p-6 space-y-6">
                    <!-- Información básica del espacio -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Información del Espacio
                        </h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-form.label for="id_espacio" :value="__('ID del Espacio')"
                                    class="font-medium text-left text-gray-700" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <x-icons.user class="w-5 h-5 text-gray-400" aria-hidden="true" />
                                    </x-slot>
                                    <x-form.input id="id_espacio" class="block w-full" type="text" name="id_espacio"
                                        :value="old('id_espacio')" placeholder="{{ __('Ej: ESP-001') }}" required />
                                </x-form.input-with-icon-wrapper>
                            </div>

                            <div class="space-y-2">
                                <x-form.label for="nombre_espacio" :value="__('Nombre del Espacio')"
                                    class="font-medium text-left text-gray-700" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <x-icons.building-office class="w-5 h-5 text-gray-400" aria-hidden="true" />
                                    </x-slot>
                                    <x-form.input id="nombre_espacio" class="block w-full" type="text"
                                        name="nombre_espacio" :value="old('nombre_espacio')"
                                        placeholder="{{ __('Ej: Laboratorio de Computación') }}" required />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Ubicación</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-form.label for="universidad" :value="__('Universidad')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="id_universidad" id="universidad"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadSedes()">
                                    <option value="" disabled selected>{{ __('Seleccionar Universidad') }}</option>
                                    @foreach($universidades as $universidad)
                                        <option value="{{ $universidad->id_universidad }}" {{ old('id_universidad') == $universidad->id_universidad ? 'selected' : '' }}>
                                            {{ $universidad->nombre_universidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <x-form.label for="sede" :value="__('Sede')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="id_sede" id="sede"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadFacultades()" disabled>
                                    <option value="" disabled selected>{{ __('Seleccionar Sede') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-form.label for="facultad" :value="__('Facultad')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="id_facultad" id="facultad"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadPisos()" disabled>
                                    <option value="" disabled selected>{{ __('Seleccionar Facultad') }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <x-form.label for="selectedPiso" :value="__('Piso')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="piso_id" id="selectedPiso"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required disabled>
                                    <option value="" disabled selected>{{ __('Seleccionar Piso') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Características del espacio -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Características</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-form.label for="tipo_espacio" :value="__('Tipo de Espacio')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="tipo_espacio" id="tipo_espacio"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required>
                                    <option value="" disabled selected>{{ __('Seleccionar Tipo de Espacio') }}</option>
                                    <option value="Aula" {{ old('tipo_espacio') == 'Aula' ? 'selected' : '' }}>
                                        {{ __('Aula') }}
                                    </option>
                                    <option value="Laboratorio" {{ old('tipo_espacio') == 'Laboratorio' ? 'selected' : '' }}>
                                        {{ __('Laboratorio') }}
                                    </option>
                                    <option value="Biblioteca" {{ old('tipo_espacio') == 'Biblioteca' ? 'selected' : '' }}>
                                        {{ __('Biblioteca') }}
                                    </option>
                                    <option value="Sala de Reuniones" {{ old('tipo_espacio') == 'Sala de Reuniones' ? 'selected' : '' }}>
                                        {{ __('Sala de Reuniones') }}
                                    </option>
                                    <option value="Oficinas" {{ old('tipo_espacio') == 'Oficinas' ? 'selected' : '' }}>
                                        {{ __('Oficinas') }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <x-form.label for="puestos_disponibles" :value="__('Puestos Disponibles')"
                                    class="font-medium text-left text-gray-700" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <x-icons.user class="w-5 h-5 text-gray-400" aria-hidden="true" />
                                    </x-slot>
                                    <x-form.input id="puestos_disponibles" class="block w-full" type="number"
                                        name="puestos_disponibles" :value="old('puestos_disponibles')"
                                        placeholder="{{ __('Puestos Disponibles') }}" min="1" step="1" />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de acción -->
                    <div class="flex justify-end pt-6 border-t border-gray-200">
                        <x-button class="gap-2 bg-red-600 hover:bg-red-700 focus:ring-red-500">
                            <x-icons.add class="w-5 h-5" aria-hidden="true" />
                            <span>{{ __('Agregar Espacio') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // No cargar pisos automáticamente, se cargarán cuando se seleccione facultad
        });
        function searchTable() {
            var input = document.getElementById("searchInput").value.toLowerCase();
            var table = document.getElementById("spaces-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var run = cells[0].textContent.toLowerCase();
                var name = cells[1].textContent.toLowerCase();
                var email = cells[2].textContent.toLowerCase();

                if (run.includes(input) || name.includes(input) || email.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function loadSedes() {
            const universidadId = document.getElementById('universidad').value;
            const sedeSelect = document.getElementById('sede');
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('selectedPiso');

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
                    console.error('Error cargando sedes:', error);
                    sedeSelect.innerHTML = "<option value=''>Error cargando sedes</option>";
                    sedeSelect.disabled = true;
                });
        }

        function loadFacultades() {
            const sedeId = document.getElementById('sede').value;
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('selectedPiso');

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
                    console.error('Error cargando facultades:', error);
                    facultadSelect.innerHTML = "<option value=''>Error cargando facultades</option>";
                    facultadSelect.disabled = true;
                });
        }

        function loadPisos() {
            const facultadId = document.getElementById('facultad').value;
            const pisoSelect = document.getElementById('selectedPiso');

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
                    console.error('Error cargando pisos:', error);
                    pisoSelect.innerHTML = "<option value=''>Error cargando pisos</option>";
                    pisoSelect.disabled = true;
                });
        }

        // Validación de puestos disponibles
        document.addEventListener('DOMContentLoaded', function () {
            const puestosInput = document.getElementById('puestos_disponibles');
            if (puestosInput) {
                puestosInput.addEventListener('input', function () {
                    const value = parseInt(this.value);
                    if (value < 1) {
                        this.value = 1;
                    }
                });

                puestosInput.addEventListener('blur', function () {
                    const value = parseInt(this.value);
                    if (value < 1 || isNaN(value)) {
                        this.value = 1;
                    }
                });
            }
        });

        // Recargar datos cuando se abre el modal
        document.addEventListener('livewire:load', function () {
            // Escuchar el evento de apertura del modal
            window.addEventListener('open-modal', function (event) {
                if (event.detail === 'add-espacio') {
                    // Resetear todos los selectores
                    const universidadSelect = document.getElementById('universidad');
                    const sedeSelect = document.getElementById('sede');
                    const facultadSelect = document.getElementById('facultad');
                    const pisoSelect = document.getElementById('selectedPiso');

                    universidadSelect.value = '';
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
                    sedeSelect.disabled = true;
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
                    facultadSelect.disabled = true;
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
                    pisoSelect.disabled = true;
                }
            });
        });

        // También resetear cuando se hace clic en el botón de agregar
        document.addEventListener('click', function (event) {
            if (event.target.closest('[x-on\\:click*="open-modal"]')) {
                setTimeout(() => {
                    // Resetear todos los selectores
                    const universidadSelect = document.getElementById('universidad');
                    const sedeSelect = document.getElementById('sede');
                    const facultadSelect = document.getElementById('facultad');
                    const pisoSelect = document.getElementById('selectedPiso');

                    universidadSelect.value = '';
                    sedeSelect.innerHTML = "<option value=''>Seleccione una sede</option>";
                    sedeSelect.disabled = true;
                    facultadSelect.innerHTML = "<option value=''>Seleccione una facultad</option>";
                    facultadSelect.disabled = true;
                    pisoSelect.innerHTML = "<option value=''>Seleccione un piso</option>";
                    pisoSelect.disabled = true;
                }, 100);
            }
        });
    </script>

</x-app-layout>
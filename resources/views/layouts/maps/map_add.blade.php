<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Crear Nuevo Mapa') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Universidad -->
            <div>
                <label for="universidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Universidad</label>
                <select id="universidad" name="universidad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Seleccione una universidad</option>
                    @foreach($universidades as $universidad)
                        <option value="{{ $universidad->id_universidad }}">{{ $universidad->nombre_universidad }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sede -->
            <div>
                <label for="sede" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sede</label>
                <select id="sede" name="sede" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" disabled>
                    <option value="">Seleccione una sede</option>
                </select>
            </div>

            <!-- Facultad -->
            <div>
                <label for="facultad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facultad</label>
                <select id="facultad" name="facultad" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" disabled>
                    <option value="">Seleccione una facultad</option>
                </select>
            </div>

            <!-- Piso -->
            <div>
                <label for="piso" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piso</label>
                <select id="piso" name="piso" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" disabled>
                    <option value="">Seleccione un piso</option>
                </select>
            </div>
        </div>

        <!-- Aquí irá el resto del contenido del formulario -->
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const universidadSelect = document.getElementById('universidad');
            const sedeSelect = document.getElementById('sede');
            const facultadSelect = document.getElementById('facultad');
            const pisoSelect = document.getElementById('piso');

            // Función para resetear y deshabilitar un selector
            function resetSelect(select, disabled = true) {
                select.innerHTML = `<option value="">Seleccione ${select.name}</option>`;
                select.disabled = disabled;
                select.value = '';
            }

            // Función para habilitar un selector
            function enableSelect(select) {
                select.disabled = false;
            }

            // Función para deshabilitar un selector
            function disableSelect(select) {
                select.disabled = true;
                resetSelect(select);
            }

            // Evento para cargar sedes cuando se selecciona una universidad
            universidadSelect.addEventListener('change', function() {
                const universidadId = this.value;
                console.log('ID de Universidad seleccionada:', universidadId);
                
                // Resetear y deshabilitar todos los selectores dependientes
                disableSelect(sedeSelect);
                disableSelect(facultadSelect);
                disableSelect(pisoSelect);

                if (universidadId) {
                    // Habilitar el selector de sede
                    enableSelect(sedeSelect);
                    
                    // Cargar las sedes
                    fetch(`/mapas/sedes/${universidadId}`)
                        .then(response => {
                            console.log('Respuesta del servidor:', response);
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw new Error(err.error || 'Error en la respuesta del servidor');
                                });
                            }
                            return response.json();
                        })
                        .then(sedes => {
                            console.log('Sedes recibidas:', sedes);
                            if (sedes.length === 0) {
                                console.warn('No se encontraron sedes para esta universidad');
                            }
                            sedeSelect.innerHTML = '<option value="">Seleccione una sede</option>';
                            sedes.forEach(sede => {
                                const option = document.createElement('option');
                                option.value = sede.id_sede;
                                option.textContent = sede.nombre_sede;
                                sedeSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error al cargar sedes:', error.message);
                            disableSelect(sedeSelect);
                        });
                }
            });

            // Evento para cargar facultades cuando se selecciona una sede
            sedeSelect.addEventListener('change', function() {
                const sedeId = this.value;
                console.log('ID de Sede seleccionada:', sedeId);
                
                // Resetear y deshabilitar los selectores dependientes
                disableSelect(facultadSelect);
                disableSelect(pisoSelect);

                if (sedeId) {
                    // Habilitar el selector de facultad
                    enableSelect(facultadSelect);
                    
                    // Cargar las facultades
                    fetch(`/mapas/facultades-por-sede/${sedeId}`)
                        .then(response => {
                            console.log('Respuesta del servidor:', response);
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw new Error(err.error || 'Error en la respuesta del servidor');
                                });
                            }
                            return response.json();
                        })
                        .then(facultades => {
                            console.log('Facultades recibidas:', facultades);
                            if (facultades.length === 0) {
                                console.warn('No se encontraron facultades para esta sede');
                            }
                            facultadSelect.innerHTML = '<option value="">Seleccione una facultad</option>';
                            facultades.forEach(facultad => {
                                const option = document.createElement('option');
                                option.value = facultad.id_facultad;
                                option.textContent = facultad.nombre_facultad;
                                facultadSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error al cargar facultades:', error.message);
                            disableSelect(facultadSelect);
                        });
                }
            });

            // Evento para cargar pisos cuando se selecciona una facultad
            facultadSelect.addEventListener('change', function() {
                const facultadId = this.value;
                console.log('ID de Facultad seleccionada:', facultadId);
                
                // Resetear y deshabilitar el selector de piso
                disableSelect(pisoSelect);

                if (facultadId) {
                    // Habilitar el selector de piso
                    enableSelect(pisoSelect);
                    
                    // Cargar los pisos
                    fetch(`/mapas/pisos/${facultadId}`)
                        .then(response => {
                            console.log('Respuesta del servidor:', response);
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw new Error(err.error || 'Error en la respuesta del servidor');
                                });
                            }
                            return response.json();
                        })
                        .then(pisos => {
                            console.log('Pisos recibidos:', pisos);
                            if (pisos.length === 0) {
                                console.warn('No se encontraron pisos para esta facultad');
                            }
                            pisoSelect.innerHTML = '<option value="">Seleccione un piso</option>';
                            pisos.forEach(piso => {
                                const option = document.createElement('option');
                                option.value = piso.id_piso;
                                option.textContent = `Piso ${piso.numero_piso}`;
                                pisoSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error al cargar pisos:', error.message);
                            disableSelect(pisoSelect);
                        });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

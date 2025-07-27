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
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por RUN o Nombre"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="justify-end max-w-xs gap-2"
                x-on:click.prevent="$dispatch('open-modal', 'add-espacio')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>
        </div>
        <livewire:spaces-table />
        <x-modal name="add-espacio" :show="$errors->any()" focusable>

            @slot('title')
                <h1 class="text-lg font-medium text-white dark:text-gray-100">
                    Agregar Espacio </h1>
            @endslot
            <form method="POST" action="{{ route('spaces.store') }}">
                @csrf

                <!-- Campos hidden con valores por defecto -->
                <input type="hidden" name="id_universidad" value="UCSC">
                <input type="hidden" name="id_facultad" value="IT_TH">
                <input type="hidden" name="estado" value="Disponible">

                <div class="grid gap-6 p-6">

                    <div class="space-y-2">
                        <x-form.label for="id_espacio" :value="__('ID del Espacio')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-icons.user class="w-5 h-5" aria-hidden="true" />
                            </x-slot>
                            <x-form.input id="id_espacio" class="block w-full" type="text" name="id_espacio"
                                :value="old('id_espacio')" placeholder="{{ __('Ej: ESP-001') }}" required />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_espacio" :value="__('Nombre del Espacio')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-icons.building-office class="w-5 h-5" aria-hidden="true" />
                            </x-slot>
                            <x-form.input id="nombre_espacio" class="block w-full" type="text" name="nombre_espacio"
                                :value="old('nombre_espacio')" placeholder="{{ __('Ej: Laboratorio de Computación') }}" required />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="piso_id" :value="__('Piso')" class="text-left text-black" />
                        <select name="piso_id" id="selectedPiso"
                            class="block w-full text-black border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Piso') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="tipo_espacio" :value="__('Tipo de Espacio')" class="text-left" />
                        <select name="tipo_espacio" id="tipo_espacio"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
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
                            <option value="Sala de Reuniones"
                                {{ old('tipo_espacio') == 'Sala de Reuniones' ? 'selected' : '' }}>
                                {{ __('Sala de Reuniones') }}
                            </option>
                            <option value="Oficinas" {{ old('tipo_espacio') == 'Oficinas' ? 'selected' : '' }}>
                                {{ __('Oficinas') }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="puestos_disponibles" :value="__('Puestos Disponibles')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-icons.user class="w-5 h-5" aria-hidden="true" />
                            </x-slot>
                            <x-form.input id="puestos_disponibles" class="block w-full" type="number"
                                name="puestos_disponibles" :value="old('puestos_disponibles')"
                                placeholder="{{ __('Puestos Disponibles') }}" min="1" step="1" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-icons.add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar Espacio') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadPisos();
        });

        function loadPisos() {
            fetch('/pisos/IT_TH')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    const selectPiso = document.getElementById("selectedPiso");
                    selectPiso.innerHTML = "<option value=''>Seleccione un piso</option>";

                    if (data && data.length > 0) {
                        data.forEach(piso => {
                            const option = document.createElement("option");
                            option.value = piso.id;
                            option.textContent = `Piso ${piso.numero_piso}`;
                            selectPiso.appendChild(option);
                        });
                        selectPiso.disabled = false;
                    } else {
                        selectPiso.innerHTML = "<option value=''>No hay pisos disponibles</option>";
                        selectPiso.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error cargando pisos:', error);
                    const selectPiso = document.getElementById("selectedPiso");
                    selectPiso.innerHTML = "<option value=''>Error cargando pisos</option>";
                    selectPiso.disabled = true;
                });
        }

        // Validación de puestos disponibles
        document.addEventListener('DOMContentLoaded', function() {
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

        // Recargar pisos cuando se abre el modal
        document.addEventListener('livewire:load', function() {
            // Escuchar el evento de apertura del modal
            window.addEventListener('open-modal', function(event) {
                if (event.detail === 'add-espacio') {
                    loadPisos();
                }
            });
        });

        // También recargar cuando se hace clic en el botón de agregar
        document.addEventListener('click', function(event) {
            if (event.target.closest('[x-on\\:click*="open-modal"]')) {
                setTimeout(loadPisos, 100); // Pequeño delay para asegurar que el modal esté abierto
            }
        });
    </script>

</x-app-layout>

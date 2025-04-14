<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Espacios') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button variant="primary" class="justify-end max-w-xs gap-2"
            x-on:click.prevent="$dispatch('open-modal', 'add-espacio')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('spaces-table')

    <div class="space-y-1">
        <x-modal name="add-espacio" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('spaces.store') }}">
                @csrf

                <div class="grid gap-6 p-6">

                    <div class="space-y-2">
                        <x-form.label for="id_universidad" :value="__('Universidad')" class="text-left" />
                        <select id="selectedUniversidad" name="id_universidad" class="w-full p-2 border rounded">
                            <option value="">Seleccione</option>
                            @foreach ($universidades as $uni)
                                <option value="{{ $uni->id_universidad }}">{{ $uni->nombre_universidad }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="id_facultad" :value="__('Facultad')" class="text-left" />
                        <select id="selectedFacultad" name="id_facultad" class="w-full p-2 border rounded"
                            disabled>
                            <option value="">Seleccione</option>
                        </select>
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
                                {{ __('Aula') }}</option>
                            <option value="Laboratorio" {{ old('tipo_espacio') == 'Laboratorio' ? 'selected' : '' }}>
                                {{ __('Laboratorio') }}</option>
                            <option value="Biblioteca" {{ old('tipo_espacio') == 'Biblioteca' ? 'selected' : '' }}>
                                {{ __('Biblioteca') }}</option>
                            <option value="Sala de Reuniones"
                                {{ old('tipo_espacio') == 'Sala de Reuniones' ? 'selected' : '' }}>
                                {{ __('Sala de Reuniones') }}</option>
                            <option value="Oficinas" {{ old('tipo_espacio') == 'Oficinas' ? 'selected' : '' }}>
                                {{ __('Oficinas') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="estado" :value="__('Estado')" class="text-left" />
                        <select name="estado" id="estado"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Estado') }}</option>
                            <option value="Disponible" {{ old('estado') == 'Disponible' ? 'selected' : '' }}>
                                {{ __('Disponible') }}</option>
                            <option value="Ocupado" {{ old('estado') == 'Ocupado' ? 'selected' : '' }}>
                                {{ __('Ocupado') }}</option>
                            <option value="Reservado" {{ old('estado') == 'Reservado' ? 'selected' : '' }}>
                                {{ __('Reservado') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="puestos_disponibles" :value="__('Puestos Disponibles')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user-group aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="puestos_disponibles" class="block w-full" type="number"
                                name="puestos_disponibles" :value="old('puestos_disponibles')"
                                placeholder="{{ __('Puestos Disponibles') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-plus-circle class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar Espacio') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        document.getElementById("selectedUniversidad").addEventListener("change", function() {
            const universidadId = this.value;
            if (universidadId) {
                fetch(`/facultades/${universidadId}`) // Cambiado aquí
                    .then(response => response.json())
                    .then(data => {
                        const selectFacultad = document.getElementById("selectedFacultad");
                        selectFacultad.innerHTML = "<option value=''>Seleccione</option>";
                        data.forEach(facultad => {
                            const option = document.createElement("option");
                            option.value = facultad.id_facultad;
                            option.textContent = facultad.nombre_facultad;
                            selectFacultad.appendChild(option);
                        });
                        selectFacultad.disabled = false;
                    });
            }
        });

        document.getElementById("selectedFacultad").addEventListener("change", function() {
            const facultadId = this.value;
            if (facultadId) {
                fetch(`/pisos/${facultadId}`) // Cambiado aquí
                    .then(response => response.json())
                    .then(data => {
                        const selectPiso = document.getElementById("selectedPiso");
                        selectPiso.innerHTML = "<option value=''>Seleccione</option>";
                        data.forEach(piso => {
                            const option = document.createElement("option");
                            option.value = piso.id;
                            option.textContent = piso.numero_piso;
                            selectPiso.appendChild(option);
                        });
                        selectPiso.disabled = false;
                    });
            }
        });

        // Cargar espacios (si es necesario)
        document.getElementById("selectedPiso").addEventListener("change", function() {
            const pisoId = this.value;
            if (pisoId) {
                fetch(`/espacios/${pisoId}`) // Cambiado aquí
                    .then(response => response.json())
                    .then(data => {
                        const selectEspacio = document.getElementById("selectedEspacio");
                        selectEspacio.innerHTML = "<option value=''>Seleccione</option>";
                        data.forEach(espacio => {
                            const option = document.createElement("option");
                            option.value = espacio.id_espacio;
                            option.textContent = espacio.tipo_espacio;
                            selectEspacio.appendChild(option);
                        });
                        selectEspacio.disabled = false;
                    });
            }
        });
    </script>

</x-app-layout>

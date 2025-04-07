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
            <form method="POST" action="{{ route('espacios.store') }}">
                @csrf

                <div class="grid gap-6 p-6">
                    <div class="space-y-2">
                        <x-form.label for="id_facultad" :value="__('Facultad')" class="text-left" />
                        <select name="id_facultad" id="id_facultad"
                            class="block w-full text-black border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required onchange="cargarPisos()">
                            <option value="" disabled selected>{{ __('Seleccionar Facultad') }}</option>
                            @foreach ($facultades as $facultad)
                                <option value="{{ $facultad->id }}"
                                    {{ old('id_facultad') == $facultad->id ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id" :value="__('Piso')" class="text-left text-black" />
                        <select name="id" id="id"
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
        document.getElementById("id_facultad").addEventListener("change", function() {
            let facultadId = this.value;
            console.log("Facultad seleccionada: ", facultadId); // Verificar el valor de la facultad seleccionada
            cargarPisos(facultadId);
        });

        function cargarPisos(facultadId) {
            console.log("Cargando pisos para la facultad ID:", facultadId); // Verificar si se ejecuta correctamente
            fetch(`/api/pisos/${facultadId}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Respuesta de pisos: ", data); // Verificar la respuesta de la API
                    let pisosSelect = document.getElementById("id");
                    pisosSelect.innerHTML = '<option value="" disabled selected>{{ __('Seleccionar Piso') }}</option>';
                    data.pisos.forEach(piso => {
                        let option = document.createElement("option");
                        option.value = piso.id;
                        option.textContent = `${piso.nombre_piso} - ${piso.facultad.nombre_facultad}`;
                        pisosSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar los pisos:', error);
                    alert('Hubo un problema al cargar los pisos. Intenta de nuevo.');
                });
        }
    </script>

</x-app-layout>

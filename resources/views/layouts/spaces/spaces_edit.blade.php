<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidad / Espacio / Editar') }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('spaces.update', $espacio->id_espacio) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid gap-4 p-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>
                    <x-form.label for="universidad" :value="__('Universidad')" />
                    <select name="id_universidad" id="id_universidad" class="block w-full" required >
                        @foreach ($universidades as $uni)
                          
                            <option value="{{ $uni->id_universidad }}"
                                {{ $espacio->piso->facultad->id_universidad == $uni->id_universidad ? 'selected' : '' }}>
                                {{ $uni->nombre_universidad }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-form.label for="facultad" :value="__('Facultad')" />
                    <select name="id_facultad" id="id_facultad" class="block w-full" required>
                        @foreach ($facultades as $fac)
                            <option value="{{ $fac->id_facultad }}"
                                {{ $espacio->piso->facultad->id_facultad == $fac->id_facultad ? 'selected' : '' }}>
                                {{ $fac->nombre_facultad }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-form.label for="piso_id" :value="__('Piso')" />
                    <select name="piso_id" id="piso_id" class="block w-full" required>
                        @foreach ($pisos as $piso)
                            <option value="{{ $piso->id }}" {{ $piso->id == $espacio->piso_id ? 'selected' : '' }}>
                                {{ $piso->nombre ?? 'Piso ' . $piso->numero_piso }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-form.label for="tipo_espacio" :value="__('Tipo de Espacio')" />
                    <select name="tipo_espacio" id="tipo_espacio" class="block w-full" required>
                        @foreach (['Aula', 'Laboratorio', 'Biblioteca', 'Sala de Reuniones', 'Oficinas'] as $tipo)
                            <option value="{{ $tipo }}"
                                {{ $espacio->tipo_espacio == $tipo ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-form.label for="estado" :value="__('Estado')" />
                    <select name="estado" id="estado" class="block w-full" required>
                        @foreach (['Disponible', 'Ocupado', 'Reservado'] as $estado)
                            <option value="{{ $estado }}" {{ $espacio->estado == $estado ? 'selected' : '' }}>
                                {{ $estado }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <x-form.label for="puestos_disponibles" :value="__('Puestos Disponibles')" />
                <x-form.input id="puestos_disponibles" class="block w-full" type="number" name="puestos_disponibles"
                    value="{{ old('puestos_disponibles', $espacio->puestos_disponibles) }}" min="0" />
            </div>

            <div class="flex justify-end mt-6">
                <x-button>{{ __('Guardar Cambios') }}</x-button>
            </div>
        </div>
    </form>

  <script>
   document.addEventListener('DOMContentLoaded', () => {
    const universidadSelect = document.getElementById('id_universidad');
    const facultadSelect = document.getElementById('id_facultad');
    const pisoSelect = document.getElementById('piso_id');

    // Función para cargar facultades
    async function cargarFacultades(universidadId, facultadIdSeleccionada = null) {
        const res = await fetch(`/api/facultades/${universidadId}`);
        const data = await res.json();

        facultadSelect.innerHTML = '';
        data.forEach(fac => {
            const option = document.createElement('option');
            option.value = fac.id_facultad;
            option.textContent = fac.nombre_facultad;
            if (facultadIdSeleccionada && fac.id_facultad == facultadIdSeleccionada) {
                option.selected = true;
            }
            facultadSelect.appendChild(option);
        });

        // Si hay una facultad seleccionada, cargar pisos
        if (facultadIdSeleccionada) {
            await cargarPisos(facultadIdSeleccionada, pisoSelect.dataset.selected);
        } else if (facultadSelect.value) {
            facultadSelect.dispatchEvent(new Event('change'));
        }
    }

    // Función para cargar pisos
    async function cargarPisos(facultadId, pisoIdSeleccionado = null) {
        const res = await fetch(`/api/pisos/${facultadId}`);
        const data = await res.json();

        pisoSelect.innerHTML = '';
        data.forEach(piso => {
            const option = document.createElement('option');
            option.value = piso.id;
            option.textContent = piso.nombre ?? 'Piso ' + piso.numero_piso;
            if (pisoIdSeleccionado && piso.id == pisoIdSeleccionado) {
                option.selected = true;
            }
            pisoSelect.appendChild(option);
        });
    }

    // Evento change para universidad
    universidadSelect.addEventListener('change', async () => {
        await cargarFacultades(universidadSelect.value);
    });

    // Evento change para facultad
    facultadSelect.addEventListener('change', async () => {
        await cargarPisos(facultadSelect.value);
    });

    // Inicializar con los valores actuales
    const universidadIdActual = universidadSelect.value;
    const facultadIdActual = facultadSelect.value;
    const pisoIdActual = pisoSelect.value;

    // Guardar el piso seleccionado en un data attribute
    pisoSelect.dataset.selected = pisoIdActual;

    // Cargar facultades y pisos manteniendo la selección actual
    if (universidadIdActual) {
        await cargarFacultades(universidadIdActual, facultadIdActual);
    }
});
  </script>
</x-app-layout>
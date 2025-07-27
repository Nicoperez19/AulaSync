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

        <form id="edit-space-form" action="{{ route('spaces.update', $espacio->id_espacio) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Campos hidden con valores por defecto -->
            <input type="hidden" name="id_universidad" value="UCSC">
            <input type="hidden" name="id_facultad" value="IT_TH">
            <input type="hidden" name="estado" value="Disponible">

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_espacio" :value="__('ID del Espacio')" />
                        <x-form.input id="id_espacio" class="block w-full" type="text" name="id_espacio"
                            value="{{ old('id_espacio', $espacio->id_espacio) }}" required />
                        <p class="text-xs text-gray-500 mt-1">Identificador único del espacio</p>
                    </div>

                    <div>
                        <x-form.label for="nombre_espacio" :value="__('Nombre del Espacio')" />
                        <x-form.input id="nombre_espacio" class="block w-full" type="text" name="nombre_espacio"
                            value="{{ old('nombre_espacio', $espacio->nombre_espacio) }}" required />
                        <p class="text-xs text-gray-500 mt-1">Nombre descriptivo del espacio</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="piso_id" :value="__('Piso')" />
                        <select name="piso_id" id="piso_id"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                            @foreach ($pisos as $piso)
                                <option value="{{ $piso->id }}"
                                    {{ $piso->id == $espacio->piso_id ? 'selected' : '' }}>
                                    Piso {{ $piso->numero_piso }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="tipo_espacio" :value="__('Tipo de Espacio')" />
                        <select name="tipo_espacio" id="tipo_espacio"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                            @foreach (['Aula', 'Laboratorio', 'Biblioteca', 'Sala de Reuniones', 'Oficinas'] as $tipo)
                                <option value="{{ $tipo }}"
                                    {{ $espacio->tipo_espacio == $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <x-form.label for="puestos_disponibles" :value="__('Puestos Disponibles')" />
                    <x-form.input id="puestos_disponibles" class="block w-full" type="number"
                        name="puestos_disponibles"
                        value="{{ old('puestos_disponibles', $espacio->puestos_disponibles) }}" min="1"
                        step="1" />
                </div>

                <div class="flex justify-end mt-6">
                    <x-button>{{ __('Guardar Cambios') }}</x-button>
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
    </script>
</x-app-layout>

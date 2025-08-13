<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Reservas') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-reserva')" variant="primary" class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
            <span>Nueva Reserva</span>
        </x-button>
    </div>

    @livewire('reservations-table')

    <x-modal name="add-reserva" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('reservas.add') }}">
            @csrf
            <div class="p-6 space-y-6">
                <!-- Filtros Universiad y Facultad -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="universidad" :value="__('Universidad')" />
                        <select id="universidad" name="universidad" class="block w-full border-gray-300 rounded-md"
                            required>
                            <option value="">Seleccione universidad</option>
                            @foreach ($universidades as $uni)
                                <option value="{{ $uni->id_universidad }}">{{ $uni->nombre_universidad }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="facultad" :value="__('Facultad')" />
                        <select id="facultad" name="facultad" class="block w-full border-gray-300 rounded-md" required>
                            <option value="">Seleccione facultad</option>
                        </select>
                    </div>
                </div>

                <!-- Espacios Disponibles (aparecerá dinámicamente) -->
                <div id="espacios-container" class="hidden">
                    <div class="mb-4 space-y-2">
                        <x-form.label :value="__('Espacio a reservar')" />
                        <div id="espacios-grid" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <!-- Espacios se cargarán aquí -->
                        </div>
                    </div>
                </div>

                <!-- Fecha y Hora -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="fecha_reserva" :value="__('Fecha de Reserva')" />
                        <x-form.input id="fecha_reserva" name="fecha_reserva" type="date" class="block w-full"
                            required />
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="hora" :value="__('Hora')" />
                        <x-form.input id="hora" name="hora" type="time" class="block w-full" required />
                    </div>
                </div>

                <!-- ID Usuario -->
                <div class="space-y-2">
                    <x-form.label for="id" :value="__('ID del Usuario')" />
                    <x-form.input id="id" name="id" type="number" class="block w-full" required />
                </div>

                <!-- Botón de Submit -->
                <div class="flex justify-end pt-4">
                    <x-button type="submit" class="gap-2">
                        <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                        {{ __('Agregar Reserva') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const universidadSelect = document.getElementById('universidad');
            const facultadSelect = document.getElementById('facultad');
            const espaciosContainer = document.getElementById('espacios-container');
            const espaciosGrid = document.getElementById('espacios-grid');

            // Cargar facultades al cambiar universidad
            universidadSelect.addEventListener('change', function() {
                facultadSelect.innerHTML = '<option value="">Seleccione facultad</option>';
                espaciosContainer.classList.add('hidden');

                if (!this.value) return;

                fetch(`/facultades/${this.value}`)
                    .then(res => res.json())
                    .then(facultades => {
                        facultades.forEach(f => {
                            facultadSelect.innerHTML +=
                                `<option value="${f.id_facultad}">${f.nombre_facultad}</option>`;
                        });
                    });
            });

            // Cargar espacios al cambiar facultad
            facultadSelect.addEventListener('change', function() {
                espaciosContainer.classList.add('hidden');

                if (!this.value || !universidadSelect.value) return;

                fetch(`/espacios-disponibles?universidad=${universidadSelect.value}&facultad=${this.value}`)
                    .then(res => res.json())
                    .then(espacios => {
                        espaciosGrid.innerHTML = '';

                        if (espacios.length === 0) {
                            espaciosGrid.innerHTML =
                                '<p class="text-gray-500">No hay espacios disponibles</p>';
                            espaciosContainer.classList.remove('hidden');
                            return;
                        }

                        espacios.forEach(espacio => {
                            const card = document.createElement('label');
                            card.className =
                                'block cursor-pointer';
                            card.innerHTML = `
                                <input type="radio" name="id_espacio" value="${espacio.id_espacio}" class="hidden peer" required>
                                <div class="p-4 transition-all duration-200 bg-white border-2 rounded-lg shadow-sm hover:border-blue-400 hover:shadow-md peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-lg peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:transform peer-checked:-translate-y-1">
                                    <h4 class="font-semibold text-gray-800">${espacio.tipo_espacio}</h4>
                                    <p class="text-sm text-gray-600">Capacidad: ${espacio.puestos_disponibles}</p>
                                    <p class="text-sm text-gray-600">Piso: ${espacio.piso_numero}</p>
                                </div>
                            `;
                            espaciosGrid.appendChild(card);
                        });

                        espaciosContainer.classList.remove('hidden');
                    });
            });

            // Manejar el evento de cambio para las tarjetas
            document.addEventListener('change', function(e) {
                if (e.target.name === 'id_espacio') {
                    // Opcional: puedes agregar lógica adicional aquí si es necesario
                    // Espacio seleccionado
                }
            });
        });
    </script>
</x-app-layout>

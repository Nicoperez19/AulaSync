<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-tie"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Jefes de Carrera</h2>
                    <p class="text-sm text-gray-500">Administra los jefes de carrera del sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">

        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por Nombre, Email o Carrera"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-jefe-carrera')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Jefe de Carrera
            </x-button>
        </div>
        <livewire:jefes-carrera-table />

        <x-modal name="add-jefe-carrera" :show="$errors->any()" focusable>
            @slot('title')
            <div class="relative flex items-center justify-between p-2 bg-red-700">
                <div class="flex items-center gap-3">
                    <div class="p-4 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Jefe de Carrera
                    </h2>
                </div>
                <button @click="show = false"
                    class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                <span
                    class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span
                    class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
            @endslot

            <form method="POST" action="{{ route('jefes-carrera.store') }}" class="p-6">
                @csrf

                <div class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="nombre" value="Nombre *" />
                            <x-form.input id="nombre" name="nombre" type="text"
                                class="w-full @error('nombre') border-red-500 @enderror" required maxlength="100"
                                placeholder="Ej: Juan Pérez" value="{{ old('nombre') }}" />
                            @error('nombre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="email" value="Email *" />
                            <x-form.input id="email" name="email" type="email"
                                class="w-full @error('email') border-red-500 @enderror" required
                                placeholder="Ej: jperez@universidad.cl" value="{{ old('email') }}" />
                            @error('email')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="telefono" value="Teléfono" />
                            <x-form.input id="telefono" name="telefono" type="text"
                                class="w-full @error('telefono') border-red-500 @enderror" maxlength="20"
                                placeholder="Ej: +56 9 1234 5678" value="{{ old('telefono') }}" />
                            @error('telefono')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="id_carrera" value="Carrera *" />
                            <select name="id_carrera" id="id_carrera"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_carrera') border-red-500 @enderror"
                                required>
                                <option value="" disabled selected>{{ __('Seleccionar Carrera') }}</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id_carrera }}" {{ old('id_carrera') == $carrera->id_carrera ? 'selected' : '' }}>
                                        {{ $carrera->nombre }} ({{ $carrera->areaAcademica->nombre_area_academica ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_carrera')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Crear Jefe de Carrera') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        function searchTable() {
            var input = document.getElementById("searchInput").value.toLowerCase();
            var table = document.getElementById("jefes-carrera-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                if (cells.length < 4) continue;
                var nombre = cells[0].textContent.toLowerCase();
                var email = cells[1].textContent.toLowerCase();
                var telefono = cells[2].textContent.toLowerCase();
                var carrera = cells[3].textContent.toLowerCase();

                if (nombre.includes(input) || email.includes(input) || telefono.includes(input) || carrera.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

    </script>

</x-app-layout>

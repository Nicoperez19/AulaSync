<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-map-marker-alt"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Sedes</h2>
                    <p class="text-sm text-gray-500">Administra las sedes disponibles en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">

        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por Nombre o ID"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-sede')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Sede
            </x-button>
        </div>
        <livewire:sedes-table />

        <x-modal name="add-sede" :show="$errors->any()" focusable>
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
                        Agregar Sede
                    </h2>
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

            <form method="POST" action="{{ route('sedes.store') }}" class="p-6">
                @csrf

                <div class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="id_sede" value="ID Sede *" />
                            <x-form.input id="id_sede" name="id_sede" type="text"
                                class="w-full @error('id_sede') border-red-500 @enderror" required maxlength="20"
                                placeholder="Ej: TH" value="{{ old('id_sede') }}" />
                            @error('id_sede')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="nombre_sede" value="Nombre Sede *" />
                            <x-form.input id="nombre_sede" name="nombre_sede" type="text"
                                class="w-full @error('nombre_sede') border-red-500 @enderror" required maxlength="100"
                                placeholder="Ej: Talcahuano" value="{{ old('nombre_sede') }}" />
                            @error('nombre_sede')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="id_universidad" value="Universidad *" />
                            <select name="id_universidad" id="id_universidad"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_universidad') border-red-500 @enderror"
                                required>
                                <option value="" disabled selected>{{ __('Seleccionar Universidad') }}</option>
                                @foreach($universidades as $universidad)
                                    <option value="{{ $universidad->id_universidad }}" {{ old('id_universidad') == $universidad->id_universidad ? 'selected' : '' }}>
                                        {{ $universidad->nombre_universidad }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_universidad')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="comuna_id" value="Comuna *" />
                            <select name="comuna_id" id="comuna_id"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('comuna_id') border-red-500 @enderror"
                                required>
                                <option value="" disabled selected>{{ __('Seleccionar Comuna') }}</option>
                                @foreach($comunas as $comuna)
                                    <option value="{{ $comuna->id }}" {{ old('comuna_id') == $comuna->id ? 'selected' : '' }}>
                                        {{ $comuna->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('comuna_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Crear Sede') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        function searchTable() {
            var input = document.getElementById("searchInput").value.toLowerCase();
            var table = document.getElementById("sedes-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                if (cells.length < 4) continue;
                var id = cells[0].textContent.toLowerCase();
                var nombre = cells[1].textContent.toLowerCase();
                var universidad = cells[2].textContent.toLowerCase();
                var comuna = cells[3].textContent.toLowerCase();

                if (id.includes(input) || nombre.includes(input) || universidad.includes(input) || comuna.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

    </script>

</x-app-layout>
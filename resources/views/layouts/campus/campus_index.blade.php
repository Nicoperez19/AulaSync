<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-building"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Campus</h2>
                    <p class="text-sm text-gray-500">Administra los campus disponibles en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" wire:model.live="search" placeholder="Buscar por Nombre o ID"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-campus')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Campus
            </x-button>
        </div>
        <livewire:campus-table />
        <x-modal name="add-campus" :show="$errors->any()" focusable>
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
                        Agregar Campus
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

            <form method="POST" action="{{ route('campus.store') }}" class="p-6">
                @csrf

                <div class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="id_campus" value="ID Campus *" />
                            <x-form.input id="id_campus" name="id_campus" type="text"
                                class="w-full @error('id_campus') border-red-500 @enderror" required maxlength="20"
                                placeholder="Ej: CSA" value="{{ old('id_campus') }}" />
                            @error('id_campus')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="nombre_campus" value="Nombre Campus *" />
                            <x-form.input id="nombre_campus" name="nombre_campus" type="text"
                                class="w-full @error('nombre_campus') border-red-500 @enderror" required maxlength="100"
                                placeholder="Ej: Campus San Andrés" value="{{ old('nombre_campus') }}" />
                            @error('nombre_campus')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_sede" value="Sede *" />
                        <select name="id_sede" id="id_sede"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_sede') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Sede') }}</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}" {{ old('id_sede') == $sede->id_sede ? 'selected' : '' }}>
                                    {{ $sede->nombre_sede }} - {{ $sede->universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_sede')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Crear Campus') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
    <script>
        function searchTable() {
            var input = document.querySelector('input[wire\\:model\\.live="search"]').value.toLowerCase();
            var table = document.getElementById("campus-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");

                if (cells.length < 4) continue;

                var id = cells[0].textContent.toLowerCase();
                var nombre = cells[1].textContent.toLowerCase();
                var sede = cells[2].textContent.toLowerCase();
                var universidad = cells[3].textContent.toLowerCase();

                if (id.includes(input) || nombre.includes(input) || sede.includes(input) || universidad.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.querySelector('input[wire\\:model\\.live="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', searchTable);
            }
        });
    </script>



</x-app-layout>
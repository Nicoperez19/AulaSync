<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-graduation-cap"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Áreas Académicas</h2>
                    <p class="text-sm text-gray-500">Administra las áreas académicas disponibles en el sistema</p>
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
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-academic-area')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                    Agregar Área Académica
            </x-button>
        </div>
        <livewire:academic-areas-table />

        <x-modal name="add-academic-area" :show="$errors->any()" focusable>
        @slot('title')
            <div class="relative flex items-center justify-between p-2 bg-red-700">
                <div class="flex items-center gap-3">
                    <div class="p-4 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Área Académica
                    </h2>
                </div>
                <button @click="show = false" class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
        @endslot

        <form method="POST" action="{{ route('academic_areas.add') }}" class="p-6">
            @csrf

            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="id_area_academica" value="ID Área Académica *" />
                        <x-form.input id="id_area_academica" name="id_area_academica" type="text"
                            class="w-full @error('id_area_academica') border-red-500 @enderror" required maxlength="20"
                            placeholder="Ej: ESC_EDUSAL" value="{{ old('id_area_academica') }}" />
                        @error('id_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_area_academica" value="Nombre Área Académica *" />
                        <x-form.input id="nombre_area_academica" name="nombre_area_academica" type="text"
                            class="w-full @error('nombre_area_academica') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: Escuela de Educación y Salud" value="{{ old('nombre_area_academica') }}" />
                        @error('nombre_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="tipo_area_academica" value="Tipo de Área *" />
                        <select name="tipo_area_academica" id="tipo_area_academica"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('tipo_area_academica') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Tipo') }}</option>
                            <option value="departamento" {{ old('tipo_area_academica') == 'departamento' ? 'selected' : '' }}>Departamento</option>
                            <option value="escuela" {{ old('tipo_area_academica') == 'escuela' ? 'selected' : '' }}>Escuela</option>
                        </select>
                        @error('tipo_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_facultad" value="Facultad *" />
                        <select name="id_facultad" id="id_facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_facultad') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Facultad') }}</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}" {{ old('id_facultad') == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }} - {{ $facultad->sede->nombre_sede ?? 'Sin Sede' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_facultad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Crear Área Académica') }}</x-button>
                </div>
            </div>
        </form>
    </x-modal>
    </div>

   <script>
    function searchTable() {    
        var input = document.getElementById("searchInput").value.toLowerCase();
        var table = document.getElementById("academic-areas-table");
        var rows = table.getElementsByTagName("tr");

        for (var i = 1; i < rows.length; i++) {
            var cells = rows[i].getElementsByTagName("td");
            
            if (cells.length < 6) continue;
            
            var id = cells[0].textContent.toLowerCase();
            var nombre = cells[1].textContent.toLowerCase();
            var tipo = cells[2].textContent.toLowerCase();
            var facultad = cells[3].textContent.toLowerCase();
            var sede = cells[4].textContent.toLowerCase();
            var universidad = cells[5].textContent.toLowerCase();

            if (id.includes(input) || nombre.includes(input) || tipo.includes(input) || facultad.includes(input) || sede.includes(input) || universidad.includes(input)) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
   </script>


</x-app-layout>

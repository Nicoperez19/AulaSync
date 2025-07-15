<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-book-open"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Asignaturas</h2>
                    <p class="text-sm text-gray-500">Administra las asignaturas disponibles en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <livewire:subjects-table />

        <!-- Modal -->
        <x-modal name="add-asignatura" :show="$errors->any()" focusable>
            {{-- Encabezado con botón de cerrar --}}
            <x-slot name="title">
                <div class="relative flex items-center justify-center p-2 bg-light-cloud-blue dark:bg-dark-eval-1">
                    <button type="button"
                        class="absolute right-0 text-white hover:text-gray-300"
                        x-on:click="$dispatch('close')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h2 class="text-2xl font-bold text-white">Agregar Asignatura</h2>
                </div>
            </x-slot>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('asignaturas.store') }}">
                @csrf
                <div class="p-6 space-y-6">
                    <!-- Mostrar errores de validación -->
                    @if ($errors->any())
                        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Primera fila: ID y Código -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- ID Asignatura -->
                        <div class="space-y-2">
                            <x-form.label for="id_asignatura" value="ID Asignatura *" />
                            <x-form.input 
                                id="id_asignatura" 
                                name="id_asignatura" 
                                type="text" 
                                class="w-full @error('id_asignatura') border-red-500 @enderror" 
                                required 
                                maxlength="20" 
                                placeholder="Ej: ASG001"
                                value="{{ old('id_asignatura') }}" />
                            @error('id_asignatura')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Código Asignatura -->
                        <div class="space-y-2">
                            <x-form.label for="codigo_asignatura" value="Código Asignatura *" />
                            <x-form.input 
                                id="codigo_asignatura" 
                                name="codigo_asignatura" 
                                type="text" 
                                class="w-full @error('codigo_asignatura') border-red-500 @enderror" 
                                required 
                                maxlength="100" 
                                placeholder="Ej: MAT101"
                                value="{{ old('codigo_asignatura') }}" />
                            @error('codigo_asignatura')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Segunda fila: Nombre (completo) -->
                    <div class="space-y-2">
                        <x-form.label for="nombre_asignatura" value="Nombre Asignatura *" />
                        <x-form.input 
                            id="nombre_asignatura" 
                            name="nombre_asignatura" 
                            type="text" 
                            class="w-full @error('nombre_asignatura') border-red-500 @enderror" 
                            required 
                            maxlength="100" 
                            placeholder="Ej: Matemáticas Básicas"
                            value="{{ old('nombre_asignatura') }}" />
                        @error('nombre_asignatura')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tercera fila: Sección y Período -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Sección -->
                        <div class="space-y-2">
                            <x-form.label for="seccion" value="Sección *" />
                            <x-form.input 
                                id="seccion" 
                                name="seccion" 
                                type="text" 
                                class="w-full @error('seccion') border-red-500 @enderror" 
                                required 
                                maxlength="50" 
                                placeholder="Ej: A, B, C, etc."
                                value="{{ old('seccion') }}" />
                            @error('seccion')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Período -->
                        <div class="space-y-2">
                            <x-form.label for="periodo" value="Período" />
                            <x-form.input 
                                id="periodo" 
                                name="periodo" 
                                type="text" 
                                class="w-full @error('periodo') border-red-500 @enderror" 
                                maxlength="20" 
                                placeholder="Ej: 2024-1, 2024-2"
                                value="{{ old('periodo') }}" />
                            @error('periodo')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Cuarta fila: Horas Directas e Indirectas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Horas Directas -->
                        <div class="space-y-2">
                            <x-form.label for="horas_directas" value="Horas Directas" />
                            <x-form.input 
                                id="horas_directas" 
                                name="horas_directas" 
                                type="number" 
                                class="w-full @error('horas_directas') border-red-500 @enderror" 
                                min="0" 
                                placeholder="Ej: 30"
                                value="{{ old('horas_directas') }}" />
                            @error('horas_directas')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Horas Indirectas -->
                        <div class="space-y-2">
                            <x-form.label for="horas_indirectas" value="Horas Indirectas" />
                            <x-form.input 
                                id="horas_indirectas" 
                                name="horas_indirectas" 
                                type="number" 
                                class="w-full @error('horas_indirectas') border-red-500 @enderror" 
                                min="0" 
                                placeholder="Ej: 60"
                                value="{{ old('horas_indirectas') }}" />
                            @error('horas_indirectas')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Quinta fila: Área de Conocimiento (completo) -->
                    <div class="space-y-2">
                        <x-form.label for="area_conocimiento" value="Área de Conocimiento" />
                        <x-form.input 
                            id="area_conocimiento" 
                            name="area_conocimiento" 
                            type="text" 
                            class="w-full @error('area_conocimiento') border-red-500 @enderror" 
                            maxlength="100" 
                            placeholder="Ej: Ciencias Básicas"
                            value="{{ old('area_conocimiento') }}" />
                        @error('area_conocimiento')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sexta fila: Profesor y Carrera -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Profesor -->
                        <div class="space-y-2">
                            <x-form.label for="run_profesor" value="Docente Responsable *" />
                            <select 
                                name="run_profesor" 
                                id="run_profesor" 
                                class="w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 @error('run_profesor') border-red-500 @enderror" 
                                required>
                                <option value="">Seleccione un profesor</option>
                                @foreach ($profesores as $profesor)
                                    <option value="{{ $profesor->run_profesor }}" {{ old('run_profesor') == $profesor->run_profesor ? 'selected' : '' }}>
                                        {{ $profesor->name }} ({{ $profesor->run_profesor }})
                                    </option>
                                @endforeach
                            </select>
                            @error('run_profesor')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Carrera -->
                        <div class="space-y-2">
                            <x-form.label for="id_carrera" value="Carrera *" />
                            <select 
                                name="id_carrera" 
                                id="id_carrera" 
                                class="w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 @error('id_carrera') border-red-500 @enderror" 
                                required>
                                <option value="">Seleccione una carrera</option>
                                @foreach ($carreras as $carrera)
                                    <option value="{{ $carrera->id_carrera }}" {{ old('id_carrera') == $carrera->id_carrera ? 'selected' : '' }}>
                                        {{ $carrera->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_carrera')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Botón Guardar -->
                    <div class="flex justify-end pt-4">
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-plus-circle class="w-6 h-6" />
                            {{ __('Guardar Asignatura') }}
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>

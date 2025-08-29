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

            <div class="flex items-center gap-2">
                <x-button href="{{ route('asignaturas.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-m font-medium border border-gray-300 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form id="edit-subject-form" action="{{ route('asignaturas.update', $asignatura->id_asignatura) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_asignatura" :value="__('ID Asignatura')" />
                        <x-form.input id="id_asignatura" class="block w-full" type="text" name="id_asignatura"
                            value="{{ old('id_asignatura', $asignatura->id_asignatura) }}" required />
                    </div>

                    <div>
                        <x-form.label for="codigo_asignatura" :value="__('Código Asignatura')" />
                        <x-form.input id="codigo_asignatura" class="block w-full" type="text" name="codigo_asignatura"
                            value="{{ old('codigo_asignatura', $asignatura->codigo_asignatura) }}" required />
                    </div>
                </div>

                <div>
                    <x-form.label for="nombre_asignatura" :value="__('Nombre Asignatura')" />
                    <x-form.input id="nombre_asignatura" class="block w-full" type="text" name="nombre_asignatura"
                        value="{{ old('nombre_asignatura', $asignatura->nombre_asignatura) }}" required />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="seccion" :value="__('Sección')" />
                        <x-form.input id="seccion" class="block w-full" type="text" name="seccion"
                            value="{{ old('seccion', $asignatura->seccion) }}" required />
                    </div>

                    <div>
                        <x-form.label for="periodo" :value="__('Período')" />
                        <x-form.input id="periodo" class="block w-full" type="text" name="periodo"
                            value="{{ old('periodo', $asignatura->periodo) }}" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="horas_directas" :value="__('Horas Directas')" />
                        <x-form.input id="horas_directas" class="block w-full" type="number" name="horas_directas"
                            value="{{ old('horas_directas', $asignatura->horas_directas) }}" min="0" />
                    </div>

                    <div>
                        <x-form.label for="horas_indirectas" :value="__('Horas Indirectas')" />
                        <x-form.input id="horas_indirectas" class="block w-full" type="number" name="horas_indirectas"
                            value="{{ old('horas_indirectas', $asignatura->horas_indirectas) }}" min="0" />
                    </div>
                </div>

                <div>
                    <x-form.label for="area_conocimiento" :value="__('Área de Conocimiento')" />
                    <x-form.input id="area_conocimiento" class="block w-full" type="text" name="area_conocimiento"
                        value="{{ old('area_conocimiento', $asignatura->area_conocimiento) }}" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <x-form.label for="run_profesor" :value="__('Profesor Titular')" />
                        <select name="run_profesor" id="run_profesor"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required>
                            <option value="" disabled>{{ __('Seleccionar Profesor') }}</option>
                            @foreach($profesores as $profesor)
                                <option value="{{ $profesor->run_profesor }}" 
                                    {{ $asignatura->run_profesor == $profesor->run_profesor ? 'selected' : '' }}>
                                    {{ $profesor->name }} ({{ $profesor->run_profesor }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-form.label for="run_profesor_reemplazo" :value="__('Profesor Reemplazo (opcional)')" />
                        <select name="run_profesor_reemplazo" id="run_profesor_reemplazo"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m">
                            <option value="">Sin reemplazo</option>
                            @foreach($profesores as $profesor)
                                <option value="{{ $profesor->run_profesor }}" 
                                    {{ $asignatura->run_profesor_reemplazo == $profesor->run_profesor ? 'selected' : '' }}>
                                    {{ $profesor->name }} ({{ $profesor->run_profesor }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-form.label for="id_carrera" :value="__('Carrera')" />
                        <select name="id_carrera" id="id_carrera"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required>
                            <option value="" disabled>{{ __('Seleccionar Carrera') }}</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}" 
                                    {{ $asignatura->id_carrera == $carrera->id_carrera ? 'selected' : '' }}>
                                    {{ $carrera->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Guardar Cambios') }}</x-button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-subject-form');

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
        });
    </script>
</x-app-layout>
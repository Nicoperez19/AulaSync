<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-chalkboard-teacher"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Profesores</h2>
                    <p class="text-sm text-gray-500">Administra los profesores registrados en el sistema</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('professors.index') }}" 
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
        <form id="edit-professor-form" action="{{ route('professors.update', $profesor->run_profesor) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="run_profesor" :value="__('RUN')" />
                        <x-form.input id="run_profesor" class="block w-full" type="text" name="run_profesor"
                            value="{{ old('run_profesor', $profesor->run_profesor) }}" required maxlength="8" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>
                    <div>
                        <x-form.label for="name" :value="__('Nombre')" />
                        <x-form.input id="name" class="block w-full" type="text" name="name"
                            value="{{ old('name', $profesor->name) }}" required maxlength="255" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="email" :value="__('Email')" />
                        <x-form.input id="email" class="block w-full" type="email" name="email"
                            value="{{ old('email', $profesor->email) }}" required maxlength="255" />
                    </div>
                    <div>
                        <x-form.label for="celular" :value="__('Celular')" />
                        <x-form.input id="celular" class="block w-full" type="text" name="celular"
                            value="{{ old('celular', $profesor->celular) }}" maxlength="9" pattern="9[0-9]{8}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                        <x-form.input id="fecha_nacimiento" class="block w-full" type="date" name="fecha_nacimiento"
                            value="{{ old('fecha_nacimiento', $profesor->fecha_nacimiento) }}" />
                    </div>
                    <div>
                        <x-form.label for="anio_ingreso" :value="__('Año de Ingreso')" />
                        <x-form.input id="anio_ingreso" class="block w-full" type="number" name="anio_ingreso"
                            value="{{ old('anio_ingreso', $profesor->anio_ingreso) }}" min="1900" max="{{ date('Y') + 1 }}" />
                    </div>
                </div>

                <div>
                    <x-form.label for="direccion" :value="__('Dirección')" />
                    <x-form.input id="direccion" class="block w-full" type="text" name="direccion"
                        value="{{ old('direccion', $profesor->direccion) }}" maxlength="255" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="tipo_profesor" :value="__('Tipo de Profesor')" />
                        <select name="tipo_profesor" id="tipo_profesor"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required>
                            <option value="" disabled>{{ __('Seleccionar Tipo') }}</option>
                            <option value="Profesor Responsable" {{ $profesor->tipo_profesor == 'Profesor Responsable' ? 'selected' : '' }}>Profesor Responsable</option>
                            <option value="Profesor Colaborador" {{ $profesor->tipo_profesor == 'Profesor Colaborador' ? 'selected' : '' }}>Profesor Colaborador</option>
                            <option value="Ayudante" {{ $profesor->tipo_profesor == 'Ayudante' ? 'selected' : '' }}>Ayudante</option>
                        </select>
                    </div>

                    <div>
                        <x-form.label for="id_universidad" :value="__('Universidad')" />
                        <select name="id_universidad" id="id_universidad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m">
                            <option value="">{{ __('Seleccionar Universidad') }}</option>
                            @foreach($universidades as $universidad)
                                <option value="{{ $universidad->id_universidad }}" 
                                    {{ $profesor->id_universidad == $universidad->id_universidad ? 'selected' : '' }}>
                                    {{ $universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_facultad" :value="__('Facultad')" />
                        <select name="id_facultad" id="id_facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m">
                            <option value="">{{ __('Seleccionar Facultad') }}</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}" 
                                    {{ $profesor->id_facultad == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }} - {{ $facultad->sede->nombre_sede ?? 'Sin Sede' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="id_carrera" :value="__('Carrera')" />
                        <select name="id_carrera" id="id_carrera"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m">
                            <option value="">{{ __('Seleccionar Carrera') }}</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}" 
                                    {{ $profesor->id_carrera == $carrera->id_carrera ? 'selected' : '' }}>
                                    {{ $carrera->nombre }} - {{ $carrera->areaAcademica->nombre_area_academica ?? 'Sin Área' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <x-form.label for="id_area_academica" :value="__('Área Académica')" />
                    <select name="id_area_academica" id="id_area_academica"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m">
                        <option value="">{{ __('Seleccionar Área Académica') }}</option>
                        @foreach($areasAcademicas as $areaAcademica)
                            <option value="{{ $areaAcademica->id_area_academica }}" 
                                {{ $profesor->id_area_academica == $areaAcademica->id_area_academica ? 'selected' : '' }}>
                                {{ $areaAcademica->nombre_area_academica }} - {{ $areaAcademica->facultad->nombre_facultad ?? 'Sin Facultad' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Guardar Cambios') }}</x-button>
                </div>
            </div>
        </form>
    </div>

    <!-- Sección de Clases Temporales como Colaborador -->
    @if($clasesTemporales->count() > 0)
        <div class="mt-8 p-6 bg-white rounded-lg shadow-lg">
            <div class="mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-2xl font-bold leading-tight flex items-center gap-3">
                    <div class="p-2 rounded-lg" style="background-color: #cd1627;">
                        <i class="text-lg text-white fa-solid fa-graduation-cap"></i>
                    </div>
                    Clases Temporales como Colaborador
                </h3>
                <p class="text-sm text-gray-600 mt-2">Clases donde este profesor colabora como profesor adicional</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($clasesTemporales as $clase)
                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Header con color -->
                        <div class="p-4" style="background-color: #cd1627;">
                            <h4 class="text-lg font-semibold text-white truncate">
                                {{ $clase->nombre_asignatura_temporal }}
                            </h4>
                            <p class="text-sm text-red-100">
                                @if($clase->asignatura)
                                    {{ $clase->asignatura->nombre_asignatura }}
                                @else
                                    Asignatura Temporal
                                @endif
                            </p>
                        </div>

                        <!-- Contenido -->
                        <div class="p-4 space-y-3">
                            <!-- Fechas -->
                            <div class="flex items-start gap-2">
                                <i class="text-red-600 fa-solid fa-calendar mt-1 text-sm"></i>
                                <div class="text-sm">
                                    <p class="text-gray-600">
                                        <strong>{{ \Carbon\Carbon::parse($clase->fecha_inicio)->locale('es')->format('d M') }}</strong>
                                        -
                                        <strong>{{ \Carbon\Carbon::parse($clase->fecha_termino)->locale('es')->format('d M Y') }}</strong>
                                    </p>
                                </div>
                            </div>

                            <!-- Horarios -->
                            @if($clase->planificaciones->count() > 0)
                                <div class="flex items-start gap-2">
                                    <i class="text-red-600 fa-solid fa-clock mt-1 text-sm"></i>
                                    <div class="text-sm flex-1">
                                        <p class="text-gray-600">
                                            <strong>{{ $clase->planificaciones->count() }}</strong> módulo(s) asignado(s)
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Cantidad de inscritos -->
                            <div class="flex items-start gap-2">
                                <i class="text-red-600 fa-solid fa-users mt-1 text-sm"></i>
                                <div class="text-sm">
                                    <p class="text-gray-600">
                                        <strong>{{ $clase->cantidad_inscritos }}</strong> estudiante(s)
                                    </p>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    @if($clase->estado === 'Activo')
                                        style="background-color: #d1fae5; color: #065f46;"
                                    @else
                                        style="background-color: #fee2e2; color: #991b1b;"
                                    @endif
                                >
                                    {{ $clase->estado }}
                                </span>
                            </div>

                            <!-- Enlace a clase -->
                            <div class="pt-2 border-t border-gray-100">
                                <a href="{{ route('clases-temporales.show', $clase->id) }}"
                                   class="text-sm font-medium text-white rounded block text-center py-2 transition-opacity hover:opacity-90"
                                   style="background-color: #cd1627;">
                                    Ver Clase Temporal
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        @if($profesor->tipo_profesor === 'Profesor Colaborador')
            <div class="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center gap-3">
                    <i class="text-2xl text-blue-600 fa-solid fa-info-circle"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900">Sin Clases Temporales</h3>
                        <p class="text-sm text-blue-700">Este profesor aún no está asignado a ninguna clase temporal.</p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-professor-form');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: '¿Seguro de guardar los cambios?',
                    text: "Estás a punto de actualizar la información del profesor.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</x-app-layout> 
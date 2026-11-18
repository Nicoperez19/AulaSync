<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">
                        {{ isset($profesorColaborador) ? 'Editar' : 'Nueva' }} Asignatura Temporal
                    </h2>
                    <p class="text-sm text-gray-500">Configure profesor, horarios y sala para la asignatura temporal</p>
                </div>
            </div>
            <a href="{{ route('clases-temporales.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="mr-2 fa-solid fa-arrow-left"></i>Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between max-w-4xl mx-auto h-20">
                    <div class="flex items-center h-full">
                        <div class="flex flex-col items-center h-full justify-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full step-indicator bg-red-600" id="step-1-indicator">
                                <span class="font-bold text-white">1</span>
                            </div>
                            <div class="mt-2 text-xs font-medium text-center text-gray-600 whitespace-nowrap">Profesor</div>
                        </div>
                    </div>
                    <div class="flex-1 h-1 mx-2 step-line bg-gray-300 self-center" id="line-1"></div>
                    <div class="flex items-center h-full">
                        <div class="flex flex-col items-center h-full justify-center">
                            <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full step-indicator" id="step-2-indicator">
                                <span class="font-bold text-white">2</span>
                            </div>
                            <div class="mt-2 text-xs font-medium text-center text-gray-600 whitespace-nowrap">Asignatura</div>
                        </div>
                    </div>
                    <div class="flex-1 h-1 mx-2 bg-gray-300 step-line self-center" id="line-2"></div>
                    <div class="flex items-center h-full">
                        <div class="flex flex-col items-center h-full justify-center">
                            <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full step-indicator" id="step-3-indicator">
                                <span class="font-bold text-white">3</span>
                            </div>
                            <div class="mt-2 text-xs font-medium text-center text-gray-600 whitespace-nowrap">Horarios</div>
                        </div>
                    </div>
                    <div class="flex-1 h-1 mx-2 bg-gray-300 step-line self-center" id="line-3"></div>
                    <div class="flex items-center h-full">
                        <div class="flex flex-col items-center h-full justify-center">
                            <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full step-indicator" id="step-4-indicator">
                                <span class="font-bold text-white">4</span>
                            </div>
                            <div class="mt-2 text-xs font-medium text-center text-gray-600 whitespace-nowrap">Sala</div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" 
                  action="{{ isset($profesorColaborador) ? route('clases-temporales.update', $profesorColaborador) : route('clases-temporales.store') }}"
                  id="formProfesorColaborador">
                @csrf
                @if(isset($profesorColaborador))
                    @method('PUT')
                @endif

                <!-- Step 1: Buscar Profesor -->
                <div class="step-content" id="step-1">
                    <div class="bg-white rounded-lg shadow overflow-visible">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <h3 class="text-lg font-semibold text-white">
                                <i class="fa-solid fa-user mr-2"></i>Paso 1: Buscar Profesor por RUN
                            </h3>
                            <p class="text-sm text-white mt-1">Si el RUN no existe, se habilitarán campos para registrar un nuevo profesor</p>
                        </div>
                        <div class="p-6 overflow-visible">
                            <div class="mb-4 relative z-50">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    RUN del Profesor <span class="text-red-500">*</span>
                                    <span class="text-xs text-gray-500">(solo números, sin dígito verificador)</span>
                                </label>
                                <input type="text" 
                                       id="run_search" 
                                       autocomplete="off"
                                       pattern="[0-9]*"
                                       inputmode="numeric"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                       placeholder="Ingrese RUN (ej: 12345678)">
                                <input type="hidden" name="run_profesor_colaborador" id="run_profesor_colaborador">
                                <input type="hidden" name="profesor_option" id="profesor_option" value="existente">
                                
                                <!-- Sugerencias mientras escribe -->
                                <div id="suggestions-list" class="absolute z-[9999] w-full bg-white border border-gray-300 rounded-md shadow-xl hidden max-h-80 overflow-y-auto mt-1 left-0" style="top: 100%;"></div>
                            </div>

                            <!-- Resultados de búsqueda -->
                            <div id="search-results" class="mb-4 hidden">
                                <div class="border border-green-300 bg-green-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i class="fa-solid fa-check-circle text-green-600 text-xl mt-1"></i>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-green-800">Profesor Encontrado</h4>
                                            <div id="profesor-info" class="mt-2 text-sm text-gray-700"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario para nuevo profesor -->
                            <div id="nuevo-profesor-form" class="hidden">
                                <div class="border border-yellow-300 bg-yellow-50 rounded-lg p-4 mb-4">
                                    <div class="flex items-start gap-3">
                                        <i class="fa-solid fa-exclamation-triangle text-yellow-600 text-xl mt-1"></i>
                                        <div>
                                            <h4 class="font-semibold text-yellow-800">RUN no encontrado</h4>
                                            <p class="text-sm text-yellow-700 mt-1">Complete los datos para registrar un nuevo profesor</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">RUN</label>
                                        <input type="text" name="nuevo_run" id="nuevo_run" readonly
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Nombre Completo <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nuevo_nombre" id="nuevo_nombre"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                               placeholder="Juan Pérez González">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" name="nuevo_email" id="nuevo_email"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                               placeholder="juan.perez@email.com">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono (opcional)</label>
                                        <input type="text" name="nuevo_celular" id="nuevo_celular"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                               placeholder="+56912345678">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Asignatura -->
                <div class="step-content hidden" id="step-2">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <h3 class="text-lg font-semibold text-white">
                                <i class="fa-solid fa-book mr-2"></i>Paso 2: Información de la Asignatura
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre de la Asignatura <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nombre_asignatura_temporal" required id="nombre_asignatura"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                           placeholder="Ej: Taller de Programación Avanzada">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Fecha Inicio <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="fecha_inicio" required id="fecha_inicio"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Fecha Término <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="fecha_termino" required id="fecha_termino"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Cantidad de Inscritos <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="cantidad_inscritos" required id="cantidad_inscritos" min="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                           placeholder="Número de estudiantes">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Tipo de Clase <span class="text-red-500">*</span>
                                    </label>
                                    <select name="tipo_clase" required id="tipo_clase"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        <option value="temporal">Temporal</option>
                                        <option value="reforzamiento">Reforzamiento</option>
                                        <option value="recuperacion">Recuperación</option>
                                        <option value="actividad_externa">Actividad Externa</option>
                                        <option value="actividad_interna">Actividad Interna</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">
                                        <span class="inline-block px-2 py-0.5 bg-purple-200 text-purple-700 rounded mr-2">Temporal</span>
                                        <span class="inline-block px-2 py-0.5 bg-orange-200 text-orange-700 rounded mr-2">Reforzamiento</span>
                                        <span class="inline-block px-2 py-0.5 bg-green-200 text-green-700 rounded mr-2">Recuperación</span>
                                        <span class="inline-block px-2 py-0.5 bg-sky-200 text-sky-700 rounded mr-2">Actividad Externa</span>
                                        <span class="inline-block px-2 py-0.5 bg-indigo-200 text-indigo-700 rounded">Actividad Interna</span>
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción (opcional)</label>
                                    <textarea name="descripcion" rows="3" id="descripcion"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                              placeholder="Breve descripción de la asignatura..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Calendario de Horarios -->
                <div class="step-content hidden" id="step-3">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-white">
                                        <i class="fa-solid fa-calendar-alt mr-2"></i>Paso 3: Seleccionar Horarios
                                    </h3>
                                </div>
                                <div class="flex items-center gap-3">
                                    <!-- Help Icon -->
                                    <div class="relative group cursor-help">
                                        <div class="flex items-center gap-1 text-white hover:text-gray-200 transition">
                                            <i class="fa-solid fa-circle-question text-lg"></i>
                                            <span class="text-sm font-medium">¿Cómo seleccionar horarios?</span>
                                        </div>
                                        <!-- Help Menu -->
                                        <div class="hidden group-hover:block absolute right-0 mt-2 w-72 bg-gray-900 text-white rounded-lg shadow-xl p-4 text-xs z-50 border border-gray-700">
                                            <p class="font-bold mb-3 text-white text-sm">📋 Cómo seleccionar horarios:</p>
                                            <ul class="space-y-2 text-gray-200">
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">1.</span> Haz clic en el módulo inicial</li>
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">2.</span> Arrastra hasta el módulo final</li>
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">3.</span> Los módulos se marcarán en <span class="font-bold text-red-400">rojo</span></li>
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">4.</span> Puedes seleccionar en diferentes días</li>
                                                <li class="flex items-start gap-2"><span class="text-red-400 font-bold">5.</span> Usa el turno para filtrar horarios</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- Turno Select -->
                                    <select id="turno-select" class="px-3 py-1 text-sm border border-gray-300 rounded-md bg-white text-gray-800 focus:outline-none">
                                        <option value="diurno">Diurno (08:10 - 19:00)</option>
                                        <option value="vespertino">Vespertino (19:10 - 23:00)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse" id="horario-grid">
                                    <thead>
                                        <tr>
                                            <th class="border border-gray-300 p-2 bg-gray-100 w-32">Módulo</th>
                                            <th class="border border-gray-300 p-2 bg-gray-100">Lunes</th>
                                            <th class="border border-gray-300 p-2 bg-gray-100">Martes</th>
                                            <th class="border border-gray-300 p-2 bg-gray-100">Miércoles</th>
                                            <th class="border border-gray-300 p-2 bg-gray-100">Jueves</th>
                                            <th class="border border-gray-300 p-2 bg-gray-100">Viernes</th>
                                            <th class="border border-gray-300 p-2 bg-gray-100">Sábado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="horario-tbody">
                                        @php
                                            $modulos = [];
                                            foreach (\App\Helpers\ModulosHelper::getHorariosModulos()['lunes'] ?? [] as $num => $horario) {
                                                $modulos[$num] = substr($horario['inicio'], 0, 5) . ' - ' . substr($horario['fin'], 0, 5);
                                            }
                                            $dias = ['lunes' => 'LU', 'martes' => 'MA', 'miercoles' => 'MI', 'jueves' => 'JU', 'viernes' => 'VI', 'sabado' => 'SA'];
                                            $sabado_modulos = [1, 2, 3, 4, 5, 6];
                                            $modulos_diurnos = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                                            $modulos_vespertinos = [12, 13, 14, 15];
                                        @endphp
                                        @foreach($modulos as $numModulo => $horario)
                                            @if(in_array($numModulo, $modulos_diurnos))
                                                @php $turno = 'diurno'; @endphp
                                            @else
                                                @php $turno = 'vespertino'; @endphp
                                            @endif
                                            <tr class="turno-row" data-turno="{{ $turno }}">
                                                <td class="border border-gray-300 p-2 bg-gray-50 text-center text-xs font-medium whitespace-nowrap">
                                                    <div>{{ $numModulo }}</div>
                                                    <div class="text-gray-500 text-xs">{{ $horario }}</div>
                                                </td>
                                                @foreach($dias as $dia => $prefijo)
                                                    @if($dia === 'sabado' && !in_array($numModulo, $sabado_modulos))
                                                        <td class="border border-gray-300 p-0 bg-gray-400"></td>
                                                    @elseif($dia === 'sabado' && $numModulo > 6)
                                                        <td class="border border-gray-300 p-0 bg-gray-600">
                                                            <div class="modulo-cell h-8 cursor-not-allowed" data-dia="{{ $dia }}" data-modulo="{{ $numModulo }}" data-id-modulo="{{ $prefijo }}.{{ $numModulo }}" data-disabled="true"></div>
                                                        </td>
                                                    @else
                                                        <td class="border border-gray-300 p-0">
                                                            <div class="modulo-cell h-8 cursor-pointer hover:bg-blue-100 transition-colors" data-dia="{{ $dia }}" data-modulo="{{ $numModulo }}" data-id-modulo="{{ $prefijo }}.{{ $numModulo }}"></div>
                                                        </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg transition-all duration-300" id="instrucciones-info">
                                <div class="flex items-start gap-2">
                                    <i class="fa-solid fa-info-circle text-blue-600 mt-1"></i>
                                    <div class="text-sm text-blue-800">
                                        <strong>Instrucciones:</strong> Haz clic en el módulo inicial y arrastra hasta el módulo final. 
                                        Los módulos seleccionados se marcarán en <span class="px-2 py-1 text-white rounded" style="background-color: #cd1627;">rojo</span>.
                                        Puedes seleccionar múltiples bloques en diferentes días.
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4" id="horarios-seleccionados-info"></div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Selección de Sala -->
                <div class="step-content hidden" id="step-4">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <h3 class="text-lg font-semibold text-white">
                                <i class="fa-solid fa-door-open mr-2"></i>Paso 4: Seleccionar Sala
                            </h3>
                            <p class="text-sm text-white mt-1">Salas ordenadas por menor ocupación en los horarios seleccionados</p>
                        </div>
                        <div class="p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="mostrar-salas-especificas" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50" onchange="toggleSalasEspecificas()">
                                    <span class="ml-2 text-sm text-gray-700">Mostrar salas específicas (Auditorios, Laboratorios y Talleres)</span>
                                </label>
                                <button type="button" onclick="mostrarSalasDescartadas()" class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90" style="background-color: #cd1627;">
                                    <i class="fa-solid fa-ban mr-2"></i>Ver salas descartadas
                                </button>
                            </div>
                            <div id="salas-disponibles-container">
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fa-solid fa-spinner fa-spin text-3xl mb-2"></i>
                                    <p>Cargando salas disponibles...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-6">
                    <button type="button" id="btn-prev" 
                            class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hidden">
                        <i class="fa-solid fa-arrow-left mr-2"></i>Anterior
                    </button>
                    <div></div>
                    <div class="flex gap-2">
                        <a href="{{ route('clases-temporales.index') }}" 
                           class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="button" id="btn-next" 
                                class="px-6 py-2 text-sm font-medium text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background-color: #cd1627;" disabled>
                            Siguiente<i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                        <button type="submit" id="btn-submit" 
                                class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 hidden disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fa-solid fa-check mr-2"></i>Guardar
                        </button>
                    </div>
                </div>

                <!-- Hidden input for horarios -->
                <input type="hidden" name="planificaciones" id="planificaciones-input">
            </form>
        </div>
    </div>

    <!-- Modal Salas Descartadas -->
    <div id="modal-salas-descartadas" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50" style="display: none;">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="relative bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-2/3 max-h-[80vh] overflow-hidden flex flex-col">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold" style="color: #cd1627;">
                        <i class="fa-solid fa-ban mr-2"></i>Salas Descartadas
                    </h3>
                    <button onclick="cerrarModalDescartadas()" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-times text-2xl"></i>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 px-6 py-4">
                    <div id="modal-descartadas-content">
                        <p class="text-center text-gray-500 py-8">Cargando...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .modulo-cell.selected {
            background-color: #cd1627 !important;
            color: white;
            font-weight: bold;
        }
        .modulo-cell:hover:not(.selected) {
            background-color: #fee2e2;
        }
        .step-indicator {
            transition: all 0.3s ease;
        }
        .step-line {
            transition: all 0.3s ease;
        }
        .suggestion-item {
            transition: background-color 0.2s;
        }
        #suggestions-list:not(.hidden) {
            display: block;
        }
    </style>
    @endpush

    @push('scripts')
<script>
window.ClasesTemporalesCreateConfig = {
    profesores: @json($profesores)
};
</script>
@vite(['resources/js/views/clases-temporales/create.js'])
@endpush
</x-app-layout>

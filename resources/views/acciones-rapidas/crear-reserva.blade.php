<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fas fa-calendar-plus"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Crear Nueva Reserva</h2>
                    <p class="text-sm text-gray-500">Registrar reserva para profesor o solicitante externo</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('quick-actions.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 text-slate-700 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    <svg class="w-4 h-4 shrink-0 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </x-slot>

    @push('styles')
    <style>
        #autocomplete-results {
            z-index: 1000;
            top: 100%;
            left: 0;
            right: 0;
            transition: opacity 0.2s ease-in-out;
        }
        #autocomplete-results .hover\:bg-gray-100:hover {
            background-color: #f3f4f6;
        }
        #autocomplete-results.hidden {
            opacity: 0;
            pointer-events: none;
        }
        #autocomplete-results:not(.hidden) {
            opacity: 1;
            pointer-events: auto;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('js/admin-panel.js') }}"></script>
    @endpush

    <div class="space-y-6">

        <!-- Formulario -->
        <form id="form-crear-reserva" onsubmit="procesarCrearReserva(event)">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Información del Responsable -->
                <div class="bg-white overflow-visible shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Información del
                            Responsable</h2>

                        <!-- Búsqueda por RUN con Autocompletado -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Buscar persona por RUN o nombre
                            </label>
                            <div class="relative">
                                <input type="text" id="run-busqueda" placeholder="Ingrese RUN o nombre para buscar..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    autocomplete="off" />
                                <div id="autocomplete-results"
                                    class="absolute z-[9999] w-full bg-white border border-gray-300 rounded-md shadow-xl hidden max-h-60 overflow-y-auto mt-1">
                                    <!-- Los resultados del autocompletado aparecerán aquí -->
                                </div>
                            </div>
                            <div id="resultado-busqueda" class="mt-2 text-sm text-green-600"></div>
                        </div>

                        <div class="border-t pt-6">
                            <p class="text-sm text-gray-600 mb-4">Complete la información del responsable:</p>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                                    <input type="text" id="nombre-responsable" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">RUN *</label>
                                    <input type="text" id="run-responsable" required placeholder="Sin puntos ni guión"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico *</label>
                                    <input type="email" id="correo-responsable" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                    <input type="tel" id="telefono-responsable" placeholder="9 dígitos"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                                    <select id="tipo-responsable" required onchange="toggleAsignaturaField()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="">Seleccione tipo</option>
                                        <option value="profesor">Profesor</option>
                                        <option value="colaborador">Profesor Colaborador</option>
                                        <option value="solicitante">Solicitante externo</option>
                                    </select>
                                </div>

                                <!-- Campo para seleccionar asignatura (solo visible cuando es profesor) -->
                                <div id="asignatura-field" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Asignatura *</label>
                                    <select id="id-asignatura"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="">Seleccione una asignatura</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Seleccione la asignatura para esta reserva o use
                                        "Otro" si no aplica</p>
                                </div>

                                <!-- Campo para buscar asignatura (solo visible cuando es colaborador) -->
                                <div id="buscar-asignatura-field" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Asignatura *</label>
                                    <div class="relative">
                                        <input type="text" id="buscar-asignatura"
                                            placeholder="Buscar por código o nombre de asignatura..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            autocomplete="off" />
                                        <div id="autocomplete-asignaturas"
                                            class="absolute z-[9999] w-full bg-white border border-gray-300 rounded-md shadow-xl hidden max-h-60 overflow-y-auto mt-1">
                                            <!-- Los resultados aparecerán aquí -->
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Busque cualquier asignatura del instituto</p>
                                </div>

                                <!-- Campos para actividad externa (solo visible cuando es solicitante) -->
                                <div id="actividad-externa-fields" class="hidden space-y-4">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="fa-solid fa-info-circle text-blue-500"></i>
                                            <span class="text-sm font-medium text-blue-800">Información de la
                                                Actividad e Institución Externa</span>
                                        </div>
                                        <p class="text-xs text-blue-600">Complete la institución o entidad solicitante y los datos de la actividad para los reportes de ocupación.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Institución / Entidad Solicitante</label>
                                        <input type="text" id="institucion-solicitante" list="lista-instituciones-externas"
                                            placeholder="Ej: Servicio de Salud, Municipalidad, Hospital, CESFAM..."
                                            maxlength="120"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                                        <datalist id="lista-instituciones-externas">
                                            <option value="Servicio de Salud"></option>
                                            <option value="Municipalidad"></option>
                                            <option value="Hospital"></option>
                                            <option value="CESFAM"></option>
                                            <option value="SEREMI"></option>
                                            <option value="DAEM / DEM"></option>
                                            <option value="Gobierno Regional"></option>
                                            <option value="Colegio / Liceo"></option>
                                            <option value="Empresa Externa"></option>
                                            <option value="Particular / Otro"></option>
                                        </datalist>
                                        <p class="text-xs text-gray-500 mt-1">Permite filtrar directamente en el reporte de Solicitudes y Reservas (Servicio de Salud, Municipalidad, etc.)</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Actividad
                                            *</label>
                                        <input type="text" id="nombre-actividad"
                                            placeholder="Ej: Capacitación Servicio de Salud, Reunión Municipal, Charla..."
                                            maxlength="255"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción de la
                                            Actividad</label>
                                        <textarea id="descripcion-actividad" rows="2"
                                            placeholder="Breve descripción de la actividad, participantes esperados, etc."
                                            maxlength="500"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de la Reserva -->
                <div class="bg-white overflow-visible shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Detalles de la Reserva
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Espacio *</label>
                                <select id="espacio-reserva" required onchange="actualizarModulosDisponibles()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Cargando espacios...</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Modalidad de Reserva *</label>
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors bg-green-50 border-green-500" id="label-modalidad-puntual">
                                        <input type="radio" name="tipo_frecuencia" value="puntual" checked onchange="toggleModalidadReserva('puntual')" class="text-green-600 focus:ring-green-500">
                                        <div class="ml-2">
                                            <span class="block text-xs font-bold text-gray-800">Puntual / Espontánea</span>
                                            <span class="block text-[11px] text-gray-500">Reserva para un único día</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" id="label-modalidad-recurrente">
                                        <input type="radio" name="tipo_frecuencia" value="recurrente" onchange="toggleModalidadReserva('recurrente')" class="text-green-600 focus:ring-green-500">
                                        <div class="ml-2">
                                            <span class="block text-xs font-bold text-gray-800">Recurrente</span>
                                            <span class="block text-[11px] text-gray-500">Repetir semanalmente</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" id="label-fecha-inicio">Fecha de Inicio *</label>
                                <input type="date" id="fecha-reserva" required min="{{ date('Y-m-d') }}"
                                    value="{{ date('Y-m-d') }}" onchange="cargarModulosParaSeleccion(); actualizarPrevisualizacionRecurrente();"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" />
                            </div>

                            <div id="contenedor-fecha-fin" class="hidden p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                <label class="block text-sm font-medium text-purple-900 mb-1">Fecha de Término *</label>
                                <input type="date" id="fecha-fin-reserva" min="{{ date('Y-m-d') }}" onchange="actualizarPrevisualizacionRecurrente()"
                                    class="w-full px-3 py-2 border border-purple-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white" />
                                <p class="text-xs text-purple-700 mt-2 font-medium" id="resumen-recurrencia-texto"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Módulo inicial *</label>
                                    <select id="modulo-inicial" required onchange="actualizarModulosFinales()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="">Seleccione módulo inicial</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Módulo final *</label>
                                    <select id="modulo-final" required onchange="verificarConflictosReserva()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="">Seleccione módulo final</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                <textarea id="observaciones-reserva" rows="3" placeholder="Observaciones adicionales..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                            </div>

                            <!-- Banner de conflictos de planificación -->
                            <div id="conflicto-banner" class="hidden">
                                <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-red-800 mb-1">
                                                <i class="fa-solid fa-lock mr-1"></i> Espacio Ocupado — Conflicto de Horario
                                            </h4>
                                            <div id="conflicto-detalles" class="text-sm text-red-700 space-y-1"></div>
                                            <p class="text-xs text-red-600 mt-2">La sala tiene programación activa. Si la sala está físicamente desocupada, puede forzar la reserva a continuación.</p>
                                        </div>
                                    </div>

                                    <!-- Checkbox forzar con hold de 3 segundos y botón directo -->
                                    <div class="mt-4 pt-3 border-t border-red-200">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <div class="relative">
                                                <button type="button" id="btn-forzar"
                                                    class="relative flex items-center gap-2 px-4 py-2 bg-white border-2 border-red-300 rounded-lg text-red-700 text-sm font-medium hover:bg-red-50 transition-colors select-none cursor-pointer"
                                                    title="Mantener presionado 3 segundos para forzar">
                                                    <div id="forzar-progress"
                                                        class="absolute inset-0 bg-red-200 rounded-lg opacity-40 transition-all duration-100"
                                                        style="width: 0%"></div>
                                                    <span class="relative z-10 flex items-center gap-2">
                                                        <svg id="forzar-icon" class="w-4 h-4" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                        </svg>
                                                        <span id="forzar-texto">Mantener presionado 3s para forzar</span>
                                                    </span>
                                                </button>
                                            </div>
                                            <button type="button" onclick="activarForzar()"
                                                class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 border border-amber-300 rounded-lg text-amber-800 text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                                                title="Forzar el uso de la sala sin esperar">
                                                <i class="fa-solid fa-unlock-keyhole text-amber-600"></i>
                                                <span>Forzar inmediatamente</span>
                                            </button>
                                            <input type="hidden" id="forzar-reserva" value="0" />
                                        </div>
                                        <p id="forzar-estado" class="text-xs text-gray-500 mt-2 hidden">
                                            <i class="fa-solid fa-check-circle text-green-500 mr-1"></i>
                                            Reserva forzada activada. Se creará la reserva ignorando la planificación
                                            existente.
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <a href="{{ route('quick-actions.index') }}"
                            class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                            <i class="fa-solid fa-plus w-4 h-4 mr-2 inline"></i>
                            Crear Reserva
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            window.CrearReservaConfig = {
                gestionarReservasUrl: "{{ route('quick-actions.gestionar-reservas') }}",
                modulosLunes: @json(\App\Helpers\ModulosHelper::getHorariosModulos()['lunes'] ?? [])
            };
        </script>
        @vite(['resources/js/views/acciones-rapidas/crear-reserva.js'])
    @endpush
</x-app-layout>

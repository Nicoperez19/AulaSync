@extends('layouts.quick_actions.app')

@section('title', 'Crear Reserva - Acciones Rápidas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Crear Nueva Reserva</h1>
                    <p class="text-gray-600 mt-1">Registrar reserva para profesor o solicitante externo</p>
                </div>
                <a href="{{ route('quick-actions.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fa-solid fa-arrow-left w-4 h-4 mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <form id="form-crear-reserva" onsubmit="procesarCrearReserva(event)">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Información del Responsable -->
            <div class="bg-white overflow-visible shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Información del Responsable</h2>

                    <!-- Búsqueda por RUN con Autocompletado -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Buscar persona por RUN o nombre
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="run-busqueda"
                                placeholder="Ingrese RUN o nombre para buscar..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                autocomplete="off"
                            />
                            <div id="autocomplete-results" class="absolute z-[9999] w-full bg-white border border-gray-300 rounded-md shadow-xl hidden max-h-60 overflow-y-auto mt-1">
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
                                <input
                                    type="text"
                                    id="nombre-responsable"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RUN *</label>
                                <input
                                    type="text"
                                    id="run-responsable"
                                    required
                                    placeholder="Sin puntos ni guión"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico *</label>
                                <input
                                    type="email"
                                    id="correo-responsable"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input
                                    type="tel"
                                    id="telefono-responsable"
                                    placeholder="9 dígitos"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                                <select
                                    id="tipo-responsable"
                                    required
                                    onchange="toggleAsignaturaField()"
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
                                <select
                                    id="id-asignatura"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Seleccione una asignatura</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Seleccione la asignatura para esta reserva</p>
                            </div>

                            <!-- Campo para buscar asignatura (solo visible cuando es colaborador) -->
                            <div id="buscar-asignatura-field" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Asignatura *</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="buscar-asignatura"
                                        placeholder="Buscar por código o nombre de asignatura..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        autocomplete="off"
                                    />
                                    <div id="autocomplete-asignaturas" class="absolute z-[9999] w-full bg-white border border-gray-300 rounded-md shadow-xl hidden max-h-60 overflow-y-auto mt-1">
                                        <!-- Los resultados aparecerán aquí -->
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Busque cualquier asignatura del instituto</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de la Reserva -->
            <div class="bg-white overflow-visible shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Detalles de la Reserva</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Espacio *</label>
                            <select
                                id="espacio-reserva"
                                required
                                onchange="actualizarModulosDisponibles()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Cargando espacios...</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                            <input
                                type="date"
                                id="fecha-reserva"
                                required
                                min="{{ date('Y-m-d') }}"
                                value="{{ date('Y-m-d') }}"
                                onchange="cargarModulosParaSeleccion()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo inicial *</label>
                                <select
                                    id="modulo-inicial"
                                    required
                                    onchange="actualizarModulosFinales()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Seleccione módulo inicial</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo final *</label>
                                <select
                                    id="modulo-final"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Seleccione módulo final</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea
                                id="observaciones-reserva"
                                rows="3"
                                placeholder="Observaciones adicionales..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('quick-actions.index') }}"
                       class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancelar
                    </a>
                    <button
                        type="submit"
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
// Función específica para el mantenedor
async function procesarCrearReserva(event) {
    event.preventDefault();

    const formData = {
        nombre: document.getElementById('nombre-responsable').value.trim(),
        run: document.getElementById('run-responsable').value.trim(),
        correo: document.getElementById('correo-responsable').value.trim(),
        telefono: document.getElementById('telefono-responsable').value.trim() || null,
        tipo: document.getElementById('tipo-responsable').value,
        id_asignatura: document.getElementById('id-asignatura').value || null,
        espacio: document.getElementById('espacio-reserva').value,
        fecha: document.getElementById('fecha-reserva').value,
        modulo_inicial: parseInt(document.getElementById('modulo-inicial').value),
        modulo_final: parseInt(document.getElementById('modulo-final').value),
        observaciones: document.getElementById('observaciones-reserva').value.trim()
    };

    console.log('📤 Datos a enviar:', formData);
    console.log('🏢 ID Espacio seleccionado:', formData.espacio);

    // Validaciones
    if (!formData.nombre || !formData.run || !formData.correo || !formData.tipo) {
        Swal.fire('Error', 'Complete todos los campos obligatorios del responsable', 'error');
        return;
    }

    // Validar asignatura si es profesor (no colaborador)
    if (formData.tipo === 'profesor' && !formData.id_asignatura) {
        Swal.fire('Error', 'Debe seleccionar una asignatura para la reserva del profesor', 'error');
        return;
    }

    // Validar que se haya seleccionado un espacio válido
    if (!formData.espacio) {
        Swal.fire('Error', 'Debe seleccionar un espacio para la reserva', 'error');
        return;
    }

    if (!formData.espacio || !formData.fecha || !formData.modulo_inicial || !formData.modulo_final) {
        Swal.fire('Error', 'Complete todos los campos obligatorios de la reserva', 'error');
        return;
    }

    if (formData.modulo_inicial > formData.modulo_final) {
        Swal.fire('Error', 'El módulo inicial no puede ser mayor al módulo final', 'error');
        return;
    }

    try {
        // Mostrar loading
        Swal.fire({
            title: 'Creando reserva...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch('/quick-actions/api/crear-reserva', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                title: '¡Reserva Creada Exitosamente!',
                html: `
                    <div class="text-left bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                        <div class="flex items-center mb-3">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-semibold text-green-800">Detalles de la Reserva</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <strong>ID Reserva:</strong> <span class="text-blue-600 font-mono ml-1">${result.id_reserva}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <strong>Responsable:</strong> <span class="ml-1">${result.datos.responsable}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <strong>Espacio:</strong> <span class="ml-1">${result.datos.espacio}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                </svg>
                                <strong>Fecha:</strong> <span class="ml-1">${result.datos.fecha}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <strong>Módulos:</strong> <span class="ml-1">${result.datos.modulos}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <strong>Hora inicio:</strong> <span class="ml-1">${result.datos.hora}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <strong>Tipo:</strong> <span class="px-2 py-1 rounded text-xs ml-1 ${result.datos.tipo === 'Académica' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}">${result.datos.tipo}</span>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-yellow-50 border-l-2 border-yellow-400 rounded">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-yellow-800">Creado por: ${result.datos.creado_por}</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1 ml-6">Esta reserva incluye observaciones automáticas de trazabilidad</p>
                        </div>
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'Ir a Gestión de Reservas',
                showCancelButton: true,
                cancelButtonText: 'Crear otra reserva',
                cancelButtonColor: '#6B7280',
                confirmButtonColor: '#10B981',
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('quick-actions.gestionar-reservas') }}";
                } else {
                    // Limpiar formulario para crear otra reserva
                    document.getElementById('form-crear-reserva').reset();
                    document.getElementById('resultado-busqueda').innerHTML = '';
                    document.getElementById('fecha-reserva').value = new Date().toISOString().split('T')[0];
                    cargarEspaciosDisponibles();
                    cargarModulosParaSeleccion();
                    console.log('✅ Formulario limpiado para nueva reserva');
                }
            });
        } else {
            Swal.fire('Error', result.mensaje || 'Error al crear la reserva', 'error');
        }
    } catch (error) {
        console.error('Error al crear reserva:', error);
        Swal.fire('Error', 'Error de conexión al crear la reserva', 'error');
    }
}

// Debug function para probar la conectividad
async function testConnectivity() {
    console.log('🧪 Probando conectividad...');
    try {
        const response = await fetch('/quick-actions/debug/test');
        const data = await response.json();
        console.log('✅ Test de conectividad exitoso:', data);

        // Test adicional de espacios
        const espaciosResponse = await fetch('/quick-actions/debug/espacios');
        const espaciosData = await espaciosResponse.json();
        console.log('🏢 Debug espacios:', espaciosData);

        // Test adicional de personas
        const personasResponse = await fetch('/quick-actions/debug/personas');
        const personasData = await personasResponse.json();
        console.log('👥 Debug personas:', personasData);

    } catch (error) {
        console.error('❌ Test de conectividad falló:', error);
    }
}

// Variables para autocompletado
let timeoutId = null;
let selectedPersona = null;

// Función para buscar personas (autocompletado)
async function buscarPersonas(termino) {
    if (termino.length < 2) {
        document.getElementById('autocomplete-results').classList.add('hidden');
        return;
    }

    try {
        const response = await fetch(`/quick-actions/api/buscar-personas?q=${encodeURIComponent(termino)}`);
        const data = await response.json();

        if (data.success && data.personas.length > 0) {
            mostrarResultadosAutocompletado(data.personas);
        } else {
            document.getElementById('autocomplete-results').classList.add('hidden');
        }
    } catch (error) {
        console.error('Error al buscar personas:', error);
        document.getElementById('autocomplete-results').classList.add('hidden');
    }
}

// Mostrar resultados del autocompletado
function mostrarResultadosAutocompletado(personas) {
    const resultsDiv = document.getElementById('autocomplete-results');
    resultsDiv.innerHTML = '';

    if (personas.length === 0) {
        resultsDiv.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">No se encontraron personas</div>';
        resultsDiv.classList.remove('hidden');
        return;
    }

    personas.forEach(persona => {
        const item = document.createElement('div');
        item.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors';
        item.innerHTML = `
            <div class="font-semibold text-sm text-gray-900">${persona.nombre}</div>
            <div class="text-xs text-gray-600 mt-0.5">${persona.run} - ${persona.tipo === 'profesor' ? 'Profesor' : 'Solicitante'}</div>
        `;

        item.addEventListener('click', () => seleccionarPersona(persona));
        resultsDiv.appendChild(item);
    });

    resultsDiv.classList.remove('hidden');
}

// Seleccionar una persona del autocompletado
function seleccionarPersona(persona) {
    selectedPersona = persona;

    // Completar campos automáticamente
    document.getElementById('run-busqueda').value = `${persona.run} - ${persona.nombre}`;
    document.getElementById('nombre-responsable').value = persona.nombre;
    document.getElementById('run-responsable').value = persona.run;
    document.getElementById('correo-responsable').value = persona.email || '';
    document.getElementById('telefono-responsable').value = persona.telefono || '';
    document.getElementById('tipo-responsable').value = persona.tipo;

    // Activar campo de asignatura si es profesor
    toggleAsignaturaField();

    // Ocultar resultados
    document.getElementById('autocomplete-results').classList.add('hidden');

    // Mostrar mensaje de confirmación
    document.getElementById('resultado-busqueda').innerHTML = `
        <i class="fa-solid fa-check-circle w-4 h-4 mr-1"></i>
        Información cargada para: ${persona.nombre} (${persona.tipo === 'profesor' ? 'Profesor' : 'Solicitante'})
    `;

    console.log('✅ Persona seleccionada:', persona);
}

// Configurar eventos del autocompletado
function configurarAutocompletado() {
    const input = document.getElementById('run-busqueda');
    const resultsDiv = document.getElementById('autocomplete-results');

    // Evento de escritura
    input.addEventListener('input', function(e) {
        const valor = e.target.value.trim();

        // Cancelar búsqueda anterior
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        // Nueva búsqueda con delay
        timeoutId = setTimeout(() => {
            buscarPersonas(valor);
        }, 300);
    });

    // Ocultar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.classList.add('hidden');
        }
    });

    // Limpiar información cuando se borra el campo
    input.addEventListener('input', function(e) {
        if (e.target.value.trim() === '') {
            document.getElementById('nombre-responsable').value = '';
            document.getElementById('run-responsable').value = '';
            document.getElementById('correo-responsable').value = '';
            document.getElementById('telefono-responsable').value = '';
            document.getElementById('tipo-responsable').value = '';
            document.getElementById('resultado-busqueda').innerHTML = '';
            selectedPersona = null;
        }
    });
}

// Mapeo de módulos a horarios para crear reserva
const horariosModulosCrearReserva = {
    1:  '08:10 - 09:00',
    2:  '09:10 - 10:00',
    3:  '10:10 - 11:00',
    4:  '11:10 - 12:00',
    5:  '12:10 - 13:00',
    6:  '13:10 - 14:00',
    7:  '14:10 - 15:00',
    8:  '15:10 - 16:00',
    9:  '16:10 - 17:00',
    10: '17:10 - 18:00',
    11: '18:10 - 19:00',
    12: '19:10 - 20:00',
    13: '20:10 - 21:00',
    14: '21:10 - 22:00',
    15: '22:10 - 23:00',
    16: '23:10 - 00:00'
};

// Función para cargar módulos con horarios
function cargarModulosParaSeleccion() {
    const moduloInicialSelect = document.getElementById('modulo-inicial');
    const moduloFinalSelect = document.getElementById('modulo-final');

    if (!moduloInicialSelect || !moduloFinalSelect) {
        console.error('❌ No se encontraron los selectores de módulos');
        return;
    }

    // Limpiar opciones existentes (excepto la primera opción)
    moduloInicialSelect.innerHTML = '<option value="">Seleccionar módulo inicial</option>';
    moduloFinalSelect.innerHTML = '<option value="">Seleccionar módulo final</option>';

    // Agregar opciones de módulos con horarios
    for (let i = 1; i <= 12; i++) {
        const horario = horariosModulosCrearReserva[i];
        const optionTextInicial = `Módulo ${i} (${horario})`;
        const optionTextFinal = `Módulo ${i} (${horario})`;

        // Opción para módulo inicial
        const optionInicial = document.createElement('option');
        optionInicial.value = i;
        optionInicial.textContent = optionTextInicial;
        moduloInicialSelect.appendChild(optionInicial);

        // Opción para módulo final
        const optionFinal = document.createElement('option');
        optionFinal.value = i;
        optionFinal.textContent = optionTextFinal;
        moduloFinalSelect.appendChild(optionFinal);
    }

    console.log('✅ Módulos con horarios cargados');

    // Agregar listener para filtrar módulos finales según el inicial seleccionado
    moduloInicialSelect.addEventListener('change', function() {
        const moduloInicialSeleccionado = parseInt(this.value);

        if (moduloInicialSeleccionado) {
            // Limpiar y recargar módulo final con opciones válidas
            moduloFinalSelect.innerHTML = '<option value="">Seleccionar módulo final</option>';

            // Solo mostrar módulos finales >= al inicial
            for (let i = moduloInicialSeleccionado; i <= 12; i++) {
                const horario = horariosModulosCrearReserva[i];
                const optionText = `Módulo ${i} (${horario})`;

                const option = document.createElement('option');
                option.value = i;
                option.textContent = optionText;
                moduloFinalSelect.appendChild(option);
            }

            console.log(`📝 Módulos finales filtrados desde módulo ${moduloInicialSeleccionado}`);
        } else {
            // Si no hay módulo inicial, mostrar todos los módulos finales
            cargarModulosParaSeleccion();
        }
    });
}

// Función para mostrar/ocultar el campo de asignatura
function toggleAsignaturaField() {
    const tipoSelect = document.getElementById('tipo-responsable');
    const asignaturaField = document.getElementById('asignatura-field');
    const buscarAsignaturaField = document.getElementById('buscar-asignatura-field');
    const asignaturaSelect = document.getElementById('id-asignatura');
    const buscarAsignaturaInput = document.getElementById('buscar-asignatura');

    // Limpiar campos
    asignaturaSelect.innerHTML = '<option value="">Seleccione una asignatura</option>';
    buscarAsignaturaInput.value = '';

    if (tipoSelect.value === 'profesor') {
        // Profesor regular: mostrar select de sus asignaturas
        asignaturaField.classList.remove('hidden');
        buscarAsignaturaField.classList.add('hidden');
        cargarAsignaturasProfesor();
    } else if (tipoSelect.value === 'colaborador') {
        // Profesor colaborador: mostrar búsqueda de asignaturas
        asignaturaField.classList.add('hidden');
        buscarAsignaturaField.classList.remove('hidden');
        configurarBusquedaAsignaturas();
    } else {
        // Solicitante: ocultar ambos
        asignaturaField.classList.add('hidden');
        buscarAsignaturaField.classList.add('hidden');
    }
}

// Función para cargar asignaturas del profesor
async function cargarAsignaturasProfesor() {
    const runProfesor = document.getElementById('run-responsable').value.trim();
    const asignaturaSelect = document.getElementById('id-asignatura');

    if (!runProfesor) {
        asignaturaSelect.innerHTML = '<option value="">Primero seleccione un profesor</option>';
        console.warn('⚠️ No hay RUN de profesor para cargar asignaturas');
        return;
    }

    try {
        console.log('📚 Cargando asignaturas para profesor:', runProfesor);
        asignaturaSelect.innerHTML = '<option value="">Cargando asignaturas...</option>';

        const response = await fetch(`/api/profesor/${runProfesor}/asignaturas`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log('📦 Datos recibidos de asignaturas:', data);

        asignaturaSelect.innerHTML = '<option value="">Seleccione una asignatura</option>';

        if (data.success && data.asignaturas && data.asignaturas.length > 0) {
            console.log(`✅ Se encontraron ${data.asignaturas.length} asignaturas para el profesor`);
            data.asignaturas.forEach(asignatura => {
                const option = document.createElement('option');
                option.value = asignatura.id_asignatura;
                option.textContent = `${asignatura.codigo_asignatura} - ${asignatura.nombre_asignatura}`;
                asignaturaSelect.appendChild(option);
            });
        } else {
            console.warn('⚠️ No se encontraron asignaturas para el profesor:', runProfesor);
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No se encontraron asignaturas para este profesor';
            asignaturaSelect.appendChild(option);

            // Mostrar notificación al usuario
            Swal.fire({
                title: 'Sin asignaturas',
                text: 'Este profesor no tiene asignaturas asignadas. Puede contactar al administrador o usar el tipo "Profesor Colaborador" para buscar cualquier asignatura.',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        }
    } catch (error) {
        console.error('❌ Error al cargar asignaturas:', error);
        asignaturaSelect.innerHTML = '<option value="">Error al cargar asignaturas</option>';

        Swal.fire({
            title: 'Error',
            text: 'No se pudieron cargar las asignaturas. Por favor intente nuevamente.',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    }
}

// Función para buscar asignaturas (colaboradores)
let timeoutAsignaturas = null;
async function buscarAsignaturas(termino) {
    if (termino.length < 2) {
        document.getElementById('autocomplete-asignaturas').classList.add('hidden');
        return;
    }

    try {
        const response = await fetch(`/quick-actions/api/buscar-asignaturas?q=${encodeURIComponent(termino)}`);
        const data = await response.json();

        if (data.success && data.asignaturas.length > 0) {
            mostrarResultadosAsignaturas(data.asignaturas);
        } else {
            document.getElementById('autocomplete-asignaturas').classList.add('hidden');
        }
    } catch (error) {
        console.error('Error al buscar asignaturas:', error);
        document.getElementById('autocomplete-asignaturas').classList.add('hidden');
    }
}

// Mostrar resultados de búsqueda de asignaturas
function mostrarResultadosAsignaturas(asignaturas) {
    const resultsDiv = document.getElementById('autocomplete-asignaturas');
    resultsDiv.innerHTML = '';

    if (asignaturas.length === 0) {
        resultsDiv.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">No se encontraron asignaturas</div>';
        resultsDiv.classList.remove('hidden');
        return;
    }

    asignaturas.forEach(asignatura => {
        const item = document.createElement('div');
        item.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors';
        item.innerHTML = `
            <div class="font-semibold text-sm text-gray-900">${asignatura.codigo_asignatura}</div>
            <div class="text-xs text-gray-600 mt-0.5">${asignatura.nombre_asignatura}</div>
        `;

        item.addEventListener('click', () => seleccionarAsignatura(asignatura));
        resultsDiv.appendChild(item);
    });

    resultsDiv.classList.remove('hidden');
    console.log('✅ Mostrando', asignaturas.length, 'asignaturas');
}

// Seleccionar asignatura del autocompletado
function seleccionarAsignatura(asignatura) {
    const buscarInput = document.getElementById('buscar-asignatura');
    const asignaturaSelect = document.getElementById('id-asignatura');

    buscarInput.value = `${asignatura.codigo_asignatura} - ${asignatura.nombre_asignatura}`;

    // Guardar el ID en un campo oculto o en el select
    asignaturaSelect.innerHTML = `<option value="${asignatura.id_asignatura}" selected>${asignatura.codigo_asignatura} - ${asignatura.nombre_asignatura}</option>`;

    document.getElementById('autocomplete-asignaturas').classList.add('hidden');

    console.log('✅ Asignatura seleccionada:', asignatura);
}

// Configurar búsqueda de asignaturas para colaboradores
function configurarBusquedaAsignaturas() {
    const input = document.getElementById('buscar-asignatura');
    const resultsDiv = document.getElementById('autocomplete-asignaturas');

    if (!input || !resultsDiv) return;

    // Evento de escritura
    input.addEventListener('input', function(e) {
        const valor = e.target.value.trim();

        if (timeoutAsignaturas) {
            clearTimeout(timeoutAsignaturas);
        }

        timeoutAsignaturas = setTimeout(() => {
            buscarAsignaturas(valor);
        }, 300);
    });

    // Ocultar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.classList.add('hidden');
        }
    });
}

// Función para cargar espacios disponibles
async function cargarEspaciosDisponibles() {
    const espacioSelect = document.getElementById('espacio-reserva');

    if (!espacioSelect) {
        console.error('❌ No se encontró el select de espacios');
        return;
    }

    try {
        espacioSelect.innerHTML = '<option value="">Cargando espacios...</option>';

        const response = await fetch('/quick-actions/api/espacios');
        const data = await response.json();

        if (data.success && data.data) {
            espacioSelect.innerHTML = '<option value="">Seleccione un espacio</option>' +
                data.data.map(espacio => {
                    const nombre = espacio.nombre_espacio || espacio.nombre_tipo_espacio || 'Sin nombre';
                    return `<option value="${espacio.id_espacio}">${espacio.id_espacio} - ${nombre}</option>`;
                }).join('');

            console.log('✅ Espacios cargados:', data.data.length);
        } else {
            espacioSelect.innerHTML = '<option value="">No hay espacios disponibles</option>';
            console.warn('⚠️ No se encontraron espacios');
        }
    } catch (error) {
        console.error('❌ Error al cargar espacios:', error);
        espacioSelect.innerHTML = '<option value="">Error al cargar espacios</option>';
    }
}

// Función para actualizar módulos disponibles según espacio (placeholder)
function actualizarModulosDisponibles() {
    // Por ahora, esta función solo recarga los módulos
    // En el futuro podría filtrar módulos ocupados según el espacio y fecha
    cargarModulosParaSeleccion();
    console.log('🔄 Módulos actualizados para el espacio seleccionado');
}

// Función para actualizar módulos finales
function actualizarModulosFinales() {
    // Ya implementada en cargarModulosParaSeleccion
    console.log('🔄 Actualizando módulos finales...');
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando carga de datos...');

    // Verificar que SweetAlert esté disponible
    if (typeof Swal !== 'undefined') {
        console.log('✅ SweetAlert2 cargado correctamente');
    } else {
        console.error('❌ SweetAlert2 no está disponible');
    }

    testConnectivity();
    cargarEspaciosDisponibles();
    cargarModulosParaSeleccion();
    configurarAutocompletado();
    console.log('🔍 Autocompletado configurado');
});
</script>
@endpush
@endsection

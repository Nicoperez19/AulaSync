@extends('layouts.quick_actions.app')

@section('title', 'Gestionar Reservas - Acciones Rápidas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-4">
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
                                <i class="fas fa-calendar-check mr-2 sm:mr-3 text-blue-600"></i>
                                Gestión de Reservas
                            </h1>
                            <p class="text-sm sm:text-base text-gray-600 mt-1">Administrar estados de reservas activas y finalizadas</p>
                        </div>
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full">
                            <i class="fa-solid fa-circle-check text-green-600 text-sm"></i>
                            <div class="flex items-center gap-1">
                                <span class="text-xs font-medium text-green-700">Activas:</span>
                                <span class="text-sm font-bold text-green-900" id="stats-activas-header">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <a href="{{ route('quick-actions.crear-reserva') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm sm:text-base rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Nueva Reserva
                    </a>
                    <a href="{{ route('quick-actions.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm sm:text-base rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget móvil de reservas activas -->
    <div class="sm:hidden bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-3">
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check text-green-600 text-lg"></i>
                <span class="text-sm font-medium text-green-700">Activas:</span>
                <span class="text-xl font-bold text-green-900" id="stats-activas-mobile">0</span>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select 
                        id="filtro-estado-reserva"
                        onchange="filtrarReservas()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                        <option value="">Todos los estados</option>
                        <option value="activa">Activas</option>
                        <option value="finalizada">Finalizadas</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input 
                        type="date"
                        id="filtro-fecha-reserva"
                        onchange="filtrarReservas()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordenar por</label>
                    <select 
                        id="ordenar-reservas"
                        onchange="aplicarOrdenamiento()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                        <option value="fecha-desc">Fecha (más reciente)</option>
                        <option value="fecha-asc">Fecha (más antigua)</option>
                        <option value="responsable-asc">Responsable (A-Z)</option>
                        <option value="responsable-desc">Responsable (Z-A)</option>
                        <option value="espacio-asc">Espacio (A-Z)</option>
                        <option value="espacio-desc">Espacio (Z-A)</option>
                        <option value="hora-asc">Hora (temprano-tarde)</option>
                        <option value="hora-desc">Hora (tarde-temprano)</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button 
                        onclick="cargarReservas()"
                        class="w-full px-4 py-2 bg-blue-600 text-white text-sm sm:text-base rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-rotate-right w-4 h-4 mr-2 inline"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de reservas -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6">
            <!-- Versión Desktop -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10">
                                        <input type="checkbox" id="select-all-reservas" onchange="toggleSelectAllReservas(this)" class="rounded">
                                    </th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:text-gray-700 w-20" onclick="ordenarPor('estado')">
                                        Estado
                                        <i class="fa-solid fa-sort ml-1 text-xs"></i>
                                    </th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:text-gray-700 w-24" onclick="ordenarPor('espacio')">
                                        Espacio
                                        <i class="fa-solid fa-sort ml-1 text-xs"></i>
                                    </th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:text-gray-700" onclick="ordenarPor('responsable')">
                                        Responsable
                                        <i class="fa-solid fa-sort ml-1 text-xs"></i>
                                    </th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:text-gray-700 w-28" onclick="ordenarPor('fecha')">
                                        Fecha
                                        <i class="fa-solid fa-sort ml-1 text-xs"></i>
                                    </th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32">
                                        Módulos
                                    </th>
                                    <th class="px-2 py-3 text-right text-xs font-medium text-gray-500 uppercase w-32">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabla-reservas-body" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="7" class="px-3 sm:px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                                            <p>Cargando reservas...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Versión Mobile/Tablet (Cards) -->
            <div id="tabla-reservas-cards" class="lg:hidden space-y-4">
                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                    <p>Cargando reservas...</p>
                </div>
            </div>
            
            <!-- Controles de acciones en lote -->
            <div id="acciones-lote" class="border-t border-gray-200 bg-gray-50 px-4 py-3 sm:px-6 sm:py-4" style="display: none;">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-sm text-gray-700">
                        <span id="contador-seleccionadas">0</span> reserva(s) seleccionada(s)
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <button 
                            type="button"
                            id="btn-finalizar-lote"
                            onclick="finalizarReservasEnLote()"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-xmark w-4 h-4 mr-2"></i>
                            Finalizar Seleccionadas
                        </button>
                        <button 
                            type="button"
                            onclick="limpiarSeleccion()"
                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            <i class="fa-solid fa-times w-4 h-4 mr-2"></i>
                            Limpiar Selección
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<!-- Modal para editar reserva -->
<div id="modal-editar-reserva" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fa-solid fa-edit text-blue-600 mr-2"></i>
                Editar Reserva
            </h3>
            <button onclick="cerrarModalEditar()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-times text-2xl"></i>
            </button>
        </div>
        
        <form id="form-editar-reserva-modal" onsubmit="guardarEdicionReserva(event)">
            <input type="hidden" id="edit-reserva-id">
            
            <!-- Información del responsable (solo lectura) -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Información del Responsable</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">Nombre:</span>
                        <span class="text-gray-900" id="edit-responsable-nombre">-</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">RUN:</span>
                        <span class="text-gray-900" id="edit-responsable-run">-</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Tipo:</span>
                        <span class="text-gray-900" id="edit-responsable-tipo">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Campos editables -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Espacio *</label>
                        <select 
                            id="edit-codigo-espacio"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Cargando espacios...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                        <input 
                            type="date" 
                            id="edit-fecha"
                            required
                            min="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Módulo inicial *</label>
                        <select 
                            id="edit-modulo-inicial"
                            required
                            onchange="actualizarModulosFinalesModal()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccione módulo inicial</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Módulo final *</label>
                        <select 
                            id="edit-modulo-final"
                            required
                            onchange="actualizarPreviewHorario()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccione módulo final</option>
                        </select>
                    </div>
                </div>
                
                <!-- Horario original (FIJO - no cambia) -->
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-clock text-gray-600 mt-0.5"></i>
                        <div class="text-sm">
                            <span class="font-medium text-gray-700">Horario original (referencia):</span>
                            <span class="text-gray-900" id="horario-original-fijo">-</span>
                        </div>
                    </div>
                </div>
                
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones originales</label>
                    <textarea 
                        id="edit-observaciones-originales"
                        rows="2"
                        disabled
                        class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600 text-sm"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Agregar observaciones
                        <span class="text-xs text-gray-500">(se agregará a las existentes)</span>
                    </label>
                    <textarea 
                        id="edit-observaciones-nuevas"
                        rows="2"
                        placeholder="Escriba observaciones adicionales..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex justify-end gap-3">
                <button 
                    type="button"
                    onclick="cerrarModalEditar()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
                <button 
                    type="submit"
                    id="btn-guardar-edicion"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-save mr-2"></i>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Variables específicas para gestión de reservas
let reservasOriginales = [];
let ordenActual = {campo: 'fecha', direccion: 'desc'};

// Función para editar reserva - Definida al inicio para estar disponible
window.editarReserva = async function(idReserva) {
    console.log('🟢 Abriendo modal de edición para reserva:', idReserva);
    
    // Buscar la reserva en los datos originales
    const reserva = reservasOriginales.find(r => r.id == idReserva);
    
    if (!reserva) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró la reserva'
        });
        return;
    }
    
    if (reserva.estado !== 'activa') {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Solo se pueden editar reservas activas'
        });
        return;
    }
    
    // Cargar espacios y módulos si no están cargados
    await cargarEspaciosParaModal();
    await cargarModulosParaModal();
    
    // Llenar el modal con los datos de la reserva
    document.getElementById('edit-reserva-id').value = reserva.id;
    document.getElementById('edit-responsable-nombre').textContent = reserva.nombre_responsable || 'Sin nombre';
    document.getElementById('edit-responsable-run').textContent = reserva.run_responsable || 'N/A';
    document.getElementById('edit-responsable-tipo').textContent = reserva.tipo_responsable || 'N/A';
    document.getElementById('edit-codigo-espacio').value = reserva.id_espacio;
    document.getElementById('edit-fecha').value = reserva.fecha;
    
    // Guardar el horario original FIJO como referencia
    const horarioOriginal = reserva.modulos_info && reserva.modulos_info.rango_horario 
        ? `Módulo ${reserva.modulos_info.modulo_inicial || '?'} a Módulo ${reserva.modulos_info.modulo_final || '?'} (${reserva.modulos_info.rango_horario})`
        : `Hora inicio: ${reserva.hora ? reserva.hora.substring(0, 5) : 'N/A'} - ${reserva.modulos || 1} módulo(s)`;
    document.getElementById('horario-original-fijo').textContent = horarioOriginal;
    
    // Configurar módulos basado en la reserva actual
    const cantModulos = parseInt(reserva.modulos || 1);
    const horaInicio = reserva.hora ? reserva.hora.substring(0, 5) : '';
    
    // Intentar determinar módulo inicial basado en la hora
    if (horaInicio && modulosCargados.length > 0) {
        const moduloInicial = modulosCargados.find(m => m.hora_inicio === horaInicio + ':00');
        if (moduloInicial) {
            document.getElementById('edit-modulo-inicial').value = moduloInicial.id_modulo;
            actualizarModulosFinalesModal();
            
            // Seleccionar módulo final basado en cantidad
            const indexInicial = modulosCargados.findIndex(m => m.id_modulo === moduloInicial.id_modulo);
            if (indexInicial >= 0 && indexInicial + cantModulos - 1 < modulosCargados.length) {
                const moduloFinal = modulosCargados[indexInicial + cantModulos - 1];
                document.getElementById('edit-modulo-final').value = moduloFinal.id_modulo;
                actualizarPreviewHorario();
            }
        }
    }
    
    // Observaciones - separar originales
    const observacionesOriginales = reserva.observaciones || 'Sin observaciones';
    document.getElementById('edit-observaciones-originales').value = observacionesOriginales;
    document.getElementById('edit-observaciones-nuevas').value = '';
    
    // Mostrar el modal
    document.getElementById('modal-editar-reserva').classList.remove('hidden');
}

// Cerrar modal de edición
window.cerrarModalEditar = function() {
    document.getElementById('modal-editar-reserva').classList.add('hidden');
    document.getElementById('form-editar-reserva-modal').reset();
}

// Cargar espacios para el modal
let espaciosCargados = [];
async function cargarEspaciosParaModal() {
    if (espaciosCargados.length > 0) {
        return; // Ya están cargados
    }
    
    try {
        const response = await fetch('/quick-actions/api/espacios');
        const data = await response.json();
        
        if (data.success && data.data) {
            espaciosCargados = data.data;
            const select = document.getElementById('edit-codigo-espacio');
            select.innerHTML = '<option value="">Seleccione un espacio</option>' +
                espaciosCargados.map(espacio => {
                    const nombre = espacio.nombre_espacio || espacio.nombre_tipo_espacio || 'Sin nombre';
                    return `<option value="${espacio.id_espacio}">${espacio.id_espacio} - ${nombre}</option>`;
                }).join('');
        }
    } catch (error) {
        console.error('Error al cargar espacios:', error);
    }
}

// Cargar módulos para el modal
let modulosCargados = [];
async function cargarModulosParaModal() {
    if (modulosCargados.length > 0) {
        return; // Ya están cargados
    }
    
    try {
        // Obtener módulos únicos ordenados por hora
        const response = await fetch('/api/modulos'); // Necesitarás crear esta ruta
        
        // Fallback: crear módulos por defecto si la API no existe
        modulosCargados = [
            {id_modulo: '1', hora_inicio: '08:10:00', hora_termino: '09:00:00'},
            {id_modulo: '2', hora_inicio: '09:10:00', hora_termino: '10:00:00'},
            {id_modulo: '3', hora_inicio: '10:10:00', hora_termino: '11:00:00'},
            {id_modulo: '4', hora_inicio: '11:10:00', hora_termino: '12:00:00'},
            {id_modulo: '5', hora_inicio: '12:10:00', hora_termino: '13:00:00'},
            {id_modulo: '6', hora_inicio: '13:10:00', hora_termino: '14:00:00'},
            {id_modulo: '7', hora_inicio: '14:10:00', hora_termino: '15:00:00'},
            {id_modulo: '8', hora_inicio: '15:10:00', hora_termino: '16:00:00'},
            {id_modulo: '9', hora_inicio: '16:10:00', hora_termino: '17:00:00'},
            {id_modulo: '10', hora_inicio: '17:10:00', hora_termino: '18:00:00'},
            {id_modulo: '11', hora_inicio: '18:10:00', hora_termino: '19:00:00'},
            {id_modulo: '12', hora_inicio: '19:10:00', hora_termino: '20:00:00'},
            {id_modulo: '13', hora_inicio: '20:10:00', hora_termino: '21:00:00'},
            {id_modulo: '14', hora_inicio: '21:10:00', hora_termino: '22:00:00'},
            {id_modulo: '15', hora_inicio: '22:10:00', hora_termino: '23:00:00'}
        ];
        
        const selectInicial = document.getElementById('edit-modulo-inicial');
        selectInicial.innerHTML = '<option value="">Seleccione módulo inicial</option>' +
            modulosCargados.map(modulo => 
                `<option value="${modulo.id_modulo}">Módulo ${modulo.id_modulo} (${modulo.hora_inicio.substring(0,5)} - ${modulo.hora_termino.substring(0,5)})</option>`
            ).join('');
        
    } catch (error) {
        console.error('Error al cargar módulos:', error);
    }
}

// Actualizar módulos finales disponibles
window.actualizarModulosFinalesModal = function() {
    const moduloInicial = document.getElementById('edit-modulo-inicial').value;
    const selectFinal = document.getElementById('edit-modulo-final');
    
    if (!moduloInicial) {
        selectFinal.innerHTML = '<option value="">Primero seleccione módulo inicial</option>';
        document.getElementById('preview-horario').textContent = 'Seleccione los módulos';
        return;
    }
    
    const indexInicial = modulosCargados.findIndex(m => m.id_modulo === moduloInicial);
    const modulosDisponibles = modulosCargados.slice(indexInicial);
    
    selectFinal.innerHTML = '<option value="">Seleccione módulo final</option>' +
        modulosDisponibles.map(modulo => 
            `<option value="${modulo.id_modulo}">Módulo ${modulo.id_modulo} (${modulo.hora_inicio.substring(0,5)} - ${modulo.hora_termino.substring(0,5)})</option>`
        ).join('');
    
    actualizarPreviewHorario();
}

// Actualizar preview de horario
function actualizarPreviewHorario() {
    const moduloInicialId = document.getElementById('edit-modulo-inicial').value;
    const moduloFinalId = document.getElementById('edit-modulo-final').value;
    const preview = document.getElementById('preview-horario');
    
    if (!moduloInicialId || !moduloFinalId) {
        preview.textContent = 'Seleccione los módulos';
        return;
    }
    
    const moduloInicial = modulosCargados.find(m => m.id_modulo === moduloInicialId);
    const moduloFinal = modulosCargados.find(m => m.id_modulo === moduloFinalId);
    
    if (moduloInicial && moduloFinal) {
        const horaInicio = moduloInicial.hora_inicio.substring(0,5);
        const horaFin = moduloFinal.hora_termino.substring(0,5);
        preview.textContent = `Módulo ${moduloInicialId} a Módulo ${moduloFinalId} (${horaInicio} - ${horaFin})`;
    }
}

// Guardar edición de reserva
window.guardarEdicionReserva = async function(event) {
    event.preventDefault();
    
    const idReserva = document.getElementById('edit-reserva-id').value;
    const codigoEspacio = document.getElementById('edit-codigo-espacio').value;
    const fecha = document.getElementById('edit-fecha').value;
    const moduloInicialId = document.getElementById('edit-modulo-inicial').value;
    const moduloFinalId = document.getElementById('edit-modulo-final').value;
    const observacionesOriginales = document.getElementById('edit-observaciones-originales').value;
    const observacionesNuevas = document.getElementById('edit-observaciones-nuevas').value.trim();
    
    if (!codigoEspacio || !fecha || !moduloInicialId || !moduloFinalId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor complete todos los campos requeridos'
        });
        return;
    }
    
    // Calcular hora y cantidad de módulos
    const moduloInicial = modulosCargados.find(m => m.id_modulo === moduloInicialId);
    const indexInicial = modulosCargados.findIndex(m => m.id_modulo === moduloInicialId);
    const indexFinal = modulosCargados.findIndex(m => m.id_modulo === moduloFinalId);
    const cantidadModulos = indexFinal - indexInicial + 1;
    const hora = moduloInicial.hora_inicio;
    
    // Concatenar observaciones
    let observacionesFinales = observacionesOriginales;
    if (observacionesNuevas) {
        const timestamp = new Date().toLocaleString('es-ES');
        observacionesFinales = observacionesOriginales === 'Sin observaciones' 
            ? `[${timestamp}] ${observacionesNuevas}`
            : `${observacionesOriginales}\n\n[EDITADO ${timestamp}] ${observacionesNuevas}`;
    }

    // Deshabilitar botón
    const btnGuardar = document.getElementById('btn-guardar-edicion');
    btnGuardar.disabled = true;
    
    // Mostrar loading
    Swal.fire({
        title: 'Guardando cambios...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await fetch(`/quick-actions/api/reserva/${idReserva}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id_espacio: codigoEspacio,
                fecha: fecha,
                hora: hora,
                modulos: cantidadModulos,
                observaciones: observacionesFinales
            })
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.mensaje || 'Reserva actualizada correctamente',
                showConfirmButton: false,
                timer: 1500
            });
            
            // Cerrar modal
            cerrarModalEditar();
            
            // Recargar tabla
            await cargarReservas();
        } else {
            throw new Error(data.mensaje || 'Error al actualizar la reserva');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Ocurrió un error al guardar los cambios'
        });
    } finally {
        btnGuardar.disabled = false;
    }
}

// Funciones globales para ordenamiento (necesarias para onclick en HTML)
function ordenarPor(campo) {
    console.log('🔄 CLICK EN COLUMNA - Ordenando por:', campo);
    console.log('🔄 Estado actual:', ordenActual);
    console.log('🔄 Reservas disponibles:', reservasOriginales?.length || 0);
    
    if (!reservasOriginales || reservasOriginales.length === 0) {
        console.warn('⚠️ No hay reservas para ordenar');
        return;
    }
    
    if (ordenActual.campo === campo) {
        ordenActual.direccion = ordenActual.direccion === 'asc' ? 'desc' : 'asc';
    } else {
        ordenActual.campo = campo;
        ordenActual.direccion = 'asc';
    }
    
    console.log('🔄 Nuevo estado:', ordenActual);
    aplicarOrdenamiento();
}

function aplicarOrdenamiento() {
    const select = document.getElementById('ordenar-reservas');
    if (select) {
        const valor = select.value;
        if (valor) {
            const [campo, direccion] = valor.split('-');
            ordenActual = {campo, direccion};
        }
    }
    
    if (!reservasOriginales || reservasOriginales.length === 0) return;
    
    console.log('📊 Aplicando ordenamiento:', ordenActual);
    
    const reservasOrdenadas = [...reservasOriginales].sort((a, b) => {
        let valorA, valorB;
        
        switch (ordenActual.campo) {
            case 'fecha':
                valorA = new Date(a.fecha);
                valorB = new Date(b.fecha);
                break;
            case 'responsable':
                valorA = (a.nombre_responsable || '').toLowerCase();
                valorB = (b.nombre_responsable || '').toLowerCase();
                break;
            case 'espacio':
                valorA = (a.id_espacio || '').toLowerCase();
                valorB = (b.id_espacio || '').toLowerCase();
                break;
            case 'estado':
                valorA = a.estado;
                valorB = b.estado;
                break;
            case 'hora':
                valorA = extraerPrimeraHora(a);
                valorB = extraerPrimeraHora(b);
                break;
            default:
                return 0;
        }
        
        if (valorA < valorB) return ordenActual.direccion === 'asc' ? -1 : 1;
        if (valorA > valorB) return ordenActual.direccion === 'asc' ? 1 : -1;
        return 0;
    });
    
    mostrarReservasEnTabla(reservasOrdenadas);
    actualizarEstadisticas(reservasOrdenadas);
}

function extraerPrimeraHora(reserva) {
    try {
        if (reserva.modulos_info) {
            // Nueva estructura del backend
            if (reserva.modulos_info.hora_inicio) {
                return reserva.modulos_info.hora_inicio;
            }
            // Estructura anterior por compatibilidad
            if (reserva.modulos_info.horarios && reserva.modulos_info.horarios.length > 0) {
                return reserva.modulos_info.horarios[0].inicio;
            }
        }
        // Fallback a hora de la reserva
        if (reserva.hora) {
            return reserva.hora.substring(0, 5); // HH:MM
        }
    } catch (e) {
        console.warn('Error extrayendo primera hora:', e);
    }
    return '00:00';
}

function toggleSelectAllReservas(checkbox) {
    const checkboxes = document.querySelectorAll('.reserva-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    actualizarContadorSeleccionadas();
}

// Nuevas funciones para acciones en lote
function actualizarContadorSeleccionadas() {
    const checkboxes = document.querySelectorAll('.reserva-checkbox:checked');
    const contador = checkboxes.length;
    const contadorElement = document.getElementById('contador-seleccionadas');
    const accionesLote = document.getElementById('acciones-lote');
    const btnFinalizarLote = document.getElementById('btn-finalizar-lote');
    
    if (contadorElement) {
        contadorElement.textContent = contador;
    }
    
    // Mostrar/ocultar panel de acciones
    if (accionesLote) {
        accionesLote.style.display = contador > 0 ? 'block' : 'none';
    }
    
    // Verificar si todas las seleccionadas están activas
    if (btnFinalizarLote && contador > 0) {
        const reservasSeleccionadas = Array.from(checkboxes).map(cb => cb.value);
        const todasActivas = reservasSeleccionadas.every(id => {
            const reserva = reservasOriginales.find(r => r.id == id);
            return reserva && reserva.estado === 'activa';
        });
        
        btnFinalizarLote.disabled = !todasActivas;
        btnFinalizarLote.title = todasActivas ? 'Finalizar reservas seleccionadas' : 'Solo se pueden finalizar reservas activas';
    }
}

function limpiarSeleccion() {
    const checkboxes = document.querySelectorAll('.reserva-checkbox');
    const selectAll = document.getElementById('select-all-reservas');
    
    checkboxes.forEach(cb => cb.checked = false);
    if (selectAll) selectAll.checked = false;
    
    actualizarContadorSeleccionadas();
}

async function finalizarReservasEnLote() {
    const checkboxes = document.querySelectorAll('.reserva-checkbox:checked');
    const reservasIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (reservasIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin selección',
            text: 'Debe seleccionar al menos una reserva para finalizar'
        });
        return;
    }
    
    // Verificar que todas estén activas
    const reservasSeleccionadas = reservasIds.map(id => reservasOriginales.find(r => r.id == id));
    const reservasInactivas = reservasSeleccionadas.filter(r => r.estado !== 'activa');
    
    if (reservasInactivas.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Solo se pueden finalizar reservas que estén activas'
        });
        return;
    }
    
    // Confirmar acción
    const resultado = await Swal.fire({
        title: '¿Confirmar acción?',
        text: `¿Está seguro de finalizar ${reservasIds.length} reserva(s) seleccionada(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    });
    
    if (!resultado.isConfirmed) return;
    
    // Mostrar loading
    const loadingSwal = Swal.fire({
        title: 'Finalizando reservas...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    try {
        let exitosas = 0;
        let errores = 0;
        
        // Procesar cada reserva
        for (const reservaId of reservasIds) {
            try {
                const response = await fetch(`/quick-actions/api/reserva/${reservaId}/estado`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        estado: 'finalizada'
                    })
                });
                
                console.log('📡 Response status para reserva', reservaId, ':', response.status);
                
                if (!response.ok) {
                    console.error('❌ Error HTTP:', response.status, response.statusText);
                    errores++;
                    continue;
                }
                
                const data = await response.json();
                console.log('📡 Response data para reserva', reservaId, ':', data);
                if (data.success) {
                    exitosas++;
                } else {
                    errores++;
                    console.error(`Error finalizando reserva ${reservaId}:`, data.message);
                }
            } catch (error) {
                errores++;
                console.error(`Error finalizando reserva ${reservaId}:`, error);
            }
        }
        
        Swal.close();
        
        // Mostrar resultado
        if (errores === 0) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: `Se finalizaron ${exitosas} reserva(s) correctamente`
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Proceso completado con errores',
                text: `Exitosas: ${exitosas}, Errores: ${errores}`
            });
        }
        
        // Recargar datos y limpiar selección
        await cargarReservas();
        limpiarSeleccion();
        
    } catch (error) {
        Swal.close();
        console.error('Error en proceso de finalización en lote:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error durante el proceso de finalización'
        });
    }
}

function verDetalleReserva(reservaId) {
    const reserva = reservasOriginales.find(r => r.id == reservaId);
    if (!reserva) return;
    
    console.log('👁️ Mostrando detalle de reserva:', reserva);
    
    // Formatear mejor la información de módulos para el modal
    let infoModulos = 'Sin información de módulos';
    if (reserva.modulos_info) {
        if (reserva.modulos_info.texto_completo) {
            infoModulos = reserva.modulos_info.texto_completo;
        } else if (reserva.modulos_info.modulo_inicial && reserva.modulos_info.modulo_final) {
            infoModulos = `Módulos ${reserva.modulos_info.modulo_inicial}-${reserva.modulos_info.modulo_final}`;
            if (reserva.modulos_info.rango_horario) {
                infoModulos += ` (${reserva.modulos_info.rango_horario})`;
            }
        }
    }
    
    Swal.fire({
        title: `Reserva #${reserva.id}`,
        html: `
            <div class="text-left space-y-3">
                <div><strong>Estado:</strong> <span class="px-2 py-1 rounded text-sm ${reserva.estado === 'activa' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${reserva.estado === 'activa' ? 'Activa' : 'Finalizada'}</span></div>
                <div><strong>Espacio:</strong> ${reserva.id_espacio}</div>
                <div><strong>Responsable:</strong> ${reserva.nombre_responsable || 'Sin nombre'} <br><small class="text-gray-600">${reserva.tipo_responsable || 'N/A'} - RUN: ${reserva.run_responsable}</small></div>
                <div><strong>Fecha:</strong> ${formatearFecha(reserva.fecha)}</div>
                <div><strong>Módulos y Horario:</strong> ${infoModulos}</div>
                <div><strong>Observaciones:</strong> ${reserva.observaciones || 'Sin observaciones'}</div>
            </div>
        `,
        confirmButtonText: 'Cerrar',
        width: '600px'
    });
}

// Cargar reservas al inicializar
document.addEventListener('DOMContentLoaded', function() {
    cargarReservas();
    
    // Event listener para el selector de ordenamiento
    const selectOrdenar = document.getElementById('ordenar-reservas');
    if (selectOrdenar) {
        selectOrdenar.addEventListener('change', aplicarOrdenamiento);
    }
    
    // Verificar que las funciones estén disponibles globalmente
    console.log('🔧 Funciones globales verificación:');
    console.log('🔧 ordenarPor disponible:', typeof window.ordenarPor);
    console.log('🔧 aplicarOrdenamiento disponible:', typeof window.aplicarOrdenamiento);
    console.log('🔧 toggleSelectAllReservas disponible:', typeof window.toggleSelectAllReservas);
    
    // Asegurar que estén en el scope global
    window.ordenarPor = ordenarPor;
    window.aplicarOrdenamiento = aplicarOrdenamiento;
    window.toggleSelectAllReservas = toggleSelectAllReservas;
    window.verDetalleReserva = verDetalleReserva;
    window.actualizarContadorSeleccionadas = actualizarContadorSeleccionadas;
    window.limpiarSeleccion = limpiarSeleccion;
    window.finalizarReservasEnLote = finalizarReservasEnLote;
    
    // La función cambiarEstadoReserva ya está definida globalmente arriba
    console.log('✅ Funciones asignadas al scope global');
});

// Función específica para cargar reservas en el mantenedor
async function cargarReservas() {
    try {
        const tbody = document.getElementById('tabla-reservas-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                        <p>Cargando reservas...</p>
                    </div>
                </td>
            </tr>
        `;

        console.log('🔗 URL de reservas:', '/quick-actions/api/reservas');
        const response = await fetch('/quick-actions/api/reservas');
        console.log('📡 Respuesta del servidor:', response.status, response.statusText);
        const data = await response.json();
        console.log('📋 Datos recibidos:', data);

        if (data.success && data.data) {
            reservasOriginales = data.data;
            console.log('🔍 Estructura de primera reserva:', data.data[0]);
            console.log('🔍 IDs de reservas:', data.data.map(r => r.id));
            if (data.data[0] && data.data[0].modulos_info) {
                console.log('📋 modulos_info detalle:', data.data[0].modulos_info);
                console.log('📋 tipo de modulos_info:', typeof data.data[0].modulos_info);
            }
            mostrarReservasEnTabla(data.data);
            actualizarEstadisticas(data.data);
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fa-solid fa-triangle-exclamation text-6xl text-yellow-300 mb-4"></i>
                            <p>No se encontraron reservas</p>
                            <p class="text-xs text-red-500 mt-2">Respuesta: ${JSON.stringify(data)}</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error al cargar reservas:', error);
        const tbody = document.getElementById('tabla-reservas-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-red-500">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-xmark text-6xl text-red-300 mb-4"></i>
                        <p>Error al cargar reservas: ${error.message}</p>
                        <p class="text-xs mt-2">Verifica la consola del navegador para más detalles</p>
                    </div>
                </td>
            </tr>
        `;
    }
}
    

// Mostrar reservas en la tabla
function mostrarReservasEnTabla(reservas) {
    const tbody = document.getElementById('tabla-reservas-body');
    const cardsContainer = document.getElementById('tabla-reservas-cards');
    
    if (reservas.length === 0) {
        // Vista desktop
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <p>No hay reservas con los filtros aplicados</p>
                    </div>
                </td>
            </tr>
        `;
        
        // Vista mobile
        cardsContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                <i class="fa-solid fa-folder-open text-6xl text-gray-300 mb-4"></i>
                <p>No hay reservas con los filtros aplicados</p>
            </div>
        `;
        return;
    }

    // Vista Desktop (tabla)
    tbody.innerHTML = reservas.map(reserva => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-2 py-2">
                <input type="checkbox" class="reserva-checkbox rounded" value="${reserva.id}" onchange="actualizarContadorSeleccionadas()">
            </td>
            <td class="px-2 py-2">
                <div class="flex flex-col gap-1">
                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full ${
                        reserva.estado === 'activa' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-gray-100 text-gray-800'
                    }">
                        ${reserva.estado === 'activa' ? 'Activa' : 'Fin.'}
                    </span>
                    ${reserva.editada ? '<span class="px-2 py-0.5 inline-flex text-xs font-medium rounded-full bg-blue-100 text-blue-700"><i class="fa-solid fa-pen-to-square text-xs mr-1"></i>Editada</span>' : ''}
                </div>
            </td>
            <td class="px-2 py-2 text-sm text-gray-900 font-medium">${reserva.id_espacio}</td>
            <td class="px-2 py-2">
                <div class="text-sm font-medium text-gray-900">${reserva.nombre_responsable || 'Sin nombre'}</div>
                <div class="text-xs text-gray-500">${reserva.tipo_responsable || 'N/A'}</div>
            </td>
            <td class="px-2 py-2 text-sm text-gray-900">${formatearFecha(reserva.fecha)}</td>
            <td class="px-2 py-2 text-xs text-gray-900">
                ${formatearModulosInfoCompacto(reserva.modulos_info)}
            </td>
            <td class="px-2 py-2 text-right">
                <div class="flex justify-end gap-1">
                    ${reserva.estado === 'activa' 
                        ? `<button 
                            type="button"
                            onclick="editarReserva('${reserva.id}')"
                            class="inline-flex items-center justify-center p-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors"
                            title="Editar reserva">
                            <i class="fa-solid fa-edit w-3 h-3"></i>
                        </button>
                        <button 
                            type="button"
                            onclick="cambiarEstadoReserva('${reserva.id}', 'finalizada')"
                            class="inline-flex items-center justify-center p-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 transition-colors"
                            title="Finalizar reserva">
                            <i class="fa-solid fa-xmark w-3 h-3"></i>
                        </button>`
                        : `<span class="text-xs text-gray-500 italic px-2">-</span>`
                    }
                    <button 
                        type="button"
                        onclick="verDetalleReserva('${reserva.id}')"
                        class="inline-flex items-center justify-center p-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                        title="Ver detalle">
                        <i class="fa-solid fa-eye w-3 h-3"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Vista Mobile/Tablet (cards)
    cardsContainer.innerHTML = reservas.map(reserva => `
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="reserva-checkbox rounded mt-1" value="${reserva.id}" onchange="actualizarContadorSeleccionadas()">
                    <div>
                        <h3 class="font-semibold text-gray-900 text-lg">${reserva.id_espacio}</h3>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full ${
                                reserva.estado === 'activa' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-gray-100 text-gray-800'
                            }">
                                ${reserva.estado === 'activa' ? 'Activa' : 'Finalizada'}
                            </span>
                            ${reserva.editada ? '<span class="px-2 py-1 inline-flex text-xs font-medium rounded-full bg-blue-100 text-blue-700"><i class="fa-solid fa-pen-to-square text-xs mr-1"></i>Editada</span>' : ''}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-2 text-sm">
                <div class="flex items-start">
                    <i class="fa-solid fa-user text-gray-400 w-5 mt-0.5"></i>
                    <div class="ml-2">
                        <p class="font-medium text-gray-900">${reserva.nombre_responsable || 'Sin nombre'}</p>
                        <p class="text-gray-500 text-xs">${reserva.tipo_responsable || 'N/A'} - RUN: ${reserva.run_responsable}</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <i class="fa-solid fa-book text-gray-400 w-5"></i>
                    <span class="ml-2 text-gray-700">${reserva.asignatura || 'Sin asignatura'}</span>
                </div>
                
                <div class="flex items-center">
                    <i class="fa-solid fa-calendar text-gray-400 w-5"></i>
                    <span class="ml-2 text-gray-700">${formatearFecha(reserva.fecha)}</span>
                </div>
                
                <div class="flex items-start">
                    <i class="fa-solid fa-clock text-gray-400 w-5 mt-0.5"></i>
                    <div class="ml-2 text-gray-700">
                        ${formatearModulosInfo(reserva.modulos_info)}
                    </div>
                </div>
                
                ${reserva.observaciones ? `
                <div class="flex items-start">
                    <i class="fa-solid fa-note-sticky text-gray-400 w-5 mt-0.5"></i>
                    <p class="ml-2 text-gray-600 line-clamp-2">${reserva.observaciones}</p>
                </div>
                ` : ''}
            </div>
            
            <div class="mt-4 flex gap-2">
                ${reserva.estado === 'activa' 
                    ? `<button 
                        type="button"
                        onclick="editarReserva('${reserva.id}')"
                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-edit w-4 h-4 mr-1"></i>
                        Editar
                    </button>
                    <button 
                        type="button"
                        onclick="cambiarEstadoReserva('${reserva.id}', 'finalizada')"
                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors">
                        <i class="fa-solid fa-xmark w-4 h-4 mr-1"></i>
                        Finalizar
                    </button>`
                    : `<div class="flex-1 text-center text-sm text-gray-500 italic py-2">Finalizada</div>`
                }
                <button 
                    type="button"
                    onclick="verDetalleReserva('${reserva.id}')"
                    class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <i class="fa-solid fa-eye w-4 h-4 mr-1"></i>
                    Ver Detalle
                </button>
            </div>
        </div>
    `).join('');
    
    // Actualizar contador después de mostrar la tabla
    setTimeout(() => {
        actualizarContadorSeleccionadas();
    }, 100);
}

// Formatear información de módulos de forma compacta
function formatearModulosInfoCompacto(modulosInfo) {
    // Si no hay datos
    if (!modulosInfo) {
        return '<span class="text-gray-400 text-xs">-</span>';
    }

    try {
        let info = modulosInfo;
        
        // Si es string, intentar parsearlo como JSON
        if (typeof modulosInfo === 'string') {
            try {
                info = JSON.parse(modulosInfo);
            } catch (parseError) {
                return `<span class="text-gray-700">${modulosInfo}</span>`;
            }
        }
        
        // Si tiene la estructura del backend
        if (info && typeof info === 'object' && info.modulo_inicial && info.modulo_final) {
            return `<div class="text-xs">M${info.modulo_inicial}-${info.modulo_final}</div>`;
        }
        
        // Si solo tiene hora de inicio
        if (info && info.hora_inicio) {
            return `<span class="text-xs">${info.hora_inicio}</span>`;
        }
        
        return '<span class="text-gray-400 text-xs">-</span>';
        
    } catch (e) {
        console.warn('🔧 Error al formatear módulos info compacto:', e);
        return '<span class="text-gray-400 text-xs">-</span>';
    }
}

// Formatear información de módulos con horarios
function formatearModulosInfo(modulosInfo) {
    // Si no hay datos
    if (!modulosInfo) {
        return '<span class="text-gray-500 italic">Sin información de módulos</span>';
    }

    try {
        let info = modulosInfo;
        
        // Si es string, intentar parsearlo como JSON
        if (typeof modulosInfo === 'string') {
            try {
                info = JSON.parse(modulosInfo);
            } catch (parseError) {
                // Si no es JSON válido, tratarlo como string simple
                return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${modulosInfo}</span>`;
            }
        }
        
        // Si tiene la estructura del backend (modulo_inicial, modulo_final, etc.)
        if (info && typeof info === 'object' && info.texto_completo) {
            return `
                <div class="space-y-1">
                    <div class="font-medium text-sm text-blue-800">Módulos ${info.modulo_inicial}-${info.modulo_final}</div>
                    <div class="text-xs text-gray-600">${info.rango_horario}</div>
                    <div class="text-xs text-blue-600">${info.cantidad_modulos} módulo${info.cantidad_modulos > 1 ? 's' : ''}</div>
                </div>
            `;
        }
        
        // Si tiene modulo_inicial y modulo_final pero no texto_completo
        if (info && info.modulo_inicial && info.modulo_final) {
            const rango = info.rango_horario || `${info.hora_inicio || ''} - ${info.hora_fin || ''}`;
            return `
                <div class="space-y-1">
                    <div class="font-medium text-sm text-blue-800">Módulos ${info.modulo_inicial}-${info.modulo_final}</div>
                    <div class="text-xs text-gray-600">${rango}</div>
                </div>
            `;
        }
        
        // Si solo tiene hora de inicio
        if (info && info.hora_inicio) {
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Hora: ${info.hora_inicio}</span>`;
        }
        
        // Si es un objeto pero no tiene la estructura esperada
        if (typeof info === 'object') {
            const keys = Object.keys(info);
            if (keys.length > 0) {
                return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Datos: ${JSON.stringify(info)}</span>`;
            }
        }
        
        return '<span class="text-gray-500 italic">Sin horarios definidos</span>';
        
    } catch (e) {
        console.warn('🔧 Error al formatear módulos info:', e);
        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Error al procesar</span>`;
    }
}



// Actualizar estadísticas
function actualizarEstadisticas(reservas) {
    const activas = reservas.filter(r => r.estado === 'activa').length;
    
    // Actualizar contador desktop
    const headerCounter = document.getElementById('stats-activas-header');
    if (headerCounter) {
        headerCounter.textContent = activas;
    }
    
    // Actualizar contador mobile
    const mobileCounter = document.getElementById('stats-activas-mobile');
    if (mobileCounter) {
        mobileCounter.textContent = activas;
    }
}

// Filtrar reservas
function filtrarReservas() {
    const estadoFiltro = document.getElementById('filtro-estado-reserva').value;
    const fechaFiltro = document.getElementById('filtro-fecha-reserva').value;
    
    let reservasFiltradas = [...reservasOriginales];
    
    if (estadoFiltro) {
        reservasFiltradas = reservasFiltradas.filter(r => r.estado === estadoFiltro);
    }
    
    if (fechaFiltro) {
        reservasFiltradas = reservasFiltradas.filter(r => r.fecha === fechaFiltro);
    }
    
    mostrarReservasEnTabla(reservasFiltradas);
    actualizarEstadisticas(reservasFiltradas);
}

// Cambiar estado de reserva - Función global
window.cambiarEstadoReserva = async function(idReserva, nuevoEstado) {
    // Verificar que SweetAlert esté disponible
    if (typeof Swal === 'undefined') {
        console.error('❌ SweetAlert2 no está disponible');
        alert('Error: SweetAlert2 no está cargado');
        return;
    }
    
    try {
        console.log('🔄 Iniciando cambio de estado:', { idReserva, nuevoEstado });
        
        const estadoTexto = nuevoEstado === 'activa' ? 'Activar' : 'Finalizar';
        const result = await Swal.fire({
            title: `¿${estadoTexto} reserva?`,
            text: `¿Deseas ${estadoTexto.toLowerCase()} la reserva ${idReserva}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: nuevoEstado === 'activa' ? '#10B981' : '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: `Sí, ${estadoTexto.toLowerCase()}`,
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            console.log('✅ Usuario confirmó el cambio');
            
            // Mostrar loading
            Swal.fire({
                title: `${estadoTexto}ndo reserva...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            console.log('📡 Enviando petición a:', `/quick-actions/api/reserva/${idReserva}/estado`);

            const response = await fetch(`/quick-actions/api/reserva/${idReserva}/estado`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    estado: nuevoEstado
                })
            });

            console.log('📡 Response status:', response.status);
            const data = await response.json();
            console.log('📡 Response data:', data);

            if (data.success) {
                console.log('✅ Estado cambiado exitosamente');
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.mensaje || `Reserva ${estadoTexto.toLowerCase()}da correctamente`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Recargar la tabla
                console.log('🔄 Recargando tabla de reservas...');
                cargarReservas();
            } else {
                console.error('❌ Error en la respuesta:', data);
                Swal.fire({
                    title: 'Error',
                    text: data.mensaje || `Error al ${estadoTexto.toLowerCase()} la reserva`,
                    icon: 'error'
                });
            }
        } else {
            console.log('❌ Usuario canceló el cambio');
        }
    } catch (error) {
        console.error(`❌ Error al ${estadoTexto.toLowerCase()} reserva:`, error);
        Swal.fire({
            title: 'Error de conexión',
            text: `No se pudo ${estadoTexto.toLowerCase()} la reserva. Intenta nuevamente.`,
            icon: 'error'
        });
    }
}

// Función para formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return 'Sin fecha';
    try {
        // Asegurarnos de que la fecha esté en formato correcto
        const date = new Date(fecha.includes('T') ? fecha : fecha + 'T00:00:00');
        if (isNaN(date.getTime())) {
            console.warn('Fecha inválida:', fecha);
            return fecha; // Devolver la fecha original si no se puede parsear
        }
        return date.toLocaleDateString('es-ES', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (e) {
        console.error('Error al formatear fecha:', e);
        return fecha;
    }
}

// Función de test para verificar funcionamiento
window.testCambiarEstado = function() {
    console.log('🧪 Testing cambiarEstadoReserva function...');
    console.log('SweetAlert disponible:', typeof Swal !== 'undefined');
    console.log('Función cambiarEstadoReserva disponible:', typeof window.cambiarEstadoReserva === 'function');
    
    if (typeof window.cambiarEstadoReserva === 'function') {
        console.log('✅ Todo está listo para cambiar estados');
        // Hacer un test con datos ficticios
        Swal.fire({
            title: 'Test',
            text: 'La función está funcionando correctamente',
            icon: 'success',
            timer: 2000
        });
    }
}

// Verificar cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM Cargado - Funciones disponibles:');
    console.log('- cambiarEstadoReserva:', typeof window.cambiarEstadoReserva);
    console.log('- editarReserva:', typeof window.editarReserva);
    console.log('- SweetAlert:', typeof Swal);
    console.log('Para probar, ejecuta: testCambiarEstado()');
});
</script>
@endpush
@endsection

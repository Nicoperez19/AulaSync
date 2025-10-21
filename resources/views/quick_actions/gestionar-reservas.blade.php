@extends('layouts.quick_actions.app')

@section('title', 'Gestionar Reservas - Acciones Rápidas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-calendar-check mr-2 sm:mr-3 text-blue-600"></i>
                        Gestión de Reservas
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">Administrar estados de reservas activas y finalizadas</p>
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
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all-reservas" onchange="toggleSelectAllReservas(this)" class="rounded">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" onclick="ordenarPor('estado')">
                                Estado
                                <i class="fa-solid fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" onclick="ordenarPor('espacio')">
                                Espacio
                                <i class="fa-solid fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" onclick="ordenarPor('responsable')">
                                Responsable
                                <i class="fa-solid fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" onclick="ordenarPor('asignatura')">
                                Asignatura
                                <i class="fa-solid fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" onclick="ordenarPor('fecha')">
                                Fecha
                                <i class="fa-solid fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Módulos y Horarios
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Observaciones
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabla-reservas-body" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                                    <p>Cargando reservas...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-3xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Reservas Activas</div>
                        <div class="text-2xl font-bold text-gray-900" id="stats-activas">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-xmark text-3xl text-gray-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Reservas Finalizadas</div>
                        <div class="text-2xl font-bold text-gray-900" id="stats-finalizadas">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-calendar text-3xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Total Reservas</div>
                        <div class="text-2xl font-bold text-gray-900" id="stats-total">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Variables específicas para gestión de reservas
let reservasOriginales = [];
let ordenActual = {campo: 'fecha', direccion: 'desc'};

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
                valorA = a.codigo_espacio.toLowerCase();
                valorB = b.codigo_espacio.toLowerCase();
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
                <div><strong>Espacio:</strong> ${reserva.codigo_espacio}</div>
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
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
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
                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
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
            <td class="px-4 py-3 whitespace-nowrap">
                <input type="checkbox" class="reserva-checkbox rounded" value="${reserva.id}" onchange="actualizarContadorSeleccionadas()">
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    reserva.estado === 'activa' 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-gray-100 text-gray-800'
                }">
                    ${reserva.estado === 'activa' ? 'Activa' : 'Finalizada'}
                </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">${reserva.codigo_espacio}</td>
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${reserva.nombre_responsable || 'Sin nombre'}</div>
                <div class="text-sm text-gray-500">${reserva.tipo_responsable || 'N/A'} - RUN: ${reserva.run_responsable}</div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${reserva.asignatura || 'Sin asignatura'}</td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${formatearFecha(reserva.fecha)}</td>
            <td class="px-4 py-3 text-sm text-gray-900">
                ${formatearModulosInfo(reserva.modulos_info)}
            </td>
            <td class="px-4 py-3 text-sm text-gray-900 max-w-xs">
                <div class="truncate" title="${reserva.observaciones || 'Sin observaciones'}">
                    ${reserva.observaciones || 'Sin observaciones'}
                </div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex flex-col space-y-2">
                    ${reserva.estado === 'activa' 
                        ? `<button 
                            type="button"
                            onclick="cambiarEstadoReserva('${reserva.id}', 'finalizada')"
                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 transition-colors">
                            <i class="fa-solid fa-xmark w-3 h-3 mr-1"></i>
                            Finalizar
                        </button>`
                        : `<span class="text-xs text-gray-500 italic">Finalizada</span>`
                    }
                    <button 
                        type="button"
                        onclick="verDetalleReserva('${reserva.id}')"
                        class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-eye w-3 h-3 mr-1"></i>
                        Ver
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
                        <h3 class="font-semibold text-gray-900 text-lg">${reserva.codigo_espacio}</h3>
                        <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full mt-1 ${
                            reserva.estado === 'activa' 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-gray-100 text-gray-800'
                        }">
                            ${reserva.estado === 'activa' ? 'Activa' : 'Finalizada'}
                        </span>
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
    const finalizadas = reservas.filter(r => r.estado === 'finalizada').length;
    const total = reservas.length;

    document.getElementById('stats-activas').textContent = activas;
    document.getElementById('stats-finalizadas').textContent = finalizadas;
    document.getElementById('stats-total').textContent = total;
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
    const date = new Date(fecha + 'T00:00:00');
    return date.toLocaleDateString('es-ES', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
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
    console.log('- SweetAlert:', typeof Swal);
    console.log('Para probar, ejecuta: testCambiarEstado()');
});
</script>
@endpush
@endsection

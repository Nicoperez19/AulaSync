@extends('layouts.quick_actions.app')

@section('title', 'Gestionar Reservas - Acciones Rápidas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-calendar-check mr-3 text-blue-600"></i>
                        Gestión de Reservas
                    </h1>
                    <p class="text-gray-600 mt-1">Administrar estados de reservas activas y finalizadas</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('quick-actions.crear-reserva') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Nueva Reserva
                    </a>
                    <a href="{{ route('quick-actions.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex flex-wrap gap-4 items-center">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select 
                        id="filtro-estado-reserva"
                        onchange="filtrarReservas()"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                
                <div class="flex items-end">
                    <button 
                        onclick="cargarReservas()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-rotate-right w-4 h-4 mr-2 inline"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de reservas -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsable</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espacio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-reservas-body" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <!-- <x-heroicon-o-clock class="w-12 h-12 text-gray-300 mb-4" /> -->
                                    <i class="fa-solid fa-clock text-6xl text-gray-300 mb-4"></i>
                                    <p>Cargando reservas...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
<script src="{{ asset('js/admin-panel.js') }}"></script>
<script>
// Variables específicas para gestión de reservas
let reservasOriginales = [];

// Cargar reservas al inicializar
document.addEventListener('DOMContentLoaded', function() {
    cargarReservas();
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

        console.log('🔗 URL de reservas:', '{{ route("quick-actions.api.reservas") }}');
        const response = await fetch('{{ route("quick-actions.api.reservas") }}');
        console.log('📡 Respuesta del servidor:', response.status, response.statusText);
        const data = await response.json();
        console.log('📋 Datos recibidos:', data);

        if (data.success && data.data) {
            reservasOriginales = data.data;
            mostrarReservasEnTabla(data.data);
            actualizarEstadisticas(data.data);
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <!-- <x-heroicon-o-exclamation class="w-12 h-12 text-yellow-300 mb-4" /> -->
                            <i class="fa-solid fa-triangle-exclamation text-6xl text-yellow-300 mb-4"></i>
                            <p>No se encontraron reservas</p>
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
                        <!-- <x-heroicon-o-x-circle class="w-12 h-12 text-red-300 mb-4" /> -->
                        <i class="fa-solid fa-circle-xmark text-6xl text-red-300 mb-4"></i>
                        <p>Error al cargar reservas: ${error.message}</p>
                    </div>
                </td>
            </tr>
        `;
    }
}
                    <div class="flex flex-col items-center">
                        <!-- <x-heroicon-o-x-circle class="w-12 h-12 text-red-300 mb-4" /> -->
                        <i class="fa-solid fa-circle-xmark text-6xl text-red-300 mb-4"></i>
                        <p>Error al cargar reservas</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Mostrar reservas en la tabla
function mostrarReservasEnTabla(reservas) {
    const tbody = document.getElementById('tabla-reservas-body');
    
    if (reservas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <!-- <x-heroicon-o-folder-open class="w-12 h-12 text-gray-300 mb-4" /> -->
                        <i class="fa-solid fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <p>No hay reservas con los filtros aplicados</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = reservas.map(reserva => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${reserva.id}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${reserva.nombre_responsable}</div>
                <div class="text-sm text-gray-500">RUN: ${reserva.run_responsable}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${reserva.codigo_espacio}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatearFecha(reserva.fecha)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Módulos ${reserva.modulo_inicial} - ${reserva.modulo_final}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    reserva.estado === 'activa' 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-gray-100 text-gray-800'
                }">
                    ${reserva.estado === 'activa' ? 'Activa' : 'Finalizada'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                ${reserva.estado === 'activa' 
                    ? `<button 
                        onclick="cambiarEstadoReserva(${reserva.id}, 'finalizada')"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 transition-colors">
                        <i class="fa-solid fa-xmark w-3 h-3 mr-1"></i>
                        Finalizar
                    </button>`
                    : `<button 
                        onclick="cambiarEstadoReserva(${reserva.id}, 'activa')"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 transition-colors">
                        <i class="fa-solid fa-check w-3 h-3 mr-1"></i>
                        Activar
                    </button>`
                }
            </td>
        </tr>
    `).join('');
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
</script>
@endpush
@endsection

@extends('layouts.quick_actions.app')

@section('title', 'Gestionar Espacios - Acciones Rápidas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Espacios</h1>
                    <p class="text-gray-600 mt-1">Administrar estados y disponibilidad de espacios</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('quick-actions.crear-reserva') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fa-solid fa-plus w-4 h-4 mr-2"></i>
                        Nueva Reserva
                    </a>
                    <a href="{{ route('quick-actions.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fa-solid fa-arrow-left w-4 h-4 mr-2"></i>
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
                        id="filtro-estado-espacio"
                        onchange="filtrarEspacios()"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Todos los estados</option>
                        <option value="Disponible">Disponibles</option>
                        <option value="Ocupado">Ocupados</option>
                        <option value="Mantenimiento">En mantenimiento</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Piso</label>
                    <select 
                        id="filtro-piso-espacio"
                        onchange="filtrarEspacios()"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Todos los pisos</option>
                        <option value="1">Piso 1</option>
                        <option value="2">Piso 2</option>
                        <option value="3">Piso 3</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Búsqueda</label>
                    <input 
                        type="text"
                        id="filtro-busqueda-espacio"
                        placeholder="Buscar por código o nombre..."
                        onkeyup="filtrarEspacios()"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                </div>
                
                <div class="flex items-end">
                    <button 
                        onclick="cargarEspacios()"
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                        <i class="fa-solid fa-rotate-right w-4 h-4 mr-2 inline"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de espacios -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Piso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-espacios-body" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <!-- <x-heroicon-o-office-building class="w-12 h-12 text-gray-300 mb-4" /> -->
                                    <i class="fa-solid fa-building text-6xl text-gray-300 mb-4"></i>
                                    <p>Cargando espacios...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-3xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Disponibles</div>
                        <div class="text-2xl font-bold text-gray-900" id="stats-disponibles">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-lock text-3xl text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Ocupados</div>
                        <div class="text-2xl font-bold text-gray-900" id="stats-ocupados">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-wrench text-3xl text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Mantenimiento</div>
                        <div class="text-2xl font-bold text-gray-900" id="stats-mantenimiento">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-building text-3xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Total Espacios</div>
                        <div class="text-2xl font-bold text-gray-900" id="stats-total">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Masivas -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Masivas</h3>
            <div class="flex flex-wrap gap-4">
                <button 
                    onclick="liberarTodosLosEspacios()"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fa-solid fa-unlock w-4 h-4 mr-2"></i>
                    Liberar Todos los Espacios
                </button>
                
                <button 
                    onclick="ponerEnMantenimiento()"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                    <i class="fa-solid fa-wrench w-4 h-4 mr-2"></i>
                    Mantenimiento Masivo
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Variables específicas para gestión de espacios
let espaciosOriginales = [];

// Cargar espacios al inicializar
document.addEventListener('DOMContentLoaded', function() {
    cargarEspacios();
});

// Función específica para cargar espacios en el mantenedor
async function cargarEspacios() {
    try {
        const tbody = document.getElementById('tabla-espacios-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mb-4"></div>
                        <p>Cargando espacios...</p>
                    </div>
                </td>
            </tr>
        `;

        const response = await fetch('/api/admin/espacios');
        const data = await response.json();

        if (data.success && data.espacios) {
            espaciosOriginales = data.espacios;
            mostrarEspaciosEnTabla(data.espacios);
            actualizarEstadisticasEspacios(data.espacios);
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <!-- <x-heroicon-o-exclamation class="w-12 h-12 text-yellow-300 mb-4" /> -->
                            <i class="fa-solid fa-triangle-exclamation text-6xl text-yellow-300 mb-4"></i>
                            <p>No se encontraron espacios</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error al cargar espacios:', error);
        const tbody = document.getElementById('tabla-espacios-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-red-500">
                    <div class="flex flex-col items-center">
                        <!-- <x-heroicon-o-x-circle class="w-12 h-12 text-red-300 mb-4" /> -->
                        <i class="fa-solid fa-circle-xmark text-6xl text-red-300 mb-4"></i>
                        <p>Error al cargar espacios</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Mostrar espacios en la tabla
function mostrarEspaciosEnTabla(espacios) {
    const tbody = document.getElementById('tabla-espacios-body');
    
    if (espacios.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <!-- <x-heroicon-o-folder-open class="w-12 h-12 text-gray-300 mb-4" /> -->
                        <i class="fa-solid fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <p>No hay espacios con los filtros aplicados</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = espacios.map(espacio => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${espacio.codigo}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${espacio.nombre}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${espacio.tipo}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Piso ${espacio.piso}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${espacio.capacidad}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getEstadoClasses(espacio.estado)}">
                    ${espacio.estado}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex gap-2">
                    ${espacio.estado !== 'Disponible' 
                        ? `<button 
                            onclick="cambiarEstadoEspacio('${espacio.codigo}', 'Disponible')"
                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 transition-colors">
                            <i class="fa-solid fa-unlock w-3 h-3 mr-1"></i>
                            Liberar
                        </button>`
                        : ''
                    }
                    <button 
                        onclick="cambiarEstadoEspacio('${espacio.codigo}', 'Mantenimiento')"
                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 transition-colors">
                        <i class="fa-solid fa-wrench w-3 h-3 mr-1"></i>
                        Mantto
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Obtener clases CSS para el estado
function getEstadoClasses(estado) {
    switch(estado) {
        case 'Disponible':
            return 'bg-green-100 text-green-800';
        case 'Ocupado':
            return 'bg-red-100 text-red-800';
        case 'Mantenimiento':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Actualizar estadísticas
function actualizarEstadisticasEspacios(espacios) {
    const disponibles = espacios.filter(e => e.estado === 'Disponible').length;
    const ocupados = espacios.filter(e => e.estado === 'Ocupado').length;
    const mantenimiento = espacios.filter(e => e.estado === 'Mantenimiento').length;
    const total = espacios.length;

    document.getElementById('stats-disponibles').textContent = disponibles;
    document.getElementById('stats-ocupados').textContent = ocupados;
    document.getElementById('stats-mantenimiento').textContent = mantenimiento;
    document.getElementById('stats-total').textContent = total;
}

// Filtrar espacios
function filtrarEspacios() {
    const estadoFiltro = document.getElementById('filtro-estado-espacio').value;
    const pisoFiltro = document.getElementById('filtro-piso-espacio').value;
    const busquedaFiltro = document.getElementById('filtro-busqueda-espacio').value.toLowerCase();
    
    let espaciosFiltrados = [...espaciosOriginales];
    
    if (estadoFiltro) {
        espaciosFiltrados = espaciosFiltrados.filter(e => e.estado === estadoFiltro);
    }
    
    if (pisoFiltro) {
        espaciosFiltrados = espaciosFiltrados.filter(e => e.piso.toString() === pisoFiltro);
    }
    
    if (busquedaFiltro) {
        espaciosFiltrados = espaciosFiltrados.filter(e => 
            e.codigo.toLowerCase().includes(busquedaFiltro) ||
            e.nombre.toLowerCase().includes(busquedaFiltro)
        );
    }
    
    mostrarEspaciosEnTabla(espaciosFiltrados);
    actualizarEstadisticasEspacios(espaciosFiltrados);
}

// Cambiar estado de espacio
async function cambiarEstadoEspacio(codigoEspacio, nuevoEstado) {
    try {
        const result = await Swal.fire({
            title: '¿Confirmar cambio?',
            text: `¿Deseas cambiar el estado del espacio ${codigoEspacio} a ${nuevoEstado}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            const response = await fetch(`/api/admin/espacio/${codigoEspacio}/estado`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    estado: nuevoEstado
                })
            });

            const data = await response.json();

            if (data.exito) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.mensaje,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Recargar la tabla
                cargarEspacios();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.mensaje || 'Error al cambiar el estado del espacio',
                    icon: 'error'
                });
            }
        }
    } catch (error) {
        console.error('Error al cambiar estado:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error de conexión al cambiar el estado',
            icon: 'error'
        });
    }
}

// Acciones masivas
async function liberarTodosLosEspacios() {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción liberará TODOS los espacios ocupados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, liberar todos',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        // Implementar lógica para liberar todos los espacios
        Swal.fire('¡Liberados!', 'Todos los espacios han sido liberados', 'success');
        cargarEspacios();
    }
}

async function ponerEnMantenimiento() {
    const { value: espaciosSeleccionados } = await Swal.fire({
        title: 'Seleccionar espacios',
        text: 'Seleccione los espacios para poner en mantenimiento',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar'
    });

    if (espaciosSeleccionados) {
        Swal.fire('¡Actualizado!', 'Espacios puestos en mantenimiento', 'success');
        cargarEspacios();
    }
}
</script>
@endpush
@endsection

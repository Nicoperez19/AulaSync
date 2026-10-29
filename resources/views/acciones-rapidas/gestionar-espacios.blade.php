<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fas fa-building"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Gestión de Espacios</h2>
                    <p class="text-sm text-gray-500">Administrar estados y disponibilidad de espacios</p>
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

    <div class="space-y-6">

    <!-- 1. KPIs / Estadísticas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white overflow-hidden shadow-xs border border-slate-200/80 rounded-2xl">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-2xl sm:text-3xl text-emerald-600"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <div class="text-xs sm:text-sm font-medium text-slate-500">Disponibles</div>
                        <div class="text-xl sm:text-2xl font-bold text-slate-900" id="stats-disponibles">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xs border border-slate-200/80 rounded-2xl">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-lock text-2xl sm:text-3xl text-rose-600"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <div class="text-xs sm:text-sm font-medium text-slate-500">Ocupados</div>
                        <div class="text-xl sm:text-2xl font-bold text-slate-900" id="stats-ocupados">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xs border border-slate-200/80 rounded-2xl">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-wrench text-2xl sm:text-3xl text-amber-500"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <div class="text-xs sm:text-sm font-medium text-slate-500">Mantenimiento</div>
                        <div class="text-xl sm:text-2xl font-bold text-slate-900" id="stats-mantenimiento">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xs border border-slate-200/80 rounded-2xl">
            <div class="p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-building text-2xl sm:text-3xl text-blue-600"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <div class="text-xs sm:text-sm font-medium text-slate-500">Total Espacios</div>
                        <div class="text-xl sm:text-2xl font-bold text-slate-900" id="stats-total">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Acciones Rápidas -->
    <div class="bg-white overflow-hidden shadow-xs border border-slate-200/80 rounded-2xl">
        <div class="p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-slate-900 mb-4">Acciones Rápidas</h3>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <button 
                    onclick="liberarTodosLosEspacios()"
                    class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-xs">
                    <i class="fa-solid fa-unlock w-4 h-4 mr-2"></i>
                    Liberar Todos los Espacios
                </button>
                
                <button 
                    onclick="ponerEnMantenimiento()"
                    class="inline-flex items-center justify-center px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-xl hover:bg-amber-600 transition-colors shadow-xs">
                    <i class="fa-solid fa-wrench w-4 h-4 mr-2"></i>
                    Mantenimiento Masivo
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Sección FILTROS -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 dark:text-gray-200">
                <i class="fas fa-filter text-[#950101]"></i> FILTROS
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Estado</label>
                <select 
                    id="filtro-estado-espacio"
                    onchange="filtrarEspacios()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los estados</option>
                    <option value="Disponible">Disponibles</option>
                    <option value="Ocupado">Ocupados</option>
                    <option value="Mantenimiento">En mantenimiento</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Piso</label>
                <select 
                    id="filtro-piso-espacio"
                    onchange="filtrarEspacios()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Todos los pisos</option>
                    <option value="1">Piso 1</option>
                    <option value="2">Piso 2</option>
                    <option value="3">Piso 3</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Búsqueda</label>
                <div class="relative">
                    <input 
                        type="text"
                        id="filtro-busqueda-espacio"
                        placeholder="Buscar por código o nombre..."
                        onkeyup="filtrarEspacios()"
                        class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Botones de Acción separados por línea -->
        <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button 
                type="button"
                onclick="limpiarFiltrosEspacios()"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                <i class="fas fa-undo text-xs"></i> Limpiar
            </button>
            <button 
                type="button"
                onclick="cargarEspacios()"
                class="px-4 py-2 text-sm font-semibold text-white bg-[#191919] hover:bg-black active:bg-gray-900 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                <i class="fas fa-filter text-xs"></i> Aplicar filtros
            </button>
        </div>
    </div>

    <!-- 4. Tabla de espacios -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">Código</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">Nombre</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">Tipo</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">Piso</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">Capacidad</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">Estado</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-espacios-body" class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fa-solid fa-building text-6xl text-slate-300 mb-4"></i>
                                <p>Cargando espacios...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-panel.js') }}"></script>
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

        const response = await fetch('{{ route("quick-actions.api.espacios") }}');
        const data = await response.json();

        if (data.success && data.data) {
            espaciosOriginales = data.data;
            mostrarEspaciosEnTabla(data.data);
            actualizarEstadisticasEspacios(data.data);
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
                        <p>Error al cargar espacios: ${error.message}</p>
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
                <td colspan="7" class="px-3 sm:px-6 py-12 text-center text-gray-500">
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
        <tr class="hover:bg-slate-50/80 dark:hover:bg-gray-700/50 transition-colors">
            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100 text-center align-middle">${espacio.codigo}</td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 text-center align-middle">${espacio.nombre}</td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 text-center align-middle">${espacio.tipo}</td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-center align-middle">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    Piso ${espacio.piso}
                </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-800 dark:text-gray-200 text-center align-middle">${espacio.capacidad}</td>
            <td class="px-4 py-3 whitespace-nowrap text-center align-middle">
                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full ${getEstadoClasses(espacio.estado)}">
                    ${espacio.estado}
                </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-center align-middle">
                <div class="flex items-center justify-center gap-1.5">
                    ${espacio.estado !== 'Disponible' 
                        ? `<button 
                            onclick="cambiarEstadoEspacio('${espacio.codigo}', 'Disponible')"
                            class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white border border-emerald-300 transition-colors shadow-xs">
                            <i class="fa-solid fa-unlock w-3 h-3 mr-1"></i>
                            Liberar
                        </button>`
                        : ''
                    }
                    <button 
                        onclick="cambiarEstadoEspacio('${espacio.codigo}', 'Mantenimiento')"
                        class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-600 hover:text-white border border-amber-300 transition-colors shadow-xs">
                        <i class="fa-solid fa-wrench w-3 h-3 mr-1"></i>
                        Mantenimiento
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

// Limpiar filtros
function limpiarFiltrosEspacios() {
    const estado = document.getElementById('filtro-estado-espacio'); if (estado) estado.value = '';
    const piso = document.getElementById('filtro-piso-espacio'); if (piso) piso.value = '';
    const busqueda = document.getElementById('filtro-busqueda-espacio'); if (busqueda) busqueda.value = '';
    filtrarEspacios();
}
window.limpiarFiltrosEspacios = limpiarFiltrosEspacios;

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
            String(e.codigo || '').toLowerCase().includes(busquedaFiltro) ||
            String(e.nombre || '').toLowerCase().includes(busquedaFiltro)
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
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#8D8D8D',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            const response = await fetch(`{{ url('quick-actions/api/espacio') }}/${codigoEspacio}/estado`, {
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

            if (data.success) {
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
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#8D8D8D',
        confirmButtonText: 'Sí, liberar todos',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            Swal.fire({
                title: 'Procesando...',
                text: 'Liberando espacios, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch('{{ route("quick-actions.api.espacios.liberacion-masiva") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: '¡Liberados!',
                    text: data.mensaje,
                    icon: 'success'
                });
                cargarEspacios();
            } else {
                Swal.fire('Error', data.mensaje || 'Error al liberar los espacios', 'error');
            }
        } catch (error) {
            console.error('Error en liberación masiva:', error);
            Swal.fire('Error', 'Error de conexión al realizar la liberación masiva', 'error');
        }
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
</x-app-layout>

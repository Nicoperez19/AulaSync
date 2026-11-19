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

        const response = await fetch((window.GestionarEspaciosConfig?.apiEspaciosUrl ?? '/quick-actions/api/espacios'));
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
            const response = await fetch(`${window.GestionarEspaciosConfig?.apiEspacioEstadoBaseUrl ?? '/quick-actions/api/espacio'}/${codigoEspacio}/estado`, {
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

            const response = await fetch((window.GestionarEspaciosConfig?.apiLiberacionMasivaUrl ?? '/quick-actions/api/espacios/liberacion-masiva'), {
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

Object.assign(window, { liberarTodosLosEspacios, ponerEnMantenimiento, filtrarEspacios, limpiarFiltrosEspacios, cargarEspacios, cambiarEstadoEspacio });

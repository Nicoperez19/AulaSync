/**
 * Funciones del Panel de Administración
 * Manejo de modales y operaciones administrativas
 */

// Variables globales para el panel admin
let espaciosDisponibles = [];
let modulosHorarios = {};

/**
 * ========================================
 * INICIALIZACIÓN
 * ========================================
 */

// Inicializar el panel de administración cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando Panel de Administración...');
    
    // Verificar que las funciones principales existan
    if (typeof qrInputManager === 'undefined') {
        console.warn('⚠️ qrInputManager no está disponible');
    }
    
    if (typeof Swal === 'undefined') {
        console.warn('⚠️ SweetAlert2 no está disponible');
    }
    
    if (typeof horariosModulos === 'undefined') {
        console.warn('⚠️ horariosModulos no está disponible desde el archivo principal');
    } else {
        // Usar los horarios del archivo principal si están disponibles
        modulosHorarios = horariosModulos;
        console.log('✅ Horarios de módulos importados correctamente');
    }
    
    console.log('✅ Panel de Administración inicializado');
});

/**
 * ========================================
 * FUNCIONES PRINCIPALES DEL PANEL ADMIN
 * ========================================
 */

// Función para abrir el modal de agregar reserva
function abrirModalAgregarReserva() {
    const modal = document.getElementById('modal-agregar-reserva');
    if (modal) {
        modal.classList.remove('hidden');
        qrInputManager.desactivarTodosLosInputs();
        
        // Cargar datos iniciales
        cargarEspaciosDisponibles();
        cargarModulosParaSeleccion();
        
        // Configurar event listeners para el modal
        configurarEventListenersModal();
        
        console.log('✅ Modal agregar reserva abierto');
    }
}

// Configurar event listeners específicos del modal
function configurarEventListenersModal() {
    const fechaInput = document.getElementById('fecha-reserva');
    
    // Event listener para cambio de fecha
    if (fechaInput) {
        fechaInput.addEventListener('change', function() {
            cargarModulosParaSeleccion();
            // Limpiar selecciones de módulos
            document.getElementById('modulo-inicial').value = '';
            document.getElementById('modulo-final').value = '';
        });
    }
}

// Función para cerrar el modal de agregar reserva
function cerrarModalAgregarReserva() {
    const modal = document.getElementById('modal-agregar-reserva');
    if (modal) {
        modal.classList.add('hidden');
        limpiarFormularioAgregarReserva();
        
        setTimeout(() => {
            qrInputManager.restaurarInputActivo();
        }, 200);
    }
}

// Función para abrir el modal de editar (selector)
function abrirModalEditar() {
    const modal = document.getElementById('modal-editar');
    if (modal) {
        modal.classList.remove('hidden');
        qrInputManager.desactivarTodosLosInputs();
        
        console.log('✅ Modal editar abierto');
    }
}

// Función para cerrar el modal de editar
function cerrarModalEditar() {
    const modal = document.getElementById('modal-editar');
    if (modal) {
        modal.classList.add('hidden');
        
        setTimeout(() => {
            qrInputManager.restaurarInputActivo();
        }, 200);
    }
}

// Función para confirmar vaciar reservas
async function confirmarVaciarReservas() {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción finalizará TODAS las reservas activas y liberará todos los espacios ocupados.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, vaciar todo',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        await vaciarTodasLasReservas();
    }
}

/**
 * ========================================
 * FUNCIONES PARA AGREGAR RESERVA
 * ========================================
 */

// Buscar usuario por RUN
async function buscarPorRun() {
    const runInput = document.getElementById('run-busqueda');
    const resultadoDiv = document.getElementById('resultado-busqueda');
    const run = runInput.value.trim();

    console.log('🔍 Buscando usuario con RUN:', run);

    if (!run) {
        resultadoDiv.innerHTML = '<span class="text-red-600">Por favor ingrese un RUN</span>';
        return;
    }

    try {
        resultadoDiv.innerHTML = '<span class="text-blue-600">Buscando...</span>';
        
        const response = await fetch(`/api/buscar-usuario/${run}`);
        console.log('📡 Response status búsqueda:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📦 Data búsqueda recibida:', data);

        if (data.success && data.usuario) {
            const usuario = data.usuario;
            
            // Rellenar automáticamente los campos
            document.getElementById('nombre-responsable').value = usuario.nombre || '';
            document.getElementById('run-responsable').value = usuario.run || '';
            document.getElementById('correo-responsable').value = usuario.correo || '';
            document.getElementById('telefono-responsable').value = usuario.telefono || '';
            document.getElementById('tipo-responsable').value = usuario.tipo_usuario || '';

            resultadoDiv.innerHTML = `<span class="text-green-600">✅ Usuario encontrado: ${usuario.nombre}</span>`;
            console.log('✅ Usuario encontrado y campos rellenados');
        } else {
            resultadoDiv.innerHTML = '<span class="text-orange-600">⚠️ Usuario no encontrado. Complete los datos manualmente.</span>';
            console.log('⚠️ Usuario no encontrado');
        }
    } catch (error) {
        console.error('❌ Error al buscar usuario:', error);
        resultadoDiv.innerHTML = '<span class="text-red-600">❌ Error en la búsqueda</span>';
    }
}

// Cargar espacios disponibles para el select
async function cargarEspaciosDisponibles() {
    console.log('🔄 Cargando espacios disponibles...');
    
    try {
        const response = await fetch('/api/espacios/disponibles');
        console.log('📡 Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📦 Data recibida:', data);
        
        const select = document.getElementById('espacio-reserva');
        if (select) {
            if (data.success && data.espacios) {
                select.innerHTML = '<option value="">Seleccione un espacio</option>';
                
                data.espacios.forEach(espacio => {
                    const option = document.createElement('option');
                    option.value = espacio.codigo;
                    option.textContent = `${espacio.codigo} - ${espacio.nombre} (Piso ${espacio.piso})`;
                    select.appendChild(option);
                });
                
                espaciosDisponibles = data.espacios;
                console.log(`✅ Cargados ${data.espacios.length} espacios`);
            } else {
                select.innerHTML = '<option value="">Error: No se pudieron cargar los espacios</option>';
                console.error('❌ Error en la respuesta:', data.mensaje || 'Estructura de datos incorrecta');
            }
        } else {
            console.error('❌ No se encontró el elemento select #espacio-reserva');
        }
    } catch (error) {
        console.error('❌ Error al cargar espacios:', error);
        const select = document.getElementById('espacio-reserva');
        if (select) {
            select.innerHTML = '<option value="">Error de conexión</option>';
        }
    }
}

// Cargar módulos para selección
function cargarModulosParaSeleccion() {
    const moduloInicialSelect = document.getElementById('modulo-inicial');
    const fechaInput = document.getElementById('fecha-reserva');
    
    if (moduloInicialSelect && fechaInput) {
        moduloInicialSelect.innerHTML = '<option value="">Seleccione módulo inicial</option>';
        
        // Verificar si la fecha seleccionada es hoy
        const fechaSeleccionada = new Date(fechaInput.value);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        fechaSeleccionada.setHours(0, 0, 0, 0);
        
        const esHoy = fechaSeleccionada.getTime() === hoy.getTime();
        
        let moduloMinimo = 1;
        
        if (esHoy) {
            // Si es hoy, obtener el módulo actual
            const horaActual = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const moduloActual = moduloActualNum(horaActual);
            
            if (moduloActual !== null) {
                // No permitir módulos anteriores al actual
                moduloMinimo = moduloActual;
            }
        }
        
        for (let i = moduloMinimo; i <= 16; i++) {
            const option = document.createElement('option');
            option.value = i;
            
            // Agregar información de horario si está disponible
            let textoModulo = `Módulo ${i}`;
            if (horariosModulos && Object.keys(horariosModulos).length > 0) {
                const diaActual = obtenerDiaActual();
                const horariosDia = horariosModulos[diaActual];
                if (horariosDia && horariosDia[i]) {
                    const horario = horariosDia[i];
                    textoModulo += ` (${formatearHora(horario.inicio)} - ${formatearHora(horario.fin)})`;
                }
            }
            
            option.textContent = textoModulo;
            moduloInicialSelect.appendChild(option);
        }
    }
}

// Función para formatear hora (helper)
function formatearHora(horaCompleta) {
    if (!horaCompleta) return '';
    return horaCompleta.slice(0, 5);
}

// Función para obtener día actual (helper)
function obtenerDiaActual() {
    const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    return dias[new Date().getDay()];
}

// Actualizar módulos finales disponibles
function actualizarModulosFinales() {
    const moduloInicial = parseInt(document.getElementById('modulo-inicial').value);
    const moduloFinalSelect = document.getElementById('modulo-final');
    
    if (moduloFinalSelect && moduloInicial) {
        moduloFinalSelect.innerHTML = '<option value="">Seleccione módulo final</option>';
        
        for (let i = moduloInicial; i <= 16; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Módulo ${i}`;
            moduloFinalSelect.appendChild(option);
        }
    }
}

// Procesar el formulario de agregar reserva
async function procesarAgregarReserva(event) {
    event.preventDefault();
    
    const formData = {
        nombre: document.getElementById('nombre-responsable').value.trim(),
        run: document.getElementById('run-responsable').value.trim(),
        correo: document.getElementById('correo-responsable').value.trim(),
        telefono: document.getElementById('telefono-responsable').value.trim(),
        tipo: document.getElementById('tipo-responsable').value,
        espacio: document.getElementById('espacio-reserva').value,
        fecha: document.getElementById('fecha-reserva').value,
        modulo_inicial: parseInt(document.getElementById('modulo-inicial').value),
        modulo_final: parseInt(document.getElementById('modulo-final').value),
        observaciones: document.getElementById('observaciones-reserva').value.trim()
    };

    // Validaciones
    if (!formData.nombre || !formData.run || !formData.correo || !formData.tipo) {
        Swal.fire('Error', 'Complete todos los campos obligatorios del responsable', 'error');
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

        const response = await fetch('/api/admin/crear-reserva', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire('¡Éxito!', 'Reserva creada correctamente', 'success');
            cerrarModalAgregarReserva();
            
            // Actualizar el mapa
            if (typeof actualizarColoresEspacios === 'function') {
                actualizarColoresEspacios(true);
            }
        } else {
            Swal.fire('Error', result.mensaje || 'Error al crear la reserva', 'error');
        }
    } catch (error) {
        console.error('Error al crear reserva:', error);
        Swal.fire('Error', 'Error de conexión al crear la reserva', 'error');
    }
}

// Limpiar formulario de agregar reserva
function limpiarFormularioAgregarReserva() {
    document.getElementById('form-agregar-reserva').reset();
    document.getElementById('resultado-busqueda').innerHTML = '';
    document.getElementById('fecha-reserva').value = new Date().toISOString().split('T')[0];
}

/**
 * ========================================
 * FUNCIONES PARA EDITAR RESERVAS
 * ========================================
 */

// Abrir modal de editar reservas
function abrirModalEditarReservas() {
    cerrarModalEditar(); // Cerrar el modal selector
    
    const modal = document.getElementById('modal-editar-reservas');
    if (modal) {
        modal.classList.remove('hidden');
        qrInputManager.desactivarTodosLosInputs();
        
        cargarReservas();
        console.log('✅ Modal editar reservas abierto');
    }
}

// Cerrar modal de editar reservas
function cerrarModalEditarReservas() {
    const modal = document.getElementById('modal-editar-reservas');
    if (modal) {
        modal.classList.add('hidden');
        
        setTimeout(() => {
            qrInputManager.restaurarInputActivo();
        }, 200);
    }
}

// Cargar reservas en la tabla
async function cargarReservas() {
    try {
        const tbody = document.getElementById('tabla-reservas-body');
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Cargando reservas...</td></tr>';

        const response = await fetch('/api/admin/reservas');
        const data = await response.json();

        if (data.success && data.reservas) {
            mostrarReservasEnTabla(data.reservas);
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No se encontraron reservas</td></tr>';
        }
    } catch (error) {
        console.error('Error al cargar reservas:', error);
        const tbody = document.getElementById('tabla-reservas-body');
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error al cargar reservas</td></tr>';
    }
}

// Mostrar reservas en la tabla
function mostrarReservasEnTabla(reservas) {
    const tbody = document.getElementById('tabla-reservas-body');
    
    if (reservas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay reservas</td></tr>';
        return;
    }

    tbody.innerHTML = reservas.map(reserva => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${reserva.id}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${reserva.nombre_responsable}</div>
                <div class="text-sm text-gray-500">RUN: ${reserva.run_responsable}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${reserva.codigo_espacio}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${reserva.fecha}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                Módulos ${reserva.modulo_inicial} - ${reserva.modulo_final}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    reserva.estado === 'activa' 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-gray-100 text-gray-800'
                }">
                    ${reserva.estado}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                ${reserva.estado === 'activa' 
                    ? `<button 
                        onclick="cambiarEstadoReserva(${reserva.id}, 'finalizada')"
                        class="text-red-600 hover:text-red-900">
                        Finalizar
                    </button>`
                    : `<button 
                        onclick="cambiarEstadoReserva(${reserva.id}, 'activa')"
                        class="text-green-600 hover:text-green-900">
                        Activar
                    </button>`
                }
            </td>
        </tr>
    `).join('');
}

// Cambiar estado de una reserva
async function cambiarEstadoReserva(reservaId, nuevoEstado) {
    try {
        const response = await fetch(`/api/admin/reserva/${reservaId}/estado`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ estado: nuevoEstado })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire('¡Éxito!', `Reserva ${nuevoEstado} correctamente`, 'success');
            cargarReservas(); // Recargar tabla
            
            // Actualizar el mapa
            if (typeof actualizarColoresEspacios === 'function') {
                actualizarColoresEspacios(true);
            }
        } else {
            Swal.fire('Error', result.mensaje || 'Error al cambiar estado', 'error');
        }
    } catch (error) {
        console.error('Error al cambiar estado de reserva:', error);
        Swal.fire('Error', 'Error de conexión', 'error');
    }
}

// Filtrar reservas
function filtrarReservas() {
    const estadoFiltro = document.getElementById('filtro-estado-reserva').value;
    const fechaFiltro = document.getElementById('filtro-fecha-reserva').value;
    
    // Aquí puedes implementar la lógica de filtrado
    cargarReservas(); // Por ahora solo recarga
}

/**
 * ========================================
 * FUNCIONES PARA EDITAR ESPACIOS
 * ========================================
 */

// Abrir modal de editar espacios
function abrirModalEditarEspacios() {
    cerrarModalEditar(); // Cerrar el modal selector
    
    const modal = document.getElementById('modal-editar-espacios');
    if (modal) {
        modal.classList.remove('hidden');
        qrInputManager.desactivarTodosLosInputs();
        
        cargarEspacios();
        console.log('✅ Modal editar espacios abierto');
    }
}

// Cerrar modal de editar espacios
function cerrarModalEditarEspacios() {
    const modal = document.getElementById('modal-editar-espacios');
    if (modal) {
        modal.classList.add('hidden');
        
        setTimeout(() => {
            qrInputManager.restaurarInputActivo();
        }, 200);
    }
}

// Cargar espacios en la tabla
async function cargarEspacios() {
    try {
        const tbody = document.getElementById('tabla-espacios-body');
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Cargando espacios...</td></tr>';

        const response = await fetch('/api/admin/espacios');
        const data = await response.json();

        if (data.success && data.espacios) {
            mostrarEspaciosEnTabla(data.espacios);
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No se encontraron espacios</td></tr>';
        }
    } catch (error) {
        console.error('Error al cargar espacios:', error);
        const tbody = document.getElementById('tabla-espacios-body');
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error al cargar espacios</td></tr>';
    }
}

// Mostrar espacios en la tabla
function mostrarEspaciosEnTabla(espacios) {
    const tbody = document.getElementById('tabla-espacios-body');
    
    if (espacios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay espacios</td></tr>';
        return;
    }

    tbody.innerHTML = espacios.map(espacio => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${espacio.codigo}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${espacio.nombre}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${espacio.tipo}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${espacio.piso}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${espacio.capacidad}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    espacio.estado === 'Disponible' 
                        ? 'bg-green-100 text-green-800' 
                        : espacio.estado === 'Ocupado'
                        ? 'bg-red-100 text-red-800'
                        : 'bg-yellow-100 text-yellow-800'
                }">
                    ${espacio.estado}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                ${espacio.estado !== 'Disponible' 
                    ? `<button 
                        onclick="cambiarEstadoEspacio('${espacio.codigo}', 'Disponible')"
                        class="text-green-600 hover:text-green-900">
                        Liberar
                    </button>`
                    : ''
                }
                <button 
                    onclick="cambiarEstadoEspacio('${espacio.codigo}', 'Mantenimiento')"
                    class="text-yellow-600 hover:text-yellow-900">
                    Mantenimiento
                </button>
            </td>
        </tr>
    `).join('');
}

// Cambiar estado de un espacio
async function cambiarEstadoEspacio(codigoEspacio, nuevoEstado) {
    try {
        const response = await fetch(`/api/admin/espacio/${codigoEspacio}/estado`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ estado: nuevoEstado })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire('¡Éxito!', `Estado del espacio cambiado a ${nuevoEstado}`, 'success');
            cargarEspacios(); // Recargar tabla
            
            // Actualizar el mapa
            if (typeof actualizarColoresEspacios === 'function') {
                actualizarColoresEspacios(true);
            }
        } else {
            Swal.fire('Error', result.mensaje || 'Error al cambiar estado', 'error');
        }
    } catch (error) {
        console.error('Error al cambiar estado de espacio:', error);
        Swal.fire('Error', 'Error de conexión', 'error');
    }
}

// Filtrar espacios
function filtrarEspacios() {
    const estadoFiltro = document.getElementById('filtro-estado-espacio').value;
    const pisoFiltro = document.getElementById('filtro-piso-espacio').value;
    
    // Aquí puedes implementar la lógica de filtrado
    cargarEspacios(); // Por ahora solo recarga
}

/**
 * ========================================
 * FUNCIÓN PARA VACIAR TODAS LAS RESERVAS
 * ========================================
 */

// Vaciar todas las reservas activas
async function vaciarTodasLasReservas() {
    try {
        // Mostrar loading
        Swal.fire({
            title: 'Finalizando reservas...',
            text: 'Por favor espere mientras se procesan todas las reservas',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch('/api/admin/vaciar-reservas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire(
                '¡Completado!', 
                `Se finalizaron ${result.reservas_finalizadas} reservas y se liberaron ${result.espacios_liberados} espacios`, 
                'success'
            );
            
            // Actualizar el mapa
            if (typeof actualizarColoresEspacios === 'function') {
                actualizarColoresEspacios(true);
            }
        } else {
            Swal.fire('Error', result.mensaje || 'Error al vaciar reservas', 'error');
        }
    } catch (error) {
        console.error('Error al vaciar reservas:', error);
        Swal.fire('Error', 'Error de conexión al vaciar reservas', 'error');
    }
}

// Función para actualizar módulos disponibles (placeholder)
function actualizarModulosDisponibles() {
    // Esta función se puede expandir para verificar disponibilidad real
    console.log('Actualizando módulos disponibles...');
}

// ========================================
// FUNCIONES DE DEBUG Y TESTING
// ========================================

// Función para testear la API de espacios (debug)
async function testearAPIEspacios() {
    console.log('🧪 Testeando API de espacios...');
    try {
        const response = await fetch('/api/espacios/disponibles');
        console.log('📡 Response:', response);
        const data = await response.json();
        console.log('📦 Data:', data);
        return data;
    } catch (error) {
        console.error('❌ Error:', error);
        return error;
    }
}

// Función para testear la API de búsqueda de usuario (debug)
async function testearAPIBusqueda(run = '12345678') {
    console.log('🧪 Testeando API de búsqueda con RUN:', run);
    try {
        const response = await fetch(`/api/buscar-usuario/${run}`);
        console.log('📡 Response:', response);
        const data = await response.json();
        console.log('📦 Data:', data);
        return data;
    } catch (error) {
        console.error('❌ Error:', error);
        return error;
    }
}

// Exponer funciones de debug globalmente para testing desde consola
window.testearAPIEspacios = testearAPIEspacios;
window.testearAPIBusqueda = testearAPIBusqueda;

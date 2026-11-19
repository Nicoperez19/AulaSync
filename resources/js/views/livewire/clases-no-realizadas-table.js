import '../../shared/page-config';
function initClasesNoRealizadasScripts() {
    // Listener para abrir modal automáticamente desde URL
    Livewire.on('auto-open-reagendar', (data) => {
        const id = data[0]?.id || data.id;
        if (id) {
            // Pequeño delay para asegurar que la página esté lista
            setTimeout(() => {
                window.Livewire.find(window.ClasesNoRealizadasConfig?.componentId).call('showReagendarModal', id);
            }, 500);
        }
    });

    Livewire.on('show-edit-modal', (data) => {
        const clase = data[0];
        
        Swal.fire({
            title: '<strong>Editar Estado de Clase</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}${clase.seccion && clase.seccion !== 'N/A' ? ' (Sec. ' + clase.seccion + ')' : ''}</p>
                        ${clase.ua && clase.ua !== 'N/A' ? `<p><strong>UA:</strong> ${clase.ua}</p>` : ''}
                        <p><strong>Fecha:</strong> ${clase.fecha}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select id="swal-estado" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="no_realizada" ${clase.estado === 'no_realizada' ? 'selected' : ''}>No registrada</option>
                            <option value="realizada" ${clase.estado === 'realizada' || clase.estado === 'registrada' ? 'selected' : ''}>Registrada (Docente realizó la clase)</option>
                            <option value="pendiente" ${clase.estado === 'pendiente' ? 'selected' : ''}>Pendiente de recuperación</option>
                            <option value="justificado" ${clase.estado === 'justificado' ? 'selected' : ''}>Justificado</option>
                            <option value="recuperada" ${clase.estado === 'recuperada' ? 'selected' : ''}>Recuperada</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <strong>Registrada:</strong> Docente dictó la clase (asistencia regularizada)<br>
                            <strong>Pendiente:</strong> Clase reagendada esperando recuperación<br>
                            <strong>Justificado:</strong> Clase justificada<br>
                            <strong>Recuperada:</strong> Recuperación confirmada
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea id="swal-observaciones" rows="4" class="w-full p-2 border border-gray-300 rounded-md resize-none" placeholder="Ingrese observaciones...">${clase.observaciones || ''}</textarea>
                    </div>
                </div>
            `,
            width: 600,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Guardar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#8D8D8D',
            didOpen: () => {
                document.getElementById('swal-estado').focus();
            },
            preConfirm: () => {
                const estado = document.getElementById('swal-estado').value;
                const observaciones = document.getElementById('swal-observaciones').value;
                
                if (!estado) {
                    Swal.showValidationMessage('Por favor selecciona un estado');
                    return false;
                }
                
                if (observaciones.length > 1000) {
                    Swal.showValidationMessage('Las observaciones no pueden exceder 1000 caracteres');
                    return false;
                }
                
                return { estado, observaciones };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { estado, observaciones } = result.value;
                window.Livewire.find(window.ClasesNoRealizadasConfig?.componentId).call('updateClase', clase.id, estado, observaciones);
            }
        });
    });

    Livewire.on('show-reagendar-modal', (data) => {
        const clase = data[0];
        
        console.log('Datos completos recibidos:', clase);
        
        // Variable global para almacenar módulos
        let modulosDisponibles = [];
        const fechaOriginal = clase.fecha_original; // formato: "dd/mm/yyyy"

        Swal.fire({
            title: '<strong><i class="fas fa-calendar-plus"></i> Reagendar Clase</strong>',
            html: `
                <div class="text-left space-y-4">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Importante:</strong> Al reagendar esta clase, se marcará como <strong>justificada</strong> 
                                    pero quedará <strong>pendiente de recuperación</strong>. La clase permanecerá en el listado 
                                    hasta que se confirme su realización.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg mb-4">
                        <h4 class="font-semibold text-slate-900 mb-2">Clase Original</h4>
                        <p><strong>Profesor:</strong> ${clase.profesor}</p>
                        <p><strong>Asignatura:</strong> ${clase.asignatura}${clase.seccion && clase.seccion !== 'N/A' ? ' (Sec. ' + clase.seccion + ')' : ''}</p>
                        ${clase.ua && clase.ua !== 'N/A' ? `<p><strong>UA:</strong> ${clase.ua}</p>` : ''}
                        <p><strong>Fecha:</strong> ${clase.fecha_original}</p>
                        <p><strong>Espacio:</strong> ${clase.espacio_original}</p>
                        <p><strong>Módulo:</strong> ${clase.modulo_original}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Fecha <span class="text-red-500">*</span></label>
                            <input type="date" id="swal-nueva-fecha" class="w-full p-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad de Módulos <span class="text-red-500">*</span></label>
                            <input type="number" id="swal-cantidad-modulos" class="w-full p-2 border border-gray-300 rounded-md" min="1" max="8" value="${clase.totalModulosProgramados}">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Inicio <span class="text-red-500">*</span></label>
                            <select id="swal-nuevo-modulo-inicio" class="w-full p-2 border border-gray-300 rounded-md">
                                <option value="">Cargando módulos...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Módulo Final (Automático)</label>
                        <div class="p-3 bg-gray-50 border border-gray-300 rounded-md">
                            <span id="swal-modulo-final-display" class="text-gray-700">Selecciona un módulo inicial</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nuevo Espacio <span class="text-red-500">*</span></label>
                        <select id="swal-nuevo-espacio" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="">Selecciona fecha y módulo primero</option>
                        </select>
                        <p id="espacios-disponibles-info" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Motivo del reagendamiento</label>
                        <textarea id="swal-motivo-reagendamiento" rows="3" class="w-full p-2 border border-gray-300 rounded-md resize-none" placeholder="Explique el motivo del reagendamiento..."></textarea>
                    </div>
                </div>
            `,
            width: 700,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-calendar-check"></i> Reagendar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#8D8D8D',
            didOpen: async () => {
                const dateInput = document.getElementById('swal-nueva-fecha');
                const hoy = new Date().toISOString().split('T')[0];
                dateInput.min = hoy;
                dateInput.focus();
                
                // Cargar módulos desde la API
                try {
                    const response = await fetch('/api/modulos');
                    modulosDisponibles = await response.json();
                    console.log('Módulos cargados:', modulosDisponibles);
                    
                    // Llenar select de módulos iniciales inicialmente vacío
                    const selectModuloInicio = document.getElementById('swal-nuevo-modulo-inicio');
                    selectModuloInicio.innerHTML = '<option value="">Selecciona fecha primero</option>';
                    selectModuloInicio.disabled = true;

                    // Función para filtrar módulos por día
                    const filtrarModulosPorDia = (fechaStr) => {
                        if (!fechaStr) {
                            selectModuloInicio.innerHTML = '<option value="">Selecciona fecha primero</option>';
                            selectModuloInicio.disabled = true;
                            return;
                        }

                        const fecha = new Date(fechaStr + 'T12:00:00');
                        const prefijos = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
                        const prefijo = prefijos[fecha.getDay()];

                        // Filtrar y ordenar módulos
                        const modulosFiltrados = modulosDisponibles
                            .filter(m => m.id_modulo.startsWith(prefijo + '.'))
                            .sort((a, b) => {
                                const numA = parseInt(a.id_modulo.split('.')[1]);
                                const numB = parseInt(b.id_modulo.split('.')[1]);
                                return numA - numB;
                            });

                        if (modulosFiltrados.length === 0) {
                            selectModuloInicio.innerHTML = '<option value="">No hay módulos para este día</option>';
                            selectModuloInicio.disabled = true;
                        } else {
                            selectModuloInicio.innerHTML = '<option value="">Seleccionar módulo</option>';
                            modulosFiltrados.forEach(modulo => {
                                const option = document.createElement('option');
                                option.value = modulo.id_modulo;
                                option.textContent = `${modulo.id_modulo} (${modulo.hora_inicio} - ${modulo.hora_termino})`;
                                selectModuloInicio.appendChild(option);
                            });
                            selectModuloInicio.disabled = false;
                        }
                    };

                    // Listener para cambio de fecha
                    dateInput.addEventListener('change', (e) => {
                        filtrarModulosPorDia(e.target.value);
                        // Limpiar espacio e info si cambia la fecha
                        document.getElementById('swal-nuevo-espacio').innerHTML = '<option value="">Selecciona fecha y módulo primero</option>';
                        const infoP = document.getElementById('espacios-disponibles-info');
                        if (infoP) infoP.textContent = '';
                    });
                } catch (error) {
                    console.error('Error cargando módulos:', error);
                }
                
                // Variables para controlar el cálculo
                const moduloInicio = document.getElementById('swal-nuevo-modulo-inicio');
                const moduloFinalDisplay = document.getElementById('swal-modulo-final-display');
                const cantidadModulos = document.getElementById('swal-cantidad-modulos');
                const dateInput2 = document.getElementById('swal-nueva-fecha');
                const selectEspacios = document.getElementById('swal-nuevo-espacio');
                const infoParagraph = document.getElementById('espacios-disponibles-info');
                
                // Función para cargar espacios disponibles
                const cargarEspaciosDisponibles = async () => {
                    const fecha = dateInput2.value;
                    const modulo = moduloInicio.value;
                    
                    if (!fecha || !modulo) {
                        selectEspacios.innerHTML = '<option value="">Selecciona fecha y módulo primero</option>';
                        infoParagraph.textContent = '';
                        return;
                    }

                    // Validar que no sea el mismo día
                    const [dia, mes, anio] = fechaOriginal.split('/');
                    const fechaOriginalFormato = anio + '-' + mes + '-' + dia;
                    if (fecha === fechaOriginalFormato) {
                        selectEspacios.innerHTML = '<option value="">No puedes reagendar para el mismo día</option>';
                        infoParagraph.textContent = 'El reagendamiento debe ser en una fecha diferente';
                        return;
                    }

                    // Calcular módulo final
                    const partesModulo = modulo.split('.');
                    const inicio = parseInt(partesModulo[1] || partesModulo[0]);
                    const prefijo = partesModulo[0];
                    const cantidad = parseInt(cantidadModulos.value) || clase.totalModulosProgramados;
                    const fin = inicio + cantidad - 1;
                    const moduloFinalId = prefijo + '.' + fin;

                    try {
                        const url = `/api/espacios-disponibles/${fecha}/${modulo}/${moduloFinalId}`;
                        const response = await fetch(url);
                        const data = await response.json();
                        
                        if (data.error) {
                            selectEspacios.innerHTML = '<option value="">Error: ' + data.error + '</option>';
                            infoParagraph.textContent = '';
                            return;
                        }

                        console.log('Espacios disponibles:', data);

                        if (data.espacios.length === 0) {
                            selectEspacios.innerHTML = '<option value="">No hay espacios disponibles para esta fecha y módulos</option>';
                            infoParagraph.textContent = 'Intenta con otra fecha o módulo';
                            return;
                        }

                        selectEspacios.innerHTML = '<option value="">Seleccionar espacio</option>';
                        data.espacios.forEach(espacio => {
                            const option = document.createElement('option');
                            option.value = espacio.id_espacio;
                            option.textContent = espacio.display_name;
                            selectEspacios.appendChild(option);
                        });

                        infoParagraph.textContent = 'Disponibles: ' + data.total_disponibles + ' espacio(s) • Módulo: ' + data.hora_inicio + ' - ' + data.hora_fin;
                    } catch (error) {
                        console.error('Error cargando espacios:', error);
                        selectEspacios.innerHTML = '<option value="">Error al cargar espacios</option>';
                    }
                };
                
                // Función para recalcular el módulo final y mostrar horarios
                const recalcularModuloFinal = () => {
                    if (moduloInicio.value && modulosDisponibles.length > 0) {
                        const partesModulo = moduloInicio.value.split('.');
                        const inicio = parseInt(partesModulo[1] || partesModulo[0]);
                        const prefijo = partesModulo[0];
                        const cantidad = parseInt(cantidadModulos.value) || clase.totalModulosProgramados;
                        const fin = inicio + cantidad - 1;
                        const moduloFinalId = prefijo + '.' + fin;
                        
                        // Buscar horarios de inicio y fin
                        const moduloInicial = modulosDisponibles.find(m => m.id_modulo == moduloInicio.value);
                        const moduloFinal = modulosDisponibles.find(m => m.id_modulo == moduloFinalId);
                        
                        if (moduloInicial && moduloFinal) {
                            const texto = 'Módulos ' + inicio + ' - ' + fin + ' (' + moduloInicial.hora_inicio + ' - ' + moduloFinal.hora_termino + ')';
                            moduloFinalDisplay.textContent = texto;
                        }
                        
                        // Cargar espacios disponibles cuando se selecciona módulo
                        cargarEspaciosDisponibles();
                    } else {
                        moduloFinalDisplay.textContent = 'Selecciona un módulo inicial';
                    }
                };
                
                dateInput2.addEventListener('change', cargarEspaciosDisponibles);
                moduloInicio.addEventListener('change', recalcularModuloFinal);
                cantidadModulos.addEventListener('change', recalcularModuloFinal);
                cantidadModulos.addEventListener('input', recalcularModuloFinal);
            },
            preConfirm: () => {
                const nuevaFecha = document.getElementById('swal-nueva-fecha').value;
                const nuevoEspacio = document.getElementById('swal-nuevo-espacio').value;
                const nuevoModulo = document.getElementById('swal-nuevo-modulo-inicio').value;
                const cantidadModulos = document.getElementById('swal-cantidad-modulos').value;
                const motivo = document.getElementById('swal-motivo-reagendamiento').value;
                
                if (!nuevaFecha) {
                    Swal.showValidationMessage('Por favor selecciona una fecha');
                    return false;
                }
                
                // Validar que no sea el mismo día
                const [dia, mes, anio] = fechaOriginal.split('/');
                const fechaOriginalFormato = anio + '-' + mes + '-' + dia;
                if (nuevaFecha === fechaOriginalFormato) {
                    Swal.showValidationMessage('No puedes reagendar para el mismo día');
                    return false;
                }
                
                if (!nuevoEspacio) {
                    Swal.showValidationMessage('Por favor selecciona un espacio');
                    return false;
                }
                
                if (!nuevoModulo) {
                    Swal.showValidationMessage('Por favor selecciona un módulo de inicio');
                    return false;
                }
                
                if (!cantidadModulos || parseInt(cantidadModulos) < 1 || parseInt(cantidadModulos) > 15) {
                    Swal.showValidationMessage('Por favor ingresa una cantidad válida de módulos (1-15)');
                    return false;
                }
                
                return { nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo } = result.value;
                window.Livewire.find(window.ClasesNoRealizadasConfig?.componentId).call('reagendarClase', clase.id, nuevaFecha, nuevoEspacio, nuevoModulo, cantidadModulos, motivo);
            }
        });
    });

    Livewire.on('confirm-delete', (data) => {
        const clase = data[0];
        
        Swal.fire({
            title: '¿Eliminar registro?',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Profesor:</strong> ${clase.profesor}</p>
                    <p class="mb-2"><strong>Asignatura:</strong> ${clase.asignatura}${clase.seccion && clase.seccion !== 'N/A' ? ' (Sec. ' + clase.seccion + ')' : ''}</p>
                    <p class="mb-2"><strong>Fecha:</strong> ${clase.fecha}</p>
                    <br>
                    <p class="text-red-600"><strong>Esta acción no se puede deshacer</strong></p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D',
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find(window.ClasesNoRealizadasConfig?.componentId).call('confirmDelete', clase.id);
            }
        });
    });

    Livewire.on('show-success', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const message = payload?.message || 'Operación realizada exitosamente';
        
        Swal.fire({
            title: '¡Éxito!',
            text: message,
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#10B981',
            timer: 3000,
            timerProgressBar: true
        });
    });

    Livewire.on('show-error', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const message = payload?.message || 'Ha ocurrido un error inesperado';
        
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#D12420'
        });
    });

    Livewire.on('abrir-modal-historial-auditoria', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const historial = payload?.historial || [];
        
        let htmlContent = `
            <div class="text-left text-xs text-slate-700 max-h-96 overflow-y-auto">
                <div class="mb-3 p-3 bg-slate-100 rounded-xl border border-slate-200">
                    <p class="font-bold text-slate-900">${payload.profesor}</p>
                    <p class="text-slate-600">${payload.asignatura}${payload.seccion && payload.seccion !== 'N/A' ? ' (Sec. ' + payload.seccion + ')' : ''}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Fecha de la clase: <strong>${payload.fecha}</strong></p>
                </div>
        `;
        
        if (historial.length === 0) {
            htmlContent += `
                <div class="text-center py-6 text-slate-400">
                    <i class="fas fa-history text-2xl mb-2 text-slate-300"></i>
                    <p>No se registran cambios previos para esta clase.</p>
                </div>
            `;
        } else {
            htmlContent += `<div class="space-y-2.5">`;
            historial.forEach((item) => {
                htmlContent += `
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-2xs">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-extrabold text-slate-900 text-xs">${item.accion}</span>
                            <span class="text-[10px] text-slate-400 font-mono">${item.fecha_hora}</span>
                        </div>
                        <p class="text-[11px] text-slate-600"><strong>Por:</strong> ${item.usuario} <span class="text-slate-400">(${item.rol})</span></p>
                        <p class="text-[11px] text-slate-600"><strong>Transición:</strong> <span class="line-through text-slate-400">${item.estado_anterior}</span> &rarr; <span class="font-bold text-emerald-700">${item.estado_nuevo}</span></p>
                        ${item.motivo ? `<p class="text-[11px] text-slate-800 mt-1"><strong>Motivo:</strong> ${item.motivo}</p>` : ''}
                        ${item.observaciones ? `<p class="text-[10px] text-slate-500 mt-1 p-2 bg-slate-50 rounded-lg border border-slate-100 italic">${item.observaciones}</p>` : ''}
                    </div>
                `;
            });
            htmlContent += `</div>`;
        }
        
        htmlContent += `</div>`;
        
        Swal.fire({
            title: '<i class="fas fa-history text-slate-700 mr-2"></i> Historial de Auditoría',
            html: htmlContent,
            width: '600px',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#8D8D8D',
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClasesNoRealizadasScripts);
} else {
    initClasesNoRealizadasScripts();
}

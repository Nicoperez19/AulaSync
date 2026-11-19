        document.addEventListener('DOMContentLoaded', function() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            window.cargarEspaciosDisponibles = async function() {
                try {
                    const response = await fetch('/quick-actions/api/espacios', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const data = await response.json();
                    const select = document.getElementById('espacio-reserva');
                    if (select) {
                        if (data.success && data.espacios && data.espacios.length > 0) {
                            select.innerHTML = '<option value="">Seleccione un espacio</option>';
                            data.espacios.forEach(espacio => {
                                const option = document.createElement('option');
                                option.value = espacio.codigo;
                                const cap = espacio.capacidad || espacio.capacidad_maxima;
                                const capStr = cap ? ` | Cap: ${cap} pers.` : '';
                                const estadoTag = espacio.estado && espacio.estado !== 'Disponible' ? ` [${espacio.estado.toUpperCase()}]` : '';
                                if (espacio.estado === 'Mantenimiento') {
                                    option.disabled = true;
                                }
                                option.textContent = `${espacio.codigo} - ${espacio.nombre} (Piso ${espacio.piso})${capStr}${estadoTag}`;
                                select.appendChild(option);
                            });
                            window.espaciosDisponibles = data.espacios;
                        } else {
                            select.innerHTML = '<option value="">No hay espacios disponibles</option>';
                        }
                    }
                } catch (error) {
                    console.error('Error al cargar espacios:', error);
                }
            };
        });

            function toggleModalidadReserva(modalidad) {
                const contenedorFin = document.getElementById('contenedor-fecha-fin');
                const labelInicio = document.getElementById('label-fecha-inicio');
                const labelPuntual = document.getElementById('label-modalidad-puntual');
                const labelRecurrente = document.getElementById('label-modalidad-recurrente');
                const inputFechaInicio = document.getElementById('fecha-reserva');
                const inputFechaFin = document.getElementById('fecha-fin-reserva');

                if (modalidad === 'recurrente') {
                    contenedorFin.classList.remove('hidden');
                    labelInicio.textContent = 'Fecha de Inicio (Primera reserva) *';
                    labelPuntual.classList.remove('bg-green-50', 'border-green-500');
                    labelRecurrente.classList.add('bg-purple-50', 'border-purple-500');
                    
                    // Si no tiene fecha fin, establecer 16 semanas después o fin del semestre
                    if (!inputFechaFin.value && inputFechaInicio.value) {
                        const inicio = new Date(inputFechaInicio.value + 'T12:00:00');
                        const fin = new Date(inicio);
                        fin.setDate(fin.getDate() + (15 * 7)); // 16 semanas
                        inputFechaFin.value = fin.toISOString().split('T')[0];
                    }
                    actualizarPrevisualizacionRecurrente();
                } else {
                    contenedorFin.classList.add('hidden');
                    labelInicio.textContent = 'Fecha *';
                    labelRecurrente.classList.remove('bg-purple-50', 'border-purple-500');
                    labelPuntual.classList.add('bg-green-50', 'border-green-500');
                }
            }

            function actualizarPrevisualizacionRecurrente() {
                const tipoFrecuencia = document.querySelector('input[name="tipo_frecuencia"]:checked')?.value;
                if (tipoFrecuencia !== 'recurrente') return;

                const fInicioStr = document.getElementById('fecha-reserva').value;
                const fFinStr = document.getElementById('fecha-fin-reserva').value;
                const resumenEl = document.getElementById('resumen-recurrencia-texto');

                if (!fInicioStr || !fFinStr) {
                    resumenEl.textContent = '';
                    return;
                }

                const inicio = new Date(fInicioStr + 'T12:00:00');
                const fin = new Date(fFinStr + 'T12:00:00');

                if (fin < inicio) {
                    resumenEl.textContent = '⚠️ La fecha de término debe ser posterior a la fecha de inicio.';
                    return;
                }

                const diasSemana = ['Domingos', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábados'];
                const nombreDia = diasSemana[inicio.getDay()];

                let count = 0;
                let actual = new Date(inicio);
                while (actual <= fin) {
                    count++;
                    actual.setDate(actual.getDate() + 7);
                }

                resumenEl.textContent = `🗓️ Se crearán ${count} reservas semanales (los días ${nombreDia}) desde ${fInicioStr} hasta ${fFinStr}.`;
            }

            // Función específica para el mantenedor
            async function procesarCrearReserva(event) {
                event.preventDefault();

                const forzarActivo = document.getElementById('forzar-reserva')?.value === '1';
                const tipoFrecuencia = document.querySelector('input[name="tipo_frecuencia"]:checked')?.value || 'puntual';
                const fechaFin = tipoFrecuencia === 'recurrente' ? document.getElementById('fecha-fin-reserva').value : null;

                const institucionSolicitante = document.getElementById('institucion-solicitante')?.value?.trim() || '';
                let descripcionActividadFinal = document.getElementById('descripcion-actividad')?.value?.trim() || '';
                if (institucionSolicitante && !descripcionActividadFinal.toLowerCase().includes('[institución:')) {
                    descripcionActividadFinal = `[Institución: ${institucionSolicitante}]` + (descripcionActividadFinal ? ` ${descripcionActividadFinal}` : '');
                }

                const formData = {
                    nombre: document.getElementById('nombre-responsable').value.trim(),
                    run: document.getElementById('run-responsable').value.trim(),
                    correo: document.getElementById('correo-responsable').value.trim(),
                    telefono: document.getElementById('telefono-responsable').value.trim() || null,
                    tipo: document.getElementById('tipo-responsable').value,
                    id_asignatura: document.getElementById('id-asignatura').value || null,
                    espacio: document.getElementById('espacio-reserva').value,
                    fecha: document.getElementById('fecha-reserva').value,
                    tipo_frecuencia: tipoFrecuencia,
                    fecha_fin: fechaFin,
                    modulo_inicial: parseInt(document.getElementById('modulo-inicial').value),
                    modulo_final: parseInt(document.getElementById('modulo-final').value),
                    observaciones: document.getElementById('observaciones-reserva').value.trim(),
                    nombre_actividad: document.getElementById('nombre-actividad')?.value?.trim() || null,
                    descripcion_actividad: descripcionActividadFinal || null,
                    forzar: forzarActivo,
                };



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

                // Validar nombre de actividad si es solicitante externo
                if (formData.tipo === 'solicitante' && !formData.nombre_actividad) {
                    Swal.fire('Error', 'Debe indicar el nombre de la actividad para reservas de solicitantes externos', 'error');
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

                // Validar conflictos: si hay conflictos y no se ha forzado, preguntar interactivamente si desea forzar
                if (tieneConflictos && !forzarActivo) {
                    const confirmacionPrevia = await Swal.fire({
                        title: '⚠️ Espacio con Conflicto de Horario',
                        html: `
                            <div class="text-left text-sm text-gray-700 space-y-2">
                                <p>Este espacio registra <strong>clases programadas o reservas activas</strong> en los módulos seleccionados.</p>
                                <div class="p-3 bg-amber-50 border-l-4 border-amber-500 rounded text-xs text-amber-900">
                                    <strong>¿Desea forzar la reserva de todas formas?</strong><br>
                                    Si la sala se encuentra físicamente desocupada, puede forzar su uso. La reserva quedará guardada con registro de auditoría <em>⚠️ RESERVA FORZADA</em>.
                                </div>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '⚠️ Sí, Forzar y Crear Reserva',
                        cancelButtonText: 'Cancelar y Revisar',
                        confirmButtonColor: '#D12420',
                        cancelButtonColor: '#8D8D8D',
                    });

                    if (confirmacionPrevia.isConfirmed) {
                        activarForzar();
                        formData.forzar = true;
                    } else {
                        return;
                    }
                }

                await enviarCreacionReserva(formData);
            }

            // Función para enviar la petición de creación de reserva con soporte de reintento forzado
            async function enviarCreacionReserva(formData) {
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const responseText = await response.text();
                    let result;

                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        throw new Error(`El servidor respondió con HTTP ${response.status}.`);
                    }

                    if (response.ok && result.success) {
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
                                            <strong>Espacio:</strong> <span class="ml-1">${result.datos.espacio} (${result.datos.id_espacio})</span>
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
                                            <strong>Hora inicio:</strong> 
                                            <span class="ml-1">
                                                ${result.datos.hora.slice(0, 5)} hrs.
                                            </span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <strong>Tipo:</strong> <span class="px-2 py-1 rounded text-xs ml-1 ${result.datos.tipo === 'Académica' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}">${result.datos.tipo}</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 p-3 bg-yellow-50 border-l-2 border-yellow-400 rounded">
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
                            showDenyButton: true,
                            denyButtonText: 'Descargar Comprobante',
                            denyButtonColor: '#2563EB',
                            showCancelButton: true,
                            cancelButtonText: 'Crear otra reserva',
                            cancelButtonColor: '#8D8D8D',
                            confirmButtonColor: '#10B981',
                            width: '600px'
                        }).then((dialogResult) => {
                            if (dialogResult.isDenied) {
                                const idReserva = result.id_reserva || result.reserva?.id;
                                const comprobanteUrl = result.url_comprobante || (idReserva ? `/reservas/${idReserva}/comprobante` : null);

                                if (!comprobanteUrl) {
                                    Swal.fire('Error', 'No se encontró el ID de la reserva para generar el comprobante.', 'error');
                                    return;
                                }

                                window.open(comprobanteUrl, '_blank');
                            } else if (dialogResult.isConfirmed) {
                                window.location.href = window.CrearReservaConfig?.gestionarReservasUrl ?? '/quick-actions/gestionar-reservas';
                            } else {
                                // Recargar la página para dejar el formulario en estado limpio y reinicializar todos los campos
                                window.location.reload();
                            }
                        });
                    } else if (response.status === 409 || result.tipo_error === 'clase_programada' || result.tipo_error === 'reserva_existente' || (result.mensaje && result.mensaje.toLowerCase().includes('clase programada'))) {
                        // En caso de conflicto de programación (409), ofrecer forzar interactivamente
                        const confirmacionConflicto = await Swal.fire({
                            title: '⚠️ Espacio con Programación Activa',
                            html: `
                                <div class="text-left text-sm text-gray-700 space-y-3">
                                    <p>${result.mensaje || 'Existe una clase o reserva programada en este espacio.'}</p>
                                    <div class="p-3 bg-amber-50 border-l-4 border-amber-500 rounded text-xs text-amber-900">
                                        <strong>¿Desea forzar el uso del espacio?</strong><br>
                                        Si la sala se encuentra físicamente desocupada, puede forzar la reserva para permitir el ingreso. Se registrará la etiqueta <em>⚠️ RESERVA FORZADA</em> para trazabilidad y auditoría.
                                    </div>
                                </div>
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: '⚠️ Forzar y Crear Reserva',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#D12420',
                            cancelButtonColor: '#8D8D8D',
                            width: '550px'
                        });

                        if (confirmacionConflicto.isConfirmed) {
                            activarForzar();
                            formData.forzar = true;
                            await enviarCreacionReserva(formData);
                        }
                    } else {
                        const erroresValidacion = result.errors
                            ? Object.values(result.errors).flat().join('<br>')
                            : '';
                        Swal.fire('Error', erroresValidacion || result.mensaje || result.message || 'Error al crear la reserva', 'error');
                    }
                } catch (error) {
                    console.error('Error al enviar creación de reserva:', error);
                    Swal.fire('Error', 'Error de conexión al crear la reserva', 'error');
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


            }

            // Configurar eventos del autocompletado
            function configurarAutocompletado() {
                const input = document.getElementById('run-busqueda');
                const resultsDiv = document.getElementById('autocomplete-results');

                // Evento de escritura
                input.addEventListener('input', function (e) {
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
                document.addEventListener('click', function (e) {
                    if (!input.contains(e.target) && !resultsDiv.contains(e.target)) {
                        resultsDiv.classList.add('hidden');
                    }
                });

                // Limpiar información cuando se borra el campo
                input.addEventListener('input', function (e) {
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

            // Mapeo de módulos a horarios para crear reserva (dinámico desde ModulosHelper)
            const horariosModulosCrearReserva = {};
            const modulosLunesCfg = window.CrearReservaConfig?.modulosLunes ?? {};
            Object.entries(modulosLunesCfg).forEach(([num, horario]) => {
                horariosModulosCrearReserva[num] = `${String(horario.inicio).substring(0, 5)} - ${String(horario.fin).substring(0, 5)}`;
            });

            function obtenerPrefijoDia(fechaStr) {
                if (!fechaStr) return 'LU';
                const fecha = new Date(fechaStr + 'T12:00:00'); // Evitar problemas de zona horaria
                const prefijos = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
                return prefijos[fecha.getDay()];
            }

            // Función para cargar módulos con horarios
            function cargarModulosParaSeleccion() {
                const moduloInicialSelect = document.getElementById('modulo-inicial');
                const moduloFinalSelect = document.getElementById('modulo-final');

                if (!moduloInicialSelect || !moduloFinalSelect) {

                    return;
                }

                // Limpiar opciones existentes (excepto la primera opción)
                moduloInicialSelect.innerHTML = '<option value="">Seleccionar módulo inicial</option>';
                moduloFinalSelect.innerHTML = '<option value="">Seleccionar módulo final</option>';

                // Obtener prefijo según la fecha seleccionada
                const fechaSeleccionada = document.getElementById('fecha-reserva').value;
                const prefijo = obtenerPrefijoDia(fechaSeleccionada);

                // Agregar opciones de módulos con horarios
                for (let i = 1; i <= 15; i++) {
                    const horario = horariosModulosCrearReserva[i];
                    const optionTextInicial = `${prefijo}.${i} (${horario})`;
                    const optionTextFinal = `${prefijo}.${i} (${horario})`;

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
            }

            // Función para mostrar/ocultar el campo de asignatura
            function toggleAsignaturaField() {
                const tipoSelect = document.getElementById('tipo-responsable');
                const asignaturaField = document.getElementById('asignatura-field');
                const buscarAsignaturaField = document.getElementById('buscar-asignatura-field');
                const actividadExternaFields = document.getElementById('actividad-externa-fields');
                const asignaturaSelect = document.getElementById('id-asignatura');
                const buscarAsignaturaInput = document.getElementById('buscar-asignatura');

                // Limpiar campos
                asignaturaSelect.innerHTML = '<option value="">Seleccione una asignatura</option><option value="otro">Otro</option>';
                buscarAsignaturaInput.value = '';

                if (tipoSelect.value === 'profesor') {
                    // Profesor regular: mostrar select de sus asignaturas
                    asignaturaField.classList.remove('hidden');
                    buscarAsignaturaField.classList.add('hidden');
                    actividadExternaFields.classList.add('hidden');
                    cargarAsignaturasProfesor();
                } else if (tipoSelect.value === 'colaborador') {
                    // Profesor colaborador: mostrar búsqueda de asignaturas
                    asignaturaField.classList.add('hidden');
                    buscarAsignaturaField.classList.remove('hidden');
                    actividadExternaFields.classList.add('hidden');
                    configurarBusquedaAsignaturas();
                } else if (tipoSelect.value === 'solicitante') {
                    // Solicitante externo: mostrar campos de actividad
                    asignaturaField.classList.add('hidden');
                    buscarAsignaturaField.classList.add('hidden');
                    actividadExternaFields.classList.remove('hidden');
                } else {
                    // Sin selección: ocultar todo
                    asignaturaField.classList.add('hidden');
                    buscarAsignaturaField.classList.add('hidden');
                    actividadExternaFields.classList.add('hidden');
                }
            }

            // Función para cargar asignaturas del profesor
            async function cargarAsignaturasProfesor() {
                const runProfesor = document.getElementById('run-responsable').value.trim();
                const asignaturaSelect = document.getElementById('id-asignatura');

                if (!runProfesor) {
                    asignaturaSelect.innerHTML = '<option value="">Primero seleccione un profesor</option><option value="otro">Otro</option>';

                    return;
                }

                try {

                    asignaturaSelect.innerHTML = '<option value="">Cargando asignaturas...</option><option value="otro">Otro</option>';

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


                    asignaturaSelect.innerHTML = '<option value="">Seleccione una asignatura</option><option value="otro">Otro</option>';

                    if (data.success && data.asignaturas && data.asignaturas.length > 0) {

                        data.asignaturas.forEach(asignatura => {
                            const option = document.createElement('option');
                            option.value = asignatura.id_asignatura;
                            option.textContent = `${asignatura.codigo_asignatura} - ${asignatura.nombre_asignatura}`;
                            asignaturaSelect.appendChild(option);
                        });
                    } else {

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

                    asignaturaSelect.innerHTML = '<option value="">Error al cargar asignaturas</option><option value="otro">Otro</option>';

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
                input.addEventListener('input', function (e) {
                    const valor = e.target.value.trim();

                    if (timeoutAsignaturas) {
                        clearTimeout(timeoutAsignaturas);
                    }

                    timeoutAsignaturas = setTimeout(() => {
                        buscarAsignaturas(valor);
                    }, 300);
                });

                // Ocultar resultados al hacer clic fuera
                document.addEventListener('click', function (e) {
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
                                const nombre = espacio.nombre_espacio || espacio.nombre || 'Sin nombre';
                                const pisoStr = espacio.piso ? ` (Piso ${espacio.piso})` : '';
                                const cap = espacio.capacidad || espacio.capacidad_maxima;
                                const capacidadStr = cap ? ` | Cap: ${cap} pers.` : '';
                                const estadoTag = espacio.estado && espacio.estado !== 'Disponible' ? ` [${espacio.estado.toUpperCase()}]` : '';

                                return `<option value="${espacio.id_espacio}">${espacio.id_espacio} - ${nombre}${pisoStr}${capacidadStr}${estadoTag}</option>`;
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

            // Función para actualizar módulos disponibles según espacio
            function actualizarModulosDisponibles() {
                cargarModulosParaSeleccion();
                verificarConflictosReserva();
                console.log('🔄 Módulos actualizados para el espacio seleccionado');
            }

            // Función para actualizar módulos finales
            function actualizarModulosFinales() {
                console.log('🔄 Actualizando módulos finales...');
                const moduloInicialSelect = document.getElementById('modulo-inicial');
                const moduloFinalSelect = document.getElementById('modulo-final');
                if (!moduloInicialSelect || !moduloFinalSelect) return;

                const moduloInicialSeleccionado = parseInt(moduloInicialSelect.value);
                if (moduloInicialSeleccionado) {
                    const moduloFinalActual = parseInt(moduloFinalSelect.value);
                    moduloFinalSelect.innerHTML = '<option value="">Seleccionar módulo final</option>';

                    const fechaSeleccionada = document.getElementById('fecha-reserva')?.value;
                    const prefijo = obtenerPrefijoDia(fechaSeleccionada);

                    for (let i = moduloInicialSeleccionado; i <= 15; i++) {
                        const horario = horariosModulosCrearReserva[i] || '';
                        const optionText = `${prefijo}.${i} (${horario})`;

                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = optionText;
                        moduloFinalSelect.appendChild(option);
                    }

                    if (moduloFinalActual && moduloFinalActual >= moduloInicialSeleccionado) {
                        moduloFinalSelect.value = moduloFinalActual;
                    } else {
                        moduloFinalSelect.value = moduloInicialSeleccionado;
                    }
                } else {
                    cargarModulosParaSeleccion();
                }

                verificarConflictosReserva();
            }

            // =====================================================
            // VERIFICACIÓN DE CONFLICTOS DE PLANIFICACIÓN
            // =====================================================
            let conflictoTimeout = null;
            let tieneConflictos = false;

            async function verificarConflictosReserva() {
                const espacio = document.getElementById('espacio-reserva')?.value;
                const fecha = document.getElementById('fecha-reserva')?.value;
                const moduloInicial = document.getElementById('modulo-inicial')?.value;
                const moduloFinal = document.getElementById('modulo-final')?.value;

                // Limpiar estado previo
                const banner = document.getElementById('conflicto-banner');
                if (!banner) return;

                // Solo verificar si todos los campos están completos
                if (!espacio || !fecha || !moduloInicial || !moduloFinal) {
                    banner.classList.add('hidden');
                    tieneConflictos = false;
                    resetearForzar();
                    return;
                }

                // Debounce para no hacer muchas llamadas
                if (conflictoTimeout) clearTimeout(conflictoTimeout);
                conflictoTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch('/quick-actions/api/verificar-conflictos', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                espacio: espacio,
                                fecha: fecha,
                                modulo_inicial: parseInt(moduloInicial),
                                modulo_final: parseInt(moduloFinal)
                            })
                        });

                        const data = await response.json();

                        if (data.success && data.tiene_conflictos) {
                            tieneConflictos = true;
                            mostrarConflictos(data.conflictos);
                        } else {
                            tieneConflictos = false;
                            banner.classList.add('hidden');
                            resetearForzar();
                        }
                    } catch (error) {
                        console.error('Error al verificar conflictos:', error);
                        tieneConflictos = false;
                        banner.classList.add('hidden');
                    }
                }, 400);
            }

            function mostrarConflictos(conflictos) {
                const banner = document.getElementById('conflicto-banner');
                const detalles = document.getElementById('conflicto-detalles');
                if (!banner || !detalles) return;

                let html = '';

                // Agrupar planificaciones
                const planificaciones = conflictos.filter(c => c.tipo === 'planificacion');
                const reservas = conflictos.filter(c => c.tipo === 'reserva');

                if (planificaciones.length > 0) {
                    // Agrupar por asignatura+profesor para mostrar rango de módulos
                    const grupos = {};
                    planificaciones.forEach(p => {
                        const key = p.codigo + '|' + p.profesor;
                        if (!grupos[key]) {
                            grupos[key] = { ...p, modulos: [p.modulo] };
                        } else {
                            grupos[key].modulos.push(p.modulo);
                        }
                    });

                    html += '<div class="font-medium mb-1">📚 Clases programadas en este horario:</div>';
                    Object.values(grupos).forEach(g => {
                        const modulosStr = g.modulos.length > 1
                            ? `Módulos ${Math.min(...g.modulos)}-${Math.max(...g.modulos)}`
                            : `Módulo ${g.modulos[0]}`;
                        html += `<div class="ml-4 text-xs">• <strong>${g.codigo}</strong> - ${g.asignatura} (${modulosStr})<br>&nbsp;&nbsp;Prof: ${g.profesor} | ${g.carrera}</div>`;
                    });
                }

                if (reservas.length > 0) {
                    html += '<div class="font-medium mb-1 mt-2">📋 Reservas existentes:</div>';
                    reservas.forEach(r => {
                        html += `<div class="ml-4 text-xs">• <strong>${r.estado.toUpperCase()}</strong> — ${r.asignatura} (Módulos ${r.modulo_inicio}-${r.modulo_fin})<br>&nbsp;&nbsp;Responsable: ${r.responsable}</div>`;
                    });
                }

                detalles.innerHTML = html;
                banner.classList.remove('hidden');
                resetearForzar();
            }

            // =====================================================
            // BOTÓN FORZAR — MANTENER PRESIONADO 3 SEGUNDOS
            // =====================================================
            let forzarTimer = null;
            let forzarStartTime = null;
            let forzarAnimFrame = null;
            const FORZAR_DURACION_MS = 3000;

            function resetearForzar() {
                const hidden = document.getElementById('forzar-reserva');
                const texto = document.getElementById('forzar-texto');
                const estado = document.getElementById('forzar-estado');
                const progress = document.getElementById('forzar-progress');
                const btn = document.getElementById('btn-forzar');
                const icon = document.getElementById('forzar-icon');

                if (hidden) hidden.value = '0';
                if (texto) texto.textContent = 'Mantener presionado 3s para forzar';
                if (estado) estado.classList.add('hidden');
                if (progress) progress.style.width = '0%';
                if (btn) {
                    btn.classList.remove('bg-green-100', 'border-green-500', 'text-green-700');
                    btn.classList.add('bg-white', 'border-red-300', 'text-red-700');
                }
                if (icon) {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>';
                }

                if (forzarTimer) { clearTimeout(forzarTimer); forzarTimer = null; }
                if (forzarAnimFrame) { cancelAnimationFrame(forzarAnimFrame); forzarAnimFrame = null; }
                forzarStartTime = null;
            }

            function activarForzar() {
                const hidden = document.getElementById('forzar-reserva');
                const texto = document.getElementById('forzar-texto');
                const estado = document.getElementById('forzar-estado');
                const progress = document.getElementById('forzar-progress');
                const btn = document.getElementById('btn-forzar');
                const icon = document.getElementById('forzar-icon');

                if (hidden) hidden.value = '1';
                if (texto) texto.textContent = '✓ Reserva forzada';
                if (estado) estado.classList.remove('hidden');
                if (progress) progress.style.width = '100%';
                if (btn) {
                    btn.classList.remove('bg-white', 'border-red-300', 'text-red-700');
                    btn.classList.add('bg-green-100', 'border-green-500', 'text-green-700');
                }
                if (icon) {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>';
                }
            }

            function animarProgreso() {
                if (!forzarStartTime) return;
                const elapsed = Date.now() - forzarStartTime;
                const pct = Math.min((elapsed / FORZAR_DURACION_MS) * 100, 100);
                const progress = document.getElementById('forzar-progress');
                const texto = document.getElementById('forzar-texto');

                if (progress) progress.style.width = pct + '%';
                if (texto) {
                    const remaining = Math.max(0, Math.ceil((FORZAR_DURACION_MS - elapsed) / 1000));
                    texto.textContent = remaining > 0 ? `Manteniendo... ${remaining}s` : 'Soltando...';
                }

                if (pct < 100) {
                    forzarAnimFrame = requestAnimationFrame(animarProgreso);
                }
            }

            function configurarForzarCheckbox() {
                const btn = document.getElementById('btn-forzar');
                if (!btn) return;

                // Mouse events
                btn.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    if (document.getElementById('forzar-reserva')?.value === '1') {
                        // Ya está forzado, desactivar
                        resetearForzar();
                        return;
                    }
                    forzarStartTime = Date.now();
                    forzarAnimFrame = requestAnimationFrame(animarProgreso);
                    forzarTimer = setTimeout(() => {
                        activarForzar();
                        forzarTimer = null;
                    }, FORZAR_DURACION_MS);
                });

                btn.addEventListener('mouseup', () => {
                    if (forzarTimer) {
                        // Soltó antes de 3 segundos
                        clearTimeout(forzarTimer);
                        forzarTimer = null;
                        if (forzarAnimFrame) { cancelAnimationFrame(forzarAnimFrame); forzarAnimFrame = null; }
                        forzarStartTime = null;
                        const progress = document.getElementById('forzar-progress');
                        const texto = document.getElementById('forzar-texto');
                        if (progress) progress.style.width = '0%';
                        if (texto && document.getElementById('forzar-reserva')?.value !== '1') {
                            texto.textContent = 'Mantener presionado 3s para forzar';
                        }
                    }
                });

                btn.addEventListener('mouseleave', () => {
                    if (forzarTimer) {
                        clearTimeout(forzarTimer);
                        forzarTimer = null;
                        if (forzarAnimFrame) { cancelAnimationFrame(forzarAnimFrame); forzarAnimFrame = null; }
                        forzarStartTime = null;
                        const progress = document.getElementById('forzar-progress');
                        const texto = document.getElementById('forzar-texto');
                        if (progress) progress.style.width = '0%';
                        if (texto && document.getElementById('forzar-reserva')?.value !== '1') {
                            texto.textContent = 'Mantener presionado 3s para forzar';
                        }
                    }
                });

                // Touch events (mobile)
                btn.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    if (document.getElementById('forzar-reserva')?.value === '1') {
                        resetearForzar();
                        return;
                    }
                    forzarStartTime = Date.now();
                    forzarAnimFrame = requestAnimationFrame(animarProgreso);
                    forzarTimer = setTimeout(() => {
                        activarForzar();
                        // Vibración haptica si está disponible
                        if (navigator.vibrate) navigator.vibrate(200);
                        forzarTimer = null;
                    }, FORZAR_DURACION_MS);
                });

                btn.addEventListener('touchend', () => {
                    if (forzarTimer) {
                        clearTimeout(forzarTimer);
                        forzarTimer = null;
                        if (forzarAnimFrame) { cancelAnimationFrame(forzarAnimFrame); forzarAnimFrame = null; }
                        forzarStartTime = null;
                        const progress = document.getElementById('forzar-progress');
                        const texto = document.getElementById('forzar-texto');
                        if (progress) progress.style.width = '0%';
                        if (texto && document.getElementById('forzar-reserva')?.value !== '1') {
                            texto.textContent = 'Mantener presionado 3s para forzar';
                        }
                    }
                });
            }

            // Inicializar al cargar la página
            document.addEventListener('DOMContentLoaded', function () {
                console.log('🚀 Iniciando carga de datos...');

                // Verificar que SweetAlert esté disponible
                if (typeof Swal !== 'undefined') {
                    console.log('✅ SweetAlert2 cargado correctamente');
                } else {
                    console.error('❌ SweetAlert2 no está disponible');
                }

                cargarEspaciosDisponibles();
                cargarModulosParaSeleccion();
                configurarAutocompletado();
                configurarForzarCheckbox();

                // Verificar conflictos cuando cambien los inputs relevantes
                ['fecha-reserva', 'espacio-reserva', 'modulo-inicial', 'modulo-final'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.addEventListener('change', () => verificarConflictosReserva());
                    }
                });

                // Keep-Alive de sesión y refresco de CSRF cada 5 minutos
                async function ejecutarKeepAlive() {
                    try {
                        const res = await fetch('/keep-alive?t=' + Date.now());
                        if (res.ok) {
                            const data = await res.json();
                            if (data.csrf_token) {
                                const metaCsrf = document.querySelector('meta[name="csrf-token"]');
                                if (metaCsrf) metaCsrf.setAttribute('content', data.csrf_token);
                            }
                        }
                    } catch (e) {
                        console.warn('Keep-alive falló:', e);
                    }
                }
                setInterval(ejecutarKeepAlive, 5 * 60 * 1000);
                setTimeout(ejecutarKeepAlive, 2000);

                // Refresco automático de espacios disponibles cada 30 segundos si el formulario está en reposo
                setInterval(() => {
                    const responsable = document.getElementById('nombre-responsable')?.value;
                    if (!responsable) {
                        cargarEspaciosDisponibles();
                    }
                }, 30000);

                console.log('🔍 Autocompletado y verificación de conflictos configurados');
            });

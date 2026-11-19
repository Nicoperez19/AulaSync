import '../../shared/page-config';
    let reservasGlobales = [];
    let salasEstudioGlobales = [];

    // MOTOR DE ESCANEO PLANO DIGITAL
    let bufferQR = '';
    let lastKeyTime = Date.now();
    let lastBufferLength = 0;
    let processingTimeout = null;
    let inputSalaTimeout = null;
    let devolucionTimer = null;
    let reservaTimer = null;

    let lastScannedBuffer = null;
    let lastScannedTime = 0;

    let usuarioEscaneado = null;

    function extraerRUNFromBuffer(str) {
        if (!str) return null;
        str = str.trim();

        const runUrlMatch = str.match(/[?&]run=([^&/]+)/i);
        if (runUrlMatch) return runUrlMatch[1].replace(/[^0-9kK]/g, '');

        const runMatch = str.match(/RUN[^0-9]*([0-9.Kk-]+)/i);
        if (runMatch) return runMatch[1].replace(/[^0-9kK]/g, '');

        const runMatchAlt = str.match(/([0-9]{1,2}(?:\.[0-9]{3}){2}-?[0-9Kk]|[0-9]{7,9}-?[0-9Kk]?)/i);
        if (runMatchAlt) return runMatchAlt[1].replace(/[^0-9kK]/g, '');

        const limpio = str.replace(/[^0-9kK]/g, '');
        return limpio.length >= 6 ? limpio : str;
    }

    function extraerCodigoEspacio(str) {
        if (!str) return null;
        
        let limpio = str.trim().toUpperCase().replace(/['"`?_\s]/g, '-').replace(/-+/g, '-');
        const limpioSoloAlfa = limpio.replace(/[^A-Z0-9]/g, '');

        let encontrado = salasEstudioGlobales.find(s => {
            const idNorm = (s.id_espacio || '').toUpperCase();
            const idSoloAlfa = idNorm.replace(/[^A-Z0-9]/g, '');
            const nomNorm = (s.nombre_espacio || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            return idNorm === limpio || idSoloAlfa === limpioSoloAlfa || nomNorm === limpioSoloAlfa;
        });

        if (encontrado) return encontrado.id_espacio;

        const match = limpio.match(/([A-Z]{1,4})[-']?([A-Z0-9]{1,4})/i);
        if (match) {
            const posibleId = (match[1] + '-' + match[2]).toUpperCase();
            const encontradoMatch = salasEstudioGlobales.find(s => s.id_espacio.toUpperCase() === posibleId);
            if (encontradoMatch) return encontradoMatch.id_espacio;
            return posibleId;
        }

        return limpio;
    }

    document.addEventListener('DOMContentLoaded', function () {
        cargarEspaciosEstudio();
        cargarReservas();
        mantenerEnfoqueGlobal();

        // GESTOR DE ESCÁNER PLANO DIGITAL
        window.addEventListener('keydown', handleKeyScan);

        setInterval(mantenerEnfoqueGlobal, 2000);

        setInterval(verificarNotificacionesServidor, 30000);
        verificarNotificacionesServidor();
    });

    function handleKeyScan(event) { if (typeof Swal !== 'undefined' && Swal.isVisible()) return;
        const activeElem = document.activeElement;
        const isInputForm = activeElem && (activeElem.tagName === 'INPUT' || activeElem.tagName === 'TEXTAREA' || activeElem.tagName === 'SELECT');
        const inputGlobal = document.getElementById('qr-input-global');
        const inputSalaModal = document.getElementById('input-qr-sala');

        if (isInputForm && activeElem !== inputGlobal && activeElem !== inputSalaModal) {
            return;
        }

        const now = Date.now();
        if (now - lastKeyTime > 300) {
            bufferQR = '';
        }
        lastKeyTime = now;

        const isEnter = (event.key === 'Enter' || event.keyCode === 13 || event.which === 13);

        if (!isEnter) {
            if (event.key && event.key.length === 1) {
                bufferQR += event.key;

                if (bufferQR.length > lastBufferLength) {
                    lastBufferLength = bufferQR.length;

                    if (processingTimeout) clearTimeout(processingTimeout);

                    processingTimeout = setTimeout(() => {
                        if (bufferQR && bufferQR.length >= 2) {
                            const raw = bufferQR;
                            bufferQR = '';
                            procesarLecturaGeneral(raw);
                        }
                    }, 120);
                }
            }
            return;
        }

        if (processingTimeout) clearTimeout(processingTimeout);
        if (bufferQR.length >= 2) {
            event.preventDefault();
            const raw = bufferQR;
            bufferQR = '';
            procesarLecturaGeneral(raw);
        }
    }

    function mantenerEnfoqueGlobal() { if (typeof Swal !== 'undefined' && Swal.isVisible()) return;
        const inputGlobal = document.getElementById('qr-input-global');
        const activeElem = document.activeElement;
        const modalSala = document.getElementById('modal-seleccionar-sala');
        
        if (modalSala && !modalSala.classList.contains('hidden')) {
            const inputSala = document.getElementById('input-qr-sala');
            if (inputSala && activeElem !== inputSala) {
                inputSala.focus();
            }
            return;
        }

        if (inputGlobal && (!activeElem || activeElem === document.body)) {
            inputGlobal.focus();
        }
    }

    function onInputSalaChange() {
        if (inputSalaTimeout) clearTimeout(inputSalaTimeout);
        inputSalaTimeout = setTimeout(() => {
            procesarEscaneoCodSala();
        }, 120);
    }

    let reservasFiltradas = [];
    let paginaActual = 1;
    const ITEMS_PER_PAGE = 20;

    async function cargarEspaciosEstudio() {
        try {
            const res = await fetch((window.SalasEstudioConfig?.apiEspaciosUrl ?? '/quick-actions/api/espacios'));
            const data = await res.json();
            if (data.success && data.espacios) {
                const selectFiltro = document.getElementById('filtro-espacio');
                const selectManual = document.getElementById('manual-select-espacio');
                
                selectFiltro.innerHTML = '<option value="todos">Todos los espacios</option>';
                selectManual.innerHTML = '';

                // Filtrar exclusivamente salas de estudio
                const salas = data.espacios.filter(e => 
                    (e.tipo_espacio && e.tipo_espacio.toLowerCase().includes('estudio')) ||
                    (e.nombre_espacio && e.nombre_espacio.toLowerCase().includes('estudio'))
                );

                salasEstudioGlobales = salas;

                salasEstudioGlobales.forEach(sala => {
                    const optFiltro = document.createElement('option');
                    optFiltro.value = sala.id_espacio;
                    optFiltro.textContent = `${sala.id_espacio} - ${sala.nombre_espacio || 'Sala de Estudio'}`;
                    selectFiltro.appendChild(optFiltro);

                    const optMan = document.createElement('option');
                    optMan.value = sala.id_espacio;
                    optMan.textContent = `${sala.id_espacio} - ${sala.nombre_espacio || 'Sala de Estudio'} (Cap: ${sala.capacidad_alumnos || sala.capacidad_maxima || 'N/A'})`;
                    selectManual.appendChild(optMan);
                });
            }
        } catch (e) {
            console.error('Error al cargar espacios:', e);
        }
    }

    async function cargarReservas() {
        const estado = document.getElementById('filtro-estado').value;
        const espacio = document.getElementById('filtro-espacio').value;
        const fecha = document.getElementById('filtro-fecha').value;
        const orden = document.getElementById('filtro-orden').value;

        const params = new URLSearchParams({ estado, espacio, fecha, orden });
        const tbody = document.getElementById('tabla-reservas-body');
        const cardsContainer = document.getElementById('tabla-reservas-cards');
        
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-3 sm:px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                        <p>Cargando reservas de salas de estudio...</p>
                    </div>
                </td>
            </tr>`;

        if (cardsContainer) {
            cardsContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                    <p>Cargando reservas...</p>
                </div>`;
        }

        try {
            const res = await fetch(`${window.SalasEstudioConfig?.apiReservasUrl ?? '/quick-actions/api/reservas-salas-estudio'}?${params}`);
            const data = await res.json();

            if (data.success) {
                reservasGlobales = data.reservas || [];
                aplicarFiltrosLocales();
            } else {
                tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error al cargar datos</td></tr>`;
            }
        } catch (e) {
            console.error('Error al cargar reservas:', e);
            tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error de conexión</td></tr>`;
        }
    }

    function limpiarFiltrosSalasEstudio() {
        const s = document.getElementById('filtro-solicitante'); if (s) s.value = '';
        const e = document.getElementById('filtro-estado'); if (e) e.value = 'todos';
        const esp = document.getElementById('filtro-espacio'); if (esp) esp.value = 'todos';
        const m = document.getElementById('filtro-modulo'); if (m) m.value = 'todos';
        const f = document.getElementById('filtro-fecha'); if (f) f.value = '';
        const o = document.getElementById('filtro-orden'); if (o) o.value = 'desc';
        cargarReservas();
    }
    window.limpiarFiltrosSalasEstudio = limpiarFiltrosSalasEstudio;

    function aplicarFiltrosLocales() {
        const solicitanteFiltro = (document.getElementById('filtro-solicitante')?.value || '').toLowerCase().trim();

        if (solicitanteFiltro) {
            reservasFiltradas = reservasGlobales.filter(r => {
                const nombre = String(r.nombre_responsable || '').toLowerCase();
                const run = String(r.run_responsable || r.run || '').toLowerCase();
                const id = String(r.id_reserva || r.id || '').toLowerCase();
                return nombre.includes(solicitanteFiltro) || run.includes(solicitanteFiltro) || id.includes(solicitanteFiltro);
            });
        } else {
            reservasFiltradas = [...reservasGlobales];
        }

        paginaActual = 1;
        renderizarPaginaActual();
    }

    function renderizarPaginaActual() {
        const tbody = document.getElementById('tabla-reservas-body');
        const cardsContainer = document.getElementById('tabla-reservas-cards');
        const paginacionContainer = document.getElementById('paginacion-salas-estudio');

        const total = reservasFiltradas.length;

        if (total === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 font-medium">
                        No hay reservas de salas de estudio que coincidan con los filtros
                    </td>
                </tr>`;
            if (cardsContainer) {
                cardsContainer.innerHTML = `
                    <div class="p-6 text-center text-gray-500 bg-white rounded-lg border border-gray-200">
                        No hay reservas de salas de estudio registradas
                    </div>`;
            }
            if (paginacionContainer) paginacionContainer.classList.add('hidden');
            return;
        }

        if (paginacionContainer) paginacionContainer.classList.remove('hidden');

        const totalPaginas = Math.ceil(total / ITEMS_PER_PAGE) || 1;
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;
        if (paginaActual < 1) paginaActual = 1;

        const inicio = (paginaActual - 1) * ITEMS_PER_PAGE;
        const fin = Math.min(inicio + ITEMS_PER_PAGE, total);
        const reservasPagina = reservasFiltradas.slice(inicio, fin);

        // Actualizar contadores
        const elDesde = document.getElementById('paginacion-desde');
        const elHasta = document.getElementById('paginacion-hasta');
        const elTotal = document.getElementById('paginacion-total');
        if (elDesde) elDesde.textContent = (inicio + 1).toLocaleString();
        if (elHasta) elHasta.textContent = fin.toLocaleString();
        if (elTotal) elTotal.textContent = total.toLocaleString();

        // Botones anterior / siguiente
        const btnAnt = document.getElementById('btn-pagina-anterior');
        const btnSig = document.getElementById('btn-pagina-siguiente');
        if (btnAnt) btnAnt.disabled = (paginaActual <= 1);
        if (btnSig) btnSig.disabled = (paginaActual >= totalPaginas);

        // Botones de números de página
        const numerosCont = document.getElementById('paginacion-numeros');
        if (numerosCont) {
            numerosCont.innerHTML = '';
            const maxVisible = 5;
            let startPage = Math.max(1, paginaActual - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPaginas, startPage + maxVisible - 1);
            if (endPage - startPage + 1 < maxVisible) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.onclick = () => cambiarPagina(i);
                btn.className = `px-3 py-1.5 border text-xs sm:text-sm font-semibold rounded-lg transition ${
                    i === paginaActual
                        ? 'bg-blue-600 border-blue-600 text-white shadow-sm'
                        : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'
                }`;
                numerosCont.appendChild(btn);
            }
        }

        // Renderizar tabla desktop
        tbody.innerHTML = reservasPagina.map(r => {
            let badgeEstado = '';
            if (r.estado === 'activa') {
                if (r.vencida) {
                    badgeEstado = `<span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200 animate-pulse"><i class="fas fa-exclamation-triangle"></i> Vencida (2h+)</span>`;
                } else if (r.proxima_vencer) {
                    badgeEstado = `<span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-clock"></i> Por Vencer (&lt;15m)</span>`;
                } else {
                    badgeEstado = `<span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fas fa-check-circle"></i> Activa</span>`;
                }
            } else if (r.estado === 'finalizada') {
                badgeEstado = `<span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200">Finalizada</span>`;
            } else {
                badgeEstado = `<span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full bg-red-50 text-red-600 border border-red-200">Cancelada</span>`;
            }

            const minutos = r.minutos_transcurridos || 0;
            const horas = Math.floor(minutos / 60);
            const minsRest = minutos % 60;
            const tiempoTexto = r.estado === 'activa' ? `${horas}h ${minsRest}m / 2h00m` : (r.hora_salida ? `Salida: ${r.hora_salida}` : 'Finalizada');
            const subtituloEspacio = (r.nombre_espacio && r.nombre_espacio !== r.id_espacio) ? `<div class="text-xs text-gray-500 truncate">${r.nombre_espacio}</div>` : '';

            return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-3 py-3.5 text-center">
                        <input type="checkbox" class="rounded border-gray-300 text-[#D12420] focus:ring-[#950101]">
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">${badgeEstado}</td>
                    <td class="px-4 py-3.5 text-center">
                        <div class="text-sm font-bold text-gray-900">${r.id_espacio}</div>
                        ${subtituloEspacio}
                    </td>
                    <td class="px-6 py-3.5">
                        <div class="text-sm font-semibold text-gray-900 truncate">${r.nombre_responsable}</div>
                        <div class="text-xs text-gray-500 font-mono">RUN: ${r.run_responsable}</div>
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${r.fecha_reserva}</div>
                        <div class="text-xs text-gray-500 font-mono">${r.hora_inicio ? 'Ingreso: ' + r.hora_inicio.substring(0, 5) : ''}</div>
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold ${r.vencida ? 'bg-red-50 text-red-700 border border-red-200 font-bold' : (r.proxima_vencer ? 'bg-amber-50 text-amber-800 border border-amber-200 font-bold' : 'text-gray-800')}">
                            ${tiempoTexto}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <div class="flex justify-center items-center gap-1.5">
                            ${r.estado === 'activa' ? `
                                <button onclick="devolverSalaDirecta('${r.id_espacio}', '${r.run_responsable}')" 
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-150 cursor-pointer"
                                        title="Registrar devolución y liberar sala">
                                    <i class="fas fa-undo"></i>
                                    <span>Devolver</span>
                                </button>
                            ` : ''}
                            <a href="/reservas/${r.id_reserva}/comprobante" 
                               target="_blank"
                               class="inline-flex items-center justify-center w-8 h-8 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 hover:text-[#D12420] text-xs font-semibold rounded-lg shadow-sm transition-all duration-150"
                               title="Descargar Comprobante PDF">
                                <i class="fa-solid fa-file-pdf text-red-600"></i>
                            </a>
                            <button onclick="cambiarEstadoReserva('${r.id_reserva}', 'cancelada')" 
                                    class="inline-flex items-center justify-center w-8 h-8 border border-gray-300 bg-white hover:bg-red-50 text-gray-500 hover:text-red-600 hover:border-red-300 text-xs font-semibold rounded-lg shadow-sm transition-all duration-150 cursor-pointer"
                                    title="Cancelar reserva">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        }).join('');

        // Renderizar tarjetas mobile
        if (cardsContainer) {
            cardsContainer.innerHTML = reservasPagina.map(r => `
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-900 text-base">${r.id_espacio}</span>
                        ${r.estado === 'activa' ? '<span class="px-2.5 py-0.5 text-xs font-bold bg-green-100 text-green-800 rounded-full">Activa</span>' : '<span class="px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">Finalizada</span>'}
                    </div>
                    <div class="text-sm font-semibold text-gray-800">${r.nombre_responsable}</div>
                    <div class="text-xs text-gray-500">RUN: ${r.run_responsable} • ${r.fecha_reserva}</div>
                    <div class="pt-2 flex gap-2 justify-end">
                        ${r.estado === 'activa' ? `
                            <button onclick="devolverSalaDirecta('${r.id_espacio}', '${r.run_responsable}')" class="flex-1 py-2 border border-transparent bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg shadow-xs transition-all">
                                Devolver Sala
                            </button>
                        ` : ''}
                        <a href="/reservas/${r.id_reserva}/comprobante" target="_blank" class="px-3 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg flex items-center justify-center shadow-xs transition-all">
                            <i class="fa-solid fa-file-pdf mr-1.5 text-red-600"></i> PDF
                        </a>
                    </div>
                </div>
            `).join('');
        }
    }

    function cambiarPagina(nuevaPagina) {
        paginaActual = nuevaPagina;
        renderizarPaginaActual();
    }

    // PROCESAMIENTO PRINCIPAL DE LECTURA DE PLANO DIGITAL
    async function procesarLecturaGeneral(rawText) {
        if (!rawText) return;

        if (usuarioEscaneado && usuarioEscaneado.run) {
            const codEspacio = extraerCodigoEspacio(rawText);
            if (codEspacio) {
                asignarSalaAEstudiante(codEspacio);
                return;
            }
        }

        const now = Date.now();
        if (lastScannedBuffer === rawText.trim() && (now - lastScannedTime) < 2500) {
            return;
        }
        lastScannedBuffer = rawText.trim();
        lastScannedTime = now;

        const modalProcesando = document.getElementById('modal-procesando');
        modalProcesando.classList.remove('hidden');

        const runLimpio = extraerRUNFromBuffer(rawText);

        try {
            const res = await fetch((window.SalasEstudioConfig?.apiProcesarEscaneoUrl ?? '/quick-actions/api/sala-estudio/procesar-escaneo'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.SalasEstudioConfig?.csrfToken ?? '')
                },
                body: JSON.stringify({ paso: 'validar_usuario', run: runLimpio })
            });

            const data = await res.json();
            modalProcesando.classList.add('hidden');

            if (res.status === 403 && data.vetado) {
                await Swal.fire({ title: 'Acceso Restringido', text: data.message || 'El usuario posee una sanción activa.', icon: 'warning', confirmButtonText: 'Entendido' }); mantenerEnfoqueGlobal();
                return;
            }

            // CASO 1: DEVOLUCIÓN AUTOMÁTICA
            if (data.success && data.devolucion) {
                mostrarModalDevolucion(data, data.id_espacio);
                return;
            }

            // CASO 2: USUARIO NO REGISTRADO -> REQUIERE REGISTRO DE PERFIL (Plano Digital Style)
            if (data.requiere_registro) {
                document.getElementById('reg-run').value = data.run;
                document.getElementById('reg-nombre').value = '';
                document.getElementById('reg-correo').value = '';
                document.getElementById('reg-telefono').value = '';
                document.getElementById('reg-tipo').value = 'estudiante';
                document.getElementById('modal-registro-solicitante').classList.remove('hidden');
                document.getElementById('reg-nombre').focus();
                return;
            }

            // CASO 3: USUARIO VÁLIDO SIN RESERVA ACTIVA -> MOSTRAR ESCANEO DE SALA (PASO 2)
            usuarioEscaneado = {
                run: runLimpio,
                nombre: data.nombre_estudiante || 'Usuario'
            };

            document.getElementById('sel-nombre-estudiante').textContent = usuarioEscaneado.nombre;
            document.getElementById('modal-seleccionar-sala').classList.remove('hidden');
            const inputSala = document.getElementById('input-qr-sala');
            if (inputSala) {
                inputSala.value = '';
                setTimeout(() => inputSala.focus(), 150);
            }

        } catch (e) {
            console.error('Error al procesar lectura:', e);
            modalProcesando.classList.add('hidden');
            await Swal.fire({ title: 'Error de Lectura', text: 'Error al verificar la lectura del código escaneado.', icon: 'error', confirmButtonText: 'Entendido' }); mantenerEnfoqueGlobal();
        }
    }

    async function asignarSalaAEstudiante(idEspacio) {
        const userToBook = usuarioEscaneado;
        if (!userToBook || !userToBook.run) return;

        usuarioEscaneado = null;

        const modalProcesando = document.getElementById('modal-procesando');
        modalProcesando.classList.remove('hidden');
        cerrarModalSeleccionSala();

        try {
            const res = await fetch((window.SalasEstudioConfig?.apiProcesarEscaneoUrl ?? '/quick-actions/api/sala-estudio/procesar-escaneo'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.SalasEstudioConfig?.csrfToken ?? '')
                },
                body: JSON.stringify({ paso: 'asignar_sala', id_espacio: idEspacio, run: userToBook.run })
            });

            const data = await res.json();
            modalProcesando.classList.add('hidden');

            if (data.success) {
                document.getElementById('res-estudiante').textContent = data.nombre_estudiante || userToBook.nombre || 'Usuario';
                document.getElementById('res-sala').textContent = idEspacio;
                document.getElementById('modal-reserva-exitosa').classList.remove('hidden');
                
                if (reservaTimer) clearTimeout(reservaTimer);
                reservaTimer = setTimeout(cerrarModalReservaExitosa, 2500);

                cargarReservas();
            } else {
                await Swal.fire({ title: 'No se pudo asignar', text: data.message || 'Error al asignar la sala.', icon: 'error', confirmButtonText: 'Entendido' }); mantenerEnfoqueGlobal();
            }
        } catch (e) {
            console.error('Error:', e);
            modalProcesando.classList.add('hidden');
            await Swal.fire({ title: 'Error de Conexión', text: 'Error de comunicación con el servidor.', icon: 'error', confirmButtonText: 'Entendido' }); mantenerEnfoqueGlobal();
        }
    }

    function procesarEscaneoCodSala() {
        const input = document.getElementById('input-qr-sala');
        const val = input ? input.value.trim() : '';
        if (val) {
            const codEspacio = extraerCodigoEspacio(val);
            if (codEspacio) {
                if (input) input.value = '';
                asignarSalaAEstudiante(codEspacio);
            }
        }
    }

    function mostrarModalDevolucion(data, idEspacioFallback) {
        document.getElementById('dev-estudiante').textContent = data.nombre_estudiante || 'Usuario';
        document.getElementById('dev-sala').textContent = data.id_espacio || idEspacioFallback || 'Sala';
        document.getElementById('dev-hora').textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        const iconContainer = document.getElementById('dev-icon-container');
        const icon = document.getElementById('dev-icon');
        const titulo = document.getElementById('dev-titulo');
        const subtitulo = document.getElementById('dev-subtitulo');
        const alerta = document.getElementById('dev-alerta-sancion');
        const duracion = document.getElementById('dev-duracion');

        if (duracion) {
            duracion.textContent = (data.minutos_transcurridos || 0) + ' min';
        }

        if (data.sancionado) {
            if (iconContainer) iconContainer.className = 'w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl animate-pulse';
            if (icon) icon.className = 'fas fa-exclamation-triangle';
            if (titulo) {
                titulo.textContent = '⚠️ Devolución Fuera de Plazo';
                titulo.className = 'text-xl font-bold text-red-700 mb-1';
            }
            if (subtitulo) subtitulo.textContent = 'El usuario ha superado las 2 horas máximas permitidas.';
            if (alerta) {
                alerta.classList.remove('hidden');
                alerta.innerHTML = `<strong>🚫 SANCIÓN AUTOMÁTICA (1 SEMANA):</strong><br>El usuario estuvo <b>${data.minutos_transcurridos || 0} min</b> en la sala. Ha sido bloqueado para solicitar salas de estudio por 1 semana (hasta ${data.fecha_fin_veto || "7 días"}).`;
            }
        } else {
            if (iconContainer) iconContainer.className = 'w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl';
            if (icon) icon.className = 'fas fa-check-circle';
            if (titulo) {
                titulo.textContent = '🔑 Devolución Registrada';
                titulo.className = 'text-xl font-bold text-gray-900 mb-1';
            }
            if (subtitulo) subtitulo.textContent = 'La devolución de la sala se ha completado correctamente a tiempo.';
            if (alerta) alerta.classList.add('hidden');
        }

        document.getElementById('modal-devolucion-exitosa').classList.remove('hidden');
        
        if (devolucionTimer) clearTimeout(devolucionTimer);
        devolucionTimer = setTimeout(cerrarModalDevolucion, data.sancionado ? 7000 : 2500);

        cargarReservas();
    }

    function cerrarModalDevolucion() {
        if (devolucionTimer) clearTimeout(devolucionTimer);
        document.getElementById('modal-devolucion-exitosa').classList.add('hidden');
        mantenerEnfoqueGlobal();
    }

    function cerrarModalSeleccionSala() {
        document.getElementById('modal-seleccionar-sala').classList.add('hidden');
        usuarioEscaneado = null;
        mantenerEnfoqueGlobal();
    }

    function cerrarModalReservaExitosa() {
        if (reservaTimer) clearTimeout(reservaTimer);
        document.getElementById('modal-reserva-exitosa').classList.add('hidden');
        mantenerEnfoqueGlobal();
    }

    function cerrarModalRegistro() {
        document.getElementById('modal-registro-solicitante').classList.add('hidden');
        mantenerEnfoqueGlobal();
    }

    function abrirModalManualReserva() {
        document.getElementById('modal-entrada-manual').classList.remove('hidden');
        document.getElementById('manual-run').focus();
    }

    function cerrarModalManual() {
        document.getElementById('modal-entrada-manual').classList.add('hidden');
        mantenerEnfoqueGlobal();
    }

    async function procesarReservaManualDirecta() {
        const run = document.getElementById('manual-run').value.trim();
        const idEspacio = document.getElementById('manual-select-espacio').value;

        if (!run || !idEspacio) {
            await Swal.fire({ title: 'Datos Incompletos', text: 'Por favor ingrese el RUN y seleccione una sala de estudio.', icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }

        cerrarModalManual();
        usuarioEscaneado = { run: run, nombre: 'Manual' };
        asignarSalaAEstudiante(idEspacio);
    }

    async function guardarYContinuar(e) {
        e.preventDefault();
        const run = document.getElementById('reg-run').value;
        const nombre = document.getElementById('reg-nombre').value;
        const correo = document.getElementById('reg-correo').value;
        const telefono = document.getElementById('reg-telefono').value;
        const tipo_solicitante = document.getElementById('reg-tipo').value;

        try {
            const res = await fetch((window.SalasEstudioConfig?.apiProcesarEscaneoUrl ?? '/quick-actions/api/sala-estudio/procesar-escaneo'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.SalasEstudioConfig?.csrfToken ?? '')
                },
                body: JSON.stringify({ paso: 'validar_usuario', run, nombre, correo, telefono, tipo_solicitante })
            });

            const data = await res.json();
            cerrarModalRegistro();

            if (data.success || data.requiere_registro === false) {
                usuarioEscaneado = { run: run, nombre: nombre };
                document.getElementById('sel-nombre-estudiante').textContent = nombre;
                document.getElementById('modal-seleccionar-sala').classList.remove('hidden');
                const inputSala = document.getElementById('input-qr-sala');
                if (inputSala) {
                    inputSala.value = '';
                    setTimeout(() => inputSala.focus(), 150);
                }
            } else {
                await Swal.fire({ title: 'Error al Registrar', text: data.message || 'Error al registrar el solicitante.', icon: 'error', confirmButtonText: 'Entendido' }); mantenerEnfoqueGlobal();
            }
        } catch (err) {
            console.error('Error al guardar:', err);
            await Swal.fire({ title: 'Error de Conexión', text: 'Error de comunicación con el servidor.', icon: 'error', confirmButtonText: 'Entendido' }); mantenerEnfoqueGlobal();
        }
    }

    async function devolverSalaDirecta(idEspacio, run) {
        const confirmResult = await Swal.fire({
            title: '¿Confirmar devolución?',
            text: `¿Deseas registrar la devolución y liberar la sala ${idEspacio}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#8D8D8D',
            confirmButtonText: 'Sí, devolver sala',
            cancelButtonText: 'Cancelar'
        });
        if (!confirmResult.isConfirmed) {
            mantenerEnfoqueGlobal();
            return;
        }

        try {
            const res = await fetch((window.SalasEstudioConfig?.apiProcesarEscaneoUrl ?? '/quick-actions/api/sala-estudio/procesar-escaneo'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.SalasEstudioConfig?.csrfToken ?? '')
                },
                body: JSON.stringify({ id_espacio: idEspacio, run: run })
            });
            const data = await res.json();
            if (data.success) {
                mostrarModalDevolucion(data, idEspacio);
            } else {
                await Swal.fire({ title: 'Error', text: data.message || 'Error al devolver sala.', icon: 'error', confirmButtonText: 'Entendido' }); mantenerEnfoqueGlobal();
            }
        } catch (e) {
            console.error('Error:', e);
        }
    }

    async function cambiarEstadoReserva(idReserva, nuevoEstado) {
        const esCancelacion = nuevoEstado === 'cancelada';
        const confirmResult = await Swal.fire({
            title: esCancelacion ? '¿Cancelar Reserva?' : '¿Actualizar Estado?',
            text: esCancelacion ? '¿Estás seguro de cancelar esta reserva? El espacio quedará disponible inmediatamente.' : `¿Deseas marcar esta reserva como ${nuevoEstado}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: esCancelacion ? '#D12420' : '#10B981',
            cancelButtonColor: '#8D8D8D',
            confirmButtonText: esCancelacion ? 'Sí, cancelar reserva' : 'Sí, confirmar',
            cancelButtonText: 'No, mantener'
        });
        if (!confirmResult.isConfirmed) {
            mantenerEnfoqueGlobal();
            return;
        }

        try {
            const res = await fetch(`${window.SalasEstudioConfig?.apiReservaEstadoBaseUrl ?? '/quick-actions/api/reserva'}/${idReserva}/estado`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.SalasEstudioConfig?.csrfToken ?? '')
                },
                body: JSON.stringify({ estado: nuevoEstado })
            });
            const data = await res.json();
            if (data.success) {
                cargarReservas();
            } else {
                await Swal.fire({ title: 'Error', text: data.message || 'Error al actualizar estado.', icon: 'error', confirmButtonText: 'Entendido' }); mantenerEnfoqueGlobal();
            }
        } catch (e) {
            console.error('Error:', e);
        }
    }

    async function verificarNotificacionesServidor() {
        try {
            const res = await fetch((window.SalasEstudioConfig?.apiVerificarNotifUrl ?? '/quick-actions/api/sala-estudio/verificar-notificaciones'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.SalasEstudioConfig?.csrfToken ?? '')
                }
            });
            const data = await res.json();
            if (data.success && (data.advertencias_enviadas > 0 || data.vencidos_enviadas > 0)) {
                if (window.Livewire) {
                    Livewire.dispatch('notificacionCreada');
                }
            }
        } catch (e) {
            console.error('Error al verificar notificaciones:', e);
        }
    }

Object.defineProperty(window, 'paginaActual', {
    get: () => paginaActual,
    set: (v) => { paginaActual = v; },
});

Object.assign(window, {
    abrirModalManualReserva,
    aplicarFiltrosLocales,
    cargarReservas,
    limpiarFiltrosSalasEstudio,
    cambiarPagina,
    cerrarModalDevolucion,
    cerrarModalSeleccionSala,
    onInputSalaChange,
    cerrarModalReservaExitosa,
    cerrarModalRegistro,
    cerrarModalManual,
    procesarReservaManualDirecta,
    devolverSalaDirecta,
    cambiarEstadoReserva,
    procesarEscaneoCodSala,
    guardarYContinuar,
});

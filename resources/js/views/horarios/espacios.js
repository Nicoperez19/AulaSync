        const currentYear = (window.HorariosEspaciosConfig?.currentYear ?? new Date().getFullYear());
        let horariosPorEspacio = (window.HorariosEspaciosConfig?.horariosPorEspacio ?? {});
        let espacioActualId = null;

        const filtroForm = document.getElementById('filtro-periodo-form');
        const aplicarFiltroBtn = document.getElementById('aplicar-filtro-btn');

        function aplicarFiltros() {
            const anioFiltro = currentYear;
            const semestreFiltro = document.getElementById('semestre')?.value;

            if (!semestreFiltro) {
                Swal.fire({
                    title: 'No hay datos de horarios en los espacios',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#3B82F6'
                });
                return;
            }

            // Mostrar indicador de carga
            const loadingIndicator = document.getElementById('loadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'flex';
            }

            // Cargar horarios por AJAX
            fetch(`/horarios-por-periodo?anio=${anioFiltro}&semestre=${semestreFiltro}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    // Actualizar los horarios globales
                    horariosPorEspacio = data.horariosPorEspacio;

                    // Actualizar el contador de clases en las tarjetas
                    actualizarContadoresClases();


                    // Mostrar mensaje de éxito
                    mostrarMensajeExito(`Horarios cargados para ${semestreFiltro}er Semestre ${anioFiltro}`);
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error al cargar horarios',
                        html: `
                            <div class="text-center">
                                <p class="mb-4">Error al cargar los horarios. Por favor, inténtalo de nuevo.</p>
                                <p class="text-sm text-gray-600">Si el problema persiste, contacta al administrador del sistema.</p>
                            </div>
                        `,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#EF4444'
                    });
                })
                .finally(() => {
                    // Ocultar indicador de carga
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                });
        }

        // Función para actualizar los contadores de clases en las tarjetas
        function actualizarContadoresClases() {
            document.querySelectorAll('[data-id]').forEach(card => {
                const espacioId = card.getAttribute('data-id');
                const contadorSpan = card.querySelector('.bg-violet-100');
                if (contadorSpan) {
                    const clases = horariosPorEspacio[espacioId] || [];
                    contadorSpan.textContent = clases.length + ' clases';
                }
            });
        }

        // Función para mostrar mensaje de éxito
        function mostrarMensajeExito(mensaje) {
            // Crear un toast de éxito
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>${mensaje}</span>
                </div>
            `;
            document.body.appendChild(toast);

            // Animar entrada
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);

            // Remover después de 3 segundos
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Event listeners para los filtros
        document.addEventListener('DOMContentLoaded', function () {
            if (aplicarFiltroBtn) {
                aplicarFiltroBtn.addEventListener('click', aplicarFiltros);
            }

            // Aplicar filtros automáticamente al cargar la página si no hay horarios cargados
            if (Object.keys(horariosPorEspacio).length === 0) {
                // Establecer valor por defecto en el select de semestre
                const semestreSelect = document.getElementById('semestre');

                if (semestreSelect) {
                    semestreSelect.value = '1';

                    // Aplicar filtros automáticamente
                    setTimeout(() => {
                        aplicarFiltros();
                    }, 100);
                }
            } else {

            }
        });

        const coloresClases = [
            'bg-pink-50', 'bg-blue-50', 'bg-yellow-50', 'bg-green-50', 'bg-purple-50', 'bg-orange-50', 'bg-cyan-50', 'bg-lime-50'
        ];
        function getColorClase(nombre) {
            let hash = 0;
            for (let i = 0; i < nombre.length; i++) hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
            return coloresClases[Math.abs(hash) % coloresClases.length];
        }

        function mostrarHorarioEspacio(idEspacio, nombreEspacio) {
            espacioActualId = idEspacio;

            let tipoEspacio = '';
            let nombreCompleto = '';
            const card = document.querySelector(`[data-id='${idEspacio}']`);
            if (card) {
                const tipoSpan = card.querySelector('span.rounded-full');
                if (tipoSpan) tipoEspacio = tipoSpan.textContent;

                const nombreSpan = card.querySelector('.text-base.font-semibold');
                if (nombreSpan) nombreCompleto = nombreSpan.textContent;
            }

            let periodoEspacio = '';
            const horariosEspacio = horariosPorEspacio[idEspacio] || [];
            if (horariosEspacio.length > 0 && horariosEspacio[0].periodo) {
                const partesPeriodo = horariosEspacio[0].periodo.split('-');
                if (partesPeriodo.length === 2) {
                    const anio = partesPeriodo[0];
                    const semestre = partesPeriodo[1];
                    periodoEspacio = `${semestre}° semestre - ${anio}`;
                }
            }

            const tituloFormateado = `Horario ${nombreCompleto} (${idEspacio})`;
            document.getElementById('modalEspacioTitle').textContent = tituloFormateado;
            document.getElementById('modalEspacioTipo').textContent = tipoEspacio;

            const periodoElement = document.getElementById('modalPeriodo');
            if (periodoElement && periodoEspacio) {
                periodoElement.textContent = periodoEspacio;
            }

            document.getElementById('horarioEspacioModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            document.documentElement.classList.add('overflow-hidden');

            const horarios = horariosPorEspacio[idEspacio] || [];
            const tbody = document.getElementById('horarioEspacioBody');
            tbody.innerHTML = '';

            const Modulos = (window.HorariosEspaciosConfig?.modulos ?? {});

            const diasUnicos = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

            Modulos.forEach(modulo => {
                const tr = document.createElement('tr');
                const tdHora = document.createElement('td');
                tdHora.className = 'py-3 px-4 border-b text-center text-sm text-gray-600 leading-tight';
                tdHora.innerHTML = `<div class='flex flex-col items-center justify-center'><span class='font-semibold text-gray-800'>${formatearHora(modulo.hora_inicio)} a ${formatearHora(modulo.hora_termino)}</span></div>`;
                tr.appendChild(tdHora);

                diasUnicos.forEach(dia => {
                    const td = document.createElement('td');
                    td.className = 'py-3 px-4 border-b text-center align-middle';
                    const clases = horarios.filter(h => h.dia.toLowerCase() === dia && h.hora_inicio === modulo.hora_inicio && h.hora_termino === modulo.hora_termino);

                    if (clases.length > 0) {
                        td.innerHTML = clases.map(h => `
                            <div class='p-2 rounded-lg min-h-[90px] w-[150px] mx-auto flex flex-col items-center justify-center text-center break-words text-black font-semibold ${getColorClase(h.asignatura)} shadow-md'>
                                <div class='mb-1 text-xs tracking-wide uppercase'>${h.asignatura} (${h.codigo_asignatura || 'N/A'})</div>
                                <div class='mb-1 text-xs font-normal'><i class='mr-1 fa-solid fa-user'></i>${h.profesor ? h.profesor.name : 'Sin profesor asignado'}</div>
                            </div>
                        `).join('');
                    } else {
                        td.innerHTML = `<div class='h-full min-h-[60px] flex items-center justify-center'><span class='text-sm text-gray-400'>-</span></div>`;
                    }
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }

        function formatearHora(hora) {
            if (!hora) return '';
            const [h, m] = hora.split(':');
            return `${parseInt(h)}:${m}`;
        }

        function cerrarModalEspacio() {
            document.getElementById('horarioEspacioModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            document.documentElement.classList.remove('overflow-hidden');
            espacioActualId = null;
        }

        function exportarHorarioPDF() {
            if (!espacioActualId) {
                Swal.fire({
                    title: 'No se ha seleccionado ningún espacio',
                    html: `
                        <div class="text-center">
                            <p class="mb-4">Error: No se ha seleccionado ningún espacio</p>
                            <p class="text-sm text-gray-600">Por favor, selecciona un espacio para exportar su horario en PDF.</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#3B82F6'
                });
                return;
            }

            const anioFiltro = new Date().getFullYear();
            const semestreFiltro = document.getElementById('semestre')?.value || '';

            let url = `/espacios/${espacioActualId}/export-pdf`;
            const params = new URLSearchParams();
            params.append('anio', anioFiltro);
            if (semestreFiltro) params.append('semestre', semestreFiltro);
            if (params.toString()) {
                url += '?' + params.toString();
            }

            const exportBtn = document.getElementById('exportPdfBtn');
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<i class="mr-2 fa-solid fa-spinner fa-spin"></i>Generando PDF...';
            exportBtn.disabled = true;

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al generar el PDF');
                    }
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;

                    const fecha = new Date();
                    const mes = fecha.getMonth() + 1;
                    const dia = fecha.getDate();
                    let anio = fecha.getFullYear();

                    let semestre;
                    if (mes >= 7 && dia >= 21) {
                        semestre = 2;
                    } else if (mes >= 8) {
                        semestre = 2;
                    } else {
                        semestre = 1;
                    }

                    if (anioFiltro) anio = anioFiltro;
                    if (semestreFiltro) semestre = semestreFiltro;

                    const periodo = `${anio}_${semestre}`;

                    a.download = `${espacioActualId}_horario_${periodo}.pdf`;

                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error al generar PDF',
                        html: `
                            <div class="text-center">
                                <p class="mb-4">Error al generar el PDF: ${error.message}</p>
                                <p class="text-sm text-gray-600">Por favor, inténtalo de nuevo o contacta al administrador si el problema persiste.</p>
                            </div>
                        `,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#EF4444'
                    });
                })
                .finally(() => {
                    exportBtn.innerHTML = originalText;
                    exportBtn.disabled = false;
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const exportBtn = document.getElementById('exportPdfBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', exportarHorarioPDF);
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => {
                const loading = document.getElementById('loadingIndicator');
                if (loading) loading.style.display = 'none';
            }, 500);
        });

if (typeof cerrarModalEspacio === 'function') window.cerrarModalEspacio = cerrarModalEspacio;
if (typeof abrirModalEspacio === 'function') window.abrirModalEspacio = abrirModalEspacio;

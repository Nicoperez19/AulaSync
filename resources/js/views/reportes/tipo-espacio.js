         // Filtrar mensajes de error de extensiones de Chrome
         const originalConsoleError = console.error;
         console.error = function(...args) {
             const message = args.join(' ');
             // Filtrar errores de extensiones de Chrome
             if (message.includes('chrome-extension://') || 
                 message.includes('net::ERR_FILE_NOT_FOUND') ||
                 message.includes('Content script received message')) {
                 return; // No mostrar estos errores
             }
             originalConsoleError.apply(console, args);
         };
         
         // Filtrar mensajes de log de extensiones
         const originalConsoleLog = console.log;
         console.log = function(...args) {
             const message = args.join(' ');
             // Filtrar mensajes de extensiones de Chrome
             if (message.includes('Content script received message') ||
                 message.includes('chrome-extension://')) {
                 return; // No mostrar estos mensajes
             }
             originalConsoleLog.apply(console, args);
         };
        const _cfgTipoEspacio = window.ReportesTipoEspacioConfig ?? {};

        // Gráfico de barras para Utilización por Tipo de Espacio
        let labelsUtil = _cfgTipoEspacio.labelsGrafico ?? [];
        let dataUtil = _cfgTipoEspacio.dataGrafico ?? [];
        const resumenData = _cfgTipoEspacio.resumen ?? {};
        
        // Si no hay datos, usar datos de prueba
        if (!labelsUtil || labelsUtil.length === 0) {
            // No hay datos reales, usando datos de prueba
            labelsUtil = ['Sala de Clases', 'Laboratorio', 'Auditorio', 'Oficina', 'Sala de Reuniones'];
            dataUtil = [75, 60, 85, 45, 30];
        }
        
        // Verificar que el canvas existe
        const canvasUtilizacion = document.getElementById('chartUtilizacion');
        // Canvas de utilización
        // Labels
        // Data
        
        if (canvasUtilizacion && labelsUtil && dataUtil) {
            new Chart(canvasUtilizacion.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labelsUtil,
                    datasets: [{
                        label: 'Utilización (%)',
                        data: dataUtil,
                        backgroundColor: [
                            '#3b82f6', '#06b6d4', '#f59e42', '#a78bfa', '#f472b6', '#6b7280', '#fbbf24', '#10b981',
                            '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#14b8a6', '#f59e0b'
                        ],
                        borderColor: [
                            '#2563eb', '#0891b2', '#d97706', '#9333ea', '#db2777', '#4b5563', '#f59e0b', '#059669'
                        ],
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                title: function (context) {
                                    return 'Tipo: ' + context[0].label;
                                },
                                label: function (context) {
                                    const tipo = context.label;
                                    const porcentaje = context.parsed.y;
                                    const resumenItem = resumenData.find(item => item.nombre === tipo);
                                    
                                    let tooltipText = [
                                        `Utilización: ${porcentaje}%`,
                                        `Total espacios: ${resumenItem ? resumenItem.total_espacios : 'N/A'}`,
                                        `Total reservas: ${resumenItem ? resumenItem.total_reservas : 'N/A'}`,
                                        `Horas utilizadas: ${resumenItem ? resumenItem.horas_utilizadas : 'N/A'}h`,
                                        `Estado: ${resumenItem ? resumenItem.estado : 'N/A'}`
                                    ];
                                    
                                    return tooltipText;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: { 
                            beginAtZero: true, 
                            max: 100,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
                                callback: function (value) {
                                    return value + '%';
                                }
                            },
                            title: {
                                display: true,
                                text: 'Porcentaje de Utilización',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
            // Gráfico de utilización creado exitosamente
        } else {
            // Error: No se pudo crear el gráfico de utilización
            console.log('Error al crear gráfico de utilización:', {
                canvas: canvasUtilizacion,
                labels: labelsUtil,
                data: dataUtil
            });
        }

        // Gráfico de dona para Distribución de Reservas
        const canvasReservas = document.getElementById('chartReservas');
        if (canvasReservas && resumenData && resumenData.length > 0) {
            const labelsReservas = resumenData.map(item => item.nombre);
            const dataReservas = resumenData.map(item => item.total_reservas);
            
            new Chart(canvasReservas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labelsReservas,
                    datasets: [{
                        data: dataReservas,
                        backgroundColor: [
                            '#3b82f6', '#06b6d4', '#f59e42', '#a78bfa', '#f472b6', '#6b7280', '#fbbf24', '#10b981',
                            '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#14b8a6', '#f59e0b'
                        ],
                        borderColor: [
                            '#2563eb', '#0891b2', '#d97706', '#9333ea', '#db2777', '#4b5563', '#f59e0b', '#059669'
                        ],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    const tipo = context.label;
                                    const reservas = context.parsed;
                                    const resumenItem = resumenData.find(item => item.nombre === tipo);
                                    
                                    return [
                                        `Tipo: ${tipo}`,
                                        `Reservas: ${reservas}`,
                                        `Espacios: ${resumenItem ? resumenItem.total_espacios : 'N/A'}`,
                                        `Utilización: ${resumenItem ? resumenItem.promedio : 'N/A'}%`
                                    ];
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
            // Gráfico de reservas creado exitosamente
        } else {
            // Error: No se pudo crear el gráfico de reservas
            console.log('Error al crear gráfico de reservas:', {
                canvas: canvasReservas,
                resumenData: resumenData
            });
        }

        // Gráfico comparativo de Turnos (Diurno vs Vespertino)
        const canvasTurnos = document.getElementById('chartTurnos');
        const estadisticasTurnos = _cfgTipoEspacio.estadisticasTurnos ?? [];
        
        if (canvasTurnos && estadisticasTurnos && Object.keys(estadisticasTurnos).length > 0) {
            const tiposEspacio = Object.keys(estadisticasTurnos);
            const datosDiurno = tiposEspacio.map(tipo => estadisticasTurnos[tipo]?.diurno?.promedio || 0);
            const datosVespertino = tiposEspacio.map(tipo => estadisticasTurnos[tipo]?.vespertino?.promedio || 0);
            
            new Chart(canvasTurnos.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: tiposEspacio,
                    datasets: [
                        {
                            label: 'Turno Diurno (8:00-19:00)',
                            data: datosDiurno,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                        },
                        {
                            label: 'Turno Vespertino (19:00-23:00)',
                            data: datosVespertino,
                            backgroundColor: 'rgba(139, 92, 246, 0.7)',
                            borderColor: 'rgba(139, 92, 246, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 13,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#8b5cf6',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function (context) {
                                    const tipo = context.label;
                                    const turno = context.datasetIndex === 0 ? 'diurno' : 'vespertino';
                                    const stats = estadisticasTurnos[tipo]?.[turno];
                                    
                                    if (stats) {
                                        return [
                                            `${context.dataset.label}`,
                                            `Ocupación: ${stats.promedio}%`,
                                            `Horas utilizadas: ${stats.horas_utilizadas}h`,
                                            `Total reservas: ${stats.total_reservas}`
                                        ];
                                    }
                                    return `${context.dataset.label}: ${context.parsed.y}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                },
                                font: {
                                    size: 11
                                }
                            },
                            title: {
                                display: true,
                                text: 'Porcentaje de Ocupación (%)',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // Función para cargar histórico
        function cargarHistorico(page = 1) {
            const fechaInicio = document.getElementById('filtro-hist-fecha-inicio').value;
            const fechaFin = document.getElementById('filtro-hist-fecha-fin').value;
            const tipoEspacio = document.getElementById('filtro-hist-tipo').value;

            if (!fechaInicio || !fechaFin) {
                alert('Por favor, selecciona un rango de fechas antes de buscar.');
                return;
            }

            // Mostrar spinner
            document.getElementById('spinner-historico').classList.remove('hidden');
            document.getElementById('tbody-historico').innerHTML = '';

            const params = new URLSearchParams({
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                tipo_espacio: tipoEspacio,
                page: page
            });

            const historicoBaseUrl = _cfgTipoEspacio.historicoUrl ?? '/reportes/tipo-espacio/datos-historicos';
            fetch(`${historicoBaseUrl}?${params.toString()}`)
                .then(response => response.json())
                .then(res => {
                    document.getElementById('spinner-historico').classList.add('hidden');

                    if (!res.data || res.data.length === 0) {
                        document.getElementById('tbody-historico').innerHTML = `
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="mb-2 text-4xl fas fa-search"></i>
                                    <p class="text-lg font-medium">No se encontraron registros</p>
                                    <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                                </div>
                            </td>
                        </tr>`;
                    } else {
                        let rows = '';
                        res.data.forEach(registro => {
                            rows += `
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    <div class="font-medium">${registro.profesor_solicitante}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">${registro.run}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">${registro.email}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    <div class="font-medium">${registro.espacio}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">${registro.facultad}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${registro.fecha}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${registro.hora_inicio}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${registro.hora_termino}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${registro.duracion}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${renderTipoUsuario(registro.tipo_usuario)}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${renderEstado(registro.estado)}</td>
                            </tr>`;
                        });

                        document.getElementById('tbody-historico').innerHTML = rows;

                        // KPIs
                        document.getElementById('kpi-hist-total').innerText = res.total || 0;
                        document.getElementById('kpi-hist-completadas').innerText = res.completadas || 0;
                        document.getElementById('kpi-hist-canceladas').innerText = res.canceladas || 0;
                        document.getElementById('kpi-hist-enprogreso').innerText = res.en_progreso || 0;

                        // Paginación
                        let pag = '';
                        if (res.last_page && res.last_page > 1) {
                            for (let i = 1; i <= res.last_page; i++) {
                                pag += `<button onclick='cargarHistorico(${i})' class='px-2 py-1 rounded ${i === res.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}'>${i}</button>`;
                            }
                        }
                        document.getElementById('paginacion-historico').innerHTML = pag;
                    }
                })
                .catch(error => {
                    // Error al cargar histórico
                    document.getElementById('spinner-historico').classList.add('hidden');
                    document.getElementById('tbody-historico').innerHTML = '<tr><td colspan="8" class="px-4 py-2 text-center text-red-500">Error al cargar datos: ' + error.message + '</td></tr>';
                });
        }

        function renderEstado(estado) {
            if (estado === 'activa') {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Activa</span>`;
            } else if (estado === 'finalizada') {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-green-900 dark:text-green-200">Finalizada</span>`;
            } else if (estado === 'cancelada') {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Cancelada</span>`;
            } else if (estado === 'en progreso') {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">En progreso</span>`;
            } else {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
            }
        }

        function renderTipoUsuario(tipo) {
            if (tipo === 'Profesor') {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Profesor</span>`;
            } else if (tipo === 'Solicitante') {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Solicitante</span>`;
            } else if (tipo === 'Estudiante') {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Estudiante</span>`;
            } else {
                return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">${tipo}</span>`;
            }
        }

        function exportarHistoricoExcel() {
            const fechaInicio = document.getElementById('filtro-hist-fecha-inicio').value;
            const fechaFin = document.getElementById('filtro-hist-fecha-fin').value;
            const tipoEspacio = document.getElementById('filtro-hist-tipo').value;
            
            if (!fechaInicio || !fechaFin) {
                alert('Por favor, selecciona un rango de fechas antes de exportar.');
                return;
            }
            
            const params = new URLSearchParams({
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                tipo_espacio: tipoEspacio
            });
            
            const exportExcelBase = _cfgTipoEspacio.exportExcelUrl ?? '/reportes/tipo-espacio/export/excel';
            const url = `${exportExcelBase}?${params.toString()}`;
            window.open(url, '_blank');
        }

        function exportarHistoricoPDF() {
            const fechaInicio = document.getElementById('filtro-hist-fecha-inicio').value;
            const fechaFin = document.getElementById('filtro-hist-fecha-fin').value;
            const tipoEspacio = document.getElementById('filtro-hist-tipo').value;
            
            if (!fechaInicio || !fechaFin) {
                alert('Por favor, selecciona un rango de fechas antes de exportar.');
                return;
            }
            
            const params = new URLSearchParams({
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                tipo_espacio: tipoEspacio
            });
            
            const exportPdfBase = _cfgTipoEspacio.exportPdfUrl ?? '/reportes/tipo-espacio/export/pdf';
            const url = `${exportPdfBase}?${params.toString()}`;
            window.open(url, '_blank');
        }

        window.cargarHistorico = cargarHistorico;
        window.exportarHistoricoExcel = exportarHistoricoExcel;
        window.exportarHistoricoPDF = exportarHistoricoPDF;

        // Inicializar filtros con valores por defecto
        document.getElementById('filtro-hist-fecha-inicio').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10);
        document.getElementById('filtro-hist-fecha-fin').value = new Date().toISOString().slice(0, 10);

        // Buscar al cargar y al cambiar filtros
        document.getElementById('btn-hist-buscar').onclick = () => cargarHistorico(1);
        cargarHistorico(1);

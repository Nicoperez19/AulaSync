        const _cfgEspacios = window.ReportesEspaciosConfig ?? {};
        // Datos desde el backend
        const datosGrafico = {
            labels: _cfgEspacios.labels ?? [],
            data: _cfgEspacios.data ?? [],
            dataReservas: _cfgEspacios.dataReservas ?? [],
            resumen: _cfgEspacios.resumen ?? {}
        };

        // Variables de estado
        let diaSeleccionado = _cfgEspacios.diaSeleccionado ?? '';

        // Variables de filtros
        let filtros = {
            busqueda: _cfgEspacios.filtros?.busqueda ?? '',
            tipoEspacio: _cfgEspacios.filtros?.tipoEspacio ?? '',
            piso: _cfgEspacios.filtros?.piso ?? '',
            estado: _cfgEspacios.filtros?.estado ?? '',
            fechaInicio: _cfgEspacios.filtros?.fechaInicio ?? '',
            fechaFin: _cfgEspacios.filtros?.fechaFin ?? ''
        };

        // Variables para gráficos
        let chartUtilizacion, chartReservas;

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function () {
                    // Inicializando reportes de espacios
        // Datos disponibles
            
            inicializarGraficos();
            inicializarFiltrosDinamicos();

            // Asegurar que se muestren todos los datos inicialmente
            setTimeout(() => {
                actualizarDatosFiltrados();
                // Reportes de espacios inicializados correctamente
            }, 100);
        });

        function inicializarGraficos() {
            if (datosGrafico.resumen && datosGrafico.resumen.length > 0) {
                crearGraficoUtilizacion();
            }
        }

        function crearGraficoUtilizacion() {
            const ctx = document.getElementById('chartUtilizacion');
            if (!ctx) return;

            if (chartUtilizacion) chartUtilizacion.destroy();

            // Usar todos los espacios del resumen con sus IDs
            const labels = datosGrafico.resumen.map(espacio => 
                `${espacio.nombre} (${espacio.id_espacio})`
            );
            const data = datosGrafico.resumen.map(espacio => espacio.promedio || 0);

            // Creando gráfico de utilización

            chartUtilizacion = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Porcentaje de utilización',
                        data: data,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10,
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
                                },
                                stepSize: 10
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
                    plugins: {
                        legend: { 
                            display: false 
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                title: function (context) {
                                    return context[0].label;
                                },
                                label: function (context) {
                                    return `Utilización: ${context.parsed.y}%`;
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
        }





        // Funciones para filtros dinámicos
        function inicializarFiltrosDinamicos() {
            // Event listeners para filtros dinámicos
            document.getElementById('filtro-busqueda')?.addEventListener('input', function () {
                filtros.busqueda = this.value;
                actualizarDatosFiltrados();
            });

            document.getElementById('filtro-tipo-espacio')?.addEventListener('change', function () {
                filtros.tipoEspacio = this.value;
                actualizarDatosFiltrados();
            });

            document.getElementById('filtro-piso')?.addEventListener('change', function () {
                filtros.piso = this.value;
                actualizarDatosFiltrados();
            });

            document.getElementById('filtro-estado')?.addEventListener('change', function () {
                filtros.estado = this.value;
                actualizarDatosFiltrados();
            });

            document.getElementById('filtro-fecha-inicio')?.addEventListener('change', function () {
                filtros.fechaInicio = this.value;
                actualizarDatosFiltrados();
            });

            document.getElementById('filtro-fecha-fin')?.addEventListener('change', function () {
                filtros.fechaFin = this.value;
                actualizarDatosFiltrados();
            });
        }

        function limpiarFiltros() {
            // Limpiando filtros
            
            document.getElementById('filtro-busqueda').value = '';
            document.getElementById('filtro-tipo-espacio').value = '';
            document.getElementById('filtro-piso').value = '';
            document.getElementById('filtro-estado').value = '';
            document.getElementById('filtro-fecha-inicio').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10);
            document.getElementById('filtro-fecha-fin').value = new Date().toISOString().slice(0, 10);

            filtros = {
                busqueda: '',
                tipoEspacio: '',
                piso: '',
                estado: '',
                fechaInicio: document.getElementById('filtro-fecha-inicio').value,
                fechaFin: document.getElementById('filtro-fecha-fin').value
            };

            // Filtros limpiados
            
            // Restaurar gráfico de utilización con todos los datos
            actualizarGraficoUtilizacion(datosGrafico.resumen);
            
            // Actualizar datos filtrados (que ahora mostrará todos los datos)
            actualizarDatosFiltrados();
        }

        function actualizarDatosFiltrados() {
            // Actualizando datos filtrados con filtros
            
            // Verificar que los datos estén disponibles
            if (!datosGrafico || !datosGrafico.resumen) {
                // Datos del gráfico no disponibles
                return;
            }
            
            // Filtrar datos del resumen
            const resumenFiltrado = datosGrafico.resumen.filter(espacio => {
                const cumpleBusqueda = !filtros.busqueda ||
                    espacio.nombre.toLowerCase().includes(filtros.busqueda.toLowerCase()) ||
                    espacio.id_espacio.toString().includes(filtros.busqueda);

                // Convertir a string para comparación segura
                const espacioTipo = String(espacio.tipo_espacio || '');
                const filtroTipo = String(filtros.tipoEspacio || '');
                const espacioPiso = String(espacio.piso || '');
                const filtroPiso = String(filtros.piso || '');
                const espacioEstado = String(espacio.estado || '');
                const filtroEstado = String(filtros.estado || '');

                const cumpleTipo = !filtros.tipoEspacio || espacioTipo === filtroTipo;
                const cumplePiso = !filtros.piso || espacioPiso === filtroPiso;
                const cumpleEstado = !filtros.estado || espacioEstado === filtroEstado;

                return cumpleBusqueda && cumpleTipo && cumplePiso && cumpleEstado;
            });

                    // Datos filtrados
        // Total de espacios originales
        // Total de espacios filtrados

            // Actualizar tabla de resumen
            const tbody = document.getElementById('tabla-resumen')?.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = '';
                resumenFiltrado.forEach(espacio => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50 transition-colors duration-200';
                    row.innerHTML = `
                        <td class="px-6 py-4 font-semibold text-gray-800">${espacio.nombre} (${espacio.id_espacio})</td>
                        <td class="px-6 py-4 text-gray-700">${espacio.tipo_espacio}</td>
                        <td class="px-6 py-4 text-gray-700">${espacio.piso}</td>
                        <td class="px-6 py-4 text-gray-700">${espacio.facultad}</td>
                        <td class="px-6 py-4">
                            <span
                                class="px-3 py-2 text-xs font-semibold rounded-full shadow-sm
                                ${espacio.estado == 'Disponible' ? 'bg-green-100 text-green-700 border border-green-200' :
                                (espacio.estado == 'Ocupado' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-yellow-100 text-yellow-700 border border-yellow-200')}">
                                ${espacio.estado}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700">${espacio.puestos_disponibles || 'N/A'}</td>
                        <td class="px-6 py-4 text-gray-700">${espacio.total_reservas}</td>
                        <td class="px-6 py-4 text-gray-700">${espacio.horas_utilizadas} h</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-2 font-bold text-blue-700 bg-blue-100 rounded-full shadow-sm">${espacio.promedio}%</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-2 rounded-full text-xs font-semibold shadow-sm
                                ${espacio.estado_utilizacion == 'Óptimo' ? 'bg-green-100 text-green-700 border border-green-200' :
                                (espacio.estado_utilizacion == 'Medio uso' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : 'bg-red-100 text-red-700 border border-red-200')}">
                                ${espacio.estado_utilizacion}
                            </span>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            // Actualizar gráfico de utilización con los datos filtrados
            actualizarGraficoUtilizacion(resumenFiltrado);
            

        }

        function actualizarGraficoUtilizacion(resumenFiltrado) {
            // Llamando a actualizarGraficoUtilizacion
            
            const ctx = document.getElementById('chartUtilizacion');
            if (!ctx) {
                // No se encontró el elemento chartUtilizacion
                return;
            }

            if (chartUtilizacion) {
                // Destruyendo gráfico anterior
                chartUtilizacion.destroy();
            }

            // Usar los espacios filtrados
            const labels = resumenFiltrado.map(espacio => 
                `${espacio.nombre} (${espacio.id_espacio})`
            );
            const data = resumenFiltrado.map(espacio => espacio.promedio || 0);

                    // Actualizando gráfico de utilización con datos filtrados
        // Labels
        // Data

            chartUtilizacion = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Porcentaje de utilización',
                        data: data,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10,
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
                                },
                                stepSize: 10
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
                    plugins: {
                        legend: { 
                            display: false 
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                title: function (context) {
                                    return context[0].label;
                                },
                                label: function (context) {
                                    return `Utilización: ${context.parsed.y}%`;
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
        }




Object.assign(window, {
    limpiarFiltros,
});

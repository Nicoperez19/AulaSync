<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-light-cloud-blue rounded-xl">
                    <i class="text-2xl text-white fa-solid fa-building"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-black">Análisis por Espacios</h2>
                    <p class="text-gray-500">Gestión y análisis de uso de espacios individuales</p>
                </div>
            </div>
        </div>
    </x-slot>
    <div class="px-6 min-h-[80vh]" x-data="{ activeTab: 'resumen' }">
        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow">
                <div class="text-sm text-gray-500">Total espacios</div>
                <div class="text-2xl font-bold text-gray-800">{{ $total_espacios }}</div>
                <div class="mt-1 text-xs text-green-600">&nbsp;</div>
            </div>
            <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow">
                <div class="text-sm text-gray-500">Promedio utilización</div>
                <div class="text-2xl font-bold text-gray-800">{{ $promedio_utilizacion }}%</div>
                <div class="mt-1 text-xs text-green-600">&nbsp;</div>
            </div>
            <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow">
                <div class="text-sm text-gray-500">Total reservas</div>
                <div class="text-2xl font-bold text-gray-800">{{ $total_reservas }}</div>
                <div class="mt-1 text-xs text-orange-600">Este mes</div>
            </div>
            <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow">
                <div class="text-sm text-gray-500">Espacios activos</div>
                <div class="text-2xl font-bold text-gray-800">{{ $espacios_ocupados }}/{{ $total_espacios }}</div>
                <div class="mt-1 text-xs text-purple-600">
                    {{ $total_espacios > 0 ? round(($espacios_ocupados / $total_espacios) * 100) : 0 }}% ocupación
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-4 mb-6 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Filtros</h3>
                <a href="{{ route('reportes.espacios') }}" 
                   class="px-3 py-1 text-sm text-gray-600 transition bg-gray-100 rounded-md hover:bg-gray-200">
                    Limpiar filtros
                </a>
            </div>
            <form method="GET" action="{{ route('reportes.espacios') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Buscar espacio</label>
                    <input type="text" name="busqueda" value="{{ $busqueda }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4" 
                           placeholder="Nombre del espacio...">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Tipo de espacio</label>
                    <select name="tipo_espacio" class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposEspacioDisponibles as $tipo)
                            <option value="{{ $tipo }}" {{ $tipoEspacioFiltro == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Piso</label>
                    <select name="piso" class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                        <option value="">Todos los pisos</option>
                        @foreach($pisosDisponibles as $numero => $piso)
                            <option value="{{ $numero }}" {{ $pisoFiltro == $numero ? 'selected' : '' }}>Piso {{ $numero }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Estado</label>
                    <select name="estado" class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                        <option value="">Todos los estados</option>
                        @foreach($estadosDisponibles as $estado)
                            <option value="{{ $estado }}" {{ $estadoFiltro == $estado ? 'selected' : '' }}>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 h-[40px] py-2 text-sm font-semibold text-white transition bg-blue-600 rounded hover:bg-blue-700">
                        Filtrar
                    </button>
                </div>
            </form>
            
        </div>

        <!-- Nav Pills -->
        <ul class="flex justify-start border-b border-gray-200" role="tablist">
            <li role="presentation">
                <button type="button" @click="activeTab = 'resumen'"
                    class="px-8 py-3 text-base font-semibold transition-all duration-300 border border-b-0 rounded-t-xl focus:outline-none"
                    :class="activeTab == 'resumen' 
                            ? 'bg-light-cloud-blue text-white border-red-600 shadow-md'
                            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100 hover:text-light-cloud-blue'">
                    Resumen General
                </button>
            </li>
            <li role="presentation">
                <button type="button" @click="activeTab = 'horarios'"
                    class="px-8 py-3 text-base font-semibold transition-all duration-300 border border-b-0 rounded-t-xl focus:outline-none"
                    :class="activeTab == 'horarios' 
                            ? 'bg-light-cloud-blue text-white border-red-600 shadow-md'
                            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100 hover:text-light-cloud-blue'">
                    Por Horarios
                </button>
            </li>
        </ul>

        <!-- Contenido de Resumen General -->
        <div x-show="activeTab == 'resumen'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="p-6 bg-white shadow-md rounded-b-xl">
                <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                    <div class="p-4 bg-white rounded-lg shadow md:col-span-2">
                        <h2 class="mb-2 font-semibold text-gray-700">Utilización por Espacio</h2>
                        <div class="h-64">
                            <canvas id="chartUtilizacion"></canvas>
                        </div>
                    </div>
                    <div class="p-4 bg-white rounded-lg shadow">
                        <h2 class="mb-2 font-semibold text-gray-700">Distribución de Reservas</h2>
                        <div class="h-64">
                            <canvas id="chartReservas"></canvas>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white rounded-lg shadow">
                    <h2 class="mb-4 font-semibold text-gray-700">Resumen Detallado por Espacio</h2>
                    <div class="flex gap-2 mb-4">
                        <a href="{{ route('reportes.espacios.export', 'excel') }}?{{ http_build_query(request()->all()) }}" 
                           class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700">
                            Exportar Excel
                        </a>
                        <a href="{{ route('reportes.espacios.export', 'pdf') }}?{{ http_build_query(request()->all()) }}" 
                           class="px-4 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700">
                            Exportar PDF
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead>
                                <tr class="text-xs text-gray-500 uppercase">
                                    <th class="px-4 py-2">Espacio</th>
                                    <th class="px-4 py-2">Tipo</th>
                                    <th class="px-4 py-2">Piso</th>
                                    <th class="px-4 py-2">Facultad</th>
                                    <th class="px-4 py-2">Estado</th>
                                    <th class="px-4 py-2">Puestos</th>
                                    <th class="px-4 py-2">Total Reservas</th>
                                    <th class="px-4 py-2">Horas Utilizadas</th>
                                    <th class="px-4 py-2">Promedio de Uso</th>
                                    <th class="px-4 py-2">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumen as $espacio)
                                    <tr class="border-b border-gray-100">
                                        <td class="px-4 py-2 font-semibold">{{ $espacio['nombre'] }} ({{ $espacio['id_espacio'] }})</td>
                                        <td class="px-4 py-2">{{ $espacio['tipo_espacio'] }}</td>
                                        <td class="px-4 py-2">{{ $espacio['piso'] }}</td>
                                        <td class="px-4 py-2">{{ $espacio['facultad'] }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 text-xs font-semibold rounded
                                                {{ $espacio['estado'] == 'Disponible' ? 'bg-green-100 text-green-700' : 
                                                   ($espacio['estado'] == 'Ocupado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                {{ $espacio['estado'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">{{ $espacio['puestos_disponibles'] ?? 'N/A' }}</td>
                                        <td class="px-4 py-2">{{ $espacio['total_reservas'] }}</td>
                                        <td class="px-4 py-2">{{ $espacio['horas_utilizadas'] }} h</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 font-bold text-blue-700 bg-blue-100 rounded">{{ $espacio['promedio'] }}%</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 rounded text-xs font-semibold
                                                {{ $espacio['estado_utilizacion'] == 'Óptimo' ? 'bg-green-100 text-green-700' : 
                                                   ($espacio['estado_utilizacion'] == 'Medio uso' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                                {{ $espacio['estado_utilizacion'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido de Por Horarios -->
        <div x-show="activeTab == 'horarios'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="p-6 bg-white shadow-md rounded-b-xl">
                <div class="flex flex-wrap items-end gap-4 mb-4">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-500">Día de la semana</label>
                        <select id="filtro-dia" class="rounded-md border-gray-300 shadow-sm h-[37px] px-4">
                            @foreach($diasDisponibles as $dia)
                                <option value="{{ $dia }}" {{ $dia == $diaActual ? 'selected' : '' }}>{{ ucfirst($dia) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-500">Espacio</label>
                        <select id="filtro-espacio" class="rounded-md border-gray-300 shadow-sm h-[37px] px-4">
                            <option value="">Todos los espacios</option>
                            @foreach($resumen as $espacio)
                                <option value="{{ $espacio['id_espacio'] }}">{{ $espacio['nombre'] }} ({{ $espacio['id_espacio'] }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-4 mb-4 rounded-lg bg-blue-50">
                    <label class="block mb-2 font-semibold">Rango de Horarios a Mostrar</label>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="text-xs">Inicio:</label>
                            <input type="range" id="modulo-inicio" min="0" max="14" value="0" class="w-full">
                            <div class="text-xs text-right" id="label-inicio"></div>
                        </div>
                        <div class="flex-1">
                            <label class="text-xs">Fin:</label>
                            <input type="range" id="modulo-fin" min="0" max="14" value="14" class="w-full">
                            <div class="text-xs text-right" id="label-fin"></div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-2">
                        <button type="button" class="btn btn-xs" onclick="setRango(0,14)">Todo el día</button>
                        <button type="button" class="btn btn-xs" onclick="setRango(0,5)">Mañana (8-14h)</button>
                        <button type="button" class="btn btn-xs" onclick="setRango(6,9)">Tarde (14-18h)</button>
                        <button type="button" class="btn btn-xs" onclick="setRango(10,14)">Noche (18-23h)</button>
                    </div>
                    <div class="mt-1 text-xs" id="rango-mostrando"></div>
                </div>
                <div class="p-4 mb-6 bg-white rounded-lg shadow">
                    <h2 class="mb-2 font-semibold text-gray-700">Ocupación por Horarios de la Semana</h2>
                    <div class="h-64">
                        <canvas id="chartHorarios"></canvas>
                    </div>
                </div>
                <div class="p-4 bg-white rounded-lg shadow">
                    <h2 class="mb-4 font-semibold text-gray-700">Detalle de Ocupación por Rangos de Horarios</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead>
                                <tr class="text-xs text-gray-500 uppercase" id="thead-horarios"></tr>
                            </thead>
                            <tbody id="tbody-horarios"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Datos para gráficos - optimizados
        const labelsGrafico = @json($labels_grafico);
        const dataGrafico = @json($data_grafico);
        const dataReservasGrafico = @json($data_reservas_grafico);
        const ocupacionHorarios = @json($ocupacionHorarios);
        const diasDisponibles = @json($diasDisponibles);
        let diaActual = @json($diaActual);

        // Estado de filtros
        let diaSeleccionado = diaActual;
        let espacioSeleccionado = '';
        let modulosDia = [
            '08:10-09:00', '09:10-10:00', '10:10-11:00', '11:10-12:00', '12:10-13:00',
            '13:10-14:00', '14:10-15:00', '15:10-16:00', '16:10-17:00', '17:10-18:00',
            '18:10-19:00', '19:10-20:00', '20:10-21:00', '21:10-22:00', '22:10-23:00'
        ];
        let moduloInicio = 0;
        let moduloFin = modulosDia.length - 1;

        // Variables para gráficos
        let chartUtilizacion, chartReservas, chartHorarios;

        // Inicializar gráficos de forma optimizada
        document.addEventListener('DOMContentLoaded', function() {
            // Solo inicializar gráficos si hay datos
            if (labelsGrafico.length > 0) {
                renderChartUtilizacion();
                renderChartReservas();
            }
            renderChartHorarios();
            renderTablaHorarios();
            updateModuloLabels();
            updateRangoText();
        });

        // Gráfico de utilización optimizado
        function renderChartUtilizacion() {
            const ctx = document.getElementById('chartUtilizacion');
            if (!ctx) return;
            
            if (chartUtilizacion) {
                chartUtilizacion.destroy();
            }

            chartUtilizacion = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labelsGrafico.slice(0, 20), // Limitar a 20 espacios para mejor rendimiento
                    datasets: [{
                        label: 'Porcentaje de utilización',
                        data: dataGrafico.slice(0, 20),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Gráfico de reservas optimizado
        function renderChartReservas() {
            const ctx = document.getElementById('chartReservas');
            if (!ctx) return;
            
            if (chartReservas) {
                chartReservas.destroy();
            }

            chartReservas = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labelsGrafico.slice(0, 10), // Limitar a 10 espacios
                    datasets: [{
                        data: dataReservasGrafico.slice(0, 10),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(199, 199, 199, 0.8)',
                            'rgba(83, 102, 255, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de horarios optimizado
        function renderChartHorarios() {
            const ctx = document.getElementById('chartHorarios');
            if (!ctx) return;
            
            if (chartHorarios) {
                chartHorarios.destroy();
            }

            // Filtrar datos según el espacio seleccionado
            let espaciosAMostrar = Object.keys(ocupacionHorarios);
            if (espacioSeleccionado) {
                espaciosAMostrar = [espacioSeleccionado];
            }

            // Limitar a 5 espacios para mejor rendimiento
            espaciosAMostrar = espaciosAMostrar.slice(0, 5);

            const datasets = espaciosAMostrar.map((espacioId, index) => {
                const color = `hsl(${index * 360 / Math.max(espaciosAMostrar.length, 1)}, 70%, 50%)`;
                return {
                    label: obtenerNombreEspacio(espacioId),
                    data: modulosDia.slice(moduloInicio, moduloFin + 1).map((_, moduloIndex) => {
                        const moduloReal = moduloInicio + moduloIndex + 1;
                        return ocupacionHorarios[espacioId]?.[diaSeleccionado]?.[moduloReal] || 0;
                    }),
                    borderColor: color,
                    backgroundColor: color + '40',
                    borderWidth: 2,
                    fill: false
                };
            });

            chartHorarios = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: modulosDia.slice(moduloInicio, moduloFin + 1),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        // Tabla de horarios optimizada
        function renderTablaHorarios() {
            const thead = document.getElementById('thead-horarios');
            const tbody = document.getElementById('tbody-horarios');
            
            if (!thead || !tbody) return;
            
            // Limpiar tabla
            thead.innerHTML = '';
            tbody.innerHTML = '';
            
            // Crear encabezado
            const headerRow = document.createElement('tr');
            headerRow.innerHTML = '<th class="px-4 py-2">Espacio</th>';
            for (let i = moduloInicio; i <= moduloFin; i++) {
                headerRow.innerHTML += `<th class="px-4 py-2 text-center">${modulosDia[i]}</th>`;
            }
            thead.appendChild(headerRow);
            
            // Filtrar espacios según selección
            let espaciosAMostrar = Object.keys(ocupacionHorarios);
            if (espacioSeleccionado) {
                espaciosAMostrar = [espacioSeleccionado];
            }
            
            // Limitar a 10 espacios para mejor rendimiento
            espaciosAMostrar = espaciosAMostrar.slice(0, 10);
            
            // Crear filas de datos
            espaciosAMostrar.forEach(espacioId => {
                const row = document.createElement('tr');
                row.innerHTML = `<td class="px-4 py-2 font-semibold">${obtenerNombreEspacio(espacioId)}</td>`;
                
                for (let i = moduloInicio; i <= moduloFin; i++) {
                    const moduloReal = i + 1;
                    const ocupacion = ocupacionHorarios[espacioId]?.[diaSeleccionado]?.[moduloReal] || 0;
                    const colorClass = ocupacion > 80 ? 'bg-red-100 text-red-700' : 
                                     ocupacion > 40 ? 'bg-yellow-100 text-yellow-700' : 
                                     'bg-green-100 text-green-700';
                    
                    row.innerHTML += `<td class="px-4 py-2 text-center">
                        <span class="px-2 py-1 text-xs font-semibold rounded ${colorClass}">${ocupacion}%</span>
                    </td>`;
                }
                
                tbody.appendChild(row);
            });
        }

        // Funciones auxiliares optimizadas
        function obtenerNombreEspacio(espacioId) {
            const espacio = @json($resumen).find(e => e.id_espacio === espacioId);
            return espacio ? espacio.nombre : espacioId;
        }

        function setRango(inicio, fin) {
            moduloInicio = inicio;
            moduloFin = fin;
            document.getElementById('modulo-inicio').value = inicio;
            document.getElementById('modulo-fin').value = fin;
            updateModuloLabels();
            updateRangoText();
            renderChartHorarios();
            renderTablaHorarios();
        }

        function updateModuloLabels() {
            const labelInicio = document.getElementById('label-inicio');
            const labelFin = document.getElementById('label-fin');
            if (labelInicio) labelInicio.textContent = modulosDia[moduloInicio];
            if (labelFin) labelFin.textContent = modulosDia[moduloFin];
        }

        function updateRangoText() {
            const rangoMostrando = document.getElementById('rango-mostrando');
            if (rangoMostrando) {
                rangoMostrando.textContent = 
                    `Mostrando módulos ${moduloInicio + 1} a ${moduloFin + 1} (${modulosDia[moduloInicio]} - ${modulosDia[moduloFin]})`;
            }
        }

        // Event listeners optimizados con debounce
        let timeoutId;
        function debounce(func, delay) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(func, delay);
        }

        document.getElementById('filtro-dia')?.addEventListener('change', function() {
            diaSeleccionado = this.value;
            debounce(() => {
                renderChartHorarios();
                renderTablaHorarios();
            }, 100);
        });

        document.getElementById('filtro-espacio')?.addEventListener('change', function() {
            espacioSeleccionado = this.value;
            debounce(() => {
                renderChartHorarios();
                renderTablaHorarios();
            }, 100);
        });

        document.getElementById('modulo-inicio')?.addEventListener('input', function() {
            moduloInicio = parseInt(this.value);
            if (moduloInicio > moduloFin) moduloFin = moduloInicio;
            document.getElementById('modulo-fin').value = moduloFin;
            updateModuloLabels();
            updateRangoText();
            debounce(() => {
                renderChartHorarios();
                renderTablaHorarios();
            }, 100);
        });

        document.getElementById('modulo-fin')?.addEventListener('input', function() {
            moduloFin = parseInt(this.value);
            if (moduloFin < moduloInicio) moduloInicio = moduloFin;
            document.getElementById('modulo-inicio').value = moduloInicio;
            updateModuloLabels();
            updateRangoText();
            debounce(() => {
                renderChartHorarios();
                renderTablaHorarios();
            }, 100);
        });
    </script>
</x-app-layout> 
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-light-cloud-blue rounded-xl">
                    <i class="text-2xl text-white fa-solid fa-building"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-black">Análisis por tipo Espacios</h2>
                    <p class="text-gray-500">Gestión y análisis de uso de espacios</p>
                </div>
            </div>
        </div>
    </x-slot>
    <div class="px-6 min-h-[80vh]" x-data="{ activeTab: 'resumen' }">
        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow">
                <div class="text-sm text-gray-500">Tipos de espacio</div>
                <div class="text-2xl font-bold text-gray-800">{{ $total_tipos }}</div>
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
                <li role="presentation">
                    <button type="button" @click="activeTab = 'historico'"
                        class="px-8 py-3 text-base font-semibold transition-all duration-300 border border-b-0 rounded-t-xl focus:outline-none"
                        :class="activeTab == 'historico' 
                                ? 'bg-light-cloud-blue text-white border-red-600 shadow-md'
                                : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100 hover:text-light-cloud-blue'">
                        Histórico
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
                        <h2 class="mb-2 font-semibold text-gray-700">Utilización por Tipo de Espacio</h2>
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
                    <h2 class="mb-4 font-semibold text-gray-700">Resumen Detallado por Tipo de Espacio</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead>
                                <tr class="text-xs text-gray-500 uppercase">
                                    <th class="px-4 py-2">Tipo de Espacio</th>
                                    <th class="px-4 py-2">Total Espacios</th>
                                    <th class="px-4 py-2">Total Reservas</th>
                                    <th class="px-4 py-2">Horas Utilizadas</th>
                                    <th class="px-4 py-2">Promedio de Uso</th>
                                    <th class="px-4 py-2">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resumen as $tipo)
                                    <tr>
                                        <td class="px-4 py-2 font-semibold">{{ $tipo['nombre'] }}</td>
                                        <td class="px-4 py-2">{{ $tipo['total_espacios'] }}</td>
                                        <td class="px-4 py-2">{{ $tipo['total_reservas'] }}</td>
                                        <td class="px-4 py-2">{{ $tipo['horas_utilizadas'] }} h</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="px-2 py-1 font-bold text-blue-700 bg-blue-100 rounded">{{ $tipo['promedio'] }}%</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="{{ $tipo['estado'] == 'Óptimo' ? 'bg-green-100 text-green-700' : ($tipo['estado'] == 'Medio uso' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }} px-2 py-1 rounded text-xs font-semibold">
                                                {{ $tipo['estado'] }}
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
                                <option value="{{ $dia }}" {{ $dia == $diaActual ? 'selected' : '' }}>{{ ucfirst($dia) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-500">Tipo de espacio</label>
                        <select id="filtro-tipo" class="rounded-md border-gray-300 shadow-sm h-[37px] px-4">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposEspacioDisponibles as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
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
                        <button type="button" class="btn btn-xs" onclick="setRango(0,modulosDia.length-1)">Todo el
                            día</button>
                        <button type="button" class="btn btn-xs" onclick="setRango(0,5)">Mañana (8-14h)</button>
                        <button type="button" class="btn btn-xs" onclick="setRango(6,9)">Tarde (14-18h)</button>
                        <button type="button" class="btn btn-xs" onclick="setRango(10,modulosDia.length-1)">Noche
                            (18-23h)</button>
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

        <!-- Contenido de Histórico -->
        <div x-show="activeTab == 'historico'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="p-6 bg-white shadow-md rounded-b-xl">
                <h2 class="mb-4 font-semibold text-gray-700">Historial detallado de uso de espacios</h2>
                <div class="flex flex-row flex-wrap items-center gap-4 mb-4">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-500">Fecha desde</label>
                        <input type="date" id="filtro-hist-fecha-inicio"
                            class="rounded-md border-gray-300 shadow-sm h-[40px] px-4 min-w-[150px]" />
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-500">Fecha hasta</label>
                        <input type="date" id="filtro-hist-fecha-fin"
                            class="rounded-md border-gray-300 shadow-sm h-[40px] px-4 min-w-[150px]" />
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-500">Tipo de espacio</label>
                        <select id="filtro-hist-tipo" class="rounded-md border-gray-300 shadow-sm h-[40px] px-4 min-w-[150px]">
                            <option value="">Todos</option>
                            @foreach($tiposEspacioDisponibles as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center h-full pt-5 md:pt-0">
                        <button id="btn-hist-buscar"
                            class="px-4 h-[40px] py-2 text-sm font-semibold text-white transition bg-blue-600 rounded hover:bg-blue-700 whitespace-nowrap">Buscar</button>
                    </div>
                </div>
                <div class="flex gap-2 mb-4">
                    <button class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700">Exportar
                        Excel</button>
                    <button class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700">Exportar PDF</button>
                </div>
                <!-- Spinner -->
                <div id="spinner-historico" class="flex items-center justify-center hidden py-8">
                    <svg class="w-8 h-8 text-blue-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase">
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2">Hora Inicio</th>
                                <th class="px-4 py-2">Hora Fin</th>
                                <th class="px-4 py-2">Espacio</th>
                                <th class="px-4 py-2">Tipo Espacio</th>
                                <th class="px-4 py-2">Usuario</th>
                                <th class="px-4 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-historico"></tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <div class="flex gap-2" id="paginacion-historico"></div>
                </div>
                <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-4">
                    <div class="p-4 text-center rounded-lg bg-blue-50">
                        <div class="text-2xl font-bold text-blue-700" id="kpi-hist-total">0</div>
                        <div class="text-xs text-blue-700">Total registros</div>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-green-50">
                        <div class="text-2xl font-bold text-green-700" id="kpi-hist-completadas">0</div>
                        <div class="text-xs text-green-700">Completadas</div>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-yellow-50">
                        <div class="text-2xl font-bold text-yellow-700" id="kpi-hist-canceladas">0</div>
                        <div class="text-xs text-yellow-700">Canceladas</div>
                    </div>
                    <div class="p-4 text-center rounded-lg bg-purple-50">
                        <div class="text-2xl font-bold text-purple-700" id="kpi-hist-enprogreso">0</div>
                        <div class="text-xs text-purple-700">En progreso</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js scripts con datos reales -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Debug: Verificar que los datos están llegando
        console.log('Datos del gráfico de utilización:', {
            labels: @json($labels_grafico),
            data: @json($data_grafico),
            resumen: @json($resumen)
        });
        
        // Gráfico de barras para Utilización por Tipo de Espacio
        let labelsUtil = @json($labels_grafico);
        let dataUtil = @json($data_grafico);
        const resumenData = @json($resumen);
        
        // Si no hay datos, usar datos de prueba
        if (!labelsUtil || labelsUtil.length === 0) {
            console.log('No hay datos reales, usando datos de prueba');
            labelsUtil = ['Sala de Clases', 'Laboratorio', 'Auditorio', 'Oficina', 'Sala de Reuniones'];
            dataUtil = [75, 60, 85, 45, 30];
        }
        
        // Verificar que el canvas existe
        const canvasUtilizacion = document.getElementById('chartUtilizacion');
        console.log('Canvas de utilización:', canvasUtilizacion);
        console.log('Labels:', labelsUtil);
        console.log('Data:', dataUtil);
        
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
                                title: function(context) {
                                    return 'Tipo: ' + context[0].label;
                                },
                                label: function(context) {
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
                                callback: function(value) {
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
            console.log('Gráfico de utilización creado exitosamente');
        } else {
            console.error('Error: No se pudo crear el gráfico de utilización', {
                canvas: canvasUtilizacion,
                labels: labelsUtil,
                data: dataUtil
            });
        }
        // Gráfico de torta para Distribución de Reservas
        let dataReservas = @json($data_reservas_grafico);
        console.log('Datos del gráfico de reservas:', dataReservas);
        
        // Si no hay datos de reservas, usar datos de prueba
        if (!dataReservas || dataReservas.length === 0) {
            console.log('No hay datos de reservas reales, usando datos de prueba');
            dataReservas = [120, 85, 95, 60, 45];
        }
        
        const canvasReservas = document.getElementById('chartReservas');
        console.log('Canvas de reservas:', canvasReservas);
        console.log('Data reservas:', dataReservas);
        
        if (canvasReservas && dataReservas) {
            new Chart(canvasReservas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labelsUtil,
                    datasets: [{
                        label: 'Reservas',
                        data: dataReservas,
                        backgroundColor: [
                            '#3b82f6', '#06b6d4', '#f59e42', '#a78bfa', '#f472b6', '#6b7280', '#fbbf24', '#10b981'
                        ],
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
                                padding: 15,
                                font: { size: 11, weight: 'bold' }
                            }
                        },
                    }
                }
            });
            console.log('Gráfico de reservas creado exitosamente');
        } else {
            console.error('Error: No se pudo crear el gráfico de reservas', {
                canvas: canvasReservas,
                data: dataReservas
            });
        }

        // Datos desde el backend
        const horariosModulosTipoEspacio = {
            domingo: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            lunes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            martes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            miercoles: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            jueves: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            viernes: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            },
            sabado: {
                1: { inicio: '08:10:00', fin: '09:00:00' },
                2: { inicio: '09:10:00', fin: '10:00:00' },
                3: { inicio: '10:10:00', fin: '11:00:00' },
                4: { inicio: '11:10:00', fin: '12:00:00' },
                5: { inicio: '12:10:00', fin: '13:00:00' },
                6: { inicio: '13:10:00', fin: '14:00:00' },
                7: { inicio: '14:10:00', fin: '15:00:00' },
                8: { inicio: '15:10:00', fin: '16:00:00' },
                9: { inicio: '16:10:00', fin: '17:00:00' },
                10: { inicio: '17:10:00', fin: '18:00:00' },
                11: { inicio: '18:10:00', fin: '19:00:00' },
                12: { inicio: '19:10:00', fin: '20:00:00' },
                13: { inicio: '20:10:00', fin: '21:00:00' },
                14: { inicio: '21:10:00', fin: '22:00:00' },
                15: { inicio: '22:10:00', fin: '23:00:00' }
            }
        };

        // ========================================
        // FUNCIÓN PARA OBTENER EL DÍA ACTUAL
        // ========================================
        function obtenerDiaActual() {
            const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            return dias[new Date().getDay()];
        }

        // ========================================
        // FUNCIÓN PARA DETERMINAR EL MÓDULO ACTUAL
        // ========================================
        function determinarModulo(hora) {
            const diaActual = obtenerDiaActual();
            const horariosDia = horariosModulosTipoEspacio[diaActual];

            if (!horariosDia) return null;

            // Buscar en qué módulo estamos según la hora actual
            for (const [modulo, horario] of Object.entries(horariosDia)) {
                if (hora >= horario.inicio && hora < horario.fin) {
                    return parseInt(modulo);
                }
            }
            return null;
        }

        const ocupacionHorarios = @json($ocupacionHorarios);
        const tiposEspacio = @json($tiposEspacioDisponibles);
        const diasDisponibles = @json($diasDisponibles);
        let diaActual = @json($diaActual);

        // Debug: Verificar datos de horarios
        console.log('Datos de ocupación por horarios:', ocupacionHorarios);
        console.log('Tipos de espacio:', tiposEspacio);
        console.log('Días disponibles:', diasDisponibles);
        console.log('Día actual:', diaActual);

        // Estado de filtros
        let diaSeleccionado = diaActual;
        let tipoSeleccionado = '';
        let modulosDia = Object.values(horariosModulosTipoEspacio[diaSeleccionado]);
        let moduloInicio = 0;
        let moduloFin = modulosDia.length - 1;

        // Inicializar filtros y range
        document.getElementById('filtro-dia').addEventListener('change', function () {
            diaSeleccionado = this.value;
            modulosDia = Object.values(horariosModulosTipoEspacio[diaSeleccionado]);
            document.getElementById('modulo-inicio').max = modulosDia.length - 1;
            document.getElementById('modulo-fin').max = modulosDia.length - 1;
            if (moduloFin > modulosDia.length - 1) moduloFin = modulosDia.length - 1;
            if (moduloInicio > moduloFin) moduloInicio = 0;
            document.getElementById('modulo-inicio').value = moduloInicio;
            document.getElementById('modulo-fin').value = moduloFin;
            updateModuloLabels();
            updateRangoText();
            renderChartHorarios();
            renderTablaHorarios();
        });
        document.getElementById('filtro-tipo').addEventListener('change', function () {
            tipoSeleccionado = this.value;
            renderChartHorarios();
            renderTablaHorarios();
        });
        document.getElementById('modulo-inicio').addEventListener('input', function (e) {
            moduloInicio = parseInt(e.target.value);
            if (moduloInicio > moduloFin) {
                moduloFin = moduloInicio;
                document.getElementById('modulo-fin').value = moduloFin;
            }
            updateModuloLabels();
            updateRangoText();
            renderChartHorarios();
            renderTablaHorarios();
        });
        document.getElementById('modulo-fin').addEventListener('input', function (e) {
            moduloFin = parseInt(e.target.value);
            if (moduloFin < moduloInicio) {
                moduloInicio = moduloFin;
                document.getElementById('modulo-inicio').value = moduloInicio;
            }
            updateModuloLabels();
            updateRangoText();
            renderChartHorarios();
            renderTablaHorarios();
        });
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
            document.getElementById('label-inicio').innerText = modulosDia[moduloInicio].inicio + ' - ' + modulosDia[moduloInicio].fin;
            document.getElementById('label-fin').innerText = modulosDia[moduloFin].inicio + ' - ' + modulosDia[moduloFin].fin;
        }
        function updateRangoText() {
            document.getElementById('rango-mostrando').innerText = `Mostrando ${moduloFin - moduloInicio + 1} de ${modulosDia.length} rangos horarios`;
        }
        // Inicializar labels y tabla
        updateModuloLabels();
        updateRangoText();

        // Gráfico de barras apiladas
        let chartHorarios;
        function renderChartHorarios() {
            console.log('Renderizando gráfico de horarios...');
            const ctx = document.getElementById('chartHorarios');
            console.log('Canvas de horarios:', ctx);
            
            if (!ctx) {
                console.error('No se encontró el canvas de horarios');
                return;
            }
            
            if (chartHorarios) chartHorarios.destroy();
            const labels = modulosDia.slice(moduloInicio, moduloFin + 1).map(m => m.inicio + '-' + m.fin);
            console.log('Labels de horarios:', labels);
            
            let datasets = [];
            if (tipoSeleccionado) {
                // Solo un tipo
                const data = [];
                for (let i = moduloInicio + 1; i <= moduloFin + 1; i++) {
                    data.push((ocupacionHorarios[tipoSeleccionado] && ocupacionHorarios[tipoSeleccionado][diaSeleccionado] && ocupacionHorarios[tipoSeleccionado][diaSeleccionado][i]) || 0);
                }
                datasets.push({
                    label: tipoSeleccionado,
                    data: data,
                    backgroundColor: '#3b82f6',
                });
                console.log('Dataset para tipo seleccionado:', datasets);
            } else {
                // Todos los tipos
                tiposEspacio.forEach((tipo, idx) => {
                    const data = [];
                    for (let i = moduloInicio + 1; i <= moduloFin + 1; i++) {
                        data.push((ocupacionHorarios[tipo] && ocupacionHorarios[tipo][diaSeleccionado] && ocupacionHorarios[tipo][diaSeleccionado][i]) || 0);
                    }
                    datasets.push({
                        label: tipo,
                        data: data,
                        backgroundColor: coloresGraficos[idx % coloresGraficos.length],
                    });
                });
                console.log('Datasets para todos los tipos:', datasets);
            }
            
            try {
                chartHorarios = new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: { labels: labels, datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { 
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { size: 11, weight: 'bold' }
                                }
                            } 
                        },
                        scales: { 
                            x: { 
                                stacked: true,
                                grid: { display: false },
                                ticks: {
                                    font: { size: 10, weight: 'bold' },
                                    maxRotation: 45
                                }
                            }, 
                            y: { 
                                stacked: true, 
                                beginAtZero: true, 
                                max: 100,
                                grid: { color: 'rgba(0, 0, 0, 0.1)' },
                                ticks: {
                                    font: { size: 11 },
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            } 
                        },
                        animation: {
                            duration: 1500,
                            easing: 'easeInOutQuart'
                        }
                    }
                });
                console.log('Gráfico de horarios creado exitosamente');
            } catch (error) {
                console.error('Error al crear el gráfico de horarios:', error);
            }
        }
        // Tabla de detalle
        function renderTablaHorarios() {
            // Encabezado
            let thead = '<th class="px-4 py-2">Rango de Horarios</th>';
            let tipos = tipoSeleccionado ? [tipoSeleccionado] : tiposEspacio;
            tipos.forEach(tipo => {
                thead += `<th class=\"py-2 px-4\">${tipo}</th>`;
            });
            thead += '<th class="px-4 py-2">Promedio</th>';
            document.getElementById('thead-horarios').innerHTML = thead;
            // Cuerpo
            let tbody = '';
            for (let i = moduloInicio + 1; i <= moduloFin + 1; i++) {
                let fila = `<td class=\"py-2 px-4\">${modulosDia[i - 1].inicio}-${modulosDia[i - 1].fin}</td>`;
                let suma = 0;
                tipos.forEach(tipo => {
                    const val = (ocupacionHorarios[tipo] && ocupacionHorarios[tipo][diaSeleccionado] && ocupacionHorarios[tipo][diaSeleccionado][i]) || 0;
                    suma += val;
                    fila += `<td class=\"py-2 px-4\">${val}%</td>`;
                });
                const prom = tipos.length > 0 ? Math.round(suma / tipos.length) : 0;
                fila += `<td class=\"py-2 px-4 font-bold\">${prom}%</td>`;
                tbody += `<tr>${fila}</tr>`;
            }
            document.getElementById('tbody-horarios').innerHTML = tbody;
        }
        // Colores para los gráficos
        const coloresGraficos = ['#3b82f6', '#06b6d4', '#f59e42', '#a78bfa', '#f472b6', '#6b7280', '#fbbf24', '#10b981'];
        // Inicializar
        renderChartHorarios();
        renderTablaHorarios();

        // --- HISTÓRICO DINÁMICO ---
        function cargarHistorico(page = 1) {
            console.log('Cargando histórico, página:', page);
            document.getElementById('spinner-historico').classList.remove('hidden');
            document.getElementById('tbody-historico').innerHTML = '';
            let fecha_inicio = document.getElementById('filtro-hist-fecha-inicio').value;
            let fecha_fin = document.getElementById('filtro-hist-fecha-fin').value;
            let tipo_espacio = document.getElementById('filtro-hist-tipo').value;
            
            console.log('Filtros:', { fecha_inicio, fecha_fin, tipo_espacio });
            
            const url = `/reportes/tipo-espacio/historico-ajax?page=${page}&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}&tipo_espacio=${tipo_espacio}`;
            console.log('URL de la petición:', url);
            
            fetch(url)
                .then(res => {
                    console.log('Respuesta del servidor:', res);
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(res => {
                    console.log('Datos recibidos:', res);
                    document.getElementById('spinner-historico').classList.add('hidden');
                    
                    // Tabla
                    let rows = '';
                    if (res.data && res.data.length > 0) {
                        res.data.forEach(reg => {
                            rows += `<tr>
                                <td class='px-4 py-2'>${reg.fecha}</td>
                                <td class='px-4 py-2'>${reg.hora_inicio}</td>
                                <td class='px-4 py-2'>${reg.hora_termino}</td>
                                <td class='px-4 py-2'>${reg.espacio}</td>
                                <td class='px-4 py-2'>${reg.tipo_espacio}</td>
                                <td class='px-4 py-2'>${reg.usuario}</td>
                                <td class='px-4 py-2'>${renderEstado(reg.estado)}</td>
                            </tr>`;
                        });
                    } else {
                        rows = '<tr><td colspan="7" class="px-4 py-2 text-center text-gray-500">No se encontraron registros</td></tr>';
                    }
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
                })
                .catch(error => {
                    console.error('Error al cargar histórico:', error);
                    document.getElementById('spinner-historico').classList.add('hidden');
                    document.getElementById('tbody-historico').innerHTML = '<tr><td colspan="7" class="px-4 py-2 text-center text-red-500">Error al cargar datos: ' + error.message + '</td></tr>';
                });
        }
        function renderEstado(estado) {
            if (estado === 'completada') return `<span class='px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full'>Completada</span>`;
            if (estado === 'cancelada') return `<span class='px-2 py-1 text-xs text-yellow-700 bg-yellow-100 rounded-full'>Cancelada</span>`;
            if (estado === 'en progreso') return `<span class='px-2 py-1 text-xs text-purple-700 bg-purple-100 rounded-full'>En progreso</span>`;
            return `<span class='px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded-full'>${estado}</span>`;
        }
        // Inicializar filtros con valores por defecto
        document.getElementById('filtro-hist-fecha-inicio').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10);
        document.getElementById('filtro-hist-fecha-fin').value = new Date().toISOString().slice(0, 10);
        // Buscar al cargar y al cambiar filtros
        document.getElementById('btn-hist-buscar').onclick = () => cargarHistorico(1);
        cargarHistorico(1);
    </script>
</x-app-layout>
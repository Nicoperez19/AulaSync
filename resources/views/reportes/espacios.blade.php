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
            <div class="flex flex-col justify-between p-3 bg-white rounded-lg shadow border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-medium text-gray-600">Total espacios</div>
                    <div class="p-1.5 bg-blue-100 rounded-lg">
                        <i class="text-base text-blue-600 fas fa-building"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-gray-800" data-kpi="total-espacios">{{ $total_espacios }}</div>
                <div class="text-xs text-gray-500">Espacios registrados</div>
            </div>
            <div class="flex flex-col justify-between p-3 bg-white rounded-lg shadow border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-medium text-gray-600">Promedio utilización</div>
                    <div class="p-1.5 bg-green-100 rounded-lg">
                        <i class="text-base text-green-600 fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-gray-800" data-kpi="promedio-utilizacion">
                    {{ $promedio_utilizacion }}%
                </div>
                <div class="text-xs text-gray-500">Este mes</div>
            </div>
            <div class="flex flex-col justify-between p-3 bg-white rounded-lg shadow border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-medium text-gray-600">Total reservas</div>
                    <div class="p-1.5 bg-orange-100 rounded-lg">
                        <i class="text-base text-orange-600 fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-gray-800" data-kpi="total-reservas">{{ $total_reservas }}</div>
                <div class="text-xs text-gray-500">Este mes</div>
            </div>
            <div class="flex flex-col justify-between p-3 bg-white rounded-lg shadow border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-medium text-gray-600">Espacios activos</div>
                    <div class="p-1.5 bg-purple-100 rounded-lg">
                        <i class="text-base text-purple-600 fas fa-users"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-gray-800" data-kpi="espacios-ocupados">
                    {{ $espacios_ocupados }}/{{ $total_espacios }}
                </div>
                <div class="text-xs text-gray-500" data-kpi="porcentaje-ocupacion">
                    {{ $total_espacios > 0 ? round(($espacios_ocupados / $total_espacios) * 100) : 0 }}% ocupación
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-4 mb-6 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Filtros de Búsqueda</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-7">
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Buscar espacio</label>
                    <input type="text" id="filtro-busqueda" value="{{ $busqueda }}"
                        class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4"
                        placeholder="Nombre del espacio...">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Tipo de espacio</label>
                    <select id="filtro-tipo-espacio" class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposEspacioDisponibles as $tipo)
                            <option value="{{ $tipo }}" {{ $tipoEspacioFiltro == $tipo ? 'selected' : '' }}>{{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Piso</label>
                    <select id="filtro-piso" class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                        <option value="">Todos los pisos</option>
                        @foreach($pisosDisponibles as $numero => $piso)
                            <option value="{{ $numero }}" {{ $pisoFiltro == $numero ? 'selected' : '' }}>Piso {{ $numero }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Estado</label>
                    <select id="filtro-estado" class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                        <option value="">Todos los estados</option>
                        @foreach($estadosDisponibles as $estado)
                            <option value="{{ $estado }}" {{ $estadoFiltro == $estado ? 'selected' : '' }}>{{ $estado }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Fecha desde</label>
                    <input type="date" id="filtro-fecha-inicio" value="{{ $fechaInicio }}"
                        class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Fecha hasta</label>
                    <input type="date" id="filtro-fecha-fin" value="{{ $fechaFin }}"
                        class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="limpiarFiltros()"
                        class="w-full px-4 h-[40px] py-2 text-sm font-semibold text-white transition bg-gray-600 rounded hover:bg-gray-700">
                        <i class="mr-2 fas fa-eraser"></i>Limpiar filtros
                    </button>
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
                <div class="mb-6 p-4 rounded-lg bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200">
                    <div class="flex items-center">
                        <i class="mr-3 text-2xl text-purple-600 fas fa-chart-bar"></i>
                        <div>
                            <h3 class="text-lg font-bold text-purple-800">Resumen General de Espacios</h3>
                            <p class="text-sm text-purple-700">Análisis completo del uso y ocupación de todos los espacios disponibles</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 mb-8">
                    <div class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg border border-blue-200">
                        <h2 class="mb-4 text-lg font-bold text-blue-800">Utilización por Espacio</h2>
                        <div class="h-80">
                            <canvas id="chartUtilizacion"></canvas>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-lg border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-800 border-b border-gray-300 pb-3">Resumen Detallado por Espacio</h2>
                        <div class="flex gap-2">
                            <a href="{{ route('reportes.espacios.export', 'excel') }}?{{ http_build_query(request()->all()) }}"
                                class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700">
                                <i class="mr-2 fas fa-file-excel"></i>Exportar Excel
                            </a>
                            <a href="{{ route('reportes.espacios.export', 'pdf') }}?{{ http_build_query(request()->all()) }}"
                                class="px-4 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700">
                                <i class="mr-2 fas fa-file-pdf"></i>Exportar PDF
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left bg-white rounded-lg shadow-sm" id="tabla-resumen">
                            <thead class="bg-white border-b border-gray-400">
                                <tr class="text-xs text-gray-600 uppercase">
                                    <th class="px-6 py-4 font-semibold">Espacio</th>
                                    <th class="px-6 py-4 font-semibold">Tipo</th>
                                    <th class="px-6 py-4 font-semibold">Piso</th>
                                    <th class="px-6 py-4 font-semibold">Facultad</th>
                                    <th class="px-6 py-4 font-semibold">Estado</th>
                                    <th class="px-6 py-4 font-semibold">Puestos</th>
                                    <th class="px-6 py-4 font-semibold">Total Reservas</th>
                                    <th class="px-6 py-4 font-semibold">Horas Utilizadas</th>
                                    <th class="px-6 py-4 font-semibold">Promedio de Uso</th>
                                    <th class="px-6 py-4 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($resumen as $espacio)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-semibold text-gray-800">{{ $espacio['nombre'] }}
                                            ({{ $espacio['id_espacio'] }})</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $espacio['tipo_espacio'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $espacio['piso'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $espacio['facultad'] }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-2 text-xs font-semibold rounded-full shadow-sm
                                                {{ $espacio['estado'] == 'Disponible' ? 'bg-green-100 text-green-700 border border-green-200' :
                                                ($espacio['estado'] == 'Ocupado' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-yellow-100 text-yellow-700 border border-yellow-200') }}">
                                                {{ $espacio['estado'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">{{ $espacio['puestos_disponibles'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $espacio['total_reservas'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $espacio['horas_utilizadas'] }} h</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-2 font-bold text-blue-700 bg-blue-100 rounded-full shadow-sm">{{ $espacio['promedio'] }}%</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-2 rounded-full text-xs font-semibold shadow-sm
                                                {{ $espacio['estado_utilizacion'] == 'Óptimo' ? 'bg-green-100 text-green-700 border border-green-200' :
                                                ($espacio['estado_utilizacion'] == 'Medio uso' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : 'bg-red-100 text-red-700 border border-red-200') }}">
                                                {{ $espacio['estado_utilizacion'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <i class="mb-2 text-4xl fas fa-search"></i>
                                                <p class="text-lg font-medium">No se encontraron espacios</p>
                                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
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
                <div class="mb-6 p-4 rounded-lg bg-gradient-to-r from-green-50 to-green-100 border border-green-200">
                    <div class="flex items-center">
                        <i class="mr-3 text-2xl text-green-600 fas fa-history"></i>
                        <div>
                            <h3 class="text-lg font-bold text-green-800">Histórico de Reservas</h3>
                            <p class="text-sm text-green-700">Consulta el historial completo de reservas de espacios con filtros avanzados</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 border-b border-gray-300 pb-3">Histórico de Reservas de Espacios</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('reportes.espacios.export', 'excel') }}?{{ http_build_query(request()->all()) }}&fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}"
                            class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700">
                            <i class="mr-2 fas fa-file-excel"></i>Exportar Excel
                        </a>
                        <a href="{{ route('reportes.espacios.export', 'pdf') }}?{{ http_build_query(request()->all()) }}&fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}"
                            class="px-4 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700">
                            <i class="mr-2 fas fa-file-pdf"></i>Exportar PDF
                        </a>
                    </div>
                </div>

                <div class="p-4 mb-6 text-sm text-blue-700 rounded-lg bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200">
                    <i class="mr-2 fas fa-info-circle"></i>
                    Mostrando reservas del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al
                    {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left bg-white rounded-lg shadow-sm">
                        <thead class="bg-white border-b border-gray-400">
                            <tr class="text-xs text-gray-600 uppercase">
                                <th class="px-6 py-4 font-semibold">Fecha</th>
                                <th class="px-6 py-4 font-semibold">Hora Inicio</th>
                                <th class="px-6 py-4 font-semibold">Hora Fin</th>
                                <th class="px-6 py-4 font-semibold">Espacio</th>
                                <th class="px-6 py-4 font-semibold">Tipo</th>
                                <th class="px-6 py-4 font-semibold">Piso</th>
                                <th class="px-6 py-4 font-semibold">Facultad</th>
                                <th class="px-6 py-4 font-semibold">Profesor/Solicitante</th>
                                <th class="px-6 py-4 font-semibold">Horas</th>
                                <th class="px-6 py-4 font-semibold">Duración</th>
                                <th class="px-6 py-4 font-semibold">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($historico as $reserva)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 font-semibold text-gray-800">{{ $reserva['fecha'] }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $reserva['hora_inicio'] }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $reserva['hora_fin'] }}</td>
                                    <td class="px-6 py-4 font-semibold text-blue-600">{{ $reserva['espacio'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full shadow-sm">
                                            {{ $reserva['tipo_espacio'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $reserva['piso'] }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $reserva['facultad'] }}</td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="font-medium">{{ $reserva['usuario'] }}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full shadow-sm
                                                {{ $reserva['tipo_usuario'] == 'Profesor' ? 'bg-blue-100 text-blue-700 border border-blue-200' :
                                                ($reserva['tipo_usuario'] == 'Estudiante' ? 'bg-green-100 text-green-700 border border-green-200' :
                                                    'bg-purple-100 text-purple-700 border border-purple-200') }}">
                                                {{ $reserva['tipo_usuario'] }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-orange-600">{{ $reserva['horas_utilizadas'] }}h
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $reserva['duracion'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-2 text-xs font-semibold rounded-full shadow-sm
                                            {{ $reserva['estado'] == 'Activa' ? 'bg-green-100 text-green-700 border border-green-200' :
                                            ($reserva['estado'] == 'Cancelada' ? 'bg-red-100 text-red-700 border border-red-200' :
                                                'bg-yellow-100 text-yellow-700 border border-yellow-200') }}">
                                            {{ $reserva['estado'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="mb-2 text-4xl fas fa-history"></i>
                                            <p class="text-lg font-medium">No se encontraron reservas</p>
                                            <p class="text-sm">No hay registros de reservas para el período seleccionado</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Datos desde el backend
        const datosGrafico = {
            labels: @json($labels_grafico),
            data: @json($data_grafico),
            dataReservas: @json($data_reservas_grafico),
            resumen: @json($resumen)
        };

        // Variables de estado
        let diaSeleccionado = @json($diaActual);

        // Variables de filtros
        let filtros = {
            busqueda: @json($busqueda),
            tipoEspacio: @json($tipoEspacioFiltro),
            piso: @json($pisoFiltro),
            estado: @json($estadoFiltro),
            fechaInicio: @json($fechaInicio),
            fechaFin: @json($fechaFin)
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



    </script>
</x-app-layout>
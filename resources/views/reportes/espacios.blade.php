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
                <div class="text-2xl font-bold text-gray-800" data-kpi="total-espacios">{{ $total_espacios }}</div>
                <div class="mt-1 text-xs text-green-600">Espacios registrados</div>
            </div>
            <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow">
                <div class="text-sm text-gray-500">Promedio utilización</div>
                <div class="text-2xl font-bold text-gray-800" data-kpi="promedio-utilizacion">{{ $promedio_utilizacion }}%</div>
                <div class="mt-1 text-xs text-blue-600">Este mes</div>
            </div>
            <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow">
                <div class="text-sm text-gray-500">Total reservas</div>
                <div class="text-2xl font-bold text-gray-800" data-kpi="total-reservas">{{ $total_reservas }}</div>
                <div class="mt-1 text-xs text-orange-600">Este mes</div>
            </div>
            <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow">
                <div class="text-sm text-gray-500">Espacios activos</div>
                <div class="text-2xl font-bold text-gray-800" data-kpi="espacios-ocupados">{{ $espacios_ocupados }}/{{ $total_espacios }}</div>
                <div class="mt-1 text-xs text-purple-600" data-kpi="porcentaje-ocupacion">
                    {{ $total_espacios > 0 ? round(($espacios_ocupados / $total_espacios) * 100) : 0 }}% ocupación
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-4 mb-6 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Filtros de Búsqueda</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
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
                            <option value="{{ $tipo }}" {{ $tipoEspacioFiltro == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Piso</label>
                    <select id="filtro-piso" class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                        <option value="">Todos los pisos</option>
                        @foreach($pisosDisponibles as $numero => $piso)
                            <option value="{{ $numero }}" {{ $pisoFiltro == $numero ? 'selected' : '' }}>Piso {{ $numero }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500">Estado</label>
                    <select id="filtro-estado" class="w-full rounded-md border-gray-300 shadow-sm h-[40px] px-4">
                        <option value="">Todos los estados</option>
                        @foreach($estadosDisponibles as $estado)
                            <option value="{{ $estado }}" {{ $estadoFiltro == $estado ? 'selected' : '' }}>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="button" onclick="limpiarFiltros()" class="flex-1 px-4 h-[40px] py-2 text-sm font-semibold text-white transition bg-gray-600 rounded hover:bg-gray-700">
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
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-700">Resumen Detallado por Espacio</h2>
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
                        <table class="min-w-full text-sm text-left" id="tabla-resumen">
                            <thead>
                                <tr class="text-xs text-gray-500 uppercase bg-gray-50">
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
                                @forelse($resumen as $espacio)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
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
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
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
                        <button type="button" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setRango(0,14)">Todo el día</button>
                        <button type="button" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setRango(0,5)">Mañana (8-14h)</button>
                        <button type="button" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setRango(6,9)">Tarde (14-18h)</button>
                        <button type="button" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setRango(10,14)">Noche (18-23h)</button>
                    </div>
                    <div class="mt-1 text-xs" id="rango-mostrando"></div>
                </div>
                
                <div class="p-4 mb-6 bg-white rounded-lg shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-700">Ocupación por Horarios de la Semana</h2>
                        <div class="flex gap-2">
                            <button type="button" onclick="exportarHorariosExcel()" 
                                    class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700">
                                <i class="mr-2 fas fa-file-excel"></i>Exportar Excel
                            </button>
                            <button type="button" onclick="exportarHorariosPDF()" 
                                    class="px-4 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700">
                                <i class="mr-2 fas fa-file-pdf"></i>Exportar PDF
                            </button>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="chartHorarios"></canvas>
                    </div>
                </div>
                
                <div class="p-4 bg-white rounded-lg shadow">
                    <h2 class="mb-4 font-semibold text-gray-700">Detalle de Ocupación por Rangos de Horarios</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead>
                                <tr class="text-xs text-gray-500 uppercase bg-gray-50" id="thead-horarios"></tr>
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
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-700">Histórico de Reservas de Espacios</h2>
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
                
                <div class="p-4 mb-4 text-sm text-gray-600 bg-blue-50 rounded-lg">
                    <i class="mr-2 fas fa-info-circle"></i>
                    Mostrando reservas del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase bg-gray-50">
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2">Hora Inicio</th>
                                <th class="px-4 py-2">Hora Fin</th>
                                <th class="px-4 py-2">Espacio</th>
                                <th class="px-4 py-2">Tipo</th>
                                <th class="px-4 py-2">Piso</th>
                                <th class="px-4 py-2">Facultad</th>
                                <th class="px-4 py-2">Usuario</th>
                                <th class="px-4 py-2">Tipo Usuario</th>
                                <th class="px-4 py-2">Horas</th>
                                <th class="px-4 py-2">Duración</th>
                                <th class="px-4 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historico as $reserva)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-2 font-semibold">{{ $reserva['fecha'] }}</td>
                                    <td class="px-4 py-2">{{ $reserva['hora_inicio'] }}</td>
                                    <td class="px-4 py-2">{{ $reserva['hora_fin'] }}</td>
                                    <td class="px-4 py-2 font-semibold text-blue-600">{{ $reserva['espacio'] }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded">
                                            {{ $reserva['tipo_espacio'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $reserva['piso'] }}</td>
                                    <td class="px-4 py-2">{{ $reserva['facultad'] }}</td>
                                    <td class="px-4 py-2">{{ $reserva['usuario'] }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded
                                            {{ $reserva['tipo_usuario'] == 'Profesor' ? 'bg-blue-100 text-blue-700' : 
                                               ($reserva['tipo_usuario'] == 'Estudiante' ? 'bg-green-100 text-green-700' : 
                                               'bg-purple-100 text-purple-700') }}">
                                            {{ $reserva['tipo_usuario'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 font-semibold text-orange-600">{{ $reserva['horas_utilizadas'] }}h</td>
                                    <td class="px-4 py-2">{{ $reserva['duracion'] }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded
                                            {{ $reserva['estado'] == 'Activa' ? 'bg-green-100 text-green-700' : 
                                               ($reserva['estado'] == 'Cancelada' ? 'bg-red-100 text-red-700' : 
                                               'bg-yellow-100 text-yellow-700') }}">
                                            {{ $reserva['estado'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-4 py-8 text-center text-gray-500">
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
            ocupacionHorarios: @json($ocupacionHorarios),
            resumen: @json($resumen)
        };

        // Configuración de horarios
        const modulosDia = [
            '08:10-09:00', '09:10-10:00', '10:10-11:00', '11:10-12:00', '12:10-13:00',
            '13:10-14:00', '14:10-15:00', '15:10-16:00', '16:10-17:00', '17:10-18:00',
            '18:10-19:00', '19:10-20:00', '20:10-21:00', '21:10-22:00', '22:10-23:00'
        ];

        // Variables de estado
        let diaSeleccionado = @json($diaActual);
        let moduloInicio = 0;
        let moduloFin = modulosDia.length - 1;
        
        // Variables de filtros
        let filtros = {
            busqueda: @json($busqueda),
            tipoEspacio: @json($tipoEspacioFiltro),
            piso: @json($pisoFiltro),
            estado: @json($estadoFiltro)
        };

        // Variables para gráficos
        let chartUtilizacion, chartReservas, chartHorarios;

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            inicializarGraficos();
            inicializarFiltros();
            inicializarFiltrosDinamicos();
            actualizarModuloLabels();
            actualizarRangoText();
            
            // Asegurar que se muestren todos los datos inicialmente
            setTimeout(() => {
                actualizarDatosFiltrados();
            }, 100);
        });

        function inicializarGraficos() {
            if (datosGrafico.labels.length > 0) {
                crearGraficoUtilizacion();
                crearGraficoReservas();
            }
            crearGraficoHorarios();
            renderizarTablaHorarios();
        }

        function crearGraficoUtilizacion() {
            const ctx = document.getElementById('chartUtilizacion');
            if (!ctx) return;
            
            if (chartUtilizacion) chartUtilizacion.destroy();

            chartUtilizacion = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: datosGrafico.labels.slice(0, 20),
                    datasets: [{
                        label: 'Porcentaje de utilización',
                        data: datosGrafico.data.slice(0, 20),
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
                        legend: { display: false }
                    }
                }
            });
        }

        function crearGraficoReservas() {
            const ctx = document.getElementById('chartReservas');
            if (!ctx) return;
            
            if (chartReservas) chartReservas.destroy();

            chartReservas = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: datosGrafico.labels.slice(0, 10),
                    datasets: [{
                        data: datosGrafico.dataReservas.slice(0, 10),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)', 'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)',
                            'rgba(199, 199, 199, 0.8)', 'rgba(83, 102, 255, 0.8)',
                            'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)'
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
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }

        function crearGraficoHorarios() {
            const ctx = document.getElementById('chartHorarios');
            if (!ctx) return;
            
            if (chartHorarios) chartHorarios.destroy();

            const espaciosAMostrar = obtenerEspaciosAMostrar();
            const datasets = espaciosAMostrar.map((espacioId, index) => {
                const color = `hsl(${index * 360 / Math.max(espaciosAMostrar.length, 1)}, 70%, 50%)`;
                return {
                    label: obtenerNombreEspacio(espacioId),
                    data: modulosDia.slice(moduloInicio, moduloFin + 1).map((_, moduloIndex) => {
                        const moduloReal = moduloInicio + moduloIndex + 1;
                        return datosGrafico.ocupacionHorarios[espacioId]?.[diaSeleccionado]?.[moduloReal] || 0;
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
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }

        function renderizarTablaHorarios() {
            const thead = document.getElementById('thead-horarios');
            const tbody = document.getElementById('tbody-horarios');
            
            if (!thead || !tbody) return;
            
            // Crear encabezado
            thead.innerHTML = '<th class="px-4 py-2">Espacio</th>';
            for (let i = moduloInicio; i <= moduloFin; i++) {
                thead.innerHTML += `<th class="px-4 py-2 text-center">${modulosDia[i]}</th>`;
            }
            
            // Crear filas de datos
            const espaciosAMostrar = obtenerEspaciosAMostrar();
            tbody.innerHTML = '';
            
            espaciosAMostrar.forEach(espacioId => {
                const row = document.createElement('tr');
                row.innerHTML = `<td class="px-4 py-2 font-semibold">${obtenerNombreEspacio(espacioId)}</td>`;
                
                for (let i = moduloInicio; i <= moduloFin; i++) {
                    const moduloReal = i + 1;
                    const ocupacion = datosGrafico.ocupacionHorarios[espacioId]?.[diaSeleccionado]?.[moduloReal] || 0;
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

        function obtenerEspaciosAMostrar() {
            // Obtener espacios filtrados del resumen
            const espaciosFiltrados = datosGrafico.resumen.filter(espacio => {
                const cumpleBusqueda = !filtros.busqueda || 
                    espacio.nombre.toLowerCase().includes(filtros.busqueda.toLowerCase()) ||
                    espacio.id_espacio.toString().includes(filtros.busqueda);
                
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
            
            // Obtener los IDs de los espacios filtrados que tienen datos de ocupación
            const espaciosConDatos = espaciosFiltrados
                .map(espacio => espacio.id_espacio)
                .filter(id => datosGrafico.ocupacionHorarios[id]);
            
            return espaciosConDatos; // Mostrar todos los espacios filtrados
        }

        function obtenerNombreEspacio(espacioId) {
            const espacio = datosGrafico.resumen.find(e => e.id_espacio === espacioId);
            if (espacio) {
                return `${espacio.nombre} (${espacioId})`;
            }
            return espacioId;
        }

        function setRango(inicio, fin) {
            moduloInicio = inicio;
            moduloFin = fin;
            document.getElementById('modulo-inicio').value = inicio;
            document.getElementById('modulo-fin').value = fin;
            actualizarModuloLabels();
            actualizarRangoText();
            actualizarGraficos();
        }

        function actualizarModuloLabels() {
            const labelInicio = document.getElementById('label-inicio');
            const labelFin = document.getElementById('label-fin');
            if (labelInicio) labelInicio.textContent = modulosDia[moduloInicio];
            if (labelFin) labelFin.textContent = modulosDia[moduloFin];
        }

        function actualizarRangoText() {
            const rangoMostrando = document.getElementById('rango-mostrando');
            if (rangoMostrando) {
                rangoMostrando.textContent = 
                    `Mostrando módulos ${moduloInicio + 1} a ${moduloFin + 1} (${modulosDia[moduloInicio]} - ${modulosDia[moduloFin]})`;
            }
        }

        function actualizarGraficos() {
            crearGraficoHorarios();
            renderizarTablaHorarios();
        }

        function inicializarFiltros() {
            // Filtro de día
        document.getElementById('filtro-dia')?.addEventListener('change', function() {
            diaSeleccionado = this.value;
                actualizarGraficos();
            });



            // Filtros de rango
        document.getElementById('modulo-inicio')?.addEventListener('input', function() {
            moduloInicio = parseInt(this.value);
            if (moduloInicio > moduloFin) moduloFin = moduloInicio;
            document.getElementById('modulo-fin').value = moduloFin;
                actualizarModuloLabels();
                actualizarRangoText();
                actualizarGraficos();
        });

        document.getElementById('modulo-fin')?.addEventListener('input', function() {
            moduloFin = parseInt(this.value);
            if (moduloFin < moduloInicio) moduloInicio = moduloFin;
            document.getElementById('modulo-inicio').value = moduloInicio;
                actualizarModuloLabels();
                actualizarRangoText();
                actualizarGraficos();
            });
        }

        // Funciones para filtros dinámicos
        function inicializarFiltrosDinamicos() {
            // Event listeners para filtros dinámicos
            document.getElementById('filtro-busqueda')?.addEventListener('input', function() {
                filtros.busqueda = this.value;
                actualizarDatosFiltrados();
            });

            document.getElementById('filtro-tipo-espacio')?.addEventListener('change', function() {
                filtros.tipoEspacio = this.value;
                actualizarDatosFiltrados();
            });

            document.getElementById('filtro-piso')?.addEventListener('change', function() {
                filtros.piso = this.value;
                actualizarDatosFiltrados();
            });

            document.getElementById('filtro-estado')?.addEventListener('change', function() {
                filtros.estado = this.value;
                actualizarDatosFiltrados();
            });
        }

        function limpiarFiltros() {
            document.getElementById('filtro-busqueda').value = '';
            document.getElementById('filtro-tipo-espacio').value = '';
            document.getElementById('filtro-piso').value = '';
            document.getElementById('filtro-estado').value = '';
            
            filtros = {
                busqueda: '',
                tipoEspacio: '',
                piso: '',
                estado: ''
            };
            
            actualizarDatosFiltrados();
        }

        function actualizarDatosFiltrados() {
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
                
                const cumpleTodos = cumpleBusqueda && cumpleTipo && cumplePiso && cumpleEstado;
                
                return cumpleTodos;
            });

            // Actualizar KPIs
            actualizarKPIs(resumenFiltrado);
            
            // Actualizar gráficos
            actualizarGraficosConFiltros(resumenFiltrado);
            
            // Actualizar tabla de resumen
            actualizarTablaResumen(resumenFiltrado);
        }

        function actualizarKPIs(resumenFiltrado) {
            const totalEspacios = resumenFiltrado.length;
            const promedioUtilizacion = resumenFiltrado.length > 0 ? 
                Math.round(resumenFiltrado.reduce((sum, espacio) => sum + parseFloat(espacio.promedio), 0) / resumenFiltrado.length) : 0;
            const totalReservas = resumenFiltrado.reduce((sum, espacio) => sum + parseInt(espacio.total_reservas), 0);
            const espaciosOcupados = resumenFiltrado.filter(espacio => espacio.estado === 'Ocupado').length;

            // Actualizar elementos del DOM
            document.querySelector('[data-kpi="total-espacios"]').textContent = totalEspacios;
            document.querySelector('[data-kpi="promedio-utilizacion"]').textContent = promedioUtilizacion + '%';
            document.querySelector('[data-kpi="total-reservas"]').textContent = totalReservas;
            document.querySelector('[data-kpi="espacios-ocupados"]').textContent = espaciosOcupados + '/' + totalEspacios;
            document.querySelector('[data-kpi="porcentaje-ocupacion"]').textContent = 
                totalEspacios > 0 ? Math.round((espaciosOcupados / totalEspacios) * 100) + '% ocupación' : '0% ocupación';
        }

        function actualizarGraficosConFiltros(resumenFiltrado) {
            // Actualizar datos para gráficos
            const labelsFiltrados = resumenFiltrado.map(espacio => espacio.nombre);
            const dataFiltrados = resumenFiltrado.map(espacio => parseFloat(espacio.promedio));
            const dataReservasFiltrados = resumenFiltrado.map(espacio => parseInt(espacio.total_reservas));

            // Actualizar gráfico de utilización
            if (chartUtilizacion) {
                chartUtilizacion.data.labels = labelsFiltrados.slice(0, 20);
                chartUtilizacion.data.datasets[0].data = dataFiltrados.slice(0, 20);
                chartUtilizacion.update();
            }

            // Actualizar gráfico de reservas
            if (chartReservas) {
                chartReservas.data.labels = labelsFiltrados.slice(0, 10);
                chartReservas.data.datasets[0].data = dataReservasFiltrados.slice(0, 10);
                chartReservas.update();
            }

            // Actualizar gráfico de horarios
            actualizarGraficos();
        }

        function actualizarTablaResumen(resumenFiltrado) {
            const tbody = document.querySelector('#tabla-resumen tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            if (resumenFiltrado.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="mb-2 text-4xl fas fa-search"></i>
                                <p class="text-lg font-medium">No se encontraron espacios</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            resumenFiltrado.forEach(espacio => {
                const row = document.createElement('tr');
                row.className = 'border-b border-gray-100 hover:bg-gray-50';
                
                const estadoClass = espacio.estado === 'Disponible' ? 'bg-green-100 text-green-700' : 
                                   espacio.estado === 'Ocupado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700';
                
                const estadoUtilizacionClass = espacio.estado_utilizacion === 'Óptimo' ? 'bg-green-100 text-green-700' : 
                                             espacio.estado_utilizacion === 'Medio uso' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700';
                
                row.innerHTML = `
                    <td class="px-4 py-2 font-semibold">${espacio.nombre} (${espacio.id_espacio})</td>
                    <td class="px-4 py-2">${espacio.tipo_espacio}</td>
                    <td class="px-4 py-2">${espacio.piso}</td>
                    <td class="px-4 py-2">${espacio.facultad}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded ${estadoClass}">${espacio.estado}</span>
                    </td>
                    <td class="px-4 py-2">${espacio.puestos_disponibles || 'N/A'}</td>
                    <td class="px-4 py-2">${espacio.total_reservas}</td>
                    <td class="px-4 py-2">${espacio.horas_utilizadas} h</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 font-bold text-blue-700 bg-blue-100 rounded">${espacio.promedio}%</span>
                    </td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${estadoUtilizacionClass}">${espacio.estado_utilizacion}</span>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        function exportarHorariosExcel() {
            // Obtener el día seleccionado
            const diaSeleccionado = document.getElementById('filtro-dia').value;
            
            // Obtener el rango de módulos
            const moduloInicio = document.getElementById('modulo-inicio').value;
            const moduloFin = document.getElementById('modulo-fin').value;
            
            // Obtener los filtros aplicados
            const busqueda = document.getElementById('filtro-busqueda').value;
            const tipoEspacio = document.getElementById('filtro-tipo-espacio').value;
            const piso = document.getElementById('filtro-piso').value;
            const estado = document.getElementById('filtro-estado').value;
            
            // Construir la URL con todos los parámetros
            const params = new URLSearchParams({
                'fecha_inicio': diaSeleccionado,
                'fecha_fin': diaSeleccionado,
                'modulo_inicio': moduloInicio,
                'modulo_fin': moduloFin,
                'busqueda': busqueda,
                'tipo_espacio': tipoEspacio,
                'piso': piso,
                'estado': estado,
                'tipo_export': 'horarios'
            });
            
            const url = `{{ route('reportes.espacios.export', 'excel') }}?${params.toString()}`;
            window.location.href = url;
        }

        function exportarHorariosPDF() {
            // Obtener el día seleccionado
            const diaSeleccionado = document.getElementById('filtro-dia').value;
            
            // Obtener el rango de módulos
            const moduloInicio = document.getElementById('modulo-inicio').value;
            const moduloFin = document.getElementById('modulo-fin').value;
            
            // Obtener los filtros aplicados
            const busqueda = document.getElementById('filtro-busqueda').value;
            const tipoEspacio = document.getElementById('filtro-tipo-espacio').value;
            const piso = document.getElementById('filtro-piso').value;
            const estado = document.getElementById('filtro-estado').value;
            
            // Construir la URL con todos los parámetros
            const params = new URLSearchParams({
                'fecha_inicio': diaSeleccionado,
                'fecha_fin': diaSeleccionado,
                'modulo_inicio': moduloInicio,
                'modulo_fin': moduloFin,
                'busqueda': busqueda,
                'tipo_espacio': tipoEspacio,
                'piso': piso,
                'estado': estado,
                'tipo_export': 'horarios'
            });
            
            const url = `{{ route('reportes.espacios.export', 'pdf') }}?${params.toString()}`;
            window.location.href = url;
        }
    </script>
</x-app-layout> 
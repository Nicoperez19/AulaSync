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
        <!-- KPIs Principales -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <div class="flex flex-col justify-between p-3 bg-white rounded-lg shadow border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-medium text-gray-600">Tipos de espacio</div>
                    <div class="p-1.5 bg-blue-100 rounded-lg">
                        <i class="text-base text-blue-600 fas fa-building"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-gray-800">{{ $total_tipos }}</div>
                <div class="text-xs text-gray-500">Categorías disponibles</div>
            </div>

            <div class="flex flex-col justify-between p-3 bg-white rounded-lg shadow border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-medium text-gray-600">Promedio utilización</div>
                    <div class="p-1.5 bg-green-100 rounded-lg">
                        <i class="text-base text-green-600 fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-gray-800">{{ $promedio_utilizacion }}%</div>
                <div class="text-xs text-gray-500">Eficiencia general</div>
            </div>

            <div class="flex flex-col justify-between p-3 bg-white rounded-lg shadow border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-medium text-gray-600">Total reservas</div>
                    <div class="p-1.5 bg-orange-100 rounded-lg">
                        <i class="text-base text-orange-600 fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-gray-800">{{ $total_reservas }}</div>
                <div class="text-xs text-gray-500">Este mes</div>
            </div>

            <div class="flex flex-col justify-between p-3 bg-white rounded-lg shadow border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm font-medium text-gray-600">Espacios activos</div>
                    <div class="p-1.5 bg-purple-100 rounded-lg">
                        <i class="text-base text-purple-600 fas fa-users"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-gray-800">{{ $espacios_ocupados }}/{{ $total_espacios }}</div>
                <div class="text-xs text-gray-500">
                    {{ $total_espacios > 0 ? round(($espacios_ocupados / $total_espacios) * 100) : 0 }}% ocupación
                </div>
            </div>
        </div>

        <!-- KPIs por Turno -->
        @php
            $diurnoTotal = collect($estadisticasTurnos)->avg(fn($e) => $e['diurno']['promedio'] ?? 0);
            $vespertinoTotal = collect($estadisticasTurnos)->avg(fn($e) => $e['vespertino']['promedio'] ?? 0);
            $diurnoReservas = collect($estadisticasTurnos)->sum(fn($e) => $e['diurno']['total_reservas'] ?? 0);
            $vespertinoReservas = collect($estadisticasTurnos)->sum(fn($e) => $e['vespertino']['total_reservas'] ?? 0);
            $diurnoHoras = collect($estadisticasTurnos)->sum(fn($e) => $e['diurno']['horas_utilizadas'] ?? 0);
            $vespertinoHoras = collect($estadisticasTurnos)->sum(fn($e) => $e['vespertino']['horas_utilizadas'] ?? 0);
        @endphp
        <div class="grid grid-cols-1 gap-4 mb-6 lg:grid-cols-3">
            <!-- Ocupación Diurno -->
            <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow border border-blue-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-500 rounded-lg">
                            <i class="text-lg text-white fas fa-sun"></i>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-blue-700">Turno Diurno</div>
                            <div class="text-xs text-blue-600">08:00 - 19:00 hrs</div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-blue-700">{{ number_format($diurnoTotal, 1) }}%</div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-blue-200">
                    <div class="text-xs text-blue-600">
                        <i class="fas fa-calendar-check mr-1"></i>{{ $diurnoReservas }} reservas
                    </div>
                    <div class="text-xs text-blue-600">
                        <i class="fas fa-clock mr-1"></i>{{ number_format($diurnoHoras, 1) }}h
                    </div>
                </div>
            </div>

            <!-- Ocupación Vespertino -->
            <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow border border-purple-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-purple-500 rounded-lg">
                            <i class="text-lg text-white fas fa-moon"></i>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-purple-700">Turno Vespertino</div>
                            <div class="text-xs text-purple-600">19:00 - 23:00 hrs</div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-purple-700">{{ number_format($vespertinoTotal, 1) }}%</div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-purple-200">
                    <div class="text-xs text-purple-600">
                        <i class="fas fa-calendar-check mr-1"></i>{{ $vespertinoReservas }} reservas
                    </div>
                    <div class="text-xs text-purple-600">
                        <i class="fas fa-clock mr-1"></i>{{ number_format($vespertinoHoras, 1) }}h
                    </div>
                </div>
            </div>

            <!-- Comparativa -->
            <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg shadow border border-gray-200">
                <div class="flex items-center gap-2 mb-3">
                    <div class="p-2 bg-gray-500 rounded-lg">
                        <i class="text-lg text-white fas fa-balance-scale"></i>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-700">Comparativa</div>
                        <div class="text-xs text-gray-600">Diferencia de turnos</div>
                    </div>
                </div>
                <div class="space-y-2">
                    @php
                        $diff = $diurnoTotal - $vespertinoTotal;
                        $mayorTurno = $diff > 0 ? 'Diurno' : 'Vespertino';
                    @endphp
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Mayor uso:</span>
                        <span class="text-sm font-bold {{ $diff > 0 ? 'text-blue-700' : 'text-purple-700' }}">
                            {{ $mayorTurno }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Diferencia:</span>
                        <span class="text-sm font-bold text-gray-700">{{ number_format(abs($diff), 1) }}%</span>
                    </div>
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
                            <h3 class="text-lg font-bold text-purple-800">Resumen General por Tipo de Espacio</h3>
                            <p class="text-sm text-purple-700">Análisis completo del uso y ocupación de todos los tipos de espacios disponibles</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
                    <div
                        class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg border border-blue-200 md:col-span-2">
                        <h2 class="mb-4 text-lg font-bold text-blue-800">Utilización por Tipo de Espacio</h2>
                        <div class="h-64">
                            <canvas id="chartUtilizacion"></canvas>
                        </div>
                    </div>
                    <div
                        class="p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg border border-green-200">
                        <h2 class="mb-4 text-lg font-bold text-green-800">Distribución de Reservas</h2>
                        <div class="h-64">
                            <canvas id="chartReservas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Nuevo gráfico: Comparativa Diurno vs Vespertino -->
                <div class="mb-8 p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-lg border border-purple-200">
                    <h2 class="mb-4 text-lg font-bold text-purple-800 flex items-center">
                        <i class="mr-2 fas fa-chart-bar"></i>
                        Comparativa de Ocupación: Turno Diurno vs Vespertino
                    </h2>
                    <p class="mb-4 text-sm text-purple-700">Comparación del porcentaje de uso entre horarios diurnos (8:00-19:00) y vespertinos (19:00-23:00)</p>
                    <div class="h-80">
                        <canvas id="chartTurnos"></canvas>
                    </div>
                </div>

                <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-lg border border-gray-200">
                    <h2 class="mb-6 text-xl font-bold text-gray-800 border-b border-gray-300 pb-3">Resumen Detallado por
                        Tipo de Espacio</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left bg-white rounded-lg shadow-sm">
                            <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                                <tr class="text-xs text-gray-600 uppercase">
                                    <th class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider">Tipo de Espacio</th>
                                    <th class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider">Total Espacios</th>
                                    <th class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider">Total Reservas</th>
                                    <th class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider">Horas Utilizadas</th>
                                    <th class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider">Promedio de Uso</th>
                                    <th class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($resumen as $tipo)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-semibold text-gray-800">{{ $tipo['nombre'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $tipo['total_espacios'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $tipo['total_reservas'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $tipo['horas_utilizadas'] }} h</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-2 font-bold text-blue-700 bg-blue-100 rounded-full shadow-sm">{{ $tipo['promedio'] }}%</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-2 rounded-full text-xs font-semibold shadow-sm {{ $tipo['estado'] == 'Óptimo' ? 'bg-green-100 text-green-700 border border-green-200' : ($tipo['estado'] == 'Medio uso' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : 'bg-red-100 text-red-700 border border-red-200') }}">
                                                {{ $tipo['estado'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Nueva sección: Estadísticas por Turno (Diurno/Vespertino) -->
                <div class="mt-8 p-6 bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-xl shadow-lg border border-indigo-200">
                    <h2 class="mb-6 text-xl font-bold text-indigo-800 border-b border-indigo-300 pb-3 flex items-center">
                        <i class="mr-3 text-2xl fas fa-clock"></i>
                        Estadísticas por Turno (Diurno y Vespertino)
                    </h2>
                    <p class="mb-4 text-sm text-indigo-700">Análisis de ocupación separado por horarios: Diurno (8:00 - 19:00) y Vespertino (19:00 - 23:00)</p>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left bg-white rounded-lg shadow-sm">
                            <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                                <tr class="text-xs text-gray-600 uppercase">
                                    <th class="px-4 py-3.5 text-xs font-semibold text-white uppercase tracking-wider">Tipo de Espacio</th>
                                    <th class="px-6 py-4 font-semibold text-center" colspan="3">Turno Diurno (8:00 - 19:00)</th>
                                    <th class="px-6 py-4 font-semibold text-center" colspan="3">Turno Vespertino (19:00 - 23:00)</th>
                                </tr>
                                <tr class="text-xs text-gray-500 bg-gray-50">
                                    <th class="px-6 py-3"></th>
                                    <th class="px-4 py-3 text-center">Horas</th>
                                    <th class="px-4 py-3 text-center">Reservas</th>
                                    <th class="px-4 py-3 text-center">% Uso</th>
                                    <th class="px-4 py-3 text-center">Horas</th>
                                    <th class="px-4 py-3 text-center">Reservas</th>
                                    <th class="px-4 py-3 text-center">% Uso</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($resumen as $tipo)
                                    @php
                                        $statsDiurno = $estadisticasTurnos[$tipo['nombre']]['diurno'] ?? null;
                                        $statsVespertino = $estadisticasTurnos[$tipo['nombre']]['vespertino'] ?? null;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 font-semibold text-gray-800">{{ $tipo['nombre'] }}</td>
                                        
                                        <!-- Turno Diurno -->
                                        <td class="px-4 py-4 text-center text-gray-700">
                                            {{ $statsDiurno ? $statsDiurno['horas_utilizadas'] : 0 }} h
                                        </td>
                                        <td class="px-4 py-4 text-center text-gray-700">
                                            {{ $statsDiurno ? $statsDiurno['total_reservas'] : 0 }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-3 py-1 font-bold rounded-full shadow-sm
                                                {{ ($statsDiurno ? $statsDiurno['promedio'] : 0) >= 60 ? 'bg-green-100 text-green-700' : (($statsDiurno ? $statsDiurno['promedio'] : 0) >= 30 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                                {{ $statsDiurno ? $statsDiurno['promedio'] : 0 }}%
                                            </span>
                                        </td>
                                        
                                        <!-- Turno Vespertino -->
                                        <td class="px-4 py-4 text-center text-gray-700">
                                            {{ $statsVespertino ? $statsVespertino['horas_utilizadas'] : 0 }} h
                                        </td>
                                        <td class="px-4 py-4 text-center text-gray-700">
                                            {{ $statsVespertino ? $statsVespertino['total_reservas'] : 0 }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-3 py-1 font-bold rounded-full shadow-sm
                                                {{ ($statsVespertino ? $statsVespertino['promedio'] : 0) >= 60 ? 'bg-green-100 text-green-700' : (($statsVespertino ? $statsVespertino['promedio'] : 0) >= 30 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                                {{ $statsVespertino ? $statsVespertino['promedio'] : 0 }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 p-4 bg-white rounded-lg border border-indigo-200">
                        <h4 class="font-semibold text-indigo-800 mb-2">Información sobre los turnos:</h4>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li><i class="fas fa-sun text-yellow-500 mr-2"></i><strong>Turno Diurno:</strong> 08:00 - 19:00 (11 horas disponibles)</li>
                            <li><i class="fas fa-moon text-indigo-500 mr-2"></i><strong>Turno Vespertino:</strong> 19:00 - 23:00 (4 horas disponibles)</li>
                        </ul>
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
                            <h3 class="text-lg font-bold text-green-800">Histórico de Reservas por Tipo de Espacio</h3>
                            <p class="text-sm text-green-700">Consulta el historial completo de reservas de espacios con filtros avanzados</p>
                        </div>
                    </div>
                </div>

                <h2 class="mb-4 font-semibold text-gray-700">Historial detallado de uso de espacios</h2>
                <div class="flex flex-row items-center justify-between gap-4 mb-4">
                    <!-- Filtros a la izquierda -->
                    <div class="flex flex-row items-center gap-4">
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
                            <select id="filtro-hist-tipo"
                                class="rounded-md border-gray-300 shadow-sm h-[40px] px-4 min-w-[150px]">
                            <option value="">Todos</option>
                            @foreach($tiposEspacioDisponibles as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                        <div class="flex items-center h-full">
                        <button id="btn-hist-buscar"
                            class="px-4 h-[40px] py-2 text-sm font-semibold text-white transition bg-blue-600 rounded hover:bg-blue-700 whitespace-nowrap">Buscar</button>
                    </div>
                </div>

                    <!-- Botones de exportar a la derecha -->
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="exportarHistoricoExcel()"
                            class="btn btn-excel px-4 h-[40px] py-2 text-sm font-semibold">
                            <i class="mr-2 fas fa-file-excel"></i>Exportar Excel
                        </button>
                        <button type="button" onclick="exportarHistoricoPDF()"
                            class="btn btn-pdf px-4 h-[40px] py-2 text-sm font-semibold">
                            <i class="mr-2 fas fa-file-pdf"></i>Exportar PDF
                        </button>
                    </div>
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
                    <table class="min-w-full bg-white rounded-lg dark:bg-gray-800">
                        <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                            <tr>
                                <th
                                    class="px-4 py-3 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                    Profesor/Solicitante
                                </th>
                                <th
                                    class="px-4 py-3 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                    Espacio
                                </th>
                                <th
                                    class="px-4 py-3 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                    Fecha
                                </th>
                                <th
                                    class="px-4 py-3 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                    Hora entrada
                                </th>
                                <th
                                    class="px-4 py-3 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                    Hora salida
                                </th>
                                <th
                                    class="px-4 py-3 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                    Duración
                                </th>
                                <th
                                    class="px-4 py-3 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                    Tipo usuario
                                </th>
                                <th
                                    class="px-4 py-3 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbody-historico" class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"></tbody>
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
window.ReportesTipoEspacioConfig = {
    labelsGrafico: @json($labels_grafico),
    dataGrafico: @json($data_grafico),
    resumen: @json($resumen),
    estadisticasTurnos: @json($estadisticasTurnos ?? []),
    historicoUrl: "{{ route('reportes.tipo-espacio.historico') }}",
    exportExcelUrl: "{{ route('reportes.tipo-espacio.export', 'excel') }}",
    exportPdfUrl: "{{ route('reportes.tipo-espacio.export', 'pdf') }}"
};
</script>
@vite(['resources/js/views/reportes/tipo-espacio.js'])
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-gauge-high"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Dashboard</h2>
                    <p class="text-sm text-gray-500">Resumen general de uso de espacios</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Modal fijo de reloj digital y módulo actual -->
    <div id="modal-reloj"
        class="fixed bottom-6 right-8 z-50 bg-light-cloud-blue shadow-lg rounded-xl border border-gray-200 px-5 py-3 flex flex-col items-center gap-1 min-w-[162px] text-white">
        <div class="font-mono text-2xl font-bold text-center text-white" id="modal-hora-actual">19:51:28</div>
        <div class="mt-1 text-sm font-bold text-center text-white" id="modal-modulo-actual">Módulo actual: 12</div>
    </div>

    <div class="w-full px-8 pb-6">
    <div class="grid w-full grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-5">
            <!-- Total de reservas hoy -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-500">Total de Reservas Hoy</span>
                    <span class="p-2 text-blue-500 bg-blue-100 rounded-full"><i
                            class="text-xl fa-regular fa-calendar"></i></span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-bold text-blue-600">{{ $totalReservasHoy }}</span>
                </div>
            </div>

            <!-- % Ocupación Semanal -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-500">% Ocupación Semanal</span>
                    <span class="p-2 text-purple-500 bg-purple-100 rounded-full"><i
                            class="text-xl fa-solid fa-chart-column"></i></span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-bold text-purple-600">{{ $ocupacionSemanal }}%</span>
                </div>
            </div>

            <!-- % Salas Desocupadas -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-500">% Salas Desocupadas</span>
                    <span class="p-2 text-green-500 bg-green-100 rounded-full"><i
                            class="text-xl fa-solid fa-door-open"></i></span>
                </div>
                <div class="flex items-end gap-2">
                    @php
                        $totalSalas = ($salasOcupadas['ocupadas'] ?? 0) + ($salasOcupadas['libres'] ?? 0);
                        $porcentajeDesocupadas = $totalSalas > 0 ? round((($salasOcupadas['libres'] ?? 0) / $totalSalas) * 100, 2) : 0;
                    @endphp
                    <span class="text-3xl font-bold text-green-600">{{ $porcentajeDesocupadas }}%</span>
                </div>
            </div>
            <!-- Promedio Ocupación Mensual -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-500">Promedio Ocupación Mensual</span>
                    <span class="p-2 text-orange-500 bg-orange-100 rounded-full"><i
                            class="text-xl fa-solid fa-wave-square"></i></span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-bold text-orange-600">{{ $ocupacionMensual }}%</span>
                </div>
            </div>
            <!-- Sala Más Utilizada -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-500">Sala Más Utilizada</span>
                    <span class="p-2 text-yellow-500 bg-yellow-100 rounded-full"><i
                            class="text-xl fa-solid fa-star"></i></span>
                </div>
                <div class="flex flex-col gap-1 mt-2">
                    @if($salaMasUtilizada && $salaMasUtilizada->espacio)
                        <span class="text-xl font-bold text-yellow-600">
                            {{ $salaMasUtilizada->espacio->nombre_espacio }} ({{ $salaMasUtilizada->id_espacio }})
                        </span>
                    @else
                        <span class="text-gray-400">Sin datos</span>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Grid de gráficos secundarios -->
    <div class="grid w-full grid-cols-1 gap-8 px-8 md:grid-cols-2">
        <!-- Gráfico de barras: Uso por Día -->
        <div
            class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative widget-transition w-full">
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Gráfico de Barras: Uso por Día </h4>
            <p class="text-sm text-gray-500 mb-4 rango-fechas-grafico">Semana del {{ $usoPorDia['rango_fechas']['inicio'] }} al {{ $usoPorDia['rango_fechas']['fin'] }}</p>
            <canvas id="grafico-barras" width="500" height="300"></canvas>
        </div>

        <!-- Gráfico de línea: Promedio mensual -->
        <div
            class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative widget-transition w-full">
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Evolución semanal de ocupación </h4>
            <canvas id="grafico-mensual" width="500" height="300"></canvas>
        </div>
    </div>

    <!-- Widget de Acciones Rápidas -->
    @can('admin panel')
    <div class="px-8 mt-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-bold text-gray-700 flex items-center">
                    <i class="fas fa-bolt mr-2 text-blue-600"></i>
                    Acciones Rápidas
                </h4>
                <a href="{{ route('quick-actions.index') }}" 
                   class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                    Ver todas
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('quick-actions.crear-reserva') }}" 
                   class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors border border-green-200">
                    <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-plus text-white"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Crear Reserva</p>
                        <p class="text-sm text-gray-600">Nueva reserva rápida</p>
                    </div>
                </a>
                
                <a href="{{ route('quick-actions.gestionar-reservas') }}" 
                   class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors border border-blue-200">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Gestionar Reservas</p>
                        <p class="text-sm text-gray-600">Administrar estados</p>
                    </div>
                </a>
                
                <a href="{{ route('quick-actions.gestionar-espacios') }}" 
                   class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors border border-purple-200">
                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-building text-white"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Gestionar Espacios</p>
                        <p class="text-sm text-gray-600">Estados de espacios</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    @endcan

    <div class="flex flex-col w-full gap-8 p-8 md:p-8 md:flex-row">
        <div class="flex flex-col flex-1 gap-6">
            <div class="p-4 bg-white rounded-lg shadow-md md:p-6 dark:bg-gray-800">
                <!-- Encabezado con título y botón alineados -->
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Utilización de Espacios por Tipo
                    </h1>
                    <x-button class="inline-flex items-center gap-2 px-4 py-2 mt-3 text-sm font-medium hover:bg-red-700"
                        variant="primary" href="{{ route('reportes.tipo-espacio') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path fill-rule="evenodd"
                                d="M12.97 3.97a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06l6.22-6.22H3a.75.75 0 0 1 0-1.5h16.19l-6.22-6.22a.75.75 0 0 1 0-1.06Z"
                                clip-rule="evenodd" />
                        </svg>
                        Ver detalles
                    </x-button>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-300">
                    <div class="flex flex-wrap items-center gap-4">
                        <span id="modulo-actual-info"
                            class="inline-flex items-center gap-2 px-3 py-1 text-gray-500 dark:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span id="modulo-actual-text">Cargando módulo...</span>
                        </span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 text-gray-500 dark:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600 dark:text-gray-300"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span id="dia-actual-text">Cargando...</span>, {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-md md:p-6 dark:bg-gray-800">
                <div id="tabla-utilizacion-tipo-espacio" class="overflow-x-auto">
                    @include('partials.tabla_utilizacion_tipo_espacio', ['comparativaTipos' => $comparativaTipos])
                </div>
            </div>
        </div>
        <div
            class="flex flex-col items-center justify-center w-full md:w-[260px] lg:w-[340px] bg-white rounded-xl shadow-lg p-6 md:p-8 widget-transition flex-shrink-0 md:mt-0 mt-8">
            <h4 class="mb-4 text-lg font-bold text-center text-gray-700">Salas ocupadas / libres (hoy)</h4>
            <canvas id="grafico-circular-salas" class="mb-2 w-full max-w-[220px] h-auto aspect-square"
                style="max-width:220px;"></canvas>
            <div class="flex justify-center gap-4 mt-2">
                <div class="flex items-center gap-1">
                    <span class="inline-block w-4 h-4 rounded-full" style="background-color: #10b981;"></span>
                    <span class="text-sm text-gray-700">Libres</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="inline-block w-4 h-4 rounded-full" style="background-color: #ef4444;"></span>
                    <span class="text-sm text-gray-700">Ocupadas</span>
                </div>
            </div>
            <div id="salas-ocupadas" class="mt-4 text-2xl font-bold kpi-value" style="color:#a21caf;">
                {{ $salasOcupadas['ocupadas'] }} <span class="text-gray-400"> de </span>
                {{ $salasOcupadas['ocupadas'] + $salasOcupadas['libres'] }} <span class="text-gray-400"> en total</span>
            </div>
        </div>
    </div>

    <!-- Tablas -->
    <div class="w-full gap-8 px-4 md:px-8">
        <div class="flex flex-col gap-6 md:flex-row">
            <!-- Reservas Pendientes -->
            <div class="w-full p-8 mb-8 bg-white shadow-lg rounded-xl md:w-1/2">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 text-orange-600 bg-orange-100 rounded-full"><i
                                class="fas fa-exclamation-triangle"></i></span>
                        <h3 class="text-lg font-bold text-gray-700">Reservas Activas Pendientes</h3>
                    </div>
                    <span
                        class="px-3 py-1 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full">{{ $reservasSinDevolucion->count() }}
                        pendiente</span>
                </div>
                <div class="mb-4 text-xs text-gray-500">Reservas activas que requieren atención (sin devolver)</div>
                <div class="flex flex-col gap-4">
                    @forelse($reservasSinDevolucion as $reserva)
                        <div
                            class="flex flex-row items-center gap-6 p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 bg-gray-100 rounded-full">
                                    <i class="fas fa-user"></i>
                                </span>
                                <div>
                                    @if($reserva->run_profesor)
                                        <div class="font-semibold text-gray-800">
                                            {{ $reserva->profesor->name ?? 'Profesor no encontrado' }}</div>
                                        <div class="text-xs text-gray-500">RUN: {{ $reserva->profesor->run_profesor ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-blue-600">Tipo: Profesor</div>
                                    @elseif($reserva->run_solicitante)
                                        <div class="font-semibold text-gray-800">
                                            {{ $reserva->solicitante->nombre ?? 'Solicitante no encontrado' }}</div>
                                        <div class="text-xs text-gray-500">RUN:
                                            {{ $reserva->solicitante->run_solicitante ?? 'N/A' }}</div>
                                        <div class="text-xs text-green-600">Tipo: Solicitante</div>
                                    @else
                                        <div class="font-semibold text-gray-800">Usuario no identificado</div>
                                        <div class="text-xs text-gray-500">RUN: N/A</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Detalles de la reserva -->
                            <div class="flex flex-wrap gap-6 text-xs text-gray-600">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $reserva->espacio->id_espacio }}
                                    <span class="ml-1 text-gray-400">{{ $reserva->espacio->nombre_espacio }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }}
                                    <span class="ml-1 text-gray-400">Fecha reserva</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-clock"></i>
                                    {{ $reserva->hora }}
                                    <span class="ml-1 text-gray-400">Hora ingreso</span>
                                </div>
                            </div>
                        </div>

                    @empty
                        <div class="py-8 text-center text-gray-500">No hay reservas activas que requieran atención.
                        </div>
                    @endforelse
                </div>
            </div>
            <!-- Registro de Accesos -->
            <div class="w-full p-8 mb-8 bg-white shadow-lg rounded-xl md:w-1/2">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 text-blue-600 bg-blue-100 rounded-full"><i
                                class="fas fa-eye"></i></span>
                        <h3 class="text-lg font-bold text-gray-700">Registro de Accesos</h3>
                    </div>
                    <x-button class="inline-flex items-center gap-2 px-4 py-2 mt-3 text-sm font-medium hover:bg-red-700"
                        variant="primary" href="{{ route('reportes.accesos') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path fill-rule="evenodd"
                                d="M12.97 3.97a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06l6.22-6.22H3a.75.75 0 0 1 0-1.5h16.19l-6.22-6.22a.75.75 0 0 1 0-1.06Z"
                                clip-rule="evenodd" />
                        </svg>
                        Ver detalles
                    </x-button>
                </div>
                <div class="flex flex-col gap-4">
                    @forelse($accesosActuales as $acceso)
                        <div class="flex flex-col gap-2 p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
                            <div class="flex items-center gap-3 mb-2">
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 bg-gray-100 rounded-full"><i
                                        class="fas fa-user"></i></span>
                                <div>
                                    @if($acceso->run_profesor)
                                        <div class="font-semibold text-gray-800">
                                            {{ $acceso->profesor->name ?? 'Profesor no encontrado' }}</div>
                                        <div class="text-xs text-gray-500"><span class="mx-1">•</span> <span
                                                class="text-blue-700">{{ $acceso->profesor->email ?? 'N/A' }}</span></div>
                                        <div class="text-xs text-blue-600">Tipo: Profesor</div>
                                    @elseif($acceso->run_solicitante)
                                        <div class="font-semibold text-gray-800">
                                            {{ $acceso->solicitante->nombre ?? 'Solicitante no encontrado' }}</div>
                                        <div class="text-xs text-gray-500"><span class="mx-1">•</span> <span
                                                class="text-blue-700">{{ $acceso->solicitante->correo ?? 'N/A' }}</span></div>
                                        <div class="text-xs text-green-600">Tipo: Solicitante</div>
                                    @else
                                        <div class="font-semibold text-gray-800">Usuario no identificado</div>
                                        <div class="text-xs text-gray-500"><span class="mx-1">•</span> <span
                                                class="text-blue-700">N/A</span></div>
                                    @endif
                                </div>
                                <span class="flex items-center gap-1 ml-auto text-xs text-green-600"><span
                                        class="w-2 h-2 bg-green-400 rounded-full"></span> En curso</span>
                            </div>
                            <div
                                class="flex flex-col gap-4 p-3 mb-2 text-xs text-gray-700 rounded-md md:flex-row md:items-start md:justify-between bg-gray-50">

                                <!-- Bloque: Información del espacio -->
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span class="font-semibold">{{ $acceso->espacio->id_espacio }}</span> -
                                        {{ $acceso->espacio->nombre_espacio }}
                                    </div>
                                    <div class="text-gray-500">
                                        Piso {{ $acceso->espacio->piso->numero_piso ?? '-' }},
                                        {{ $acceso->espacio->piso->facultad->nombre_facultad ?? '' }}
                                    </div>
                                </div>

                                <!-- Bloque: Fechas y horas -->
                                <div class="flex flex-wrap gap-6 text-xs text-gray-600">
                                    <div>
                                        <span class="block text-gray-400">Fecha</span>
                                        <span
                                            class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($acceso->fecha_reserva)->format('d/m/Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-400">Entrada</span>
                                        <span class="font-semibold text-gray-800">{{ $acceso->hora }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-400">Salida</span>
                                        <span
                                            class="font-semibold text-gray-800">{{ $acceso->hora_salida ?? 'En curso' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-400">Tipo</span>
                                        <span
                                            class="font-semibold text-gray-800">{{ ucfirst($acceso->tipo_reserva) }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-500 bg-white ">
                            <i class="fas fa-info-circle text-blue-500 mb-2"></i>
                            <p class="font-medium">No hay usuarios actualmente en espacios.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <!-- Widget de Reservas canceladas o no utilizadas en una fila aparte -->
        {{-- <div class="w-full p-8 mb-8 bg-white shadow-lg rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700">Reservas canceladas o no utilizadas
                  
                </h3>
                <div>
                    <label for="filtro_fecha_no_utilizadas" class="mr-2 font-semibold">Fecha:</label>
                    <input type="date" id="filtro_fecha_no_utilizadas" name="filtro_fecha_no_utilizadas"
                        value="{{ request('filtro_fecha_no_utilizadas', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                        class="px-2 py-1 border rounded" />
                </div>
            </div>
            <div id="tabla-no-utilizadas-dia" class="overflow-x-auto">
                <!-- Aquí se cargará la tabla por AJAX -->
                @include('partials.tabla_no_utilizadas_dia', ['noUtilizadasDia' => $noUtilizadasDia ?? []])
            </div>
            <!-- Reporte de Salas Desocupadas -->
            <div class="w-full p-8 mb-8 bg-white shadow-lg rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-700">Salas Desocupadas (Hoy)</h3>
                </div>
                <div id="tabla-salas-desocupadas" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sala</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salasDesocupadas as $sala)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $sala->nombre_espacio }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $sala->ubicacion ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-4 py-2 text-center text-gray-400">No hay salas desocupadas hoy</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div> --}}
    </div>


    <div class="w-full px-8 mb-8">
        <div id="horarios-semana-container" class="p-6 mb-8 bg-white shadow-lg rounded-xl">
            <!-- Horarios de la semana -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-700">Horarios de la semana - Profesores asignados por espacio</h3>
                <x-button class="inline-flex items-center gap-2 px-4 py-2 mt-3 text-sm font-medium hover:bg-red-700"
                    variant="primary" href="{{ route('espacios.show') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd"
                            d="M12.97 3.97a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06l6.22-6.22H3a.75.75 0 0 1 0-1.5h16.19l-6.22-6.22a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                    Ver detalles
                </x-button>
            </div>
            <div id="horarios-semana-content">
                @include('layouts.partials.horarios-semana', ['horariosAgrupados' => $horariosAgrupados])

            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite(['resources/js/dashboard.js'])

<script>
    // ========================================
    // CONFIGURACIÓN DE HORARIOS DE MÓDULOS
    // ========================================
    window.horariosModulos = window.horariosModulos || {
        'lunes': {
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
        'martes': {
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
        'miercoles': {
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
        'jueves': {
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
        'viernes': {
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
    // DATOS PARA LOS GRÁFICOS
    // ========================================
    window.dashboardData = {
        usoPorDia: @json(array_values($usoPorDia['datos'] ?? [])),
        evolucionMensual: {
            dias: @json($evolucionMensual['dias'] ?? []),
            ocupacion: @json($evolucionMensual['ocupacion'] ?? [])
        },
        salasOcupadas: {
            ocupadas: @json($salasOcupadas['ocupadas'] ?? 0),
            libres: @json($salasOcupadas['libres'] ?? 0)
        }
    };
</script>

<!-- Estilos adicionales -->
<style>
    [x-cloak] {
        display: none !important;
    }

    /* Transiciones suaves para los widgets */
    .widget-transition {
        transition: all 0.3s ease-in-out;
    }

    .widget-loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .widget-updating {
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    /* Animación para los KPIs */
    .kpi-value {
        transition: all 0.3s ease;
    }

    .kpi-value.updating {
        transform: scale(1.05);
        color: #3b82f6;
    }

    /* Estilos para el indicador de auto-refresh */
    #auto-refresh-indicator {
        animation: slideInUp 0.3s ease-out;
    }

    @keyframes slideInUp {
        from {
            transform: translateY(10px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Estilos para los botones de control */
    .refresh-button {
        transition: all 0.2s ease-in-out;
    }

    .refresh-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .refresh-button:active {
        transform: translateY(0);
    }

    /* Animación para el icono de actualización */
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }
</style>
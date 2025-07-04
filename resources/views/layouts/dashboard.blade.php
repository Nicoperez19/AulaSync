<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="fa-solid fa-gauge-high text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Dashboard</h2>
                    <p class="text-gray-500 text-sm">Resumen general de uso de espacios</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Modal fijo de reloj digital y módulo actual -->
    <div id="modal-reloj"
        class="fixed bottom-6 right-8 z-50 bg-light-cloud-blue shadow-lg rounded-xl border border-gray-200 px-5 py-3 flex flex-col items-center gap-1 min-w-[162px] text-white">
        <div class="font-mono text-2xl font-bold text-white text-center" id="modal-hora-actual">19:51:28</div>
        <div class="mt-1 text-sm font-bold text-white text-center" id="modal-modulo-actual">Módulo actual: 12</div>
    </div>

    <div class="w-full p-6">
        <div class="grid w-full grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-5">
            <!-- Total de reservas hoy -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 font-semibold">Total de Reservas Hoy</span>
                    <span class="bg-blue-100 text-blue-500 rounded-full p-2"><i
                            class="fa-regular fa-calendar text-xl"></i></span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-bold text-blue-600">{{ $totalReservasHoy }}</span>
                </div>
            </div>
            <!-- % No Presentación -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 font-semibold">% No Presentación</span>
                    <span class="bg-green-100 text-green-500 rounded-full p-2"><i
                            class="fa-solid fa-user-xmark text-xl"></i></span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-bold text-green-600">{{ $porcentajeNoShow }}%</span>
                </div>
            </div>
            <!-- % Ocupación Semanal -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 font-semibold">% Ocupación Semanal</span>
                    <span class="bg-purple-100 text-purple-500 rounded-full p-2"><i
                            class="fa-solid fa-chart-column text-xl"></i></span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-bold text-purple-600">{{ $ocupacionSemanal }}%</span>
                </div>
            </div>
            <!-- Promedio Ocupación Mensual -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 font-semibold">Promedio Ocupación Mensual</span>
                    <span class="bg-orange-100 text-orange-500 rounded-full p-2"><i
                            class="fa-solid fa-wave-square text-xl"></i></span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-bold text-orange-600">{{ $ocupacionMensual }}%</span>
                </div>
            </div>
            <!-- Sala Más Utilizada -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 font-semibold">Sala Más Utilizada</span>
                    <span class="bg-yellow-100 text-yellow-500 rounded-full p-2"><i
                            class="fa-solid fa-star text-xl"></i></span>
                </div>
                <div class="flex flex-col gap-1 mt-2">
                    @if($salaMasUtilizada && $salaMasUtilizada->espacio)
                        <span
                            class="text-xl font-bold text-yellow-600">{{ $salaMasUtilizada->espacio->nombre_espacio ?? $salaMasUtilizada->id_espacio }}</span>
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
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Gráfico de Barras: Uso por Día <span
                    class="ml-2 cursor-pointer"
                    title="Muestra la cantidad de horas ocupadas por día de la semana">ℹ️</span></h4>
            <canvas id="grafico-barras" width="500" height="300"></canvas>
        </div>

        <!-- Gráfico de línea: Promedio mensual -->
        <div
            class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative widget-transition w-full">
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Evolución semanal de ocupación <span
                    class="ml-2 cursor-pointer"
                    title="Tendencia del promedio de ocupación de la semana actual">ℹ️</span></h4>
            <canvas id="grafico-mensual" width="500" height="300"></canvas>
        </div>
    </div>

    <div class="flex flex-col w-full gap-8 p-8  md:p-8 md:flex-row">
        <div class="flex flex-col flex-1 gap-6">
            <div class="p-4 bg-white rounded-lg shadow-md md:p-6 dark:bg-gray-800">
                <!-- Encabezado con título y botón alineados -->
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Utilización de Espacios por Tipo
                    </h1>
                    <x-button class="inline-flex items-center gap-2 px-4 py-2 mt-3 text-sm font-medium hover:bg-red-700"
                        variant="primary" href="{{ route('reporteria.tipo-espacio') }}">
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
    <div class=" w-full gap-8 px-4 md:px-8">
        <div class="flex flex-col gap-6 md:flex-row">
            <!-- Reservas Pendientes -->
            <div class="w-full p-8 mb-8 bg-white shadow-lg rounded-xl md:w-1/2">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 text-orange-600 bg-orange-100 rounded-full"><i
                                class="fas fa-exclamation-triangle"></i></span>
                        <h3 class="text-lg font-bold text-gray-700">Reservas Pendientes</h3>
                    </div>
                    <span
                        class="px-3 py-1 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full">{{ $reservasSinDevolucion->count() }}
                        activa</span>
                </div>
                <div class="mb-4 text-xs text-gray-500">Sin devolución de espacio</div>
                <div class="flex flex-col gap-4">
                    @forelse($reservasSinDevolucion as $reserva)
                        <div class="flex flex-col gap-2 p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
                            <div class="flex items-center gap-3 mb-2">
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 bg-gray-100 rounded-full"><i
                                        class="fas fa-user"></i></span>
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $reserva->user->name }}</div>
                                    <div class="text-xs text-gray-500">RUN: {{ $reserva->user->run }}</div>
                                </div>
                                <span
                                    class="px-3 py-1 ml-auto text-xs font-semibold text-blue-700 rounded-full bg-blue-50">Activa</span>
                            </div>
                            <div class="flex flex-wrap gap-4 mb-2 text-xs text-gray-600">
                                <div class="flex items-center gap-1"><i class="fas fa-map-marker-alt"></i>
                                    {{ $reserva->espacio->id_espacio }}<span
                                        class="ml-1 text-gray-400">{{ $reserva->espacio->nombre_espacio }}</span></div>
                                <div class="flex items-center gap-1"><i class="fas fa-calendar-alt"></i>
                                    {{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }} <span
                                        class="ml-1 text-gray-400">Fecha reserva</span></div>
                                <div class="flex items-center gap-1"><i class="fas fa-clock"></i> {{ $reserva->hora }} <span
                                        class="ml-1 text-gray-400">Hora ingreso</span></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-500">No hay reservas activas sin devolución en este momento.
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
                        variant="primary" href="{{ route('reporteria.accesos') }}">
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
                                    <div class="font-semibold text-gray-800">{{ $acceso->user->name }}</div>
                                    <div class="text-xs text-gray-500"><span class="mx-1">•</span> <span
                                            class="text-blue-700">{{ $acceso->user->email }}</span></div>
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
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-500">No hay accesos actuales.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <!-- Widget de Reservas canceladas o no utilizadas en una fila aparte -->
        <div class="w-full p-8 mb-8 bg-white shadow-lg rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700">Reservas canceladas o no utilizadas
                    <span class="ml-2 cursor-pointer"
                        title="Reservas que no fueron utilizadas o se cancelaron">ℹ️</span>
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
        </div>
    </div>


    <div class="w-full px-8 mb-8">
        <div id="horarios-semana-container" class="p-6 mb-8 bg-white shadow-lg rounded-xl">
            <!-- Horarios de la semana -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-700">Horarios de la semana - Usuarios asignados por espacio</h3>
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
<script>
    // ========================================
    // CONFIGURACIÓN Y VARIABLES GLOBALES
    // ========================================
    let autoRefreshInterval;
    let autoRefreshEnabled = true;
    let moduloActual = null;
    let moduloCheckInterval;
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

    function obtenerDiaActual() {
        const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        return dias[new Date().getDay()];
    }

    function obtenerModuloActual(hora = null) {
        const diaActual = obtenerDiaActual();
        const horaAhora = hora || new Date().toTimeString().slice(0, 8);

        if (!window.horariosModulos[diaActual]) {
            return null;
        }

        for (const [num, horario] of Object.entries(window.horariosModulos[diaActual])) {
            if (horaAhora >= horario.inicio && horaAhora < horario.fin) {
                return parseInt(num);
            }
        }
        return null;
    }

    // ========================================
    // FUNCIONES DE ACTUALIZACIÓN DE WIDGETS
    // ========================================

    // Función principal de actualización de widgets
    function actualizarWidgets(data) {
        const errores = [];

        // Actualizar KPIs con manejo individual de errores
        try {
            actualizarKPIs(data);
        } catch (error) {
            errores.push(`Error al actualizar KPIs: ${error.message}`);
        }

        // Actualizar gráficos con manejo individual
        try {
            actualizarGraficoBarras(data.usoPorDia);
        } catch (error) {
            errores.push(`Error al actualizar gráfico de uso por día: ${error.message}`);
        }

        try {
            actualizarGraficoEvolucionMensual(data.evolucionMensual);
        } catch (error) {
            errores.push(`Error al actualizar gráfico de evolución mensual: ${error.message}`);
        }

        try {
            actualizarGraficoCircularSalas(data.salasOcupadas);
        } catch (error) {
            errores.push(`Error al actualizar gráfico circular de salas: ${error.message}`);
        }

        // Actualizar horarios de la semana si hay datos
        try {
            if (data.horariosAgrupados) {
                actualizarHorariosSemanaConDatos(data.horariosAgrupados);
            }
        } catch (error) {
            errores.push(`Error al actualizar horarios de la semana: ${error.message}`);
        }

        // Ocultar indicadores de carga
        ocultarCargando();

        // Mostrar errores si los hay
        if (errores.length > 0) {
            if (errores.length === 1) {
                mostrarNotificacion(errores[0], 'error', 4000);
            } else {
                mostrarNotificacion(`${errores.length} errores en la actualización. Revisa la consola para más detalles.`, 'error', 5000);
                console.error('Errores detallados:', errores);
            }
        } else {
            console.log('Dashboard actualizado exitosamente:', new Date().toLocaleTimeString());
        }
    }

    // Función para actualizar todos los KPIs
    function actualizarKPIs(data) {
        if (!data) return;

        const kpis = [
            { id: 'ocupacion-semanal', valor: (data.ocupacionSemanal || 0) + '%' },
            { id: 'ocupacion-mensual', valor: (data.ocupacionMensual || 0) + '%' },
            { id: 'usuarios-sin-escaneo', valor: (data.usuariosSinEscaneo || 0) + ' usuarios sin registrar asistencia hoy' },
            { id: 'horas-utilizadas', valor: (data.horasUtilizadas?.utilizadas || 0) + ' / ' + (data.horasUtilizadas?.disponibles || 0) },
            { id: 'salas-ocupadas', valor: (data.salasOcupadas?.ocupadas || 0) + ' / ' + (data.salasOcupadas?.libres || 0) }
        ];

        kpis.forEach(kpi => {
            actualizarKPI(kpi.id, kpi.valor);
        });
    }

    // ========================================
    // FUNCIONES DE DETECCIÓN DE MÓDULO
    // ========================================

    function verificarCambioModulo() {
        const nuevoModulo = obtenerModuloActual();

        if (nuevoModulo !== moduloActual) {
            if (moduloActual !== null) {
                console.log(`Cambio de módulo detectado: ${moduloActual} → ${nuevoModulo}`);
                actualizarHorariosSemana();
                actualizarIndicadorModuloInfo(nuevoModulo);
            }
            moduloActual = nuevoModulo;
        }
    }

    function actualizarIndicadorModuloInfo(modulo) {
        const textoModulo = document.getElementById('modulo-actual-text');
        if (!textoModulo) return;

        const diaActual = obtenerDiaActual();
        const horarios = window.horariosModulos[diaActual];

        if (modulo && horarios && horarios[modulo]) {
            const horario = horarios[modulo];
            const inicio = horario.inicio.substring(0, 5);
            const fin = horario.fin.substring(0, 5);
            textoModulo.textContent = `Módulo ${modulo} (${inicio} - ${fin})`;
        } else {
            textoModulo.textContent = 'No hay módulo disponible';
        }
    }

    function actualizarDiaActual() {
        const textoDia = document.getElementById('dia-actual-text');
        if (!textoDia) return;

        const diaActual = obtenerDiaActual();
        const diasCapitalizados = {
            'lunes': 'Lunes',
            'martes': 'Martes',
            'miercoles': 'Miércoles',
            'jueves': 'Jueves',
            'viernes': 'Viernes',
            'sabado': 'Sábado',
            'domingo': 'Domingo'
        };

        textoDia.textContent = diasCapitalizados[diaActual] || diaActual;
    }

    function iniciarVerificacionModulo() {
        // Verificar inmediatamente
        verificarCambioModulo();

        // Verificar cada 30 segundos
        moduloCheckInterval = setInterval(verificarCambioModulo, 30000);
        console.log('Verificación de módulos iniciada');
    }

    function detenerVerificacionModulo() {
        if (moduloCheckInterval) {
            clearInterval(moduloCheckInterval);
            moduloCheckInterval = null;
            console.log('Verificación de módulos detenida');
        }
    }

    function actualizarHorariosSemana() {
        const contenedor = document.getElementById('horarios-semana-content');
        if (!contenedor) return;

        // Mostrar indicador de carga
        contenedor.innerHTML = '<div class="flex items-center justify-center p-8"><div class="w-8 h-8 border-b-2 border-blue-600 rounded-full animate-spin"></div><span class="ml-2">Actualizando horarios...</span></div>';

        fetch('/dashboard/horarios-semana')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al obtener horarios de la semana');
                }
                return response.text();
            })
            .then(html => {
                contenedor.innerHTML = html;
                console.log('Horarios de la semana actualizados');
            })
            .catch(error => {
                console.error('Error al actualizar horarios:', error);
                contenedor.innerHTML = '<div class="text-center text-red-500 p-4">Error al cargar los horarios</div>';
            });
    }

    function actualizarHorariosSemanaConDatos(horariosAgrupados) {
        const contenedor = document.getElementById('horarios-semana-content');
        if (!contenedor) return;

        // Actualizar el contenido con los datos recibidos
        console.log('Datos de horarios recibidos:', horariosAgrupados);

        // Por ahora, llamamos a la función que hace fetch para obtener el HTML actualizado
        actualizarHorariosSemana();
    }

    // ========================================
    // FUNCIONES DE ACTUALIZACIÓN INDIVIDUALES
    // ========================================

    function actualizarKPI(id, valor) {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.classList.add('updating');
            elemento.textContent = valor;
            setTimeout(() => {
                elemento.classList.remove('updating');
            }, 300);
        }
    }

    function actualizarGraficoBarras(usoPorDia) {
        if (window.graficoBarras && usoPorDia) {
            const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const datos = dias.map(dia => usoPorDia[dia] || 0);

            window.graficoBarras.data.datasets[0].data = datos;
            window.graficoBarras.update('active');
        }
    }

    function actualizarGraficoEvolucionMensual(evolucionMensual) {
        if (window.graficoMensual && evolucionMensual) {
            window.graficoMensual.data.labels = evolucionMensual.dias;
            window.graficoMensual.data.datasets[0].data = evolucionMensual.ocupacion;
            window.graficoMensual.update('active');
        }
    }

    function actualizarGraficoCircularSalas(salasOcupadas) {
        if (window.graficoCircularSalas && salasOcupadas) {
            const ocupadas = salasOcupadas.ocupadas || 0;
            const libres = salasOcupadas.libres || 0;

            window.graficoCircularSalas.data.datasets[0].data = [ocupadas, libres];
            window.graficoCircularSalas.update('active');

            // Actualizar el elemento HTML que muestra las salas ocupadas
            const elementoSalas = document.getElementById('salas-ocupadas');
            if (elementoSalas) {
                const total = ocupadas + libres;
                elementoSalas.innerHTML = `${ocupadas} <span class="text-gray-400"> de </span> ${total} <span class="text-gray-400"> en total</span>`;
            }
        }
    }

    // ========================================
    // SISTEMA DE AUTO-REFRESH MEJORADO
    // ========================================

    function iniciarAutoRefresh() {
        if (!autoRefreshEnabled) return;

        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }

        autoRefreshInterval = setInterval(function () {
            actualizarDashboard();
        }, 30000);

        console.log('Auto-refresh iniciado cada 30 segundos');
    }

    function detenerAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
            console.log('Auto-refresh detenido');
        }
    }

    function actualizarDashboard() {
        mostrarIndicadorActualizacion();

        return fetch('/dashboard/widget-data')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al obtener datos del dashboard');
                }
                return response.json();
            })
            .then(data => {
                actualizarWidgets(data);
                return data;
            })
            .catch(error => {
                console.error('Error en auto-refresh:', error);
                mostrarNotificacion('Error al actualizar el dashboard: ' + error.message, 'error');
            });
    }

    function mostrarIndicadorActualizacion() {
        let indicador = document.getElementById('auto-refresh-indicator');
        if (!indicador) {
            indicador = document.createElement('div');
            indicador.id = 'auto-refresh-indicator';
            indicador.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-3 py-1 rounded-full text-xs opacity-75 z-50';
            indicador.innerHTML = '🔄 Actualizando...';
            document.body.appendChild(indicador);
        }
        if (indicador) indicador.style.display = 'block';
        setTimeout(() => {
            if (indicador) indicador.style.display = 'none';
        }, 2000);
    }

    // ========================================
    // FUNCIONES DE CARGA Y UTILIDADES
    // ========================================

    function mostrarCargando() {
        const widgets = document.querySelectorAll('.bg-white.rounded-xl.shadow-lg');
        widgets.forEach(widget => {
            widget.classList.add('opacity-50');
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10';
            loadingDiv.innerHTML = '<div class="w-8 h-8 border-b-2 border-blue-600 rounded-full animate-spin"></div>';
            widget.style.position = 'relative';
            widget.appendChild(loadingDiv);
        });
    }

    function ocultarCargando() {
        const widgets = document.querySelectorAll('.bg-white.rounded-xl.shadow-lg');
        widgets.forEach(widget => {
            widget.classList.remove('opacity-50');
            const loadingDiv = widget.querySelector('.absolute.inset-0');
            if (loadingDiv) {
                loadingDiv.remove();
            }
        });
    }

    // ========================================
    // INICIALIZACIÓN DE GRÁFICOS
    // ========================================

    // Gráfico de barras: Uso por Día
    window.graficoBarras = new Chart(document.getElementById('grafico-barras'), {
        type: 'bar',
        data: {
            labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            datasets: [{
                label: 'Horas ocupadas',
                data: {!! json_encode(array_values($usoPorDia)) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.7)'
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad de reservas'
                    }
                }
            }
        }
    });

    // Gráfico de línea: Evolución mensual
    window.graficoMensual = new Chart(document.getElementById('grafico-mensual'), {
        type: 'line',
        data: {
            labels: {!! json_encode($evolucionMensual['dias']) !!},
            datasets: [{
                label: 'Ocupación (%)',
                data: {!! json_encode($evolucionMensual['ocupacion']) !!},
                borderColor: 'rgba(59,130,246,1)',
                backgroundColor: 'rgba(59,130,246,0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Porcentaje de ocupación'
                    }
                }
            }
        }
    });

    // Gráfico circular: Salas ocupadas/libres
    window.graficoCircularSalas = new Chart(document.getElementById('grafico-circular-salas'), {
        type: 'doughnut',
        data: {
            labels: ['Ocupadas', 'Libres'],
            datasets: [{
                data: [{{ $salasOcupadas['ocupadas'] }}, {{ $salasOcupadas['libres'] }}],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)', // rojo
                    'rgba(16, 185, 129, 0.7)' // verde
                ],
                borderWidth: 2
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const porcentaje = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + porcentaje + '%)';
                        },
                        afterLabel: function (context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const porcentajeOcupacion = ((context.dataset.data[0] / total) * 100).toFixed(1);
                            return 'Porcentaje de ocupación del día: ' + porcentajeOcupacion + '%';
                        }
                    }
                }
            }
        },
        plugins: [{
            id: 'centerText',
            beforeDraw: function (chart) {
                const { ctx, chartArea: { left, top, width, height } } = chart;
                ctx.save();

                const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                const ocupadas = chart.data.datasets[0].data[0];
                const porcentajeOcupacion = total > 0 ? ((ocupadas / total) * 100).toFixed(1) : '0';

                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = 'bold 24px Arial';
                ctx.fillStyle = '#374151';
                ctx.fillText(porcentajeOcupacion + '%', left + width / 2, top + height / 2 - 10);

                ctx.font = '14px Arial';
                ctx.fillStyle = '#6B7280';
                ctx.fillText('Ocupación', left + width / 2, top + height / 2 + 15);

                ctx.restore();
            }
        }]
    });

    // ========================================
    // INICIALIZACIÓN Y EVENT LISTENERS
    // ========================================

    document.addEventListener('DOMContentLoaded', function () {
        // Iniciar auto-refresh
        iniciarAutoRefresh();

        // Iniciar verificación de módulos
        iniciarVerificacionModulo();

        // Inicializar indicador del módulo actual
        const moduloInicial = obtenerModuloActual();
        actualizarIndicadorModuloInfo(moduloInicial);
        moduloActual = moduloInicial;

        // Inicializar día actual
        actualizarDiaActual();

        // Detener auto-refresh cuando la página no esté visible
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                detenerAutoRefresh();
                detenerVerificacionModulo();
            } else if (autoRefreshEnabled) {
                iniciarAutoRefresh();
                iniciarVerificacionModulo();
            }
        });

        // Event listener para filtro de fecha
        const input = document.getElementById('filtro_fecha_no_utilizadas');
        if (input) {
            input.addEventListener('change', function () {
                const fecha = input.value;
                fetch(`/dashboard/no-utilizadas-dia?fecha=${fecha}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('tabla-no-utilizadas-dia').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error al cargar tabla de no utilizadas:', error);
                        mostrarNotificacion('Error al cargar los datos de la tabla', 'error');
                    });
            });
        }
    });

    // Modal fijo de reloj digital y módulo actual
    function actualizarModalReloj() {
        const ahora = new Date();
        // Hora en formato 24h
        const hora = ahora.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        document.getElementById('modal-hora-actual').textContent = hora;
        // Módulo actual
        let modulo = '-';
        if (typeof obtenerModuloActual === 'function') {
            const moduloNum = obtenerModuloActual();
            if (moduloNum) modulo = moduloNum;
        }
        document.getElementById('modal-modulo-actual').textContent = 'Módulo actual: ' + modulo;
    }
    actualizarModalReloj();
    setInterval(actualizarModalReloj, 1000);
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
            transform: translateY(100%);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 0.75;
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
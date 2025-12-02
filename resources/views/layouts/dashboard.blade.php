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

    <!-- Loading Spinner Overlay -->
    <div id="dashboard-loading" class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-90" style="display: flex;">
        <div class="text-center">
            <div class="inline-block w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-4 text-lg font-semibold text-gray-700">Cargando Dashboard...</p>
        </div>
    </div>

    <!-- Modal fijo de reloj digital y módulo actual -->
    <div id="modal-reloj"
        class="fixed bottom-6 right-8 z-50 bg-light-cloud-blue shadow-lg rounded-xl border border-gray-200 px-5 py-3 flex flex-col items-center gap-1 min-w-[162px] text-white">
        <div class="font-mono text-2xl font-bold text-center text-white" id="modal-hora-actual">19:51:28</div>
        <div class="mt-1 text-sm font-bold text-center text-white" id="modal-modulo-actual">Módulo actual: 12</div>
    </div>

    <div class="w-full px-8 pb-6">
    <div class="grid w-full grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-4">
            <!-- Estadísticas de Reservas y Salas -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-visible">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-500">Estadísticas de Hoy</span>
                        <div class="group relative cursor-help">
                            <i class="text-sm text-gray-400 fa-solid fa-circle-info hover:text-blue-500 transition-colors"></i>
                            <div class="invisible group-hover:visible absolute top-full left-1/2 transform -translate-x-1/2 mt-2 w-56 p-3 bg-gray-800 text-white text-xs rounded-lg shadow-lg z-50 whitespace-normal">
                                <p class="font-semibold mb-2">Estadísticas de Hoy:</p>
                                <p class="mb-2"><strong>Total Reservas:</strong> Contabiliza todas las reservas registradas durante el día actual.</p>
                                <p class="text-gray-300"><strong>Mayor cantidad de reservas:</strong> La sala con más reservas registradas en el día.</p>
                                <p class="text-gray-300"><strong>Mayor ocupación de módulos:</strong> La sala con mayor porcentaje de ocupación calculado como (módulos utilizados / 15) × 100.</p>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-800"></div>
                            </div>
                        </div>
                    </div>
                    <span class="p-2 text-blue-500 bg-blue-100 rounded-full"><i
                            class="text-xl fa-solid fa-chart-line"></i></span>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">Total Reservas:</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $totalReservasHoy }}</span>
                    </div>
                    <div class="flex flex-row gap-4 mt-1">
                        @if($salasUtilizadas['mas_reservas'])
                            <div class="flex-1">
                                <span class="text-xs text-gray-400">Mayor cantidad de reservas:</span>
                                <div class="text-sm font-bold text-yellow-600">{{ $salasUtilizadas['mas_reservas']->espacio->id_espacio ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $salasUtilizadas['mas_reservas']->total }} reservas</div>
                            </div>
                        @endif
                        @if($salasUtilizadas['mas_ocupada'])
                            <div class="flex-1">
                                <span class="text-xs text-gray-400">Mayor ocupación de módulos:</span>
                                <div class="text-sm font-bold text-orange-600">{{ $salasUtilizadas['mas_ocupada']->espacio->id_espacio ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ number_format(($salasUtilizadas['mas_ocupada']->total / 15) * 100, 2) }}% ocupación</div>
                            </div>
                        @else
                            @if($salasUtilizadas['mas_reservas'])
                                <div class="text-xs text-gray-400">Sin datos para módulos</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- % Ocupación Semanal -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-visible">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-500">% Ocupación Semanal </span>
                        <div class="group relative cursor-help">
                            <i class="text-sm text-gray-400 fa-solid fa-circle-info hover:text-blue-500 transition-colors"></i>
                            <div class="invisible group-hover:visible absolute top-full left-1/2 transform -translate-x-1/2 mt-2 w-60 p-3 bg-gray-800 text-white text-xs rounded-lg shadow-lg z-50 whitespace-normal">
                                <p class="font-semibold mb-2">Cálculo de Ocupación Semanal:</p>
                                <p class="mb-2">Promedio de ocupación de espacios de lunes a sábado (sábado hasta las 13:00hrs).</p>
                                <p class="font-semibold text-gray-200 mb-1">Fórmula:</p>
                                <p class="bg-gray-700 p-2 rounded text-gray-100 mb-2 font-mono text-xs">Promedio del promedio de ocupación por hora (Solo Reservas)</p>
                                <p class="text-gray-300 mb-1"><strong>Diurno:</strong> 8:00 - 19:00 (11 horas)</p>
                                <p class="text-gray-300 mb-1"><strong>Vespertino:</strong> 19:00 - 23:00 (4 horas)</p>
                                <p class="text-gray-300"><strong>Sábado:</strong> 8:00 - 13:00 (5 horas)</p>
                                <p class="text-gray-300 mt-2">Se promedian todos los valores horarios de la semana. No incluye clases regulares.</p>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-800"></div>
                            </div>
                        </div>
                    </div>
                    <span class="p-2 text-purple-500 bg-purple-100 rounded-full"><i
                            class="text-xl fa-solid fa-chart-column"></i></span>
                </div>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Diurno (8-19h):</span>
                        <span id="ocupacion-semanal-diurno" class="text-lg font-bold text-purple-600">{{ is_numeric($ocupacionSemanal['diurno']) ? $ocupacionSemanal['diurno'] : 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Vespertino (19-23h):</span>
                        <span id="ocupacion-semanal-vespertino" class="text-lg font-bold text-indigo-600">{{ is_numeric($ocupacionSemanal['vespertino']) ? $ocupacionSemanal['vespertino'] : 0 }}%</span>
                    </div>
                    <div class="pt-1 mt-1 border-t border-gray-200">
                        <span class="text-xs text-gray-400">Total: </span>
                        <span id="ocupacion-semanal" class="text-2xl font-bold text-purple-700">{{ is_numeric($ocupacionSemanal['total']) ? $ocupacionSemanal['total'] : 0 }}%</span>
                    </div>
                </div>
            </div>

            <!-- % Salas Desocupadas -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-visible">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-500">% Salas de Clases Desocupadas</span>
                        <div class="group relative cursor-help">
                            <i class="text-sm text-gray-400 fa-solid fa-circle-info hover:text-green-500 transition-colors"></i>
                            <div class="invisible group-hover:visible absolute top-full left-1/2 transform -translate-x-1/2 mt-2 w-56 p-3 bg-gray-800 text-white text-xs rounded-lg shadow-lg z-50 whitespace-normal">
                                <p class="font-semibold mb-2">Cálculo de Salas Desocupadas:</p>
                                <p class="mb-2">Porcentaje de salas libres en el momento actual.</p>
                                <p class="font-semibold text-gray-200 mb-1">Fórmula:</p>
                                <p class="bg-gray-700 p-2 rounded text-gray-100 mb-2 font-mono text-xs">(Salas Libres / Total Salas) × 100</p>
                                <p class="text-gray-300">Se contabiliza en tiempo real basado en el estado actual de todas las salas.</p>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-800"></div>
                            </div>
                        </div>
                    </div>
                    <span class="p-2 text-green-500 bg-green-100 rounded-full"><i
                            class="text-xl fa-solid fa-door-open"></i></span>
                </div>
                <div class="flex flex-col gap-2">
                    @php
                        $totalSalasTotal = ($salasOcupadas['total']['ocupadas'] ?? 0) + ($salasOcupadas['total']['libres'] ?? 0);
                        $porcentajeDesocupadasTotal = $totalSalasTotal > 0 ? round((($salasOcupadas['total']['libres'] ?? 0) / $totalSalasTotal) * 100, 2) : 0;
                    @endphp
                    <div class="text-center">
                        <div class="text-5xl font-bold text-green-600">{{ $porcentajeDesocupadasTotal }}%</div>
                    </div>
                    <div class="flex items-center justify-between pt-2 mt-2 border-t border-gray-200">
                        <div class="text-center flex-1">
                            <div class="text-xs text-gray-400">Ocupadas</div>
                            <div class="text-xl font-bold text-red-600">{{ $salasOcupadas['total']['ocupadas'] ?? 0 }}</div>
                        </div>
                        <div class="text-center flex-1">
                            <div class="text-xs text-gray-400">Libres</div>
                            <div class="text-xl font-bold text-green-600">{{ $salasOcupadas['total']['libres'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Promedio Ocupación Mensual -->
            <div
                class="flex flex-col justify-between p-6 bg-white shadow-lg rounded-2xl border border-gray-100 min-h-[140px] relative overflow-visible">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-500">Promedio Ocupación Mensual</span>
                        <div class="group relative cursor-help">
                            <i class="text-sm text-gray-400 fa-solid fa-circle-info hover:text-orange-500 transition-colors"></i>
                            <div class="invisible group-hover:visible absolute top-full left-1/2 transform -translate-x-1/2 mt-2 w-60 p-3 bg-gray-800 text-white text-xs rounded-lg shadow-lg z-50 whitespace-normal">
                                <p class="font-semibold mb-2">Cálculo de Ocupación Mensual Promedio:</p>
                                <p class="mb-2">Porcentaje promedio de ocupación de espacios durante todo el mes actual (Solo Reservas).</p>
                                <p class="font-semibold text-gray-200 mb-1">Fórmula:</p>
                                <p class="bg-gray-700 p-2 rounded text-gray-100 mb-2 font-mono text-xs">(Total Horas Reservadas / Total Horas Disponibles) × 100</p>
                                <p class="text-gray-300 mb-1"><strong>Diurno:</strong> 8:00 - 19:00 (11 horas)</p>
                                <p class="text-gray-300"><strong>Vespertino:</strong> 19:00 - 23:00 (4 horas)</p>
                                <p class="text-gray-300 mt-2">Se promedia entre todos los días del mes. No incluye clases regulares.</p>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-800"></div>
                            </div>
                        </div>
                    </div>
                    <span class="p-2 text-orange-500 bg-orange-100 rounded-full"><i
                            class="text-xl fa-solid fa-wave-square"></i></span>
                </div>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Diurno (8-19h):</span>
                        <span id="ocupacion-mensual-diurno" class="text-lg font-bold text-orange-600">{{ is_numeric($ocupacionMensual['diurno']) ? $ocupacionMensual['diurno'] : 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Vespertino (19-23h):</span>
                        <span id="ocupacion-mensual-vespertino" class="text-lg font-bold text-red-600">{{ is_numeric($ocupacionMensual['vespertino']) ? $ocupacionMensual['vespertino'] : 0 }}%</span>
                    </div>
                    <div class="pt-1 mt-1 border-t border-gray-200">
                        <span class="text-xs text-gray-400">Total: </span>
                        <span id="ocupacion-mensual" class="text-2xl font-bold text-orange-700">{{ is_numeric($ocupacionMensual['total']) ? $ocupacionMensual['total'] : 0 }}%</span>
                    </div>
                </div>
            </div>
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

    <!-- Widget de Reportes -->
    @can('reportes')
    <div class="px-8 mt-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-bold text-gray-700 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                    Reportes
                </h4>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('reportes.accesos') }}"
                   class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors border border-blue-200">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-sign-in-alt text-white"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Accesos</p>
                        <p class="text-sm text-gray-600">Registros de entrada</p>
                    </div>
                </a>

                <a href="{{ route('reportes.espacios') }}"
                   class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors border border-purple-200">
                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-door-open text-white"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Espacios</p>
                        <p class="text-sm text-gray-600">Análisis por espacios</p>
                    </div>
                </a>

                <a href="{{ route('reportes.tipo-espacio') }}"
                   class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors border border-indigo-200">
                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-layer-group text-white"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Tipo Espacio</p>
                        <p class="text-sm text-gray-600">Por categoría</p>
                    </div>
                </a>

                <a href="{{ route('reportes.salas-estudio') }}"
                   class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors border border-green-200">
                    <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-book text-white"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Salas Estudio</p>
                        <p class="text-sm text-gray-600">Análisis de uso</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- Sistema de pestañas para estadísticas -->
    <div class="px-8 mt-8 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                Estadísticas Detalladas
            </h3>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6" x-data="{ activeTab: 'graficos', salasViewMode: 'individual', ocupacionViewMode: 'total' }">
                <nav class="flex space-x-4" aria-label="Tabs">
                    <button @click="activeTab = 'graficos'"
                            :class="activeTab === 'graficos' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Gráficos
                    </button>
                    <button @click="activeTab = 'utilizacion'; cargarTabUtilizacion();"
                            :class="activeTab === 'utilizacion' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-building mr-2"></i>
                        Utilización
                    </button>
                    <button @click="activeTab = 'accesos'"
                            :class="activeTab === 'accesos' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-users mr-2"></i>
                        Accesos
                    </button>
                    <button @click="activeTab = 'clases-no-realizadas'; cargarTabClasesNoRealizadas();"
                            :class="activeTab === 'clases-no-realizadas' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Clases no registradas
                    </button>
                </nav>

                <!-- Tab Content: Gráficos -->
                <div x-show="activeTab === 'graficos'" class="mt-6">
                    <div class="grid w-full grid-cols-1 gap-8 md:grid-cols-2">
                        <!-- Gráfico 1: Línea - % Ocupación por Día (INTERCAMBIADO) -->
                        <div class="p-8 bg-gray-50 rounded-xl shadow flex flex-col items-center min-h-[300px] relative widget-transition w-full">
                            <h4 class="flex items-center gap-2 mb-2 font-semibold text-gray-700">
                                <i class="fas fa-percentage text-purple-600"></i>
                                % Ocupación por Día
                                <div class="group relative inline-block">
                                    <i class="fas fa-info-circle text-gray-400 hover:text-gray-600 cursor-help text-sm"></i>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg p-2 w-60 z-50">
                                        <p class="font-semibold mb-1">Cálculo:</p>
                                        <p><strong>Total:</strong> Promedio de los porcentajes de ocupación de cada hora del día</p>
                                        <p class="mt-1"><strong>Por Turno:</strong> Ocupación del turno diurno (8-19h) y vespertino (19-23h) por separado</p>
                                        <p class="mt-1"><strong>Por Tipo:</strong> Ocupación por cada tipo de espacio (máximo de espacios ocupados / total)</p>
                                        <p class="mt-1"><strong>Por Sala:</strong> (Módulos utilizados / 15) × 100 por cada sala</p>
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                                    </div>
                                </div>
                            </h4>
                            <div class="flex gap-2 flex-wrap">
                                <button @click="ocupacionViewMode = 'total'; document.dispatchEvent(new CustomEvent('ocupacionViewModeChange', {detail: {modo: 'total'}}))" :class="{'bg-blue-500 text-white': ocupacionViewMode === 'total', 'bg-gray-200 text-gray-700': ocupacionViewMode !== 'total'}" class="px-3 py-1 rounded text-sm font-medium transition">
                                    Total
                                </button>
                                <button @click="ocupacionViewMode = 'turno'; document.dispatchEvent(new CustomEvent('ocupacionViewModeChange', {detail: {modo: 'turno'}}))" :class="{'bg-blue-500 text-white': ocupacionViewMode === 'turno', 'bg-gray-200 text-gray-700': ocupacionViewMode !== 'turno'}" class="px-3 py-1 rounded text-sm font-medium transition">
                                    Por Turno
                                </button>
                                <button @click="ocupacionViewMode = 'tipo'; document.dispatchEvent(new CustomEvent('ocupacionViewModeChange', {detail: {modo: 'tipo'}}))" :class="{'bg-blue-500 text-white': ocupacionViewMode === 'tipo', 'bg-gray-200 text-gray-700': ocupacionViewMode !== 'tipo'}" class="px-3 py-1 rounded text-sm font-medium transition">
                                    Por Tipo
                                </button>
                                <button @click="ocupacionViewMode = 'sala'; document.dispatchEvent(new CustomEvent('ocupacionViewModeChange', {detail: {modo: 'sala'}}))" :class="{'bg-blue-500 text-white': ocupacionViewMode === 'sala', 'bg-gray-200 text-gray-700': ocupacionViewMode !== 'sala'}" class="px-3 py-1 rounded text-sm font-medium transition">
                                    Por Sala
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 rango-fechas-ocupacion">Semana del {{ $ocupacionPorDia['rango_fechas']['inicio'] }} al {{ $ocupacionPorDia['rango_fechas']['fin'] }}</p>
                            <canvas id="grafico-ocupacion-por-dia" width="500" height="250"></canvas>
                        </div>

                        <!-- Gráfico 2: Línea - Salas Individuales por Día -->
                        <div class="p-8 bg-gray-50 rounded-xl shadow flex flex-col items-center min-h-[300px] relative widget-transition w-full">
                            <h4 class="flex items-center gap-2 mb-2 font-semibold text-gray-700">
                                <i class="fas fa-chart-line text-green-600"></i>
                                Reservas por Sala
                                <div class="group relative inline-block">
                                    <i class="fas fa-info-circle text-gray-400 hover:text-gray-600 cursor-help text-sm"></i>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg p-2 w-48 z-50">
                                        <p class="font-semibold mb-1">Cálculo:</p>
                                        <p><strong>Individual:</strong> Número de reservas por cada sala de forma individual.</p>
                                        <p class="mt-1"><strong>Por Tipo:</strong> Total de reservas agrupadas por tipo de espacio.</p>
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                                    </div>
                                </div>
                            </h4>
                            <div class="flex gap-2">
                                <button @click="salasViewMode = 'individual'; document.dispatchEvent(new CustomEvent('salasViewModeChange', {detail: {modo: 'individual'}}))" :class="{'bg-blue-500 text-white': salasViewMode === 'individual', 'bg-gray-200 text-gray-700': salasViewMode !== 'individual'}" class="px-3 py-1 rounded text-sm font-medium transition">
                                    Individual
                                </button>
                                <button @click="salasViewMode = 'tipo'; document.dispatchEvent(new CustomEvent('salasViewModeChange', {detail: {modo: 'tipo'}}))" :class="{'bg-blue-500 text-white': salasViewMode === 'tipo', 'bg-gray-200 text-gray-700': salasViewMode !== 'tipo'}" class="px-3 py-1 rounded text-sm font-medium transition">
                                    Por Tipo
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 rango-fechas-grafico-salas">Semana del {{ $salasUtilizadasPorDia['rango_fechas']['inicio'] }} al {{ $salasUtilizadasPorDia['rango_fechas']['fin'] }}</p>
                            <canvas id="grafico-salas-individuales" width="500" height="250"></canvas>
                        </div>

                        <!-- Gráfico 3: Barras - Cantidad de Reservas por Día (INTERCAMBIADO) -->
                        <div class="p-8 bg-gray-50 rounded-xl shadow flex flex-col items-center min-h-[300px] relative widget-transition w-full">
                            <h4 class="flex items-center gap-2 mb-2 font-semibold text-gray-700">
                                <i class="fas fa-chart-bar text-blue-600"></i>
                                Cantidad de Reservas por Día
                                <div class="group relative inline-block">
                                    <i class="fas fa-info-circle text-gray-400 hover:text-gray-600 cursor-help text-sm"></i>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg p-2 w-48 z-50">
                                        <p class="font-semibold mb-1">Cálculo:</p>
                                        <p>Muestra el número total de reservas registradas para cada día de la semana.</p>
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                                    </div>
                                </div>
                            </h4>
                            <p class="text-xs text-gray-500 mb-4 rango-fechas-grafico">Semana del {{ $usoPorDia['rango_fechas']['inicio'] }} al {{ $usoPorDia['rango_fechas']['fin'] }}</p>
                            <canvas id="grafico-reservas-por-dia" width="500" height="250"></canvas>
                        </div>

                        <!-- Gráfico 4: Barras - Disponibilidad de Salas (INTERCAMBIADO) -->
                        <div class="p-8 bg-gray-50 rounded-xl shadow flex flex-col items-center min-h-[300px] relative widget-transition w-full">
                            <h4 class="flex items-center gap-2 mb-2 font-semibold text-gray-700">
                                <i class="fas fa-chart-bar text-green-600"></i>
                                Disponibilidad de Salas
                                <div class="group relative inline-block">
                                    <i class="fas fa-info-circle text-gray-400 hover:text-gray-600 cursor-help text-sm"></i>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg p-2 w-48 z-50">
                                        <p class="font-semibold mb-1">Cálculo:</p>
                                        <p>Porcentaje promedio de salas desocupadas (disponibles) por cada día de la semana.</p>
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                                    </div>
                                </div>
                            </h4>
                            <p class="text-xs text-gray-500 mb-2">Total de salas: <strong>{{ $disponibilidadSalas['totalSalas'] }}</strong></p>
                            <p class="text-xs text-gray-500 mb-4 rango-fechas-disponibilidad">Semana del {{ $disponibilidadSalas['rango_fechas']['inicio'] }} al {{ $disponibilidadSalas['rango_fechas']['fin'] }}</p>
                            <canvas id="grafico-disponibilidad-salas" width="500" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Utilización -->
                <div x-show="activeTab === 'utilizacion'" x-cloak class="mt-6">
                    <!-- Header con información del día -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                                    Utilización de Espacios por Tipo
                                </h2>
                                <div class="flex flex-wrap items-center gap-4 text-sm">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-white rounded-lg shadow-sm">
                                        <i class="fas fa-clock text-blue-600"></i>
                                        <span id="modulo-actual-text" class="font-medium text-gray-700">Cargando módulo...</span>
                                    </span>
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-white rounded-lg shadow-sm">
                                        <i class="fas fa-calendar-day text-blue-600"></i>
                                        <span class="font-medium text-gray-700">
                                            <span id="dia-actual-text">Cargando...</span>, {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <x-button class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold shadow-md hover:shadow-lg transition-all"
                                variant="primary" href="{{ route('reportes.tipo-espacio') }}">
                                <i class="fas fa-file-alt"></i>
                                Ver Reporte Completo
                            </x-button>
                        </div>
                    </div>

                    <div class="flex flex-col xl:flex-row gap-6">
                        <!-- Panel principal de utilización -->
                        <div class="flex-1">
                            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                                <div id="tabla-utilizacion-tipo-espacio">
                                    @include('partials.tabla_utilizacion_tipo_espacio', ['comparativaTipos' => $comparativaTipos])
                                </div>
                            </div>
                        </div>

                        <!-- Widget lateral de salas -->
                        <div class="xl:w-[340px] flex-shrink-0">
                            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 sticky top-6">
                                <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-door-open text-blue-600"></i>
                                    Estado Actual de Espacios
                                </h4>
                                <div class="flex justify-center items-center mb-4">
                                    <div class="w-[220px] h-[220px]">
                                        <canvas id="grafico-circular-salas" width="220" height="220"></canvas>
                                    </div>
                                </div>
                                <div class="flex justify-center gap-6 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-4 h-4 rounded-full bg-green-500"></span>
                                        <span class="text-sm font-medium text-gray-700">Libres</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-4 h-4 rounded-full bg-red-500"></span>
                                        <span class="text-sm font-medium text-gray-700">Ocupadas</span>
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-gray-200">
                                    <div id="salas-ocupadas" class="text-center">
                                        <div class="text-3xl font-bold text-purple-600">
                                            {{ $espaciosOcupadosTotal['ocupadas'] }}
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            de {{ $espaciosOcupadosTotal['ocupadas'] + $espaciosOcupadosTotal['libres'] }} espacios en total
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Accesos -->
                <div x-show="activeTab === 'accesos'" x-cloak class="mt-6">
                    <div class="flex flex-col gap-6 md:flex-row">
                        <!-- Reservas Pendientes -->
                        <div class="w-full p-8 bg-white shadow-lg rounded-xl md:w-1/2">
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
            <div class="w-full p-8 bg-white shadow-lg rounded-xl md:w-1/2">
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
    </div>

                <!-- Tab Content: Clases No Realizadas -->
                <div x-show="activeTab === 'clases-no-realizadas'" x-cloak class="mt-6">
                    <div id="clases-no-realizadas-tab-content" class="p-8 text-center text-gray-500">
                        <div class="flex items-center justify-center">
                            <div class="w-8 h-8 border-b-2 border-blue-600 rounded-full animate-spin"></div>
                            <span class="ml-2">Cargando estadísticas...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                <h3 class="text-lg font-bold text-gray-700">Horarios del día actual - Módulos actuales</h3>
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

        <!-- Clases No Realizadas Hoy -->
        <div class="p-6 bg-white shadow-lg rounded-xl">
            <div class="flex items-center gap-3 mb-6">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                <h3 class="text-lg font-bold text-gray-700">Clases No Realizadas Hoy</h3>
                @if($clasesNoRealizadasHoy->count() > 0)
                    <span class="inline-flex items-center justify-center px-3 py-1 ml-auto text-sm font-bold text-white bg-red-600 rounded-full">{{ $clasesNoRealizadasHoy->count() }}</span>
                @endif
            </div>

            @if($clasesNoRealizadasHoy->count() > 0)
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                    @foreach($clasesNoRealizadasHoy as $clase)
                        <div class="p-3 transition-shadow border-l-4 rounded shadow-sm hover:shadow-md bg-red-50 border-red-500">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h4 class="font-semibold text-gray-800 text-xs leading-tight flex-1">
                                    {{ $clase->asignatura->nombre_asignatura ?? 'N/A' }}
                                </h4>
                                @if($clase->estado === 'recuperada')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold text-white bg-green-600 flex-shrink-0">
                                        <i class="fas fa-check mr-0.5"></i> Recuperada
                                    </span>
                                @endif
                            </div>

                            <div class="text-xs text-gray-700 space-y-1">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user text-red-600 w-3"></i>
                                    <span class="font-medium truncate">{{ $clase->profesor->name ?? 'N/A' }}</span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <i class="fas fa-door-open text-red-600 w-3"></i>
                                    <span class="font-medium">{{ $clase->espacio->id_espacio ?? 'N/A' }}</span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <i class="fas fa-clock text-red-600 w-3"></i>
                                    @php
                                        $idModulo = $clase->id_modulo;
                                        if ($idModulo) {
                                            $modulos = explode(',', $idModulo);
                                            $primerModulo = trim($modulos[0]);
                                            $partes = explode('.', $primerModulo);
                                            $numeroModulo = $partes[1] ?? $primerModulo;
                                        } else {
                                            $numeroModulo = 'N/A';
                                        }
                                    @endphp
                                    <span class="font-medium">Módulo {{ $numeroModulo }}</span>
                                </div>

                                @if($clase->motivo)
                                    <div class="pt-1 mt-1 border-t border-red-200">
                                        <p class="text-xs text-gray-600 italic">{{ $clase->motivo }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-center justify-center p-8">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-5xl text-green-600 mb-3"></i>
                        <p class="text-lg font-medium text-gray-700">No hay clases no registradas hoy</p>
                        <p class="text-sm text-gray-500 mt-1">Todas las clases programadas se han realizado correctamente</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    // ========================================
    // VARIABLES GLOBALES
    // ========================================
    let autoRefreshInterval = null;
    let autoRefreshEnabled = true;
    let moduloActual = null;
    let moduloCheckInterval = null;

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
    // LAZY LOADING DE TABS
    // ========================================

    window.tabLoaded = {
        utilizacion: false,
        accesos: false,
        clasesNoRealizadas: false
    };

    function cargarTabUtilizacion() {
        if (window.tabLoaded.utilizacion) return;

        const contenedor = document.getElementById('tabla-utilizacion-tipo-espacio');
        if (!contenedor) return;

        contenedor.innerHTML = '<div class="flex items-center justify-center p-8"><div class="w-8 h-8 border-b-2 border-blue-600 rounded-full animate-spin"></div><span class="ml-2">Cargando datos...</span></div>';

        fetch('/dashboard/utilizacion-data')
            .then(response => response.json())
            .then(data => {
                actualizarTablaUtilizacionTipoEspacio(data.comparativaTipos);
                if (data.salasOcupadas && window.graficoCircularSalas) {
                    actualizarGraficoCircularSalas(data.salasOcupadas);
                }
                window.tabLoaded.utilizacion = true;
            })
            .catch(error => {
                console.error('Error cargando datos de utilización:', error);
                contenedor.innerHTML = '<div class="p-4 text-center text-red-500">Error al cargar los datos</div>';
            });
    }

    function cargarTabAccesos() {
        if (window.tabLoaded.accesos) return;

        const contenedor = document.getElementById('accesos-tab-content');
        if (!contenedor) return;

        contenedor.innerHTML = '<div class="flex items-center justify-center p-8"><div class="w-8 h-8 border-b-2 border-blue-600 rounded-full animate-spin"></div><span class="ml-2">Cargando accesos...</span></div>';

        fetch('/dashboard/accesos-data')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                contenedor.innerHTML = html;
                window.tabLoaded.accesos = true;
            })
            .catch(error => {
                console.error('Error cargando datos de accesos:', error);
                contenedor.innerHTML = '<div class="p-4 text-center text-red-500">Error al cargar los accesos: ' + error.message + '</div>';
            });
    }

    function cargarTabClasesNoRealizadas() {
        if (window.tabLoaded.clasesNoRealizadas) return;

        const contenedor = document.getElementById('clases-no-realizadas-tab-content');
        if (!contenedor) return;

        contenedor.innerHTML = '<div class="flex items-center justify-center p-8"><div class="w-8 h-8 border-b-2 border-blue-600 rounded-full animate-spin"></div><span class="ml-2">Cargando estadísticas...</span></div>';

        fetch('/dashboard/clases-no-realizadas-data')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                contenedor.innerHTML = html;
                window.tabLoaded.clasesNoRealizadas = true;
                
                // Ejecutar scripts inyectados
                const scripts = contenedor.querySelectorAll('script');
                scripts.forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.src) {
                        newScript.src = script.src;
                    } else {
                        newScript.textContent = script.textContent;
                    }
                    document.body.appendChild(newScript);
                    document.body.removeChild(newScript);
                });
            })
            .catch(error => {
                console.error('Error cargando clases no realizadas:', error);
                contenedor.innerHTML = '<div class="p-4 text-center text-red-500">Error al cargar los datos: ' + error.message + '</div>';
            });
    }

    // ========================================
    // DATOS PARA LOS GRÁFICOS
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
            console.warn('Error actualizando gráfico circular:', error);
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

        // Actualizar tabla de utilización por tipo de espacio
        try {
            if (data.comparativaTipos) {
                actualizarTablaUtilizacionTipoEspacio(data.comparativaTipos);
            }
        } catch (error) {
            errores.push(`Error al actualizar tabla de utilización por tipo: ${error.message}`);
        }

        // Ocultar indicadores de carga
        ocultarCargando();

        // Mostrar errores si los hay
        if (errores.length > 0) {
            if (errores.length === 1) {
                mostrarNotificacion(errores[0], 'error', 4000);
            } else {
                mostrarNotificacion(`${errores.length} errores en la actualización.`, 'error', 5000);
            }
        }
    }

    // Función para actualizar todos los KPIs
    function actualizarKPIs(data) {
        if (!data) return;

        // Actualizar ocupación semanal (con turnos)
        if (data.ocupacionSemanal) {
            const elemDiurno = document.getElementById('ocupacion-semanal-diurno');
            const elemVespertino = document.getElementById('ocupacion-semanal-vespertino');
            const elemTotal = document.getElementById('ocupacion-semanal');

            // Ensure we're getting numeric values, not objects
            const diurno = typeof data.ocupacionSemanal.diurno === 'number' ? data.ocupacionSemanal.diurno : 0;
            const vespertino = typeof data.ocupacionSemanal.vespertino === 'number' ? data.ocupacionSemanal.vespertino : 0;
            const total = typeof data.ocupacionSemanal.total === 'number' ? data.ocupacionSemanal.total : 0;

            if (elemDiurno) elemDiurno.textContent = diurno + '%';
            if (elemVespertino) elemVespertino.textContent = vespertino + '%';
            if (elemTotal) elemTotal.textContent = total + '%';
        }

        // Actualizar ocupación mensual (con turnos)
        if (data.ocupacionMensual) {
            const elemDiurno = document.getElementById('ocupacion-mensual-diurno');
            const elemVespertino = document.getElementById('ocupacion-mensual-vespertino');
            const elemTotal = document.getElementById('ocupacion-mensual');

            // Ensure we're getting numeric values, not objects
            const diurno = typeof data.ocupacionMensual.diurno === 'number' ? data.ocupacionMensual.diurno : 0;
            const vespertino = typeof data.ocupacionMensual.vespertino === 'number' ? data.ocupacionMensual.vespertino : 0;
            const total = typeof data.ocupacionMensual.total === 'number' ? data.ocupacionMensual.total : 0;

            if (elemDiurno) elemDiurno.textContent = diurno + '%';
            if (elemVespertino) elemVespertino.textContent = vespertino + '%';
            if (elemTotal) elemTotal.textContent = total + '%';
        }

        // Actualizar usuarios sin escaneo
        if (data.usuariosSinEscaneo !== undefined) {
            const elem = document.getElementById('usuarios-sin-escaneo');
            const value = typeof data.usuariosSinEscaneo === 'number' ? data.usuariosSinEscaneo : 0;
            if (elem) elem.textContent = value + ' usuarios sin registrar asistencia hoy';
        }
    }

    // ========================================
    // FUNCIONES DE DETECCIÓN DE MÓDULO
    // ========================================

    function obtenerDiaActual() {
        const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        return dias[new Date().getDay()];
    }

    function obtenerModuloActual(hora = null) {
        const diaActual = obtenerDiaActual();
        const horaAhora = hora || new Date().toTimeString().slice(0, 8);

        if (!window.horariosModulos || !window.horariosModulos[diaActual]) {
            return null;
        }

        for (const [num, horario] of Object.entries(window.horariosModulos[diaActual])) {
            if (horaAhora >= horario.inicio && horaAhora <= horario.fin) {
                return parseInt(num);
            }
        }
        return null;
    }

    function verificarCambioModulo() {
        const nuevoModulo = obtenerModuloActual();

        if (nuevoModulo !== moduloActual) {
            if (moduloActual !== null) {

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

    }

    function detenerVerificacionModulo() {
        if (moduloCheckInterval) {
            clearInterval(moduloCheckInterval);
            moduloCheckInterval = null;

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
            })
            .catch(error => {
                console.error('Error al actualizar horarios:', error);
                contenedor.innerHTML = '<div class="p-4 text-center text-red-500">Error al cargar los horarios: ' + error.message + '</div>';
            });
    }

    function actualizarHorariosSemanaConDatos(horariosAgrupados) {
        const contenedor = document.getElementById('horarios-semana-content');
        if (!contenedor) return;

        // Actualizar el contenido con los datos recibidos


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
            const datos = dias.map(dia => usoPorDia.datos[dia] || 0);

            window.graficoBarras.data.datasets[0].data = datos;
            window.graficoBarras.update('active');

            // Actualizar el rango de fechas si está disponible
            if (usoPorDia.rango_fechas) {
                const rangoElement = document.querySelector('.rango-fechas-grafico');
                if (rangoElement) {
                    rangoElement.textContent = `Semana del ${usoPorDia.rango_fechas.inicio} al ${usoPorDia.rango_fechas.fin}`;
                }
            }
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
        if (window.graficoCircularSalas && salasOcupadas && salasOcupadas.total) {
            const ocupadas = salasOcupadas.total.ocupadas || 0;
            const libres = salasOcupadas.total.libres || 0;
            
            // Solo actualizar si tenemos datos válidos (evitar resetear a 0)
            if (ocupadas >= 0 && libres >= 0 && (ocupadas + libres) > 0) {
                window.graficoCircularSalas.data.datasets[0].data = [libres, ocupadas];
                window.graficoCircularSalas.update('active');

                // Actualizar el elemento HTML que muestra las salas ocupadas
                const elementoSalas = document.getElementById('salas-ocupadas');
                if (elementoSalas) {
                    const total = ocupadas + libres;
                    elementoSalas.innerHTML = `
                        <div class="text-3xl font-bold text-purple-600">
                            ${ocupadas}
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            de ${total} salas en total
                        </div>
                    `;
                }
            }
        }
    }

    function actualizarTablaUtilizacionTipoEspacio(comparativaTipos) {
        const contenedor = document.getElementById('tabla-utilizacion-tipo-espacio');
        if (!contenedor || !comparativaTipos) return;

        // Mapeo de iconos por tipo de espacio
        const iconos = {
            'Aula': 'fa-graduation-cap',
            'Laboratorio': 'fa-flask',
            'Auditorio': 'fa-volume-up',
            'Sala de Estudio': 'fa-book',
            'Taller': 'fa-tools',
            'Sala de Reuniones': 'fa-comments',
            'Sala de Clases': 'fa-chalkboard-teacher'
        };

        if (!Array.isArray(comparativaTipos) || comparativaTipos.length === 0) {
            contenedor.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-lg font-medium text-gray-500">No hay datos de utilización de espacios</p>
                    <p class="mt-1 text-sm text-gray-400">Los datos aparecerán aquí cuando haya espacios registrados</p>
                </div>
            `;
            return;
        }

        let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';

        comparativaTipos.forEach(data => {
            const icono = iconos[data.nombre] || iconos[data.tipo] || 'fa-door-closed';
            const nombre = data.nombre || data.tipo || 'Tipo no especificado';
            const porcentaje = data.porcentaje || 0;
            const ocupados = data.ocupados || 0;
            const total = data.total || 0;

            html += `
                <div class="flex flex-col justify-between p-4 bg-white rounded-lg shadow border border-gray-200 min-h-[120px]">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100">
                                <i class="fas ${icono} text-xl text-gray-400"></i>
                            </span>
                            <span class="font-semibold text-gray-900">${nombre}</span>
                        </div>
                        <span class="text-xs font-bold text-gray-500">${porcentaje}%</span>
                    </div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-gray-500">${ocupados} de ${total} ocupadas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="absolute left-0 top-0 h-2 rounded-full" style="width: ${porcentaje}%; background: #8C0303;"></div>
                        </div>
                        <span class="ml-2 text-xs text-gray-600 font-semibold">${ocupados}/${total}</span>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        contenedor.innerHTML = html;
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


    }

    function detenerAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;

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
    // INICIALIZACIÓN DE GRÁFICOS (LAZY LOADING)
    // ========================================

    // Variables globales para los gráficos
    window.graficoBarras = null;
    window.graficoMensual = null;
    window.graficoCircularSalas = null;
    window.chartsInitialized = false;

    // Función para inicializar gráficos de forma diferida
    function initializeCharts() {
        if (window.chartsInitialized) return;

        try {
            // ============================================
            // GRÁFICO 1: Barras - Cantidad de Reservas por Día
            // ============================================
            const canvasReservasPerDia = document.getElementById('grafico-reservas-por-dia');
            if (canvasReservasPerDia && !window.graficoReservasPerDia) {
                window.graficoReservasPerDia = new Chart(canvasReservasPerDia, {
                    type: 'bar',
                    data: {
                        labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                        datasets: [{
                            label: 'Cantidad de reservas',
                            data: {!! json_encode(array_values($usoPorDia['datos'])) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Reservas: ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            }

            // ============================================
            // GRÁFICO 2: Línea - Salas Individuales por Día
            // ============================================
            const canvasSalasIndividuales = document.getElementById('grafico-salas-individuales');
            if (canvasSalasIndividuales && !window.graficoSalasIndividuales) {
                // Paleta de colores vibrante y variada
                const colores = [
                    'rgb(59, 130, 246)',    // Azul vibrante
                    'rgb(239, 68, 68)',     // Rojo coral
                    'rgb(34, 197, 94)',     // Verde esmeralda
                    'rgb(168, 85, 247)',    // Púrpura profundo
                    'rgb(245, 158, 11)',    // Ámbar dorado
                    'rgb(59, 130, 246)',    // Cian brillante
                    'rgb(236, 72, 153)',    // Rosa vibrante
                    'rgb(249, 115, 22)',    // Naranja quemado
                    'rgb(6, 182, 212)',     // Turquesa brillante
                    'rgb(139, 92, 246)',    // Violeta profundo
                    'rgb(34, 197, 94)',     // Verde Lima
                    'rgb(239, 68, 68)',     // Rojo claro
                    'rgb(244, 63, 94)',     // Fresa
                    'rgb(251, 146, 60)',    // Naranja suave
                    'rgb(107, 114, 128)',   // Gris pizarra
                    'rgb(236, 72, 153)',    // Fuchsia
                ];
                
                // Datos para vista individual
                const datasetsIndividual = [];
                const salasData = {!! json_encode($salasUtilizadasPorDia['salas'] ?? []) !!};
                if (salasData && Array.isArray(salasData)) {
                    salasData.forEach((sala, index) => {
                    const color = colores[index % colores.length];
                    datasetsIndividual.push({
                        label: 'Sala: ' + sala.sala,
                        data: sala.datos,
                        borderColor: color,
                        backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.08)'),
                        fill: false,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: color,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 2.5
                    });
                });
                }

                // Datos para vista por tipo
                const datasetsPorTipo = [];
                const tiposData = {!! json_encode($salasPorTipoPorDia['tipos'] ?? []) !!};
                if (tiposData && Array.isArray(tiposData)) {
                    tiposData.forEach((tipo, index) => {
                        const color = colores[index % colores.length];
                        datasetsPorTipo.push({
                            label: 'Tipo: ' + tipo.tipo,
                            data: tipo.datos,
                            borderColor: color,
                            backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.08)'),
                            fill: false,
                            tension: 0.4,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBackgroundColor: color,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            borderWidth: 2.5
                        });
                    });
                }

                window.graficoSalasIndividuales = new Chart(canvasSalasIndividuales, {
                    type: 'line',
                    data: {
                        labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                        datasets: datasetsIndividual
                    },
                    options: {
                        responsive: false,
                        plugins: {
                            legend: { position: 'bottom', maxHeight: 50 },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + ' reservas';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });

                // Guardar los datos para cambiar de modo
                window.salasDatasets = {
                    individual: datasetsIndividual,
                    tipo: datasetsPorTipo
                };
            }

            // ============================================
            // GRÁFICO 3: Línea - % Ocupación por Día
            // ============================================
            const canvasOcupacionPerDia = document.getElementById('grafico-ocupacion-por-dia');
            if (canvasOcupacionPerDia && !window.graficoOcupacionPerDia) {
                const ocupacionTotalData = {!! json_encode(array_values($ocupacionPorDia['datos'] ?? [])) !!};
                const ocupacionPorTurnoData = {!! json_encode($ocupacionPorTurno['datos'] ?? []) !!};

                // Datasets para vista Total
                const datasetTotal = [{
                    label: 'Ocupación (%)',
                    data: ocupacionTotalData,
                    borderColor: 'rgba(168, 85, 247, 1)',
                    backgroundColor: 'rgba(168, 85, 247, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }];

                // Datasets para vista Por Turno
                const datasetsTurno = [
                    {
                        label: 'Diurno (%)',
                        data: ocupacionPorTurnoData.diurno,
                        borderColor: 'rgba(251, 191, 36, 1)',
                        backgroundColor: 'rgba(251, 191, 36, 0.2)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(251, 191, 36, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Vespertino (%)',
                        data: ocupacionPorTurnoData.vespertino,
                        borderColor: 'rgba(168, 85, 247, 1)',
                        backgroundColor: 'rgba(168, 85, 247, 0.2)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ];

                // Datasets para vista Por Tipo
                const coloresTipo = [
                    'rgba(59, 130, 246, 1)',     // Azul
                    'rgba(239, 68, 68, 1)',      // Rojo
                    'rgba(34, 197, 94, 1)',      // Verde
                    'rgba(168, 85, 247, 1)',     // Púrpura
                    'rgba(245, 158, 11, 1)',     // Ámbar
                    'rgba(6, 182, 212, 1)',      // Cian
                    'rgba(236, 72, 153, 1)',     // Rosa
                    'rgba(249, 115, 22, 1)'      // Naranja
                ];
                
                const datasetsTipo = [];
                const tipoOcupacionData = {!! json_encode($ocupacionPorTipo['tipos'] ?? []) !!};
                tipoOcupacionData.forEach((tipo, index) => {
                    const colorBase = coloresTipo[index % coloresTipo.length];
                    
                    datasetsTipo.push({
                        label: tipo.tipo + ' (%)',
                        data: tipo.datos,
                        borderColor: colorBase,
                        backgroundColor: 'rgba(0, 0, 0, 0)',
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: colorBase,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 2.5
                    });
                });

                // Datasets para vista Por Sala - Usar los mismos colores que en "Individual" de Reservas por Sala
                const coloresSala = [
                    'rgb(59, 130, 246)',    // Azul vibrante
                    'rgb(239, 68, 68)',     // Rojo coral
                    'rgb(34, 197, 94)',     // Verde esmeralda
                    'rgb(168, 85, 247)',    // Púrpura profundo
                    'rgb(245, 158, 11)',    // Ámbar dorado
                    'rgb(59, 130, 246)',    // Cian brillante
                    'rgb(236, 72, 153)',    // Rosa vibrante
                    'rgb(249, 115, 22)',    // Naranja quemado
                    'rgb(6, 182, 212)',     // Turquesa brillante
                    'rgb(139, 92, 246)',    // Violeta profundo
                    'rgb(34, 197, 94)',     // Verde Lima
                    'rgb(239, 68, 68)',     // Rojo claro
                    'rgb(244, 63, 94)',     // Fresa
                    'rgb(251, 146, 60)',    // Naranja suave
                    'rgb(107, 114, 128)',   // Gris pizarra
                    'rgb(236, 72, 153)',    // Fuchsia
                ];
                
                const datasetsSala = [];
                const salaOcupacionData = {!! json_encode($ocupacionPorSala['salas'] ?? []) !!};
                salaOcupacionData.forEach((sala, index) => {
                    const colorBase = coloresSala[index % coloresSala.length];
                    
                    datasetsSala.push({
                        label: sala.sala,
                        data: sala.datos,
                        borderColor: colorBase,
                        backgroundColor: 'rgba(0, 0, 0, 0)',
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: colorBase,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        borderWidth: 2.5,
                        pointHoverBorderWidth: 3
                    });
                });

                window.graficoOcupacionPerDia = new Chart(canvasOcupacionPerDia, {
                    type: 'line',
                    data: {
                        labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                        datasets: datasetTotal
                    },
                    options: {
                        responsive: false,
                        plugins: {
                            legend: { display: window.graficoOcupacionPerDia ? false : true },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const porcentaje = context.parsed.y;
                                        const label = context.dataset.label;
                                        
                                        // Si es una sala (label empieza con TH-), calcular módulos del día
                                        if (label && label.startsWith('TH-')) {
                                            const modulosDia = Math.round((porcentaje / 100) * 15);
                                            return label + ': ' + porcentaje.toFixed(2) + '% (' + modulosDia + ' módulos)';
                                        }
                                        
                                        return label + ': ' + porcentaje.toFixed(2) + '%';
                                    }
                                }
                            }
                        },
                        onHover: (event, activeElements) => {
                            if (window.graficoOcupacionPerDia) {
                                const canvas = window.graficoOcupacionPerDia.canvas;
                                if (activeElements.length > 0) {
                                    const datasetIndex = activeElements[0].datasetIndex;
                                    window.graficoOcupacionPerDia.data.datasets.forEach((dataset, i) => {
                                        dataset.borderWidth = i === datasetIndex ? 4 : 2.5;
                                        dataset.pointRadius = i === datasetIndex ? 7 : 5;
                                        dataset.opacity = i === datasetIndex ? 1 : 0.3;
                                    });
                                } else {
                                    window.graficoOcupacionPerDia.data.datasets.forEach((dataset) => {
                                        dataset.borderWidth = 2.5;
                                        dataset.pointRadius = 5;
                                        dataset.opacity = 1;
                                    });
                                }
                                window.graficoOcupacionPerDia.update('none');
                            }
                        },
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
                        }
                    }
                });

                // Guardar los datos para cambiar de modo
                window.ocupacionDatasets = {
                    total: datasetTotal,
                    turno: datasetsTurno,
                    tipo: datasetsTipo,
                    sala: datasetsSala
                };
            }

            // ============================================
            // GRÁFICO 4: Barras - Disponibilidad de Salas
            // ============================================
            const canvasDisponibilidad = document.getElementById('grafico-disponibilidad-salas');
            if (canvasDisponibilidad && !window.graficoDisponibilidad) {
                const datosDisponibilidad = {!! json_encode($disponibilidadSalas['datos'] ?? []) !!};

                window.graficoDisponibilidad = new Chart(canvasDisponibilidad, {
                    type: 'bar',
                    data: {
                        labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                        datasets: [{
                            label: 'Disponibilidad (%)',
                            data: datosDisponibilidad,
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: false,
                        indexAxis: 'x',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Disponible: ' + context.parsed.y.toFixed(2) + '%';
                                    }
                                }
                            }
                        },
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
                        }
                    }
                });
            }

            // Gráfico circular: TODOS los espacios ocupados/libres
            const canvasCircular = document.getElementById('grafico-circular-salas');
            if (canvasCircular && !window.graficoCircularSalas) {
                const libresTotal = @json($espaciosOcupadosTotal['libres'] ?? 0);
                const ocupadasTotal = @json($espaciosOcupadosTotal['ocupadas'] ?? 0);
                
                window.graficoCircularSalas = new Chart(canvasCircular, {
                    type: 'doughnut',
                    data: {
                        labels: ['Libres', 'Ocupadas'],
                        datasets: [{
                            data: [libresTotal, ocupadasTotal],
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.7)', // verde para libres
                                'rgba(239, 68, 68, 0.7)' // rojo para ocupadas
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
            }

            window.chartsInitialized = true;
            console.log('✓ Charts initialized successfully');
            
            // Ocultar spinner después de que los gráficos estén listos
            setTimeout(function() {
                const loadingElement = document.getElementById('dashboard-loading');
                if (loadingElement) {
                    loadingElement.style.opacity = '0';
                    loadingElement.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => {
                        loadingElement.style.display = 'none';
                        console.log('✓ Loading spinner hidden');
                    }, 300);
                }
            }, 200);
        } catch (error) {
            console.error('Error initializing charts:', error);
            // Ocultar spinner incluso si hay error
            const loadingElement = document.getElementById('dashboard-loading');
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
        }
    }

    // ============================================
    // EVENT LISTENERS PARA TOGGLES DE GRÁFICOS
    // ============================================
    
    // Toggle de Salas: Individual vs Por Tipo
    document.addEventListener('salasViewModeChange', function(e) {
        if (window.graficoSalasIndividuales && window.salasDatasets) {
            const modo = e.detail.modo;
            window.graficoSalasIndividuales.data.datasets = window.salasDatasets[modo];
            window.graficoSalasIndividuales.update();
        }
    });

    // Toggle de Ocupación: Total vs Por Turno vs Por Tipo
    document.addEventListener('ocupacionViewModeChange', function(e) {
        if (window.graficoOcupacionPerDia) {
            const modo = e.detail.modo;
            
            // Si ya tenemos los datos, usarlos
            if (window.ocupacionDatasets && window.ocupacionDatasets[modo]) {
                window.graficoOcupacionPerDia.data.datasets = window.ocupacionDatasets[modo];
                window.graficoOcupacionPerDia.options.plugins.legend.display = (modo === 'turno' || modo === 'tipo');
                window.graficoOcupacionPerDia.update();
            } else if (modo === 'turno' || modo === 'tipo') {
                // Cargar datos via AJAX
                const tipoData = modo === 'turno' ? 'ocupacion_turno' : 'ocupacion_tipo';
                const facultad = new URLSearchParams(window.location.search).get('facultad') || '';
                const piso = new URLSearchParams(window.location.search).get('piso') || '';
                
                fetch(`/dashboard/graficos-ajax?tipo=${tipoData}&facultad=${facultad}&piso=${piso}`)
                    .then(response => response.json())
                    .then(data => {
                        if (modo === 'turno') {
                            const datasetsTurno = [
                                {
                                    label: 'Diurno (%)',
                                    data: data.datos.diurno,
                                    borderColor: 'rgba(251, 191, 36, 1)',
                                    backgroundColor: 'rgba(251, 191, 36, 0.2)',
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: 'rgba(251, 191, 36, 1)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7
                                },
                                {
                                    label: 'Vespertino (%)',
                                    data: data.datos.vespertino,
                                    borderColor: 'rgba(168, 85, 247, 1)',
                                    backgroundColor: 'rgba(168, 85, 247, 0.2)',
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7
                                }
                            ];
                            window.graficoOcupacionPerDia.data.datasets = datasetsTurno;
                        } else if (modo === 'tipo') {
                            const coloresTipo = [
                                'rgba(59, 130, 246, 1)',
                                'rgba(239, 68, 68, 1)',
                                'rgba(34, 197, 94, 1)',
                                'rgba(168, 85, 247, 1)',
                                'rgba(245, 158, 11, 1)',
                                'rgba(6, 182, 212, 1)',
                                'rgba(236, 72, 153, 1)',
                                'rgba(249, 115, 22, 1)'
                            ];
                            const datasetsTipo = [];
                            data.tipos.forEach((tipo, index) => {
                                const colorBase = coloresTipo[index % coloresTipo.length];
                                datasetsTipo.push({
                                    label: tipo.tipo + ' (%)',
                                    data: tipo.datos,
                                    borderColor: colorBase,
                                    backgroundColor: colorBase.replace(')', ', 0.15)'),
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: colorBase,
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    borderWidth: 2.5
                                });
                            });
                            window.graficoOcupacionPerDia.data.datasets = datasetsTipo;
                        }
                        window.graficoOcupacionPerDia.options.plugins.legend.display = true;
                        window.graficoOcupacionPerDia.update();
                    })
                    .catch(error => console.error('Error cargando datos:', error));
            }
        }
    });

    // ============================================
    // HOVER EFFECTS PARA LÍNEAS
    // ============================================
    
    // Efecto de hover para el gráfico de salas individuales - se inicializa en DOMContentLoaded principal

    // ========================================
    // INICIALIZACIÓN Y EVENT LISTENERS
    // ========================================

    document.addEventListener('DOMContentLoaded', function () {
        console.log('✓ DOM Content Loaded');
        
        // Inicializar gráficos con un pequeño delay para mejorar la carga inicial
        setTimeout(initializeCharts, 100);

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
                        mostrarNotificacion('Error al cargar los datos de la tabla', 'error');
                    });
            });
        }

        // ============================================
        // HOVER EFFECTS PARA LÍNEAS
        // ============================================
        
        // Efecto de hover para el gráfico de salas individuales
        if (window.graficoSalasIndividuales) {
            const canvasSalasIndividuales = document.getElementById('grafico-salas-individuales');
            if (canvasSalasIndividuales) {
                canvasSalasIndividuales.addEventListener('mousemove', function(evt) {
                    const points = window.graficoSalasIndividuales.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                    const datasets = window.graficoSalasIndividuales.data.datasets;
                    
                    datasets.forEach((dataset, index) => {
                        dataset.borderWidth = 1;
                        const color = dataset.borderColor;
                        dataset.borderColor = color.replace(/[\d.]+\)$/, '0.2)');
                    });
                    
                    if (points.length > 0) {
                        const activeDatasetIndex = points[0].datasetIndex;
                        if (datasets[activeDatasetIndex]) {
                            datasets[activeDatasetIndex].borderWidth = 3;
                            const color = datasets[activeDatasetIndex].borderColor;
                            datasets[activeDatasetIndex].borderColor = color.replace(/[\d.]+\)$/, '1)');
                        }
                    }
                    
                    window.graficoSalasIndividuales.update('none');
                });

                canvasSalasIndividuales.addEventListener('mouseleave', function() {
                    const datasets = window.graficoSalasIndividuales.data.datasets;
                    datasets.forEach((dataset) => {
                        dataset.borderWidth = 2;
                        const color = dataset.borderColor;
                        dataset.borderColor = color.replace(/[\d.]+\)$/, '1)');
                    });
                    window.graficoSalasIndividuales.update('none');
                });
            }
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

</x-app-layout>

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

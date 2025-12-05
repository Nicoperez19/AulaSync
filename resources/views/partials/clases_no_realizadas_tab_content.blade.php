<script>
    window.diasDataClases = @json($diasDelMes ?? []);
</script>
<div class="space-y-6" x-data="{ 
    vistaDetalle: 'semana',
    modalAbierto: false,
    diaSeleccionado: null,
    diasData: window.diasDataClases || {},
    abrirModal(diaKey) {
        if (this.diasData[diaKey]) {
            this.diaSeleccionado = {
                fecha: diaKey,
                ...this.diasData[diaKey]
            };
            this.modalAbierto = true;
            document.body.style.overflow = 'hidden';
        }
    },
    cerrarModal() {
        this.modalAbierto = false;
        this.diaSeleccionado = null;
        document.body.style.overflow = 'auto';
    }
}">
    <!-- Modal de detalle del día - Centrado y mejor posicionado -->
    <div x-show="modalAbierto" 
         x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="background-color: rgba(0,0,0,0.5);"
         @click.self="cerrarModal()"
         @keydown.escape.window="cerrarModal()">
        
        <!-- Contenido del modal -->
        <div x-show="modalAbierto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-xl shadow-2xl w-full max-w-lg flex flex-col"
             style="max-height: calc(100vh - 2rem);"
             @click.stop>
            
            <template x-if="diaSeleccionado">
                <div class="flex flex-col max-h-full">
                    <!-- Header fijo -->
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex-shrink-0 rounded-t-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-white">Detalle del día</h3>
                                <p class="text-purple-200 text-sm" x-text="diaSeleccionado.fecha"></p>
                            </div>
                            <button @click="cerrarModal()" class="text-white hover:text-purple-200 transition-colors p-1">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Resumen - Fijo -->
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex-shrink-0">
                        <div class="grid gap-4 text-center" :class="diaSeleccionado.por_realizar > 0 ? 'grid-cols-4' : 'grid-cols-3'">
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <p class="text-2xl font-bold text-green-600" x-text="diaSeleccionado.realizadas"></p>
                                <p class="text-xs text-gray-500">Realizadas</p>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <p class="text-2xl font-bold text-red-600" x-text="diaSeleccionado.no_realizadas"></p>
                                <p class="text-xs text-gray-500">No Realizadas</p>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm" x-show="diaSeleccionado.por_realizar > 0">
                                <p class="text-2xl font-bold text-yellow-600" x-text="diaSeleccionado.por_realizar || 0"></p>
                                <p class="text-xs text-gray-500">Por Realizar</p>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <p class="text-2xl font-bold text-blue-600" x-text="diaSeleccionado.recuperadas"></p>
                                <p class="text-xs text-gray-500">Recuperadas</p>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de clases no realizadas - Scrollable con altura mínima y máxima -->
                    <div class="px-6 py-4 overflow-y-auto flex-1 min-h-0" style="max-height: 50vh;">
                        <template x-if="diaSeleccionado.clases_no_realizadas_detalle && diaSeleccionado.clases_no_realizadas_detalle.length > 0">
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-3 flex items-center sticky top-0 bg-white py-2 z-10 -mx-6 px-6 border-b border-gray-100 shadow-sm">
                                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Clases No Realizadas
                                    <span class="ml-2 text-sm font-normal text-gray-500" x-text="'(' + diaSeleccionado.clases_no_realizadas_detalle.length + ')'}"></span>
                                </h4>
                                <div class="space-y-3">
                                    <template x-for="(clase, index) in diaSeleccionado.clases_no_realizadas_detalle" :key="index">
                                        <div class="p-4 bg-white rounded-lg border-l-4 border-red-400 shadow-sm hover:shadow-md transition-shadow">
                                            <div class="flex justify-between items-start gap-3">
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-gray-800 truncate" x-text="clase.asignatura || 'Asignatura no disponible'"></p>
                                                    <p class="text-sm text-gray-600 mt-1 flex items-center">
                                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                        <span x-text="clase.profesor || 'Profesor no disponible'"></span>
                                                    </p>
                                                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                                                        <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span x-text="(clase.modulos || 1) + ' módulo' + ((clase.modulos || 1) > 1 ? 's' : '')"></span>
                                                            <template x-if="clase.hora">
                                                                <span class="ml-1 text-gray-500" x-text="'(' + clase.hora + ')'"></span>
                                                            </template>
                                                        </span>
                                                        <span class="px-2 py-1 rounded text-xs font-medium"
                                                              :class="{
                                                                  'bg-blue-100 text-blue-700': clase.estado === 'recuperada',
                                                                  'bg-orange-100 text-orange-700': clase.estado === 'pendiente',
                                                                  'bg-red-100 text-red-700': clase.estado === 'no_realizada'
                                                              }"
                                                              x-text="clase.estado === 'recuperada' ? 'Recuperada' : (clase.estado === 'pendiente' ? 'Pendiente' : 'No realizada')">
                                                        </span>
                                                    </div>
                                                </div>
                                                <a :href="'/clases-no-realizadas?reagendar_id=' + clase.id"
                                                   class="flex-shrink-0 px-3 py-2 bg-purple-600 text-white text-xs font-medium rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-1 shadow-sm"
                                                   x-show="clase.estado !== 'recuperada' && clase.id">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    Reagendar
                                                </a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        <template x-if="!diaSeleccionado.clases_no_realizadas_detalle || diaSeleccionado.clases_no_realizadas_detalle.length === 0">
                            <div class="text-center py-8">
                                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="font-medium text-gray-700">¡Todas las clases fueron realizadas!</p>
                                <p class="text-sm text-gray-500 mt-1">No hay clases pendientes de reagendar</p>
                            </div>
                        </template>
                    </div>

                    <!-- Footer fijo -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center flex-shrink-0">
                        <a href="/clases-no-realizadas" 
                           class="text-sm text-purple-600 hover:text-purple-800 font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Ver todas las clases no realizadas
                        </a>
                        <button @click="cerrarModal()" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                            Cerrar
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Información del período -->
    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex items-center gap-2 text-blue-700">
            <i class="fas fa-info-circle"></i>
            <span class="font-medium">Período académico: {{ $periodo ?? 'No definido' }} | Mes: {{ \Carbon\Carbon::create($anio, $mes, 1)->translatedFormat('F Y') }}</span>
        </div>
    </div>

    @if($totalRealizadas == 0 && $totalNoRealizadas == 0)
    <!-- Mensaje cuando no hay datos -->
    <div class="p-8 bg-gray-50 rounded-lg border border-gray-200 text-center">
        <div class="flex flex-col items-center gap-4">
            <div class="p-4 bg-gray-200 rounded-full">
                <i class="fas fa-calendar-times text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-600">No hay datos de clases para este período</h3>
            <p class="text-gray-500 max-w-md">No se encontraron planificaciones de asignaturas para el período <strong>{{ $periodo ?? 'actual' }}</strong>. Esto puede deberse a que no hay horarios cargados o el período aún no tiene datos.</p>
        </div>
    </div>
    @else
    <!-- Header con estadísticas principales -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Clases Realizadas</p>
                    <p class="text-2xl font-bold text-green-600">{{ $totalRealizadas }}</p>
                    <p class="text-xs text-gray-500">{{ $porcentajeRealizadas }}% del total</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Clases No Realizadas</p>
                    <p class="text-2xl font-bold text-red-600">{{ $totalNoRealizadas }}</p>
                    <p class="text-xs text-gray-500">{{ $porcentajeNoRealizadas }}% del total</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-times text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Clases Recuperadas</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $clasesRecuperadas }}</p>
                    <p class="text-xs text-gray-500">{{ $porcentajeRecuperadas }}% de no realizadas</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-redo text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pendientes de Recuperar</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $clasesParaRecuperar }}</p>
                    <p class="text-xs text-gray-500">Por reagendar</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    @can('clases-no-realizadas.index')
    <!-- Botón de acceso rápido a gestión de clases no realizadas -->
    <div class="flex justify-end">
        <a href="{{ route('clases-no-realizadas.index') }}" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
            <i class="fas fa-tasks"></i>
            <span>Gestionar Clases No Realizadas</span>
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    @endcan

    <!-- Gráfico de comparativa -->
    <div class="p-6 bg-white rounded-lg shadow border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
            Clases Realizadas vs No Realizadas
        </h3>
        <div class="h-80">
            <canvas id="chart-clases-no-realizadas"></canvas>
        </div>
    </div>

    <!-- Sección de Detalle con tabs -->
    <div class="p-6 bg-white rounded-lg shadow border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                Detalle de Clases
            </h3>
            
            <!-- Selector de vista -->
            <div class="flex flex-wrap gap-2">
                <button @click="vistaDetalle = 'semana'" 
                        :class="vistaDetalle === 'semana' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-calendar-week"></i>
                    Semana Actual
                </button>
                <button @click="vistaDetalle = 'calendario'" 
                        :class="vistaDetalle === 'calendario' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-calendar"></i>
                    Mes Calendario
                </button>
                <button @click="vistaDetalle = 'filtrado'" 
                        :class="vistaDetalle === 'filtrado' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-filter"></i>
                    Estadísticas Filtradas
                </button>
            </div>
        </div>

        <!-- Vista: Semana Actual -->
        <div x-show="vistaDetalle === 'semana'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @php
                $hoy = \Carbon\Carbon::now();
                $inicioSemana = $hoy->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                $finSemana = $hoy->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $diasSemana = [];
                
                for ($fecha = $inicioSemana->copy(); $fecha->lte($finSemana); $fecha->addDay()) {
                    // Solo incluir hasta hoy para estadísticas reales
                    $esFuturo = $fecha->gt($hoy);
                    $diaFormato = $fecha->format('d/m');
                    $datosDelDia = $diasDelMes[$diaFormato] ?? ['realizadas' => 0, 'no_realizadas' => 0, 'recuperadas' => 0, 'por_realizar' => 0];
                    
                    $diasSemana[$diaFormato] = $datosDelDia;
                    $diasSemana[$diaFormato]['fecha'] = $fecha->copy();
                    $diasSemana[$diaFormato]['es_hoy'] = $fecha->isToday();
                    $diasSemana[$diaFormato]['es_futuro'] = $esFuturo;
                    $diasSemana[$diaFormato]['dia_nombre'] = $fecha->locale('es')->isoFormat('dddd');
                    // Sábado tiene clases hasta las 13:00hrs, domingo no tiene clases
                    $diasSemana[$diaFormato]['es_dia_sin_clases'] = $fecha->isSunday();
                    $diasSemana[$diaFormato]['es_sabado'] = $fecha->isSaturday();
                }
            @endphp
            
            <div class="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                <p class="text-sm text-purple-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Semana del <strong>{{ $inicioSemana->format('d/m/Y') }}</strong> al <strong>{{ $finSemana->format('d/m/Y') }}</strong>
                    <span class="text-purple-500 ml-2">(Sábados: clases hasta 13:00hrs)</span>
                </p>
                <p class="text-xs text-purple-600 mt-1">
                    <i class="fas fa-mouse-pointer mr-1"></i>
                    Haz clic en un día para ver el detalle de profesores
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                @foreach($diasSemana as $dia => $datos)
                    @php
                        $porRealizar = $datos['por_realizar'] ?? 0;
                        $total = $datos['realizadas'] + $datos['no_realizadas'] + $porRealizar;
                        $porcentaje = $total > 0 ? round(($datos['realizadas'] / $total) * 100) : 0;
                        $esDiaSinClases = $datos['es_dia_sin_clases'] ?? false;
                        $esSabado = $datos['es_sabado'] ?? false;
                        $esFuturo = $datos['es_futuro'] ?? false;
                        $esClickeable = !$esDiaSinClases && !$esFuturo && $total > 0;
                    @endphp
                    <div @if($esClickeable) @click="abrirModal('{{ $dia }}')" @endif
                         class="p-4 rounded-lg border-2 transition-all min-h-[340px] flex flex-col {{ $esClickeable ? 'cursor-pointer hover:shadow-lg' : '' }} {{ $datos['es_hoy'] ? 'border-purple-500 bg-purple-50 shadow-lg' : ($esDiaSinClases ? 'border-gray-200 bg-gray-50' : ($esFuturo ? 'border-dashed border-gray-300 bg-gray-50 opacity-60' : ($esSabado ? 'border-amber-200 bg-amber-50 hover:border-amber-400' : 'border-gray-200 bg-white hover:border-purple-300'))) }}">
                        <div class="text-center mb-3">
                            <p class="text-xs uppercase font-semibold {{ $datos['es_hoy'] ? 'text-purple-600' : ($esSabado ? 'text-amber-600' : ($esFuturo ? 'text-gray-400' : 'text-gray-500')) }}">
                                {{ ucfirst($datos['dia_nombre']) }}
                            </p>
                            <p class="text-2xl font-bold {{ $datos['es_hoy'] ? 'text-purple-700' : ($esFuturo ? 'text-gray-400' : 'text-gray-800') }}">
                                {{ $datos['fecha']->format('d') }}
                            </p>
                            @if($datos['es_hoy'])
                                <span class="inline-block mt-1 px-2 py-0.5 bg-purple-600 text-white text-xs rounded-full">Hoy</span>
                            @endif
                            @if($esSabado && !$esFuturo)
                                <span class="inline-block mt-1 px-2 py-0.5 bg-amber-500 text-white text-xs rounded-full">Hasta 13:00</span>
                            @endif
                            @if($esFuturo && !$esDiaSinClases)
                                <span class="inline-block mt-1 px-2 py-0.5 bg-gray-300 text-gray-600 text-xs rounded-full">Pendiente</span>
                            @endif
                        </div>
                        
                        @if($esDiaSinClases)
                            <div class="text-center py-4 text-gray-400 text-sm">
                                <i class="fas fa-bed mr-1"></i>
                                Sin clases
                            </div>
                        @elseif($esFuturo)
                            <div class="text-center py-4 text-gray-400 text-sm">
                                <i class="fas fa-hourglass-half mr-1"></i>
                                Sin datos aún
                            </div>
                        @else
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Realizadas</span>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-bold">{{ $datos['realizadas'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">No realizadas</span>
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-bold">{{ $datos['no_realizadas'] }}</span>
                                </div>
                                @if(isset($datos['por_realizar']) && $datos['por_realizar'] > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Por realizar</span>
                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs font-bold">{{ $datos['por_realizar'] }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Recuperadas</span>
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-bold">{{ $datos['recuperadas'] ?? 0 }}</span>
                                </div>
                                
                                @if($total > 0)
                                    <div class="pt-2 border-t border-gray-100">
                                        <div class="flex items-center gap-3 h-8">
                                            <div class="flex-1 h-3 bg-gray-200 rounded-lg overflow-hidden shadow-sm">
                                                <div class="h-full {{ $porcentaje >= 80 ? 'bg-green-500' : ($porcentaje >= 50 ? 'bg-yellow-500' : 'bg-red-500') }} transition-all duration-300" style="width: {{ $porcentaje }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-gray-700 w-10 text-right">{{ $porcentaje }}%</span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if($esClickeable && $datos['no_realizadas'] > 0)
                                    <div class="pt-2 text-center">
                                        <span class="text-xs text-purple-600"><i class="fas fa-eye mr-1"></i>Ver detalle</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Resumen de la semana -->
            @php
                $totalSemanaRealizadas = collect($diasSemana)->sum('realizadas');
                $totalSemanaNoRealizadas = collect($diasSemana)->sum('no_realizadas');
                $totalSemanaRecuperadas = collect($diasSemana)->sum(function($d) { return $d['recuperadas'] ?? 0; });
                $totalSemana = $totalSemanaRealizadas + $totalSemanaNoRealizadas;
                $porcentajeSemana = $totalSemana > 0 ? round(($totalSemanaRealizadas / $totalSemana) * 100) : 0;
            @endphp
            <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-700 mb-3">Resumen de la Semana</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $totalSemanaRealizadas }}</p>
                        <p class="text-xs text-gray-500">Realizadas</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $totalSemanaNoRealizadas }}</p>
                        <p class="text-xs text-gray-500">No Realizadas</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $totalSemanaRecuperadas }}</p>
                        <p class="text-xs text-gray-500">Recuperadas</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold {{ $porcentajeSemana >= 80 ? 'text-green-600' : ($porcentajeSemana >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $porcentajeSemana }}%</p>
                        <p class="text-xs text-gray-500">Completadas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista: Calendario Mensual -->
        <div x-show="vistaDetalle === 'calendario'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @php
                $inicioMes = \Carbon\Carbon::create($anio, $mes, 1);
                $finMes = $inicioMes->copy()->endOfMonth();
                $hoyCalendario = \Carbon\Carbon::now();
                
                // Para calendario de 6 columnas (Lun-Sáb, sin Domingo)
                $primerDiaSemana = $inicioMes->dayOfWeekIso; // 1=Lunes, 7=Domingo
                $diasEnMes = $finMes->day;
                
                $calendario = [];
                
                // Ajustar para calendario sin domingos
                // Si el mes empieza en domingo (7), no hay espacios vacíos al inicio para el lunes
                $espaciosVacios = $primerDiaSemana == 7 ? 0 : ($primerDiaSemana - 1);
                
                for ($i = 0; $i < $espaciosVacios; $i++) {
                    $calendario[] = null;
                }
                
                for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                    $fecha = \Carbon\Carbon::create($anio, $mes, $dia);
                    
                    // Saltar domingos
                    if ($fecha->isSunday()) {
                        continue;
                    }
                    
                    $diaFormato = $fecha->format('d/m');
                    $esFuturo = $fecha->gt($hoyCalendario);
                    $datosDelDia = $diasDelMes[$diaFormato] ?? ['realizadas' => 0, 'no_realizadas' => 0, 'recuperadas' => 0, 'por_realizar' => 0, 'clases_no_realizadas_detalle' => []];
                    
                    $calendario[] = [
                        'dia' => $dia,
                        'fecha' => $fecha,
                        'fecha_key' => $diaFormato,
                        'datos' => $datosDelDia,
                        'es_hoy' => $fecha->isToday(),
                        'es_sabado' => $fecha->isSaturday(),
                        'es_futuro' => $esFuturo
                    ];
                }
            @endphp
            
            <div class="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                <p class="text-sm text-purple-700">
                    <i class="fas fa-calendar mr-1"></i>
                    <strong>{{ $inicioMes->locale('es')->isoFormat('MMMM YYYY') }}</strong>
                    <span class="text-purple-500 ml-2">(Sábados: clases hasta 13:00hrs | Sin domingos)</span>
                </p>
                <p class="text-xs text-purple-600 mt-1">
                    <i class="fas fa-mouse-pointer mr-1"></i>
                    Haz clic en un día para ver el detalle de profesores
                </p>
            </div>

            <!-- Leyenda -->
            <div class="flex flex-wrap gap-4 mb-4 text-xs">
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-green-500"></span>
                    <span class="text-gray-600">Realizadas</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-red-500"></span>
                    <span class="text-gray-600">No Realizadas</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-blue-500"></span>
                    <span class="text-gray-600">Recuperadas</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-gray-300 border border-dashed border-gray-400"></span>
                    <span class="text-gray-600">Días futuros</span>
                </div>
            </div>

            <!-- Calendario de 6 columnas (Lun-Sáb) -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <!-- Cabecera días de la semana -->
                <div style="display: grid; grid-template-columns: repeat(6, 1fr);" class="bg-gray-100 border-b border-gray-200">
                    @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $diaSemana)
                        <div class="p-2 text-center text-xs font-semibold text-gray-600 {{ $diaSemana === 'Sáb' ? 'bg-amber-100' : '' }}">
                            {{ $diaSemana }}
                            @if($diaSemana === 'Sáb')
                                <span class="block text-[9px] text-amber-600">(hasta 13h)</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <!-- Días del calendario -->
                <div style="display: grid; grid-template-columns: repeat(6, 1fr);">
                    @foreach($calendario as $item)
                        @if($item === null)
                            <div class="p-2 min-h-[100px] bg-gray-50 border-b border-r border-gray-100"></div>
                        @else
                            @php
                                $porRealizarCal = $item['datos']['por_realizar'] ?? 0;
                                $totalCal = $item['datos']['realizadas'] + $item['datos']['no_realizadas'] + $porRealizarCal;
                                $tieneClases = $totalCal > 0;
                                $esSabadoCal = $item['es_sabado'] ?? false;
                                $esFuturoCal = $item['es_futuro'] ?? false;
                                $esClickeableCal = !$esFuturoCal && $tieneClases;
                            @endphp
                            <div @if($esClickeableCal) @click="abrirModal('{{ $item['fecha_key'] }}')" @endif
                                 class="p-2 min-h-[140px] border-b border-r border-gray-100 transition-colors flex flex-col {{ $esClickeableCal ? 'cursor-pointer' : '' }} {{ $item['es_hoy'] ? 'bg-purple-50 ring-2 ring-purple-500 ring-inset' : ($esFuturoCal ? 'bg-gray-50 opacity-60' : ($esSabadoCal ? 'bg-amber-50 hover:bg-amber-100' : 'bg-white hover:bg-gray-50')) }}">
                                <!-- Número del día -->
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-sm font-semibold {{ $item['es_hoy'] ? 'text-purple-700' : ($esFuturoCal ? 'text-gray-400' : 'text-gray-700') }}">
                                        {{ $item['dia'] }}
                                    </span>
                                    @if($item['es_hoy'])
                                        <span class="px-1.5 py-0.5 bg-purple-600 text-white text-[10px] rounded">Hoy</span>
                                    @endif
                                </div>
                                
                                @if($esFuturoCal)
                                    <div class="text-[10px] text-gray-400 mt-2 text-center">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span class="block">Pendiente</span>
                                    </div>
                                @elseif($tieneClases)
                                    <!-- Indicadores de clases -->
                                    <div class="space-y-1">
                                        @if($item['datos']['realizadas'] > 0)
                                            <div class="flex items-center gap-1 text-[10px]">
                                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                <span class="text-green-700 font-medium">{{ $item['datos']['realizadas'] }}</span>
                                            </div>
                                        @endif
                                        @if($item['datos']['no_realizadas'] > 0)
                                            <div class="flex items-center gap-1 text-[10px]">
                                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                <span class="text-red-700 font-medium">{{ $item['datos']['no_realizadas'] }}</span>
                                            </div>
                                        @endif
                                        @if(($item['datos']['por_realizar'] ?? 0) > 0)
                                            <div class="flex items-center gap-1 text-[10px]">
                                                <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                                <span class="text-yellow-700 font-medium">{{ $item['datos']['por_realizar'] }}</span>
                                            </div>
                                        @endif
                                        @if(($item['datos']['recuperadas'] ?? 0) > 0)
                                            <div class="flex items-center gap-1 text-[10px]">
                                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                <span class="text-blue-700 font-medium">{{ $item['datos']['recuperadas'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Barra de progreso pequeña -->
                                    @php
                                        $porcentajeCal = round(($item['datos']['realizadas'] / $totalCal) * 100);
                                    @endphp
                                    <div class="mt-2 h-6">
                                        <div class="h-2.5 bg-gray-200 rounded-md overflow-hidden shadow-sm">
                                            <div class="h-full {{ $porcentajeCal >= 80 ? 'bg-green-500' : ($porcentajeCal >= 50 ? 'bg-yellow-500' : 'bg-red-500') }} transition-all duration-300" style="width: {{ $porcentajeCal }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-[10px] text-gray-400 mt-2">Sin datos</div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Tabla resumen del mes (solo días hasta hoy) -->
            <div class="mt-6">
                <h4 class="font-semibold text-gray-700 mb-3">
                    <i class="fas fa-list text-gray-500 mr-2"></i>
                    Detalle Diario
                </h4>
                <div class="overflow-x-auto max-h-96">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 font-semibold text-gray-700">Fecha</th>
                                <th class="px-4 py-2 font-semibold text-gray-700 text-center">Realizadas</th>
                                <th class="px-4 py-2 font-semibold text-gray-700 text-center">No Realizadas</th>
                                <th class="px-4 py-2 font-semibold text-gray-700 text-center">Recuperadas</th>
                                <th class="px-4 py-2 font-semibold text-gray-700 text-center">Total</th>
                                <th class="px-4 py-2 font-semibold text-gray-700 text-center">% Completadas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($diasDelMes as $dia => $datos)
                                @php
                                    $totalTabla = $datos['realizadas'] + $datos['no_realizadas'];
                                    $porcentajeTabla = $totalTabla > 0 ? round(($datos['realizadas'] / $totalTabla) * 100) : 0;
                                    $colorBarraTabla = $porcentajeTabla >= 80 ? 'bg-green-500' : ($porcentajeTabla >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $dia }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs font-semibold">
                                            {{ $datos['realizadas'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs font-semibold">
                                            {{ $datos['no_realizadas'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                            {{ $datos['recuperadas'] ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center font-semibold text-gray-800">{{ $totalTabla }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-3 h-8">
                                            <div class="flex-1 h-3 bg-gray-200 rounded-lg overflow-hidden shadow-sm">
                                                <div class="h-full {{ $colorBarraTabla }} transition-all duration-300" style="width: {{ $porcentajeTabla }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-gray-700 w-10 text-right">{{ $porcentajeTabla }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        No hay datos disponibles para el mes
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vista: Estadísticas Filtradas -->
        <div x-show="vistaDetalle === 'filtrado'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-data="{
                fechaInicio: '{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}',
                fechaFin: '{{ \Carbon\Carbon::now()->format('Y-m-d') }}',
                cargando: false,
                datosFiltrados: null,
                error: null,
                async filtrar() {
                    this.cargando = true;
                    this.error = null;
                    try {
                        const response = await fetch(`/dashboard/estadisticas-filtradas?fecha_inicio=${this.fechaInicio}&fecha_fin=${this.fechaFin}`);
                        if (!response.ok) throw new Error('Error al cargar datos');
                        this.datosFiltrados = await response.json();
                        this.$nextTick(() => this.actualizarGraficoTorta());
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.cargando = false;
                    }
                },
                actualizarGraficoTorta() {
                    const ctx = document.getElementById('chart-torta-filtrado');
                    if (!ctx || !this.datosFiltrados) return;
                    
                    if (ctx.chartInstance) {
                        ctx.chartInstance.destroy();
                    }
                    
                    ctx.chartInstance = new Chart(ctx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Clases Realizadas', 'Clases No Realizadas', 'Clases Recuperadas'],
                            datasets: [{
                                data: [
                                    this.datosFiltrados.realizadas,
                                    this.datosFiltrados.no_realizadas,
                                    this.datosFiltrados.recuperadas
                                ],
                                backgroundColor: [
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(239, 68, 68, 0.8)',
                                    'rgba(59, 130, 246, 0.8)'
                                ],
                                borderColor: ['#059669', '#dc2626', '#1d4ed8'],
                                borderWidth: 2,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: { size: 13, weight: '500' }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const porcentaje = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                            return `${context.label}: ${context.raw} (${porcentaje}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
             }"
             x-init="filtrar()">
            
            <div class="mb-4 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-purple-700 mb-1">
                            <i class="fas fa-calendar-alt mr-1"></i> Fecha Inicio
                        </label>
                        <input type="date" x-model="fechaInicio" 
                               class="w-full px-3 py-2 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-purple-700 mb-1">
                            <i class="fas fa-calendar-alt mr-1"></i> Fecha Fin
                        </label>
                        <input type="date" x-model="fechaFin"
                               class="w-full px-3 py-2 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <button @click="filtrar()" 
                                :disabled="cargando"
                                class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <i class="fas" :class="cargando ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                            <span x-text="cargando ? 'Cargando...' : 'Filtrar'"></span>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-purple-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Sábados: clases hasta 13:00hrs | Domingos: sin clases
                </p>
            </div>

            <!-- Error -->
            <template x-if="error">
                <div class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                    <p class="text-red-700"><i class="fas fa-exclamation-circle mr-2"></i><span x-text="error"></span></p>
                </div>
            </template>

            <!-- Contenido filtrado -->
            <template x-if="datosFiltrados && !cargando">
                <div class="space-y-6">
                    <!-- Período seleccionado -->
                    <div class="p-3 bg-gray-100 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-700">
                            <i class="fas fa-calendar-check mr-1 text-purple-600"></i>
                            Mostrando estadísticas del <strong x-text="new Date(fechaInicio + 'T00:00:00').toLocaleDateString('es-CL')"></strong> 
                            al <strong x-text="new Date(fechaFin + 'T00:00:00').toLocaleDateString('es-CL')"></strong>
                            (<span x-text="datosFiltrados.dias_laborales"></span> días laborales)
                        </p>
                    </div>

                    <!-- Tarjetas de resumen -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-white rounded-lg shadow border border-gray-200 text-center">
                            <p class="text-3xl font-bold text-green-600" x-text="datosFiltrados.realizadas"></p>
                            <p class="text-sm text-gray-600">Realizadas</p>
                            <p class="text-xs text-green-500" x-text="datosFiltrados.porcentaje_realizadas + '% del total'"></p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow border border-gray-200 text-center">
                            <p class="text-3xl font-bold text-red-600" x-text="datosFiltrados.no_realizadas"></p>
                            <p class="text-sm text-gray-600">No Realizadas</p>
                            <p class="text-xs text-red-500" x-text="datosFiltrados.porcentaje_no_realizadas + '% del total'"></p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow border border-gray-200 text-center">
                            <p class="text-3xl font-bold text-blue-600" x-text="datosFiltrados.recuperadas"></p>
                            <p class="text-sm text-gray-600">Recuperadas</p>
                            <p class="text-xs text-blue-500" x-text="datosFiltrados.porcentaje_recuperadas + '% de no realizadas'"></p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow border border-gray-200 text-center">
                            <p class="text-3xl font-bold text-purple-600" x-text="datosFiltrados.total"></p>
                            <p class="text-sm text-gray-600">Total Clases</p>
                            <p class="text-xs text-purple-500" x-text="'Promedio: ' + datosFiltrados.promedio_diario + '/día'"></p>
                        </div>
                    </div>

                    <!-- Gráfico de torta -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="p-6 bg-white rounded-lg shadow border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4">
                                <i class="fas fa-chart-pie text-purple-600 mr-2"></i>
                                Distribución de Clases
                            </h4>
                            <div class="h-72">
                                <canvas id="chart-torta-filtrado"></canvas>
                            </div>
                        </div>

                        <!-- Estadísticas adicionales -->
                        <div class="p-6 bg-white rounded-lg shadow border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4">
                                <i class="fas fa-chart-line text-purple-600 mr-2"></i>
                                Métricas del Período
                            </h4>
                            <div class="space-y-4">
                                <!-- Tasa de cumplimiento -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-600">Tasa de Cumplimiento</span>
                                        <span class="text-sm font-bold" 
                                              :class="datosFiltrados.porcentaje_realizadas >= 80 ? 'text-green-600' : (datosFiltrados.porcentaje_realizadas >= 50 ? 'text-yellow-600' : 'text-red-600')"
                                              x-text="datosFiltrados.porcentaje_realizadas + '%'"></span>
                                    </div>
                                    <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-500"
                                             :class="datosFiltrados.porcentaje_realizadas >= 80 ? 'bg-green-500' : (datosFiltrados.porcentaje_realizadas >= 50 ? 'bg-yellow-500' : 'bg-red-500')"
                                             :style="'width: ' + datosFiltrados.porcentaje_realizadas + '%'"></div>
                                    </div>
                                </div>

                                <!-- Tasa de recuperación -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-600">Tasa de Recuperación</span>
                                        <span class="text-sm font-bold text-blue-600" x-text="datosFiltrados.porcentaje_recuperadas + '%'"></span>
                                    </div>
                                    <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 transition-all duration-500"
                                             :style="'width: ' + datosFiltrados.porcentaje_recuperadas + '%'"></div>
                                    </div>
                                </div>

                                <!-- Estadísticas adicionales -->
                                <div class="pt-4 border-t border-gray-200 space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600"><i class="fas fa-calendar-day mr-2 text-gray-400"></i>Días en el rango</span>
                                        <span class="font-semibold text-gray-800" x-text="datosFiltrados.dias_totales"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600"><i class="fas fa-briefcase mr-2 text-gray-400"></i>Días laborales</span>
                                        <span class="font-semibold text-gray-800" x-text="datosFiltrados.dias_laborales"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600"><i class="fas fa-clock mr-2 text-gray-400"></i>Promedio diario</span>
                                        <span class="font-semibold text-gray-800" x-text="datosFiltrados.promedio_diario + ' clases'"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600"><i class="fas fa-exclamation-triangle mr-2 text-orange-400"></i>Pendientes recuperar</span>
                                        <span class="font-semibold text-orange-600" x-text="datosFiltrados.pendientes"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desglose por día de la semana -->
                    <div class="p-6 bg-white rounded-lg shadow border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-4">
                            <i class="fas fa-calendar-week text-purple-600 mr-2"></i>
                            Desglose por Día de la Semana
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                            <template x-for="(datos, dia) in datosFiltrados.por_dia_semana" :key="dia">
                                <div class="p-3 rounded-lg border text-center"
                                     :class="dia === 'Sábado' ? 'bg-amber-50 border-amber-200' : 'bg-gray-50 border-gray-200'">
                                    <p class="text-xs font-semibold text-gray-600 uppercase" x-text="dia"></p>
                                    <p class="text-xl font-bold text-gray-800" x-text="datos.total"></p>
                                    <div class="flex justify-center gap-2 mt-1 text-xs">
                                        <span class="text-green-600" x-text="'✓' + datos.realizadas"></span>
                                        <span class="text-red-600" x-text="'✗' + datos.no_realizadas"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Estado de carga -->
            <template x-if="cargando">
                <div class="flex flex-col items-center justify-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-purple-600 mb-4"></i>
                    <p class="text-gray-600">Cargando estadísticas...</p>
                </div>
            </template>
        </div>
    </div>

    <!-- Información y notas -->
    <div class="p-6 bg-blue-50 rounded-lg border border-blue-200">
        <h4 class="font-semibold text-blue-800 mb-3">
            <i class="fas fa-info-circle mr-2"></i>
            Información
        </h4>
        <ul class="text-sm text-blue-700 space-y-2">
            <li><strong>Clases Realizadas:</strong> Clases que se llevaron a cabo según lo programado</li>
            <li><strong>Clases No Realizadas:</strong> Clases que fueron programadas pero no se realizaron</li>
            <li><strong>Clases Recuperadas:</strong> Clases no realizadas que han sido reprogramadas y recuperadas</li>
            <li><strong>Período:</strong> Datos del mes de {{ strval(\Carbon\Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM')) }} de {{ $anio }}</li>
        </ul>
    </div>
    @endif
</div>

<script>
(function() {
    function initClasesNoRealizadasChart() {
        const ctx = document.getElementById('chart-clases-no-realizadas');
        if (!ctx) return;
        if (ctx.chartInstance) return;
        
        const diasLabels = @json($diasLabels ?? []);
        const datosRealizadas = @json($datosRealizadas ?? []);
        const datosNoRealizadas = @json($datosNoRealizadas ?? []);
        const datosRecuperadas = @json($datosRecuperadas ?? []);

        if (diasLabels.length === 0) return;

        try {
            ctx.chartInstance = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: diasLabels,
                    datasets: [
                        {
                            label: 'Clases Realizadas',
                            data: datosRealizadas,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: '#059669',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Clases No Realizadas',
                            data: datosNoRealizadas,
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: '#dc2626',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Clases Recuperadas',
                            data: datosRecuperadas,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: '#1d4ed8',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        x: {
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 },
                            grid: { color: 'rgba(0, 0, 0, 0.05)' }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: { size: 12, weight: '500' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 }
                        }
                    }
                }
            });
            console.log('✓ Chart clases no realizadas initialized');
        } catch (error) {
            console.error('Error initializing chart:', error);
        }
    }

    // Intentar inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initClasesNoRealizadasChart);
    } else {
        // Si ya cargó, esperar un tick para que Chart.js esté disponible
        setTimeout(initClasesNoRealizadasChart, 100);
    }
})();
</script>

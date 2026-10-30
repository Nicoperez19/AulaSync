<!-- Controles de Filtros por Rango de Fechas -->
<div class="mb-6 rounded-2xl border border-slate-200/80 bg-slate-50 p-2.5 shadow-xs sm:p-3.5">
    <div class="flex flex-row items-center gap-1.5 overflow-x-auto whitespace-nowrap [-ms-overflow-style:none] [scrollbar-width:none] sm:gap-2 xl:justify-between">
        <div class="flex min-w-0 items-center gap-1.5 sm:gap-2">
            <span class="shrink-0 text-[10px] font-black uppercase tracking-wider text-slate-500 sm:text-xs">Período:</span>
            <button onclick="filtrarStatusClases('hoy')" id="btn-status-hoy" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'hoy' ? 'bg-[#D12420] text-white shadow-xs border-[#D12420]' : 'border-[#C0C0C0] bg-white text-slate-700 hover:bg-slate-100' }}">
                Hoy
            </button>
            <button onclick="filtrarStatusClases('semana')" id="btn-status-semana" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'semana' ? 'bg-[#D12420] text-white shadow-xs border-[#D12420]' : 'border-[#C0C0C0] bg-white text-slate-700 hover:bg-slate-100' }}">
                Esta Semana
            </button>
            <button onclick="filtrarStatusClases('mes')" id="btn-status-mes" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'mes' ? 'bg-[#D12420] text-white shadow-xs border-[#D12420]' : 'border-[#C0C0C0] bg-white text-slate-700 hover:bg-slate-100' }}">
                Este Mes
            </button>
        </div>

        <!-- Rango de fechas libre -->
        <div class="flex min-w-max items-center gap-1.5 sm:gap-2">
            <span class="shrink-0 text-[10px] font-bold text-slate-500 sm:text-xs">Desde:</span>
            <input type="date" id="status-fecha-inicio" value="{{ $fecha_inicio ?: now()->format('Y-m-d') }}" class="h-8 min-w-[120px] shrink-0 rounded-lg border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 focus:border-[#D12420] focus:ring-2 focus:ring-[#D12420]/20 sm:text-xs">
            <span class="shrink-0 text-[10px] font-bold text-slate-500 sm:text-xs">Hasta:</span>
            <input type="date" id="status-fecha-fin" value="{{ $fecha_fin ?: now()->format('Y-m-d') }}" class="h-8 min-w-[120px] shrink-0 rounded-lg border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 focus:border-[#D12420] focus:ring-2 focus:ring-[#D12420]/20 sm:text-xs">
            <button onclick="filtrarStatusClasesPersonalizado()" class="shrink-0 rounded-lg bg-[#191919] px-2.5 py-1.5 text-[10px] font-bold text-white transition hover:bg-[#950101] sm:text-xs">
                Filtrar
            </button>
        </div>
    </div>
</div>

<!-- Fila con los 2 gráficos en DIVs separados -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8 items-stretch">
    <!-- DIV 1: Estado General de Clases (Gráfico Donut y Desglose) -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-6 flex flex-col justify-between">
        <div>
            <div class="mb-4">
                <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-chart-pie mr-2 text-[#950101]"></i>
                    Estado General de Clases
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Control de registro y desglose de clases en el período seleccionado</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-5 items-center sm:items-stretch mb-2">
                <!-- Gráfico Donut -->
                <div class="flex flex-col items-center justify-center shrink-0 w-[190px] sm:w-[210px] py-2">
                    <div class="w-[180px] h-[180px] sm:w-[195px] sm:h-[195px] relative">
                        <canvas id="chart-status-clases-canvas"
                                data-realizadas="{{ $realizadas }}"
                                data-recuperadas="{{ $recuperadas }}"
                                data-justificadas="{{ $justificadas }}"
                                data-total-impartidas="{{ $total_impartidas }}"
                                data-no-registradas="{{ $no_registradas }}"
                                data-pct-impartidas="{{ $pct_impartidas }}"
                                data-pct-no-registradas="{{ $pct_no_registradas }}"
                                data-total-clases="{{ $total_clases }}">
                        </canvas>

                        <!-- Etiqueta flotante central -->
                        <div id="status-clases-center-label" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none transition-all duration-200">
                            <span id="center-label-valor" class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight leading-none">{{ $pct_impartidas }}%</span>
                            <span id="center-label-sub" class="text-[10px] sm:text-[11px] font-extrabold text-[#10B981] uppercase tracking-wide mt-1">Cumplimiento</span>
                            <span id="center-label-detail" class="text-[10px] font-medium text-slate-400 tracking-tight mt-0.5 hidden"></span>
                        </div>
                    </div>
                    @if($total_clases === 0)
                        <p class="text-[11px] font-semibold text-slate-400 mt-2 text-center">Sin registros de clases (0 clases)</p>
                    @else
                        <p class="text-[11px] font-semibold text-slate-400 mt-2 text-center">Pasa el cursor sobre el gráfico para ver detalle</p>
                    @endif
                </div>

                <!-- Tarjetas Informativas / Botones Interactivos hacia Control Docente -->
                <div class="flex-1 flex flex-col gap-3 justify-center min-w-0 w-full">
                    <!-- Grupo 1: Impartidas / Efectivas -->
                    <div class="p-3 sm:p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-200 shadow-2xs transition-all duration-200">
                        <button type="button"
                                onclick="irAControlDocente('impartidas')"
                                title="Ver todas las clases impartidas en Control Docente"
                                class="w-full text-left flex items-start justify-between mb-2 gap-2 p-2 -m-1 rounded-xl bg-white hover:bg-emerald-100/90 border border-emerald-200/90 hover:border-emerald-400 hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-200 group cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 shrink-0 group-hover:scale-125 transition-transform duration-200"></span>
                                <span class="font-extrabold text-xs sm:text-sm text-emerald-950 leading-tight group-hover:text-emerald-800 transition-colors">Clases Impartidas / Efectivas</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 whitespace-nowrap group-hover:bg-emerald-300 transition-all duration-200">{{ $total_impartidas }} ({{ $pct_impartidas }}%)</span>
                                <svg class="w-4 h-4 text-emerald-600/80 group-hover:text-emerald-900 group-hover:translate-x-1 transition-all duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Detalle interno: Normales, Recuperadas, Justificadas -->
                        <div class="grid grid-cols-3 gap-2 mt-1.5 pt-2 border-t border-emerald-200/60 text-xs">
                            <button type="button"
                                    onclick="irAControlDocente('realizada')"
                                    title="Ver clases realizadas normales en Control Docente"
                                    class="text-left bg-white hover:bg-emerald-50/90 p-2 rounded-xl border border-emerald-200/90 hover:border-emerald-400 hover:shadow-md hover:-translate-y-1 active:translate-y-0 active:scale-95 transition-all duration-200 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-slate-600 group-hover:text-emerald-900 font-bold block text-[10px] sm:text-[11px] truncate transition-colors">Normales</span>
                                    <svg class="w-3 h-3 text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all duration-200 shrink-0 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs sm:text-sm font-black text-emerald-800 group-hover:text-emerald-900 transition-colors">{{ $realizadas }}</span>
                                    <span class="text-[10px] font-bold text-slate-400"> ({{ $pct_realizadas }}%)</span>
                                </div>
                            </button>

                            <button type="button"
                                    onclick="irAControlDocente('recuperada')"
                                    title="Ver clases recuperadas en Control Docente"
                                    class="text-left bg-white hover:bg-amber-50/90 p-2 rounded-xl border border-amber-200/90 hover:border-amber-400 hover:shadow-md hover:-translate-y-1 active:translate-y-0 active:scale-95 transition-all duration-200 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-slate-600 group-hover:text-amber-900 font-bold block text-[10px] sm:text-[11px] truncate transition-colors">Recuperadas</span>
                                    <svg class="w-3 h-3 text-slate-300 group-hover:text-amber-600 group-hover:translate-x-0.5 transition-all duration-200 shrink-0 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs sm:text-sm font-black text-amber-700 group-hover:text-amber-800 transition-colors">{{ $recuperadas }}</span>
                                    <span class="text-[10px] font-bold text-slate-400"> ({{ $pct_recuperadas }}%)</span>
                                </div>
                            </button>

                            <button type="button"
                                    onclick="irAControlDocente('justificado')"
                                    title="Ver clases justificadas en Control Docente"
                                    class="text-left bg-white hover:bg-slate-100 p-2 rounded-xl border border-slate-200/90 hover:border-slate-400 hover:shadow-md hover:-translate-y-1 active:translate-y-0 active:scale-95 transition-all duration-200 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-slate-500/40">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-slate-600 group-hover:text-slate-900 font-bold block text-[10px] sm:text-[11px] truncate transition-colors">Justificadas</span>
                                    <svg class="w-3 h-3 text-slate-300 group-hover:text-slate-600 group-hover:translate-x-0.5 transition-all duration-200 shrink-0 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs sm:text-sm font-black text-slate-700 group-hover:text-slate-900 transition-colors">{{ $justificadas }}</span>
                                    <span class="text-[10px] font-bold text-slate-400"> ({{ $pct_justificadas ?? 0 }}%)</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Grupo 2: No Registradas / No Realizadas -->
                    <button type="button"
                            onclick="irAControlDocente('no_realizada')"
                            title="Ver clases no registradas en Control Docente"
                            class="w-full text-left p-3 sm:p-3.5 rounded-2xl bg-rose-50/80 border-2 border-rose-200/90 hover:bg-rose-100/90 hover:border-rose-400 hover:shadow-lg hover:-translate-y-1 active:translate-y-0 active:scale-[0.99] transition-all duration-200 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-rose-500/40">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-3.5 h-3.5 rounded-full bg-rose-500 shrink-0 group-hover:scale-125 transition-transform duration-200"></span>
                                <span class="font-extrabold text-xs sm:text-sm text-rose-950 leading-tight group-hover:text-rose-900 transition-colors">No Registradas / Sin Marcar</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-rose-200 text-rose-900 whitespace-nowrap group-hover:bg-rose-300 transition-all duration-200">{{ $no_registradas }} ({{ $pct_no_registradas }}%)</span>
                                <svg class="w-4 h-4 text-rose-600/80 group-hover:text-rose-900 group-hover:translate-x-1 transition-all duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-[10px] sm:text-[11px] text-rose-700/90 font-medium mt-1.5">Clases oficiales sin marca de registro o notificadas como ausentes.</p>
                    </button>

                    @if(!empty($futuras_pendientes) && $futuras_pendientes > 0)
                        <div class="px-3 py-1.5 rounded-xl bg-slate-100/90 border border-slate-200/80 text-[10px] sm:text-[11px] text-slate-600 font-medium flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Próximas programadas en el período:
                            </span>
                            <span class="font-bold text-slate-800 whitespace-nowrap">{{ $futuras_pendientes }} pendientes</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- DIV 2: Cumplimiento por Escuela de la Sede -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-6 flex flex-col justify-between">
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-graduation-cap mr-2 text-[#950101]"></i>
                        Cumplimiento por Escuela
                    </h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Clases impartidas vs no registradas de las escuelas de la sede</p>
                </div>
                @can('reportes')
                <a href="{{ route('reportes.cumplimiento-escuelas', ['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-[#D12420] text-slate-700 hover:text-white text-xs font-bold transition shadow-2xs group self-start sm:self-auto cursor-pointer">
                    <span>Ver Reporte Completo</span>
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                </a>
                @endcan
            </div>

            @if(!empty($escuelas_stats) && count($escuelas_stats) > 0)
                <!-- Canvas para el gráfico de barras comparativo -->
                <div class="w-full bg-slate-50/70 p-3 sm:p-4 rounded-2xl border border-slate-200/80 mb-4">
                    <div class="h-[190px] sm:h-[210px] relative w-full">
                        <canvas id="chart-escuelas-canvas"
                                data-escuelas="{{ json_encode($escuelas_stats) }}">
                        </canvas>
                    </div>
                </div>

                <!-- Tarjetas interactivas por Escuela -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @foreach($escuelas_stats as $escuela)
                        @php
                            $pct = $escuela['pct'] ?? 0;
                            $badgeBg = $pct >= 90 ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($pct >= 75 ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-rose-100 text-rose-800 border-rose-200');
                            $barBg = $pct >= 90 ? 'bg-emerald-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-rose-500');
                        @endphp
                        <div class="p-3 rounded-xl bg-slate-50/60 border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-slate-300 hover:bg-white hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between gap-2.5">
                            <div>
                                <div class="flex items-start justify-between gap-1.5 mb-1.5">
                                    <h5 class="text-[11px] sm:text-xs font-bold text-slate-800 leading-snug line-clamp-2" title="{{ $escuela['nombre'] }}">
                                        {{ $escuela['nombre'] }}
                                    </h5>
                                    <span class="text-[10px] sm:text-[11px] font-black px-1.5 py-0.5 rounded-full border {{ $badgeBg }} shrink-0">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                
                                <!-- Barra de progreso -->
                                <div class="w-full bg-slate-200/70 rounded-full h-1.5 overflow-hidden mb-2">
                                    <div class="{{ $barBg }} h-1.5 rounded-full transition-all duration-300" style="width: {{ min(100, $pct) }}%"></div>
                                </div>

                                <!-- Cifras como botones interactivos -->
                                <div class="flex items-center justify-between text-[11px] text-slate-600">
                                    <button type="button"
                                            onclick="irAControlDocenteConBusqueda('{{ addslashes($escuela['nombre']) }}', 'impartidas')"
                                            title="Ver clases impartidas de esta escuela"
                                            class="flex items-center gap-1 font-bold text-emerald-700 hover:text-emerald-900 hover:underline cursor-pointer group">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 group-hover:scale-125 transition-transform"></span>
                                        <span>{{ $escuela['impartidas'] }} Imp.</span>
                                    </button>
                                    <button type="button"
                                            onclick="irAControlDocenteConBusqueda('{{ addslashes($escuela['nombre']) }}', 'no_realizada')"
                                            title="Ver clases no registradas de esta escuela"
                                            class="flex items-center gap-1 font-bold text-rose-700 hover:text-rose-900 hover:underline cursor-pointer group">
                                        <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0 group-hover:scale-125 transition-transform"></span>
                                        <span>{{ $escuela['no_registradas'] }} Sin Reg.</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Botón de acceso a Control Docente con filtro de la escuela -->
                            <button type="button"
                                    onclick="irAControlDocenteConBusqueda('{{ addslashes($escuela['nombre']) }}')"
                                    class="w-full text-center py-1.5 px-2 rounded-lg bg-white hover:bg-[#D12420] text-slate-700 hover:text-white border border-slate-200/90 hover:border-[#D12420] text-[10px] font-bold shadow-2xs hover:shadow-xs hover:-translate-y-0.5 active:translate-y-0 active:scale-95 transition-all duration-150 flex items-center justify-center gap-1 cursor-pointer group">
                                <span>Ver en Control Docente</span>
                                <i class="fas fa-chevron-right text-[8px] text-slate-400 group-hover:text-white group-hover:translate-x-0.5 transition-all"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-slate-400 bg-slate-50 rounded-2xl border border-slate-200 text-xs font-medium my-auto">
                    No hay clases registradas para desglosar por escuela en el período seleccionado.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    if (typeof window.irAControlDocente !== 'function') {
        window.irAControlDocente = function(estado) {
            const inicio = document.getElementById('status-fecha-inicio')?.value || '';
            const fin = document.getElementById('status-fecha-fin')?.value || '';
            const baseUrl = "{{ route('clases-no-realizadas.index') }}";
            const params = new URLSearchParams();
            if (estado) params.set('estado', estado);
            if (inicio) params.set('fecha_inicio', inicio);
            if (fin) params.set('fecha_fin', fin);
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        };
    }

    if (typeof window.irAControlDocenteConBusqueda !== 'function') {
        window.irAControlDocenteConBusqueda = function(search, estado = '') {
            const inicio = document.getElementById('status-fecha-inicio')?.value || '';
            const fin = document.getElementById('status-fecha-fin')?.value || '';
            const baseUrl = "{{ route('clases-no-realizadas.index') }}";
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (estado) params.set('estado', estado);
            if (inicio) params.set('fecha_inicio', inicio);
            if (fin) params.set('fecha_fin', fin);
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        };
    }
</script>

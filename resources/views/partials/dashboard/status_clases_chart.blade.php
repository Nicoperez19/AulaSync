<!-- Fila de 2 Columnas con la misma distribución de espacio (50% / 50%) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
    <!-- COLUMNA IZQUIERDA: Estado General de Clases con su Período y Filtros -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-5 flex flex-col justify-between">
        <div>
            <!-- Header con Título -->
            <div class="mb-3.5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-800 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-[#950101]"></i>
                        Estado General de Clases
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">Control de registro y desglose de clases en el período</p>
                </div>
            </div>

            <!-- Controles de Filtros por Período y Rango de Fechas (en la parte superior de la tarjeta izquierda) -->
            <div class="mb-4 rounded-xl border border-slate-200/80 bg-slate-50 p-2 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <!-- Botones rápidos de período -->
                    <div class="flex items-center gap-1.5 overflow-x-auto">
                        <span class="shrink-0 text-[10px] font-black uppercase tracking-wider text-slate-500">Período:</span>
                        <button type="button" onclick="filtrarStatusClases('hoy')" id="btn-status-hoy" class="shrink-0 rounded-lg border px-2.5 py-1 text-[10px] font-extrabold transition {{ ($rango ?? 'hoy') === 'hoy' ? 'bg-[#D12420] text-white shadow-xs border-[#D12420]' : 'border-[#C0C0C0] bg-white text-slate-700 hover:bg-slate-100' }}">
                            Hoy
                        </button>
                        <button type="button" onclick="filtrarStatusClases('semana')" id="btn-status-semana" class="shrink-0 rounded-lg border px-2.5 py-1 text-[10px] font-extrabold transition {{ ($rango ?? 'hoy') === 'semana' ? 'bg-[#D12420] text-white shadow-xs border-[#D12420]' : 'border-[#C0C0C0] bg-white text-slate-700 hover:bg-slate-100' }}">
                            Esta Semana
                        </button>
                        <button type="button" onclick="filtrarStatusClases('mes')" id="btn-status-mes" class="shrink-0 rounded-lg border px-2.5 py-1 text-[10px] font-extrabold transition {{ ($rango ?? 'hoy') === 'mes' ? 'bg-[#D12420] text-white shadow-xs border-[#D12420]' : 'border-[#C0C0C0] bg-white text-slate-700 hover:bg-slate-100' }}">
                            Este Mes
                        </button>
                    </div>

                    <!-- Rango de fechas libre -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <input type="date" id="status-fecha-inicio" value="{{ $fecha_inicio ?: now()->format('Y-m-d') }}" class="h-7 w-[110px] rounded-lg border border-slate-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-700 focus:border-[#D12420] focus:ring-1 focus:ring-[#D12420]">
                        <span class="text-[10px] text-slate-400 font-bold">-</span>
                        <input type="date" id="status-fecha-fin" value="{{ $fecha_fin ?: now()->format('Y-m-d') }}" class="h-7 w-[110px] rounded-lg border border-slate-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-700 focus:border-[#D12420] focus:ring-1 focus:ring-[#D12420]">
                        <button type="button" onclick="filtrarStatusClasesPersonalizado()" class="shrink-0 rounded-lg bg-[#191919] px-2 py-1 text-[10px] font-bold text-white transition hover:bg-[#950101] cursor-pointer">
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Gráfico Donut y Botones de Estado -->
            <div class="flex flex-col sm:flex-row gap-4 items-center sm:items-stretch mb-1">
                <!-- Gráfico Donut -->
                <div class="flex flex-col items-center justify-center shrink-0 w-[170px] sm:w-[185px]">
                    <div class="w-[160px] h-[160px] sm:w-[175px] sm:h-[175px] relative">
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

                        <!-- Etiqueta central -->
                        <div id="status-clases-center-label" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none transition-all duration-200">
                            <span id="center-label-valor" class="text-2xl font-black text-slate-800 tracking-tight leading-none">{{ $pct_impartidas }}%</span>
                            <span id="center-label-sub" class="text-[10px] font-extrabold text-[#10B981] uppercase tracking-wide mt-1">Cumplimiento</span>
                            <span id="center-label-detail" class="text-[9px] font-medium text-slate-400 tracking-tight mt-0.5 hidden"></span>
                        </div>
                    </div>
                    @if($total_clases === 0)
                        <p class="text-[10px] font-semibold text-slate-400 mt-1.5 text-center">Sin registros de clases (0)</p>
                    @else
                        <p class="text-[10px] font-semibold text-slate-400 mt-1.5 text-center">Pasa el cursor para ver detalle</p>
                    @endif
                </div>

                <!-- Botones interactivos con filtros hacia Control Docente -->
                <div class="flex-1 flex flex-col gap-2.5 justify-center min-w-0 w-full">
                    <!-- Clases Impartidas -->
                    <div class="p-2.5 rounded-xl bg-emerald-50/80 border border-emerald-200 shadow-2xs">
                        <button type="button"
                                onclick="irAControlDocente('impartidas')"
                                title="Ver todas las clases impartidas en Control Docente"
                                class="w-full text-left flex items-center justify-between p-1.5 -m-0.5 rounded-lg bg-white hover:bg-emerald-100/90 border border-emerald-200/90 hover:border-emerald-400 hover:shadow-sm hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-150 group cursor-pointer focus:outline-none">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0 group-hover:scale-125 transition-transform duration-150"></span>
                                <span class="font-extrabold text-xs text-emerald-950 truncate group-hover:text-emerald-800">Clases Impartidas</span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <span class="text-[11px] font-black px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 group-hover:bg-emerald-300">{{ $total_impartidas }} ({{ $pct_impartidas }}%)</span>
                                <svg class="w-3.5 h-3.5 text-emerald-600 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </button>

                        <div class="grid grid-cols-3 gap-1.5 mt-1.5 pt-1.5 border-t border-emerald-200/60 text-[10px]">
                            <button type="button"
                                    onclick="irAControlDocente('realizada')"
                                    title="Ver realizadas normales"
                                    class="text-left bg-white hover:bg-emerald-50/90 p-1.5 rounded-lg border border-emerald-200/90 hover:border-emerald-400 hover:shadow-xs hover:-translate-y-0.5 active:scale-95 transition-all cursor-pointer">
                                <span class="text-slate-500 font-bold block truncate">Normales</span>
                                <span class="text-xs font-black text-emerald-800">{{ $realizadas }}</span>
                                <span class="text-[9px] text-slate-400">({{ $pct_realizadas }}%)</span>
                            </button>
                            <button type="button"
                                    onclick="irAControlDocente('recuperada')"
                                    title="Ver recuperadas"
                                    class="text-left bg-white hover:bg-amber-50/90 p-1.5 rounded-lg border border-amber-200/90 hover:border-amber-400 hover:shadow-xs hover:-translate-y-0.5 active:scale-95 transition-all cursor-pointer">
                                <span class="text-slate-500 font-bold block truncate">Recuperadas</span>
                                <span class="text-xs font-black text-amber-700">{{ $recuperadas }}</span>
                                <span class="text-[9px] text-slate-400">({{ $pct_recuperadas }}%)</span>
                            </button>
                            <button type="button"
                                    onclick="irAControlDocente('justificado')"
                                    title="Ver justificadas"
                                    class="text-left bg-white hover:bg-slate-100 p-1.5 rounded-lg border border-slate-200/90 hover:border-slate-400 hover:shadow-xs hover:-translate-y-0.5 active:scale-95 transition-all cursor-pointer">
                                <span class="text-slate-500 font-bold block truncate">Justificadas</span>
                                <span class="text-xs font-black text-slate-700">{{ $justificadas }}</span>
                                <span class="text-[9px] text-slate-400">({{ $pct_justificadas ?? 0 }}%)</span>
                            </button>
                        </div>
                    </div>

                    <!-- No Registradas -->
                    <button type="button"
                            onclick="irAControlDocente('no_realizada')"
                            title="Ver clases no registradas en Control Docente"
                            class="w-full text-left p-2.5 rounded-xl bg-rose-50/80 border-2 border-rose-200/90 hover:bg-rose-100/90 hover:border-rose-400 hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-150 cursor-pointer group focus:outline-none">
                        <div class="flex items-center justify-between gap-1">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0 group-hover:scale-125 transition-transform duration-150"></span>
                                <span class="font-extrabold text-xs text-rose-950 truncate group-hover:text-rose-900">No Registradas / Sin Marcar</span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <span class="text-[11px] font-black px-2 py-0.5 rounded-full bg-rose-200 text-rose-900 group-hover:bg-rose-300">{{ $no_registradas }} ({{ $pct_no_registradas }}%)</span>
                                <svg class="w-3.5 h-3.5 text-rose-600 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                        <p class="text-[10px] text-rose-700/90 font-medium mt-1">Sin marca de registro o notificadas como ausentes.</p>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Cumplimiento por Escuela (más pequeño y ajustado) -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-5 flex flex-col justify-between">
        <div>
            <!-- Header con Título y Enlace a Reporte -->
            <div class="mb-3.5 flex items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-800 flex items-center">
                        <i class="fas fa-graduation-cap mr-2 text-[#950101]"></i>
                        Cumplimiento por Escuela
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">Comparativo de clases impartidas vs no registradas</p>
                </div>
                @can('reportes')
                <a href="{{ route('reportes.cumplimiento-escuelas', ['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]) }}" 
                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#D12420] text-slate-700 hover:text-white text-[11px] font-bold transition shadow-2xs group shrink-0 cursor-pointer">
                    <span>Ver Reporte</span>
                    <i class="fas fa-arrow-right text-[9px] group-hover:translate-x-0.5 transition-transform"></i>
                </a>
                @endcan
            </div>

            @if(!empty($escuelas_stats) && count($escuelas_stats) > 0)
                <!-- Canvas para el gráfico de barras comparativo compacto -->
                <div class="w-full bg-slate-50/70 p-2.5 rounded-xl border border-slate-200/80 mb-3">
                    <div class="h-[155px] relative w-full">
                        <canvas id="chart-escuelas-canvas"
                                data-escuelas="{{ json_encode($escuelas_stats) }}">
                        </canvas>
                    </div>
                </div>

                <!-- Tarjetas interactivas de las escuelas más pequeñas y compactas -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach($escuelas_stats as $escuela)
                        @php
                            $pct = $escuela['pct'] ?? 0;
                            $badgeBg = $pct >= 90 ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($pct >= 75 ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-rose-100 text-rose-800 border-rose-200');
                            $barBg = $pct >= 90 ? 'bg-emerald-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-rose-500');
                            $nombreCorto = preg_replace('/^Escuela de\s+/i', '', $escuela['nombre']);
                        @endphp
                        <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-200/90 shadow-2xs hover:shadow-sm hover:border-slate-300 hover:bg-white hover:-translate-y-0.5 transition-all duration-150 flex flex-col justify-between gap-2">
                            <div>
                                <div class="flex items-start justify-between gap-1 mb-1">
                                    <h5 class="text-[11px] font-bold text-slate-800 leading-snug line-clamp-2" title="{{ $escuela['nombre'] }}">
                                        {{ $nombreCorto }}
                                    </h5>
                                    <span class="text-[10px] font-black px-1.5 py-0.2 rounded-full border {{ $badgeBg }} shrink-0">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200/70 rounded-full h-1 overflow-hidden mb-1.5">
                                    <div class="{{ $barBg }} h-1 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                                </div>
                                <div class="flex items-center justify-between text-[10px]">
                                    <button type="button"
                                            onclick="irAControlDocenteConBusqueda('{{ addslashes($escuela['nombre']) }}', 'impartidas')"
                                            title="Ver clases impartidas"
                                            class="font-bold text-emerald-700 hover:text-emerald-900 hover:underline cursor-pointer">
                                        {{ $escuela['impartidas'] }} Imp.
                                    </button>
                                    <button type="button"
                                            onclick="irAControlDocenteConBusqueda('{{ addslashes($escuela['nombre']) }}', 'no_realizada')"
                                            title="Ver clases no registradas"
                                            class="font-bold text-rose-700 hover:text-rose-900 hover:underline cursor-pointer">
                                        {{ $escuela['no_registradas'] }} Sin Reg.
                                    </button>
                                </div>
                            </div>

                            <button type="button"
                                    onclick="irAControlDocenteConBusqueda('{{ addslashes($escuela['nombre']) }}')"
                                    class="w-full text-center py-1 px-1.5 rounded-md bg-white hover:bg-[#D12420] text-slate-700 hover:text-white border border-slate-200/90 hover:border-[#D12420] text-[9px] font-bold shadow-2xs hover:-translate-y-0.5 active:scale-95 transition-all flex items-center justify-center gap-1 cursor-pointer group">
                                <span>Control Docente</span>
                                <i class="fas fa-chevron-right text-[7px] text-slate-400 group-hover:text-white"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-slate-400 bg-slate-50 rounded-xl border border-slate-200 text-xs font-medium my-auto">
                    No hay clases registradas para desglosar por escuela.
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

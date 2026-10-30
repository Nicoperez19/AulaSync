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

<div class="flex flex-col md:flex-row gap-6 items-stretch">
    <!-- Gráfico Donut -->
    <div class="flex flex-col items-center justify-center shrink-0 md:w-[260px] py-4">
        <div class="w-[220px] h-[220px] relative">
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
                <span id="center-label-valor" class="text-3xl font-black text-slate-800 tracking-tight leading-none">{{ $pct_impartidas }}%</span>
                <span id="center-label-sub" class="text-[11px] font-extrabold text-[#10B981] uppercase tracking-wide mt-1">Cumplimiento</span>
                <span id="center-label-detail" class="text-[10px] font-medium text-slate-400 tracking-tight mt-0.5 hidden"></span>
            </div>
        </div>
        @if($total_clases === 0)
            <p class="text-[11px] font-semibold text-slate-400 mt-2 text-center">Sin registros de clases para el período seleccionado (0 clases)</p>
        @else
            <p class="text-[11px] font-semibold text-slate-400 mt-2 text-center">Pasa el cursor sobre el gráfico para ver el desglose</p>
        @endif
    </div>

    <!-- Tarjetas Informativas -->
    <div class="flex-1 flex flex-col gap-3 justify-center min-w-0">
        <!-- Grupo 1: Impartidas / Efectivas -->
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 transition duration-150 shadow-xs">
            <button type="button"
                    onclick="irAControlDocente('impartidas')"
                    title="Ver todas las clases impartidas en Control Docente"
                    class="w-full text-left flex items-start justify-between mb-2 gap-2 p-1.5 -m-1.5 rounded-xl hover:bg-emerald-100/70 active:bg-emerald-200/60 transition-all duration-150 group cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0 group-hover:scale-110 transition-transform"></span>
                    <span class="font-extrabold text-sm text-emerald-950 leading-tight group-hover:text-emerald-800 transition-colors">Clases Impartidas / Efectivas</span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 whitespace-nowrap group-hover:bg-emerald-300 transition-colors">{{ $total_impartidas }} ({{ $pct_impartidas }}%)</span>
                    <svg class="w-4 h-4 text-emerald-600/70 group-hover:text-emerald-900 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </button>

            <!-- Detalle interno: Botones para Realizadas Normales, Recuperadas y Justificadas -->
            <div class="grid grid-cols-3 gap-2 mt-2 pt-2 border-t border-emerald-200/60 text-xs">
                <button type="button"
                        onclick="irAControlDocente('realizada')"
                        title="Ver clases realizadas normales en Control Docente"
                        class="text-left bg-white/80 hover:bg-white p-2.5 rounded-xl border border-emerald-100 hover:border-emerald-300 hover:shadow-xs active:bg-emerald-50/50 transition-all duration-150 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="text-slate-500 group-hover:text-emerald-900 font-bold block text-[11px] truncate">Realizadas Normales</span>
                        <svg class="w-3 h-3 text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all shrink-0 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-black text-emerald-800">{{ $realizadas }}</span>
                        <span class="text-[11px] font-bold text-slate-400"> ({{ $pct_realizadas }}%)</span>
                    </div>
                </button>

                <button type="button"
                        onclick="irAControlDocente('recuperada')"
                        title="Ver clases recuperadas en Control Docente"
                        class="text-left bg-white/80 hover:bg-white p-2.5 rounded-xl border border-emerald-100 hover:border-amber-300 hover:shadow-xs active:bg-amber-50/50 transition-all duration-150 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="text-slate-500 group-hover:text-amber-900 font-bold block text-[11px] truncate">Recuperadas</span>
                        <svg class="w-3 h-3 text-slate-300 group-hover:text-amber-600 group-hover:translate-x-0.5 transition-all shrink-0 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-black text-amber-700">{{ $recuperadas }}</span>
                        <span class="text-[11px] font-bold text-slate-400"> ({{ $pct_recuperadas }}%)</span>
                    </div>
                </button>

                <button type="button"
                        onclick="irAControlDocente('justificado')"
                        title="Ver clases justificadas en Control Docente"
                        class="text-left bg-white/80 hover:bg-white p-2.5 rounded-xl border border-emerald-100 hover:border-slate-300 hover:shadow-xs active:bg-slate-50 transition-all duration-150 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-slate-500/30">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="text-slate-500 group-hover:text-slate-900 font-bold block text-[11px] truncate">Justificadas</span>
                        <svg class="w-3 h-3 text-slate-300 group-hover:text-slate-600 group-hover:translate-x-0.5 transition-all shrink-0 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-black text-slate-700">{{ $justificadas }}</span>
                        <span class="text-[11px] font-bold text-slate-400"> ({{ $pct_justificadas ?? 0 }}%)</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Grupo 2: No Registradas / No Realizadas -->
        <button type="button"
                onclick="irAControlDocente('no_realizada')"
                title="Ver clases no registradas en Control Docente"
                class="w-full text-left p-4 rounded-2xl bg-rose-50/70 border border-rose-200/80 hover:bg-rose-100/70 hover:border-rose-300 hover:shadow-xs active:bg-rose-200/60 transition-all duration-150 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-rose-500/30">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0 group-hover:scale-110 transition-transform"></span>
                    <span class="font-extrabold text-sm text-rose-950 leading-tight group-hover:text-rose-900 transition-colors">No Registradas / No Realizadas</span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-rose-200 text-rose-900 whitespace-nowrap group-hover:bg-rose-300 transition-colors">{{ $no_registradas }} ({{ $pct_no_registradas }}%)</span>
                    <svg class="w-4 h-4 text-rose-600/70 group-hover:text-rose-900 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
            <p class="text-[11px] text-rose-700/90 font-medium mt-2">Clases del horario oficial sin marca de registro o notificadas como ausentes.</p>
        </button>

        @if(!empty($futuras_pendientes) && $futuras_pendientes > 0)
            <div class="px-3 py-2 rounded-xl bg-slate-100/90 border border-slate-200/80 text-[11px] text-slate-600 font-medium flex items-center justify-between gap-2">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Próximas clases programadas en el período:
                </span>
                <span class="font-bold text-slate-800 whitespace-nowrap">{{ $futuras_pendientes }} pendientes</span>
            </div>
        @endif
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
</script>
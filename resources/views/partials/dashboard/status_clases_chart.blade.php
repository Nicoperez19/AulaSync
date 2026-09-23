<!-- Controles de Filtros por Rango de Fechas -->
<div class="mb-6 rounded-2xl border border-slate-200/80 bg-slate-50 p-2.5 shadow-xs sm:p-3.5">
    <div class="flex flex-row items-center gap-1.5 overflow-x-auto whitespace-nowrap [-ms-overflow-style:none] [scrollbar-width:none] sm:gap-2 xl:justify-between">
        <div class="flex min-w-0 items-center gap-1.5 sm:gap-2">
            <span class="shrink-0 text-[10px] font-black uppercase tracking-wider text-slate-500 sm:text-xs">Período:</span>
            <button onclick="filtrarStatusClases('hoy')" id="btn-status-hoy" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'hoy' ? 'bg-blue-600 text-white shadow-xs border-blue-600' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100' }}">
                Hoy
            </button>
            <button onclick="filtrarStatusClases('semana')" id="btn-status-semana" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'semana' ? 'bg-blue-600 text-white shadow-xs border-blue-600' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100' }}">
                Esta Semana
            </button>
            <button onclick="filtrarStatusClases('mes')" id="btn-status-mes" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'mes' ? 'bg-blue-600 text-white shadow-xs border-blue-600' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100' }}">
                Este Mes
            </button>
        </div>

        <!-- Rango de fechas libre -->
        <div class="flex min-w-max items-center gap-1.5 sm:gap-2">
            <span class="shrink-0 text-[10px] font-bold text-slate-500 sm:text-xs">Desde:</span>
            <input type="date" id="status-fecha-inicio" value="{{ $fecha_inicio ?: now()->format('Y-m-d') }}" class="h-8 min-w-[120px] shrink-0 rounded-lg border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-xs">
            <span class="shrink-0 text-[10px] font-bold text-slate-500 sm:text-xs">Hasta:</span>
            <input type="date" id="status-fecha-fin" value="{{ $fecha_fin ?: now()->format('Y-m-d') }}" class="h-8 min-w-[120px] shrink-0 rounded-lg border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-xs">
            <button onclick="filtrarStatusClasesPersonalizado()" class="shrink-0 rounded-lg bg-slate-800 px-2.5 py-1.5 text-[10px] font-bold text-white transition hover:bg-slate-900 sm:text-xs">
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
                    data-no-registradas="{{ $no_registradas }}"
                    data-pct-impartidas="{{ $pct_impartidas }}"
                    data-pct-no-registradas="{{ $pct_no_registradas }}"
                    data-total-clases="{{ $total_clases }}">
            </canvas>

            <!-- Etiqueta flotante central -->
            <div id="status-clases-center-label" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none transition-all duration-200">
                <span id="center-label-valor" class="text-3xl font-black text-slate-800 tracking-tight leading-none">{{ $pct_impartidas }}%</span>
                <span id="center-label-sub" class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wide mt-1">Cumplimiento</span>
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
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 transition duration-150 hover:shadow-sm">
            <div class="flex items-start justify-between mb-2 gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="font-extrabold text-sm text-emerald-950 leading-tight">Clases Impartidas / Efectivas</span>
                </div>
                <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 whitespace-nowrap shrink-0">{{ $total_impartidas }} ({{ $pct_impartidas }}%)</span>
            </div>

            <!-- Detalle interno -->
            <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-emerald-200/60 text-xs">
                <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100">
                    <span class="text-slate-500 font-bold block text-[11px]">Realizadas Normales</span>
                    <span class="text-sm font-black text-emerald-800">{{ $realizadas }}</span>
                    <span class="text-[11px] font-bold text-slate-400"> ({{ $pct_realizadas }}%)</span>
                </div>
                <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100">
                    <span class="text-slate-500 font-bold block text-[11px]">Recuperadas</span>
                    <span class="text-sm font-black text-amber-700">{{ $recuperadas }}</span>
                    <span class="text-[11px] font-bold text-slate-400"> ({{ $pct_recuperadas }}%)</span>
                </div>
            </div>
        </div>

        <!-- Grupo 2: No Registradas / No Realizadas -->
        <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-200/80 transition duration-150 hover:shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0"></span>
                    <span class="font-extrabold text-sm text-rose-950 leading-tight">No Registradas / No Realizadas</span>
                </div>
                <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-rose-200 text-rose-900 whitespace-nowrap shrink-0">{{ $no_registradas }} ({{ $pct_no_registradas }}%)</span>
            </div>
            <p class="text-[11px] text-rose-700/90 font-medium mt-2">Clases del horario oficial sin marca de asistencia o notificadas como ausentes.</p>
        </div>

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

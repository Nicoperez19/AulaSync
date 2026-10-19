@php
    $clasesDetalleCollection = collect($clases_detalle ?? []);
    $clasesNoRealizadasPreview = $clasesDetalleCollection->where('categoria', 'no_registrada')->take(3);
    $fechaInicioFmt = !empty($fecha_inicio) ? \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') : now()->format('d/m/Y');
    $fechaFinFmt = !empty($fecha_fin) ? \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') : now()->format('d/m/Y');
    $baseControlUrl = route('clases-no-realizadas.index');
@endphp

<!-- Controles de Filtros por Rango de Fechas -->
<div class="mb-5 rounded-2xl border border-slate-200/80 bg-slate-50 p-2.5 shadow-xs sm:p-3.5">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex min-w-0 items-center gap-1.5 sm:gap-2">
            <span class="shrink-0 text-[10px] font-black uppercase tracking-wider text-slate-500 sm:text-xs">Período:</span>
            <button type="button" onclick="filtrarStatusClases('hoy')" id="btn-status-hoy" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'hoy' ? 'bg-blue-600 text-white shadow-xs border-blue-600' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100' }}">
                Hoy
            </button>
            <button type="button" onclick="filtrarStatusClases('semana')" id="btn-status-semana" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'semana' ? 'bg-blue-600 text-white shadow-xs border-blue-600' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100' }}">
                Esta Semana
            </button>
            <button type="button" onclick="filtrarStatusClases('mes')" id="btn-status-mes" class="shrink-0 rounded-lg border px-2.5 py-1.5 text-[10px] font-extrabold transition sm:text-xs {{ ($rango ?? 'hoy') === 'mes' ? 'bg-blue-600 text-white shadow-xs border-blue-600' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100' }}">
                Este Mes
            </button>
        </div>

        <!-- Rango de fechas libre -->
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
            <span class="shrink-0 text-[10px] font-bold text-slate-500 sm:text-xs">Desde:</span>
            <input type="date" id="status-fecha-inicio" value="{{ $fecha_inicio ?: now()->format('Y-m-d') }}" class="h-8 min-w-[118px] shrink-0 rounded-lg border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-xs">
            <span class="shrink-0 text-[10px] font-bold text-slate-500 sm:text-xs">Hasta:</span>
            <input type="date" id="status-fecha-fin" value="{{ $fecha_fin ?: now()->format('Y-m-d') }}" class="h-8 min-w-[118px] shrink-0 rounded-lg border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-xs">
            <button type="button" onclick="filtrarStatusClasesPersonalizado()" class="shrink-0 rounded-lg bg-slate-800 px-2.5 py-1.5 text-[10px] font-bold text-white transition hover:bg-slate-900 sm:text-xs">
                Filtrar
            </button>
        </div>
    </div>
</div>

<div class="flex flex-col md:flex-row gap-5 items-stretch">
    <!-- Columna Izquierda: Gráfico Donut + Barra de Distribución + Acciones Rápidas -->
    <div class="flex flex-col items-center justify-between shrink-0 md:w-[245px] bg-slate-50/70 rounded-2xl border border-slate-200/70 p-4">
        <div class="w-full flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1">
            <span>Resumen de Cumplimiento</span>
            <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 font-extrabold">{{ $total_clases }} clases</span>
        </div>

        <div class="w-[195px] h-[195px] relative my-1 cursor-pointer group" onclick="abrirDetalleStatusClases('todas')" title="Haz clic para ver el listado detallado de clases">
            <canvas id="chart-status-clases-canvas"
                    data-realizadas="{{ $realizadas }}"
                    data-recuperadas="{{ $recuperadas }}"
                    data-justificadas="{{ $justificadas ?? 0 }}"
                    data-no-registradas="{{ $no_registradas }}"
                    data-pct-impartidas="{{ $pct_impartidas }}"
                    data-pct-no-registradas="{{ $pct_no_registradas }}"
                    data-total-clases="{{ $total_clases }}">
            </canvas>

            <!-- Etiqueta flotante central -->
            <div id="status-clases-center-label" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none transition-all duration-200">
                <span id="center-label-valor" class="text-3xl font-black text-slate-800 tracking-tight leading-none">{{ $pct_impartidas }}%</span>
                <span id="center-label-sub" class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wide mt-1">Cumplimiento</span>
                <span id="center-label-detail" class="text-[10px] font-medium text-slate-400 tracking-tight mt-0.5 hidden"></span>
                <span class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-[9px] font-bold text-slate-600 group-hover:bg-blue-50 group-hover:text-blue-600 transition">
                    <i class="fas fa-hand-pointer text-[8px]"></i> Ver detalle
                </span>
            </div>
        </div>

        <!-- Barra proporcional segmentada -->
        <div class="w-full mt-2">
            <div class="w-full h-2.5 rounded-full bg-slate-200 overflow-hidden flex shadow-inner">
                @if($total_clases > 0)
                    @if($pct_realizadas > 0)
                        <div style="width: {{ $pct_realizadas }}%" class="bg-emerald-500 h-full transition-all duration-500" title="Realizadas: {{ $realizadas }} ({{ $pct_realizadas }}%)"></div>
                    @endif
                    @if($pct_recuperadas > 0)
                        <div style="width: {{ $pct_recuperadas }}%" class="bg-amber-500 h-full transition-all duration-500" title="Recuperadas: {{ $recuperadas }} ({{ $pct_recuperadas }}%)"></div>
                    @endif
                    @if(($pct_justificadas ?? 0) > 0)
                        <div style="width: {{ $pct_justificadas }}%" class="bg-blue-500 h-full transition-all duration-500" title="Justificadas: {{ $justificadas }} ({{ $pct_justificadas }}%)"></div>
                    @endif
                    @if($pct_no_registradas > 0)
                        <div style="width: {{ $pct_no_registradas }}%" class="bg-rose-500 h-full transition-all duration-500" title="No Realizadas: {{ $no_registradas }} ({{ $pct_no_registradas }}%)"></div>
                    @endif
                @endif
            </div>

            <div class="grid grid-cols-2 gap-1.5 mt-3">
                <button type="button" onclick="abrirDetalleStatusClases('impartidas')" class="flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-extrabold shadow-2xs transition cursor-pointer">
                    <i class="fas fa-check-circle text-[10px]"></i>
                    <span>Realizadas ({{ $total_impartidas }})</span>
                </button>
                <button type="button" onclick="abrirDetalleStatusClases('no_registradas')" class="flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-extrabold shadow-2xs transition cursor-pointer">
                    <i class="fas fa-exclamation-circle text-[10px]"></i>
                    <span>No Realiz. ({{ $no_registradas }})</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Tarjetas Interactivas Detalladas -->
    <div class="flex-1 flex flex-col gap-3.5 justify-between min-w-0">

        <!-- 1. TARJETA CLASES IMPARTIDAS / EFECTIVAS -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-br from-emerald-50/90 via-emerald-50/50 to-white border border-emerald-200/90 shadow-2xs transition duration-200 hover:shadow-md hover:border-emerald-300">
            <!-- Cabecera clickeable -->
            <div onclick="abrirDetalleStatusClases('impartidas')" class="flex items-center justify-between gap-2 cursor-pointer group">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-600 shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="fas fa-check-double text-xs"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-emerald-950 leading-tight group-hover:text-emerald-700 transition">Clases Impartidas / Efectivas</span>
                            <span class="hidden sm:inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-md group-hover:bg-emerald-200 transition">
                                <i class="fas fa-search-plus text-[9px]"></i> Ver clases
                            </span>
                        </div>
                        <p class="text-[11px] text-emerald-700/80 font-medium truncate">Haz clic en cualquier categoría para inspeccionar las clases realizadas</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-full bg-emerald-200/90 text-emerald-950 whitespace-nowrap shadow-2xs">
                        {{ $total_impartidas }} ({{ $pct_impartidas }}%)
                    </span>
                    <i class="fas fa-chevron-right text-xs text-emerald-400 group-hover:text-emerald-700 group-hover:translate-x-0.5 transition"></i>
                </div>
            </div>

            <!-- Sub-tarjetas clickeables de Impartidas -->
            <div class="grid grid-cols-3 gap-2 mt-3 pt-2.5 border-t border-emerald-200/60 text-xs">
                <!-- Realizadas Normales -->
                <button type="button" onclick="abrirDetalleStatusClases('realizadas')" class="text-left bg-white hover:bg-emerald-50/80 p-2.5 rounded-xl border border-emerald-200/80 hover:border-emerald-400 transition group cursor-pointer shadow-2xs">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-slate-600 font-bold text-[11px] truncate group-hover:text-emerald-800">Realizadas Normales</span>
                        <i class="fas fa-arrow-up-right-from-square text-[9px] text-slate-300 group-hover:text-emerald-600 transition shrink-0"></i>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-base font-black text-emerald-700">{{ $realizadas }}</span>
                        <span class="text-[11px] font-bold text-slate-400">({{ $pct_realizadas }}%)</span>
                    </div>
                    <div class="w-full h-1 bg-emerald-100 rounded-full mt-1.5 overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min(100, $pct_realizadas) }}%"></div>
                    </div>
                </button>

                <!-- Recuperadas -->
                <button type="button" onclick="abrirDetalleStatusClases('recuperadas')" class="text-left bg-white hover:bg-amber-50/70 p-2.5 rounded-xl border border-emerald-200/80 hover:border-amber-400 transition group cursor-pointer shadow-2xs">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-slate-600 font-bold text-[11px] truncate group-hover:text-amber-800">Recuperadas</span>
                        <i class="fas fa-arrow-up-right-from-square text-[9px] text-slate-300 group-hover:text-amber-600 transition shrink-0"></i>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-base font-black text-amber-600">{{ $recuperadas }}</span>
                        <span class="text-[11px] font-bold text-slate-400">({{ $pct_recuperadas }}%)</span>
                    </div>
                    <div class="w-full h-1 bg-amber-100 rounded-full mt-1.5 overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full" style="width: {{ min(100, $pct_recuperadas) }}%"></div>
                    </div>
                </button>

                <!-- Justificadas -->
                <button type="button" onclick="abrirDetalleStatusClases('justificadas')" class="text-left bg-white hover:bg-blue-50/70 p-2.5 rounded-xl border border-emerald-200/80 hover:border-blue-400 transition group cursor-pointer shadow-2xs">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-slate-600 font-bold text-[11px] truncate group-hover:text-blue-800">Justificadas</span>
                        <i class="fas fa-arrow-up-right-from-square text-[9px] text-slate-300 group-hover:text-blue-600 transition shrink-0"></i>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-base font-black text-blue-600">{{ $justificadas }}</span>
                        <span class="text-[11px] font-bold text-slate-400">({{ $pct_justificadas ?? 0 }}%)</span>
                    </div>
                    <div class="w-full h-1 bg-blue-100 rounded-full mt-1.5 overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, $pct_justificadas ?? 0) }}%"></div>
                    </div>
                </button>
            </div>
        </div>

        <!-- 2. TARJETA NO REGISTRADAS / NO REALIZADAS (VISUAL Y DETALLADA) -->
        <div class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-br from-rose-50/90 via-rose-50/50 to-white border border-rose-200/90 shadow-2xs transition duration-200 hover:shadow-md hover:border-rose-300">
            <!-- Cabecera clickeable -->
            <div onclick="abrirDetalleStatusClases('no_registradas')" class="flex items-center justify-between gap-2 cursor-pointer group">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-8 h-8 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-rose-600 shrink-0 group-hover:bg-rose-600 group-hover:text-white transition">
                        <i class="fas fa-user-clock text-xs"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-sm text-rose-950 leading-tight group-hover:text-rose-700 transition">No Registradas / No Realizadas</span>
                            <span class="hidden sm:inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-100/90 px-2 py-0.5 rounded-md group-hover:bg-rose-200 transition">
                                <i class="fas fa-list-ul text-[9px]"></i> Ver desglose
                            </span>
                        </div>
                        <p class="text-[11px] text-rose-700/85 font-medium truncate">Clases del horario oficial sin marca de asistencia o pendientes de recuperar</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-black px-2.5 py-1 rounded-full bg-rose-200/90 text-rose-950 whitespace-nowrap shadow-2xs">
                        {{ $no_registradas }} ({{ $pct_no_registradas }}%)
                    </span>
                    <i class="fas fa-chevron-right text-xs text-rose-400 group-hover:text-rose-700 group-hover:translate-x-0.5 transition"></i>
                </div>
            </div>

            <!-- Sub-métricas visuales de No Realizadas -->
            <div class="grid grid-cols-3 gap-2 mt-3 pt-2.5 border-t border-rose-200/60 text-xs">
                <!-- Sin Registro / Ausentes -->
                <button type="button" onclick="abrirDetalleStatusClases('sin_registro')" class="text-left bg-white hover:bg-rose-50/80 p-2.5 rounded-xl border border-rose-200/80 hover:border-rose-400 transition group cursor-pointer shadow-2xs">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-slate-600 font-bold text-[11px] truncate group-hover:text-rose-800">Sin Marca / Ausentes</span>
                        <i class="fas fa-arrow-up-right-from-square text-[9px] text-slate-300 group-hover:text-rose-600 transition shrink-0"></i>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-base font-black text-rose-700">{{ $no_realizadas_sin_justificar ?? $no_registradas }}</span>
                        <span class="text-[11px] font-bold text-slate-400">({{ $pct_sin_justificar ?? $pct_no_registradas }}%)</span>
                    </div>
                    <div class="w-full h-1 bg-rose-100 rounded-full mt-1.5 overflow-hidden">
                        <div class="h-full bg-rose-500 rounded-full" style="width: {{ min(100, $pct_sin_justificar ?? $pct_no_registradas) }}%"></div>
                    </div>
                </button>

                <!-- Pendientes de Recuperación -->
                <button type="button" onclick="abrirDetalleStatusClases('pendiente_recuperacion')" class="text-left bg-white hover:bg-orange-50/80 p-2.5 rounded-xl border border-rose-200/80 hover:border-orange-400 transition group cursor-pointer shadow-2xs">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-slate-600 font-bold text-[11px] truncate group-hover:text-orange-800">Pend. Recuperación</span>
                        <i class="fas fa-arrow-up-right-from-square text-[9px] text-slate-300 group-hover:text-orange-600 transition shrink-0"></i>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-base font-black text-orange-600">{{ $no_realizadas_pendientes ?? 0 }}</span>
                        <span class="text-[11px] font-bold text-slate-400">({{ $pct_pendientes_rec ?? 0 }}%)</span>
                    </div>
                    <div class="w-full h-1 bg-orange-100 rounded-full mt-1.5 overflow-hidden">
                        <div class="h-full bg-orange-500 rounded-full" style="width: {{ min(100, $pct_pendientes_rec ?? 0) }}%"></div>
                    </div>
                </button>

                <!-- Impacto: Docentes y Salas afectadas -->
                <button type="button" onclick="abrirDetalleStatusClases('no_registradas')" class="text-left bg-white hover:bg-rose-50/80 p-2.5 rounded-xl border border-rose-200/80 hover:border-rose-400 transition group cursor-pointer shadow-2xs">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-slate-600 font-bold text-[11px] truncate group-hover:text-rose-800">Alcance en Período</span>
                        <i class="fas fa-users text-[9px] text-rose-400 group-hover:text-rose-600 transition shrink-0"></i>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] font-extrabold text-slate-700">
                        <span class="inline-flex items-center gap-1 text-rose-800" title="Docentes con clases sin registrar">
                            <i class="fas fa-chalkboard-teacher text-[10px] text-rose-500"></i> {{ $docentes_afectados ?? 0 }} doc.
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="inline-flex items-center gap-1 text-slate-600" title="Salas / laboratorios involucrados">
                            <i class="fas fa-door-open text-[10px] text-slate-400"></i> {{ $espacios_afectados ?? 0 }} salas
                        </span>
                    </div>
                    <span class="block text-[10px] text-slate-400 font-semibold mt-1 truncate">{{ $asignaturas_afectadas ?? 0 }} asignaturas afectadas</span>
                </button>
            </div>

            <!-- Vista previa visual de las clases no realizadas -->
            @if($no_registradas > 0 && $clasesNoRealizadasPreview->isNotEmpty())
                <div class="mt-3 pt-2.5 border-t border-rose-200/60">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-800 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Últimas clases sin registro detectadas
                        </span>
                        <button type="button" onclick="abrirDetalleStatusClases('no_registradas')" class="text-[11px] font-extrabold text-rose-700 hover:text-rose-900 underline decoration-rose-300 underline-offset-2 cursor-pointer">
                            Ver todas ({{ $no_registradas }}) →
                        </button>
                    </div>

                    <div class="space-y-1.5">
                        @foreach($clasesNoRealizadasPreview as $itemNoReg)
                            <div onclick="abrirDetalleStatusClases('no_registradas')" class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white/95 hover:bg-rose-50/90 border border-rose-200/70 hover:border-rose-300 transition cursor-pointer shadow-2xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="flex flex-col items-center justify-center px-2 py-1 rounded-lg bg-rose-100/80 text-rose-800 shrink-0 min-w-[52px]">
                                        <span class="text-[9px] font-black uppercase leading-none">{{ $itemNoReg['dia_corto'] ?? '' }}</span>
                                        <span class="text-[10px] font-extrabold leading-tight mt-0.5">{{ \Carbon\Carbon::parse($itemNoReg['fecha'])->format('d/m') }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-extrabold text-slate-800 truncate">{{ $itemNoReg['asignatura'] }}</span>
                                            @if(!empty($itemNoReg['seccion']))
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded">Sec. {{ $itemNoReg['seccion'] }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 font-medium truncate">
                                            <span class="truncate"><i class="fas fa-user-tie text-[9px] text-slate-400 mr-1"></i>{{ $itemNoReg['profesor'] }}</span>
                                            <span class="text-slate-300">•</span>
                                            <span class="shrink-0 font-semibold text-slate-600">{{ $itemNoReg['horario'] }} ({{ $itemNoReg['modulos'] }})</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[10px] font-extrabold text-slate-700">
                                        <i class="fas fa-door-open text-[9px] text-rose-500 mr-0.5"></i>{{ $itemNoReg['id_espacio'] }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($itemNoReg['subtipo'] ?? '') === 'pendiente_recuperacion' ? 'bg-orange-100 text-orange-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ ($itemNoReg['subtipo'] ?? '') === 'pendiente_recuperacion' ? 'Pendiente' : 'Sin marca' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Botones de acción directa al pie de No Realizadas -->
                    <div class="flex flex-wrap items-center justify-between gap-2 mt-2.5 pt-2 border-t border-rose-100">
                        <button type="button" onclick="abrirDetalleStatusClases('no_registradas')" class="inline-flex items-center gap-1.5 text-[11px] font-extrabold text-rose-700 hover:text-rose-900 cursor-pointer">
                            <i class="fas fa-expand-alt text-[10px]"></i>
                            <span>Inspeccionar detalle completo aquí</span>
                        </button>
                        <a href="{{ $baseControlUrl }}?estado=no_realizada&fecha_inicio={{ $fecha_inicio }}&fecha_fin={{ $fecha_fin }}"
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold shadow-2xs transition">
                            <span>Gestionar en Control Docente</span>
                            <i class="fas fa-arrow-right text-[9px]"></i>
                        </a>
                    </div>
                </div>
            @elseif($total_clases > 0)
                <div class="mt-2.5 flex items-center justify-between px-3 py-2 rounded-xl bg-emerald-50/90 border border-emerald-200/80 text-emerald-800 text-[11px] font-bold">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-emerald-600"></i>
                        ¡Sin ausencias ni clases sin registrar en el período seleccionado!
                    </span>
                </div>
            @endif
        </div>

        <!-- 3. PRÓXIMAS CLASES PROGRAMADAS EN EL PERÍODO -->
        @if(!empty($futuras_pendientes) && $futuras_pendientes > 0)
            <button type="button" onclick="abrirDetalleStatusClases('futuras')" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-100/90 hover:bg-slate-200/80 border border-slate-200/90 text-[11px] text-slate-700 font-semibold flex items-center justify-between gap-2 transition cursor-pointer group">
                <span class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-blue-600 shrink-0">
                        <i class="fas fa-clock text-[10px]"></i>
                    </span>
                    <span>Próximas clases programadas / en curso en el período:</span>
                </span>
                <span class="inline-flex items-center gap-1.5 font-extrabold text-slate-900 whitespace-nowrap group-hover:text-blue-700">
                    <span>{{ $futuras_pendientes }} pendientes</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-400 group-hover:text-blue-600"></i>
                </span>
            </button>
        @endif
    </div>
</div>

<!-- =========================================================================
     MODAL INTERACTIVO DE DESGLOSE DE CLASES (REALIZADAS Y NO REALIZADAS)
     ========================================================================= -->
<div id="modal-status-clases-detalle"
     data-base-control-url="{{ $baseControlUrl }}"
     data-fecha-inicio="{{ $fecha_inicio }}"
     data-fecha-fin="{{ $fecha_fin }}"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-3 sm:p-6 overflow-y-auto"
     onclick="if(event.target === this) cerrarDetalleStatusClases()">

    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden animate-fadeIn">
        <!-- Cabecera del Modal -->
        <div class="px-5 py-4 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-400 shrink-0">
                    <i class="fas fa-chalkboard-teacher text-lg"></i>
                </div>
                <div>
                    <h4 id="modal-status-titulo" class="text-base sm:text-lg font-black tracking-tight text-white">
                        Detalle de Clases del Período
                    </h4>
                    <p class="text-xs text-slate-300 font-medium flex items-center gap-2">
                        <span><i class="far fa-calendar-alt mr-1 text-blue-400"></i>{{ $fechaInicioFmt }} al {{ $fechaFinFmt }}</span>
                        <span>•</span>
                        <span>Cumplimiento: <strong class="text-emerald-400">{{ $pct_impartidas }}%</strong></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a id="modal-status-link-control"
                   href="{{ $baseControlUrl }}?fecha_inicio={{ $fecha_inicio }}&fecha_fin={{ $fecha_fin }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-extrabold shadow-sm transition">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Abrir en Control de Clases</span>
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                </a>
                <button type="button" onclick="cerrarDetalleStatusClases()" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" title="Cerrar (Esc)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Barra de Pestañas de Filtro + Buscador Instantáneo -->
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col gap-3 shrink-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <!-- Pestañas -->
                <div class="flex flex-wrap items-center gap-1.5" id="modal-status-tabs">
                    <button type="button" data-tab="todas" onclick="cambiarTabDetalleStatusClases('todas')"
                            class="status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5">
                        <span>Todas</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-slate-200/80 text-slate-800">{{ $total_clases }}</span>
                    </button>

                    <button type="button" data-tab="no_registradas" onclick="cambiarTabDetalleStatusClases('no_registradas')"
                            class="status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span>No Realizadas</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-rose-100 text-rose-800">{{ $no_registradas }}</span>
                    </button>

                    <button type="button" data-tab="impartidas" onclick="cambiarTabDetalleStatusClases('impartidas')"
                            class="status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Impartidas (Total)</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-emerald-100 text-emerald-800">{{ $total_impartidas }}</span>
                    </button>

                    <button type="button" data-tab="realizadas" onclick="cambiarTabDetalleStatusClases('realizadas')"
                            class="status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5">
                        <i class="fas fa-check text-[10px] text-emerald-600"></i>
                        <span>Realizadas Normales</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-emerald-100 text-emerald-800">{{ $realizadas }}</span>
                    </button>

                    <button type="button" data-tab="recuperadas" onclick="cambiarTabDetalleStatusClases('recuperadas')"
                            class="status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5">
                        <i class="fas fa-sync-alt text-[10px] text-amber-600"></i>
                        <span>Recuperadas</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-amber-100 text-amber-800">{{ $recuperadas }}</span>
                    </button>

                    <button type="button" data-tab="justificadas" onclick="cambiarTabDetalleStatusClases('justificadas')"
                            class="status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5">
                        <i class="fas fa-shield-alt text-[10px] text-blue-600"></i>
                        <span>Justificadas</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-blue-100 text-blue-800">{{ $justificadas }}</span>
                    </button>

                    @if(!empty($futuras_pendientes) && $futuras_pendientes > 0)
                        <button type="button" data-tab="futuras" onclick="cambiarTabDetalleStatusClases('futuras')"
                                class="status-modal-tab px-3 py-1.5 rounded-xl text-xs font-extrabold border transition cursor-pointer flex items-center gap-1.5">
                            <i class="fas fa-clock text-[10px] text-slate-500"></i>
                            <span>Próximas / En Curso</span>
                            <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-slate-200 text-slate-700">{{ $futuras_pendientes }}</span>
                        </button>
                    @endif
                </div>

                <!-- Buscador rápido dentro del modal -->
                <div class="relative w-full sm:w-64">
                    <input type="text"
                           id="modal-status-search"
                           oninput="filtrarItemsModalStatusClases()"
                           placeholder="Buscar docente, asignatura o sala..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs font-semibold border border-slate-300 rounded-xl bg-white text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <i class="fas fa-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Cuerpo del Modal: Listado Visual de Clases -->
        <div class="p-4 sm:p-5 overflow-y-auto flex-1 space-y-2.5 bg-slate-50/40" id="modal-status-lista">
            @forelse($clasesDetalleCollection as $claseItem)
                @php
                    $cat = $claseItem['categoria'] ?? 'realizada';
                    $sub = $claseItem['subtipo'] ?? '';
                    $borderClass = match($cat) {
                        'no_registrada' => 'border-l-4 border-l-rose-500 border-rose-200/80 hover:border-rose-300 bg-rose-50/20',
                        'recuperada'    => 'border-l-4 border-l-amber-500 border-amber-200/80 hover:border-amber-300 bg-amber-50/20',
                        'justificada'   => 'border-l-4 border-l-blue-500 border-blue-200/80 hover:border-blue-300 bg-blue-50/20',
                        'futura'        => 'border-l-4 border-l-slate-400 border-slate-200 hover:border-slate-300 bg-slate-50/40',
                        default         => 'border-l-4 border-l-emerald-500 border-emerald-200/80 hover:border-emerald-300 bg-white',
                    };
                    $badgeClass = match($cat) {
                        'no_registrada' => $sub === 'pendiente_recuperacion'
                            ? 'bg-orange-100 text-orange-800 border-orange-200'
                            : 'bg-rose-100 text-rose-800 border-rose-200',
                        'recuperada'    => 'bg-amber-100 text-amber-800 border-amber-200',
                        'justificada'   => 'bg-blue-100 text-blue-800 border-blue-200',
                        'futura'        => 'bg-slate-100 text-slate-700 border-slate-200',
                        default         => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    };
                    $iconClass = match($cat) {
                        'no_registrada' => $sub === 'pendiente_recuperacion' ? 'fas fa-calendar-plus text-orange-600' : 'fas fa-times-circle text-rose-600',
                        'recuperada'    => 'fas fa-sync-alt text-amber-600',
                        'justificada'   => 'fas fa-shield-alt text-blue-600',
                        'futura'        => 'fas fa-clock text-slate-500',
                        default         => $sub === 'qr' ? 'fas fa-qrcode text-emerald-600' : 'fas fa-check-circle text-emerald-600',
                    };
                    $estadoQueryParam = match($cat) {
                        'no_registrada' => $sub === 'pendiente_recuperacion' ? 'pendiente' : 'no_realizada',
                        'recuperada'    => 'recuperada',
                        'justificada'   => 'justificado',
                        'realizada'     => 'realizada',
                        default         => '',
                    };
                    $searchText = mb_strtolower(
                        ($claseItem['asignatura'] ?? '') . ' ' .
                        ($claseItem['codigo_asignatura'] ?? '') . ' ' .
                        ($claseItem['profesor'] ?? '') . ' ' .
                        ($claseItem['run_profesor'] ?? '') . ' ' .
                        ($claseItem['id_espacio'] ?? '') . ' ' .
                        ($claseItem['espacio'] ?? '') . ' ' .
                        ($claseItem['carrera'] ?? '') . ' ' .
                        ($claseItem['fecha_formateada'] ?? '')
                    );
                @endphp

                <div class="status-modal-item rounded-xl border p-3.5 shadow-2xs transition duration-150 {{ $borderClass }}"
                     data-categoria="{{ $cat }}"
                     data-subtipo="{{ $sub }}"
                     data-search="{{ $searchText }}">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <!-- Izquierda: Fecha, Horario, Sala y Asignatura -->
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <!-- Bloque Fecha y Hora -->
                            <div class="flex flex-col items-center justify-center px-2.5 py-2 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-800 shrink-0 min-w-[78px] text-center">
                                <span class="text-[10px] font-black uppercase text-slate-500 leading-none">{{ $claseItem['dia_corto'] }}</span>
                                <span class="text-xs font-black text-slate-900 mt-0.5">{{ $claseItem['fecha_formateada'] }}</span>
                                <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded mt-1">{{ $claseItem['modulos'] }}</span>
                            </div>

                            <!-- Información de Asignatura, Docente y Motivo -->
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 text-white text-[10px] font-extrabold">
                                        {{ $claseItem['codigo_asignatura'] }}
                                    </span>
                                    <h5 class="text-sm font-extrabold text-slate-900 truncate">
                                        {{ $claseItem['asignatura'] }}
                                    </h5>
                                    @if(!empty($claseItem['seccion']))
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-600">
                                            Sección {{ $claseItem['seccion'] }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-xs text-slate-600">
                                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-800">
                                        <i class="fas fa-user-tie text-blue-600 text-[11px]"></i>
                                        {{ $claseItem['profesor'] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-door-open text-slate-500 text-[10px]"></i>
                                        {{ $claseItem['espacio'] }} ({{ $claseItem['id_espacio'] }})
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-600">
                                        <i class="far fa-clock text-slate-400 text-[10px]"></i>
                                        {{ $claseItem['horario'] }}
                                    </span>
                                    @if(!empty($claseItem['carrera']))
                                        <span class="text-[11px] text-slate-500 truncate">
                                            <i class="fas fa-graduation-cap text-slate-400 mr-1"></i>{{ $claseItem['carrera'] }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Detalle / Motivo / Hora de ingreso -->
                                <div class="mt-2 flex items-center gap-1.5 text-[11px] {{ $cat === 'no_registrada' ? 'text-rose-700 font-semibold' : 'text-slate-600 font-medium' }}">
                                    <i class="{{ $iconClass }} text-[11px]"></i>
                                    <span>{{ $claseItem['detalle'] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Derecha: Estado Badge + Botón Acción en Control de Clases -->
                        <div class="flex sm:flex-row lg:flex-col items-center lg:items-end justify-between gap-2 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold border {{ $badgeClass }}">
                                <i class="{{ $iconClass }} text-[10px]"></i>
                                <span>{{ $claseItem['estado_badge'] }}</span>
                            </span>

                            @if($cat !== 'futura')
                                <a href="{{ $baseControlUrl }}?fecha_inicio={{ $claseItem['fecha'] }}&fecha_fin={{ $claseItem['fecha'] }}{{ $estadoQueryParam ? '&estado=' . $estadoQueryParam : '' }}&search={{ urlencode($claseItem['profesor'] ?? '') }}"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-600 text-slate-700 hover:text-white text-[11px] font-bold transition">
                                    <span>Ver en Control Docente</span>
                                    <i class="fas fa-arrow-right text-[9px]"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-400">
                    <i class="fas fa-calendar-times text-3xl mb-2"></i>
                    <p class="font-bold text-sm text-slate-600">No se encontraron clases en este período.</p>
                </div>
            @endforelse

            <!-- Mensaje cuando el filtro no tiene resultados -->
            <div id="modal-status-empty-filter" class="hidden text-center py-12 text-slate-400">
                <i class="fas fa-check-circle text-3xl mb-2 text-emerald-400"></i>
                <p class="font-bold text-sm text-slate-600">No hay clases en esta categoría para el filtro seleccionado.</p>
            </div>
        </div>

        <!-- Pie del Modal -->
        <div class="px-5 py-3 bg-white border-t border-slate-200 flex items-center justify-between gap-3 shrink-0 text-xs text-slate-500">
            <span id="modal-status-contador-pie" class="font-bold text-slate-700">
                Mostrando {{ $clasesDetalleCollection->count() }} registros
            </span>
            <button type="button" onclick="cerrarDetalleStatusClases()" class="px-4 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 font-bold text-slate-700 transition cursor-pointer">
                Cerrar ventana
            </button>
        </div>
    </div>
</div>

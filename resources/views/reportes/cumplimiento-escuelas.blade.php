<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Cumplimiento por Escuela de la Sede</h2>
                    <p class="text-xs sm:text-sm text-gray-500">
                        Análisis comparativo de clases impartidas (efectivas) vs no registradas por escuela de la sede
                    </p>
                </div>
            </div>

            <!-- Botones de Acción y Exportación -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('clases-no-realizadas.index') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition">
                    <i class="fas fa-calendar-check text-[#D12420]"></i>
                    <span>Control de Clases</span>
                </a>
                <a href="{{ route('reportes.cumplimiento-escuelas.export', ['format' => 'excel'] + request()->all()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar Excel</span>
                </a>
                <a href="{{ route('reportes.cumplimiento-escuelas.export', ['format' => 'pdf'] + request()->all()) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-[#D12420] hover:bg-[#950101] text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition">
                    <i class="fas fa-file-pdf"></i>
                    <span>Exportar PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="px-4 sm:px-6 py-6 space-y-6 max-w-7xl mx-auto">
        <!-- Filtros de Consulta -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200 shadow-xs">
            <form method="GET" action="{{ route('reportes.cumplimiento-escuelas') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <!-- Período / Semestre -->
                <div>
                    <label for="periodo" class="block text-xs font-bold text-gray-700 uppercase mb-1">Semestre</label>
                    <select name="periodo" id="periodo" class="w-full text-xs sm:text-sm rounded-xl border border-gray-300 bg-white py-2 px-3 focus:border-[#D12420] focus:ring-2 focus:ring-[#D12420]/20">
                        @foreach($periodosDisponibles as $p)
                            <option value="{{ $p }}" {{ $periodo === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Desde -->
                <div>
                    <label for="fecha_inicio" class="block text-xs font-bold text-gray-700 uppercase mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}"
                           class="w-full text-xs sm:text-sm rounded-xl border border-gray-300 bg-white py-2 px-3 focus:border-[#D12420] focus:ring-2 focus:ring-[#D12420]/20">
                </div>

                <!-- Hasta -->
                <div>
                    <label for="fecha_fin" class="block text-xs font-bold text-gray-700 uppercase mb-1">Hasta</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin }}"
                           class="w-full text-xs sm:text-sm rounded-xl border border-gray-300 bg-white py-2 px-3 focus:border-[#D12420] focus:ring-2 focus:ring-[#D12420]/20">
                </div>

                <!-- Escuela -->
                <div>
                    <label for="escuela" class="block text-xs font-bold text-gray-700 uppercase mb-1">Escuela</label>
                    <select name="escuela" id="escuela" class="w-full text-xs sm:text-sm rounded-xl border border-gray-300 bg-white py-2 px-3 focus:border-[#D12420] focus:ring-2 focus:ring-[#D12420]/20">
                        <option value="">Todas las Escuelas</option>
                        @foreach($escuelasDisponibles as $esc)
                            <option value="{{ $esc }}" {{ $escuelaFiltro === $esc ? 'selected' : '' }}>{{ $esc }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones Filtrar y Limpiar -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-[#191919] hover:bg-[#D12420] text-white text-xs sm:text-sm font-bold transition shadow-xs">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    <a href="{{ route('reportes.cumplimiento-escuelas') }}" class="py-2 px-3 rounded-xl border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-xs sm:text-sm font-semibold transition" title="Restablecer filtros">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tarjetas KPIs Globales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total clases -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Total Clases Evaluadas</span>
                    <span class="text-2xl sm:text-3xl font-black text-slate-800 mt-1 block">{{ $totalGeneral }}</span>
                    <span class="text-[11px] text-gray-400 font-medium">Bloques de horario oficial</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            <!-- Clases Impartidas -->
            <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/30 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Clases Impartidas</span>
                    <span class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1 block">{{ $impartidasGeneral }}</span>
                    <span class="text-[11px] text-emerald-600 font-semibold">{{ $pctGeneral }}% de efectividad</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>

            <!-- Clases No Registradas -->
            <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/30 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-rose-800 uppercase tracking-wider block">No Registradas</span>
                    <span class="text-2xl sm:text-3xl font-black text-rose-700 mt-1 block">{{ $noRegistradasGeneral }}</span>
                    <span class="text-[11px] text-rose-600 font-semibold">{{ $totalGeneral > 0 ? round(($noRegistradasGeneral / $totalGeneral) * 100, 1) : 0 }}% sin marca</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>

            <!-- % Cumplimiento General -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">% Cumplimiento Sede</span>
                    <span class="text-2xl sm:text-3xl font-black {{ $pctGeneral >= 90 ? 'text-emerald-700' : ($pctGeneral >= 75 ? 'text-amber-600' : 'text-rose-700') }} mt-1 block">
                        {{ $pctGeneral }}%
                    </span>
                    <span class="text-[11px] text-gray-400 font-medium">Promedio ponderado</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
        </div>

        <!-- Gráfico Comparativo de Barras -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-chart-column text-[#D12420]"></i>
                        Comparativo por Escuela: Impartidas vs No Registradas
                    </h3>
                    <p class="text-xs text-gray-500">Pasa el cursor sobre las barras para ver el detalle de cada escuela</p>
                </div>
            </div>
            <div class="h-80 w-full relative">
                <canvas id="chart-reporte-escuelas" data-escuelas="{{ json_encode(array_values($escuelasData)) }}"></canvas>
            </div>
        </div>

        <!-- Tabla Resumen Ejecutivo por Escuela -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-table-list text-[#D12420]"></i>
                        Tabla Resumen por Escuela
                    </h3>
                    <p class="text-xs text-gray-500">Desglose de clases impartidas, recuperadas, justificadas y no registradas</p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 bg-white border border-gray-300 rounded-full text-gray-600 self-start sm:self-auto">
                    {{ count($escuelasData) }} Escuelas evaluadas
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-bold border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3.5">Escuela</th>
                            <th class="px-4 py-3.5 text-center">Total Clases</th>
                            <th class="px-4 py-3.5 text-center text-emerald-800">Impartidas (Efectivas)</th>
                            <th class="px-3 py-3.5 text-center text-gray-600">Realizadas Norm.</th>
                            <th class="px-3 py-3.5 text-center text-amber-700">Recup.</th>
                            <th class="px-3 py-3.5 text-center text-slate-700">Justif.</th>
                            <th class="px-4 py-3.5 text-center text-rose-800">No Registradas</th>
                            <th class="px-4 py-3.5 text-center">% Cumplimiento</th>
                            <th class="px-4 py-3.5 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($escuelasData as $escuela)
                            @php
                                $pct = $escuela['pct'] ?? 0;
                                $badgeColor = $pct >= 90 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($pct >= 75 ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-rose-100 text-rose-800 border-rose-300');
                                $barColor = $pct >= 90 ? 'bg-emerald-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-rose-500');
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-5 py-4 font-bold text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2.5 h-2.5 rounded-full {{ $barColor }}"></div>
                                        <span>{{ $escuela['nombre'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center font-bold text-gray-800">{{ $escuela['total'] }}</td>
                                <td class="px-4 py-4 text-center font-extrabold text-emerald-700 bg-emerald-50/20">{{ $escuela['impartidas'] }}</td>
                                <td class="px-3 py-4 text-center text-gray-600">{{ $escuela['realizadas'] }}</td>
                                <td class="px-3 py-4 text-center text-amber-700">{{ $escuela['recuperadas'] }}</td>
                                <td class="px-3 py-4 text-center text-slate-600">{{ $escuela['justificadas'] }}</td>
                                <td class="px-4 py-4 text-center font-extrabold text-rose-700 bg-rose-50/20">{{ $escuela['no_registradas'] }}</td>
                                <td class="px-4 py-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black border {{ $badgeColor }}">
                                            {{ $pct }}%
                                        </span>
                                        <div class="w-16 bg-gray-200 rounded-full h-1 mt-1 overflow-hidden">
                                            <div class="{{ $barColor }} h-1 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <a href="{{ route('clases-no-realizadas.index', ['search' => $escuela['nombre'], 'fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'periodo' => $periodo]) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-[#D12420] text-slate-700 hover:text-white rounded-lg text-xs font-bold transition shadow-2xs">
                                        <span>Ver clases</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-gray-400">
                                    No se encontraron registros para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!-- Fila de Totales -->
                    <tfoot class="bg-gray-100 border-t-2 border-gray-300 font-extrabold text-gray-900">
                        <tr>
                            <td class="px-5 py-3.5">TOTAL GENERAL SEDE</td>
                            <td class="px-4 py-3.5 text-center">{{ $totalGeneral }}</td>
                            <td class="px-4 py-3.5 text-center text-emerald-800">{{ $impartidasGeneral }}</td>
                            <td class="px-3 py-3.5 text-center text-gray-700">{{ $realizadasGeneral }}</td>
                            <td class="px-3 py-3.5 text-center text-amber-700">{{ $recuperadasGeneral }}</td>
                            <td class="px-3 py-3.5 text-center text-slate-700">{{ $justificadasGeneral }}</td>
                            <td class="px-4 py-3.5 text-center text-rose-800">{{ $noRegistradasGeneral }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-gray-900 text-white">
                                    {{ $pctGeneral }}%
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center text-xs text-gray-500">-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tabla de Detalle de Clases -->
        @if($clasesFiltradasDetalle->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden" x-data="{ open: false }">
            <div class="p-4 sm:p-5 border-b border-gray-200 bg-gray-50/50 flex items-center justify-between cursor-pointer" @click="open = !open">
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-list-check text-[#D12420]"></i>
                        Detalle de Clases Individuales ({{ $clasesFiltradasDetalle->count() }} clases)
                    </h3>
                    <p class="text-xs text-gray-500">Haz clic para desplegar u ocultar la lista detallada de clases</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition p-2">
                    <i class="fas fa-chevron-down transform transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
            </div>

            <div x-show="open" x-collapse>
                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100 text-gray-700 uppercase font-bold sticky top-0 border-b border-gray-200 z-10">
                            <tr>
                                <th class="px-3 py-2.5">Fecha</th>
                                <th class="px-3 py-2.5">Escuela</th>
                                <th class="px-3 py-2.5">Asignatura</th>
                                <th class="px-3 py-2.5">Docente</th>
                                <th class="px-3 py-2.5 text-center">Espacio</th>
                                <th class="px-3 py-2.5 text-center">Módulo</th>
                                <th class="px-3 py-2.5 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($clasesFiltradasDetalle as $c)
                                @php
                                    $est = $c['estado_bloque'] ?? $c['estado'] ?? 'Realizada';
                                    $estColor = match($est) {
                                        'No Registrada' => 'bg-rose-100 text-rose-800',
                                        'Pendiente de Recuperación', 'Recuperada' => 'bg-amber-100 text-amber-800',
                                        'Justificada', 'Feriado/Justificado' => 'bg-green-100 text-green-800',
                                        default => 'bg-indigo-100 text-indigo-800',
                                    };
                                    $fechaFmt = ($c['fecha'] instanceof \Carbon\Carbon) ? $c['fecha']->format('d/m/Y') : \Carbon\Carbon::parse($c['fecha'])->format('d/m/Y');
                                @endphp
                                <tr class="hover:bg-gray-50/80">
                                    <td class="px-3 py-2.5 whitespace-nowrap font-medium text-gray-900">{{ $fechaFmt }}</td>
                                    <td class="px-3 py-2.5 text-gray-700 font-semibold max-w-[200px] truncate" title="{{ $c['escuela'] ?? '' }}">{{ $c['escuela'] ?? 'Sin Escuela' }}</td>
                                    <td class="px-3 py-2.5 text-gray-800">
                                        <div class="font-bold">{{ $c['asignatura'] ?? 'N/A' }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $c['codigo_asignatura'] ?? '' }} Sec. {{ $c['seccion'] ?? '' }}</div>
                                    </td>
                                    <td class="px-3 py-2.5 text-gray-700">{{ $c['profesor'] ?? 'N/A' }}</td>
                                    <td class="px-3 py-2.5 text-center font-bold text-gray-800">{{ $c['espacio'] ?? '' }}</td>
                                    <td class="px-3 py-2.5 text-center text-gray-600">{{ $c['modulo'] ?? '' }}</td>
                                    <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $estColor }}">
                                            {{ $est }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Script de Chart.js para el reporte -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('chart-reporte-escuelas');
            if (!canvas || !window.Chart) return;

            let escuelas = [];
            try {
                escuelas = JSON.parse(canvas.dataset.escuelas || '[]');
            } catch (e) {
                escuelas = [];
            }

            if (!Array.isArray(escuelas) || escuelas.length === 0) return;

            const labels = escuelas.map(e => e.nombre || 'Sin Escuela');
            const dataImpartidas = escuelas.map(e => e.impartidas || 0);
            const dataNoRegistradas = escuelas.map(e => e.no_registradas || 0);
            const dataPcts = escuelas.map(e => e.pct || 0);

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Clases Impartidas (Efectivas)',
                            data: dataImpartidas,
                            backgroundColor: '#10B981',
                            hoverBackgroundColor: '#059669',
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: 'Clases No Registradas',
                            data: dataNoRegistradas,
                            backgroundColor: '#D12420',
                            hoverBackgroundColor: '#950101',
                            borderRadius: 6,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { weight: 'bold', size: 12 },
                                useBorderRadius: true,
                                borderRadius: 4,
                                boxWidth: 14,
                                boxHeight: 14,
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { weight: 'bold', size: 13 },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                afterBody: function(context) {
                                    const idx = context[0].dataIndex;
                                    return `Cumplimiento: ${dataPcts[idx]}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 11, weight: '600' },
                                color: '#334155',
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                precision: 0,
                                font: { size: 11 },
                                color: '#64748b',
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

<div class="space-y-6">
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

    <!-- Tabla de detalles por día -->
    <div class="p-6 bg-white rounded-lg shadow border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-calendar-day text-purple-600 mr-2"></i>
            Detalle por Día de la Semana
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 font-semibold text-gray-700">Fecha</th>
                        <th class="px-6 py-3 font-semibold text-gray-700 text-center">Realizadas</th>
                        <th class="px-6 py-3 font-semibold text-gray-700 text-center">No Realizadas</th>
                        <th class="px-6 py-3 font-semibold text-gray-700 text-center">Recuperadas</th>
                        <th class="px-6 py-3 font-semibold text-gray-700 text-center">Total</th>
                        <th class="px-6 py-3 font-semibold text-gray-700 text-center">% Completadas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($diasDelMes as $dia => $datos)
                        @php
                            $total = $datos['realizadas'] + $datos['no_realizadas'];
                            $porcentaje = $total > 0 ? round(($datos['realizadas'] / $total) * 100) : 0;
                            $colorBarra = $porcentaje >= 80 ? 'bg-green-500' : ($porcentaje >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $dia }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-semibold text-xs">
                                    {{ $datos['realizadas'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-semibold text-xs">
                                    {{ $datos['no_realizadas'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold text-xs">
                                    {{ $datos['recuperadas'] ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-gray-800">{{ $total }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full {{ $colorBarra }}" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700 w-12 text-right">{{ $porcentaje }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-info-circle mr-2"></i>
                                No hay datos disponibles para el mes
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
</div>

<script>
    // Gráfico de comparativa
    const ctxClasesNoRealizadas = document.getElementById('chart-clases-no-realizadas');
    if (ctxClasesNoRealizadas) {
        const diasLabels = @json($diasLabels);
        const datosRealizadas = @json($datosRealizadas);
        const datosNoRealizadas = @json($datosNoRealizadas);
        const datosRecuperadas = @json($datosRecuperadas);

        new Chart(ctxClasesNoRealizadas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: diasLabels,
                datasets: [
                    {
                        label: 'Clases Realizadas',
                        data: datosRealizadas,
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 2,
                        borderRadius: 4
                    },
                    {
                        label: 'Clases No Realizadas',
                        data: datosNoRealizadas,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 2,
                        borderRadius: 4
                    },
                    {
                        label: 'Clases Recuperadas',
                        data: datosRecuperadas,
                        backgroundColor: '#3b82f6',
                        borderColor: '#1d4ed8',
                        borderWidth: 2,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endif

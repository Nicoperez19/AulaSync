<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <!-- Filtros globales compactos -->
    <div class="p-6 mb-8 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <div class="flex flex-wrap items-center justify-between w-full gap-4">
            <!-- Filtros básicos -->
            <div class="flex items-center gap-2">
                <label class="font-semibold">Semana:</label>
                <select class="w-40 border-gray-300 rounded-md shadow-sm">
                    <option>Semana actual</option>
                    <option>Semana anterior</option>
                    <option>Hace 2 semanas</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-semibold">Piso:</label>
                <div class="relative">
                    <select id="piso-selector" class="w-32 transition-all duration-300 border-gray-300 rounded-md shadow-sm" onchange="cambiarPiso(this.value)">
                        <option value="">Todos</option>
                        @foreach($pisos as $pisoItem)
                            <option value="{{ $pisoItem->numero_piso }}" {{ $piso == $pisoItem->numero_piso ? 'selected' : '' }}>
                                Piso {{ $pisoItem->numero_piso }}
                            </option>
                        @endforeach
                    </select>
                    <div id="piso-loading" class="absolute hidden transform -translate-y-1/2 right-2 top-1/2">
                        <div class="w-4 h-4 border-b-2 border-blue-600 rounded-full animate-spin"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-semibold">Tipo de sala:</label>
                <select class="w-40 border-gray-300 rounded-md shadow-sm">
                    <option>Todas</option>
                    @foreach($comparativaTipos as $tipo)
                        <option>{{ $tipo['tipo'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- KPIs con tendencia -->
    <div class="p-8 mx-auto mb-4 overflow-hidden max-w-7xl">
        <div class="grid w-full grid-cols-2 gap-8 sm:grid-cols-3 md:grid-cols-6">
            <!-- KPI 1: Ocupación semanal -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 relative widget-transition">
                <img src="https://img.icons8.com/color/48/000000/combo-chart--v2.png" class="w-12 h-12 mb-2" />
                <div class="text-xs text-gray-500">% Ocupación semanal</div>
                <div id="ocupacion-semanal" class="flex items-center gap-2 mt-1 text-3xl font-bold text-primary-600 kpi-value">
                    {{ $ocupacionSemanal }}%
                    <span class="text-xl text-green-500" title="Subió respecto a la semana anterior">▲</span>
                </div>
            </div>
            <!-- KPI 2: Ocupación diaria -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 relative widget-transition">
                <img src="https://img.icons8.com/color/48/000000/calendar--v2.png" class="w-12 h-12 mb-2" />
                <div class="text-xs text-gray-500">% Ocupación diaria</div>
                <div data-widget="ocupacion-diaria" class="flex gap-1 mt-1 text-xs">
                    @foreach($ocupacionDiaria as $dia => $porcentaje)
                        <span class="font-bold {{ $porcentaje > 70 ? 'text-green-700' : ($porcentaje > 50 ? 'text-yellow-500' : 'text-red-500') }}">{{ $dia }} {{ $porcentaje }}%</span>
                    @endforeach
                </div>
            </div>
            <!-- KPI: Promedio ocupación mensual -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 relative widget-transition">
                <img src="https://img.icons8.com/color/48/000000/line-chart.png" class="w-12 h-12 mb-2" />
                <div class="text-xs text-gray-500">Promedio ocupación mensual</div>
                <div id="ocupacion-mensual" class="flex items-center gap-2 mt-1 text-3xl font-bold text-primary-600 kpi-value">
                    {{ $ocupacionMensual }}%
                    <span class="text-xl text-green-500" title="Subió respecto al mes anterior">▲</span>
                </div>
            </div>
            <!-- Usuarios sin escaneo -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 widget-transition">
                <span class="mb-2 text-5xl text-red-500">❌</span>
                <div class="mb-1 text-base font-bold text-red-700">Usuarios sin escaneo</div>
                <div id="usuarios-sin-escaneo" class="text-xs text-center text-red-600 kpi-value">{{ $usuariosSinEscaneo }} usuarios sin registrar asistencia hoy</div>
            </div>
            <!-- KPI 3: Horas utilizadas / disponibles -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 widget-transition">
                <img src="https://img.icons8.com/color/48/000000/hourglass--v2.png" class="w-12 h-12 mb-2" />
                <div class="text-xs text-gray-500">Horas utilizadas / disponibles</div>
                <div id="horas-utilizadas" class="mt-1 text-2xl font-bold text-yellow-700 kpi-value">{{ $horasUtilizadas['utilizadas'] }} / {{ $horasUtilizadas['disponibles'] }}</div>
                <div class="w-full h-2 mt-2 bg-yellow-200 rounded-full" style="max-width:100px;">
                    <div class="h-2 bg-yellow-500 rounded-full" style="width: {{ ($horasUtilizadas['utilizadas'] / $horasUtilizadas['disponibles']) * 100 }}%"></div>
                </div>
            </div>
            <!-- KPI 4: Salas ocupadas / libres -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 widget-transition">
                <img src="https://img.icons8.com/color/48/000000/brick-wall.png" class="w-12 h-12 mb-2" />
                <div class="text-xs text-gray-500">Salas ocupadas / libres (hoy)</div>
                <div id="salas-ocupadas" class="text-2xl font-bold kpi-value" style="color:#a21caf; margin-top:4px;">{{ $salasOcupadas['ocupadas'] }} <span class="text-gray-400">/</span> {{ $salasOcupadas['libres'] }}</div>
            </div>
        </div>
    </div>

    <!-- Fila de nuevos KPIs -->
    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- Promedio Duración Reserva -->
        <div class="p-4 bg-white rounded-md shadow-md dark:bg-dark-eval-1">
            <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">Promedio de Duración</h3>
            <div class="flex items-baseline mt-2">
                <p id="kpi-promedio-duracion" class="text-3xl font-bold text-gray-900 dark:text-gray-100 kpi-value">{{ $promedioDuracion }}</p>
                <span class="ml-2 text-gray-500 dark:text-gray-400">minutos</span>
            </div>
        </div>

        <!-- % Reservas No Utilizadas (No Show) -->
        <div class="p-4 bg-white rounded-md shadow-md dark:bg-dark-eval-1">
            <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">% No Presentación</h3>
            <div class="flex items-baseline mt-2">
                <p id="kpi-no-show" class="text-3xl font-bold text-gray-900 dark:text-gray-100 kpi-value">{{ $porcentajeNoShow }}</p>
                <span class="ml-2 text-gray-500 dark:text-gray-400">%</span>
            </div>
        </div>

        <!-- Canceladas por Tipo de Sala -->
        <div class="p-4 bg-white rounded-md shadow-md dark:bg-dark-eval-1">
            <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">Canceladas por Tipo</h3>
            <div id="lista-canceladas-tipo" class="mt-2 text-sm text-gray-900 dark:text-gray-100">
                @forelse($canceladasPorTipo as $tipo => $cantidad)
                    <div class="flex justify-between">
                        <span class="capitalize">{{ str_replace('_', ' ', $tipo) }}</span>
                        <span class="font-bold">{{ $cantidad }}</span>
                    </div>
                @empty
                    <p>No hay datos</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Grid de gráficos -->
    <div class="grid grid-cols-1 gap-8 mx-auto max-w-7xl md:grid-cols-2 lg:grid-cols-3">
        <!-- Gráfico de barras: Uso por Día -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative widget-transition">
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Gráfico de Barras: Uso por Día <span class="ml-2 cursor-pointer" title="Muestra la cantidad de horas ocupadas por día de la semana">ℹ️</span></h4>
            <canvas id="grafico-barras" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de barras horizontal: Top 3 salas más y menos usadas -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative widget-transition">
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Top 3 Salas más usadas <span class="ml-2 cursor-pointer" title="Ranking de salas según uso mensual">ℹ️</span></h4>
            <canvas id="grafico-top-salas" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de barras: Top asignaturas por uso de espacios -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative widget-transition">
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Top asignaturas por uso <span class="ml-2 cursor-pointer" title="Asignaturas con mayor uso de espacios">ℹ️</span></h4>
            <canvas id="grafico-top-asignaturas" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de áreas: Comparativa tipos de espacios -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative widget-transition">
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Comparativa de ocupación por tipo de espacio <span class="ml-2 cursor-pointer" title="Comparación de ocupación entre aulas, laboratorios, etc.">ℹ️</span></h4>
            <canvas id="grafico-comparativa-tipos" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de línea: Promedio mensual -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative widget-transition">
            <h4 class="flex items-center gap-2 mb-4 font-semibold text-gray-700">Evolución semanal de ocupación <span class="ml-2 cursor-pointer" title="Tendencia del promedio de ocupación de la semana actual">ℹ️</span></h4>
            <canvas id="grafico-mensual" width="320" height="220"></canvas>
        </div>
    </div>

    <!-- Tabla: Reservas canceladas o no utilizadas -->
    <div class="p-8 mx-auto mt-8 overflow-hidden bg-white rounded-xl shadow-lg max-w-7xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700">Reservas canceladas o no utilizadas <span class="ml-2 cursor-pointer" title="Reservas que no fueron utilizadas o se cancelaron">ℹ️</span></h3>
            <div class="flex gap-2">
                <button class="px-3 py-1 text-white transition bg-green-500 rounded hover:bg-green-600" title="Exportar a Excel">Excel</button>
                <button class="px-3 py-1 text-white transition bg-red-500 rounded hover:bg-red-600" title="Exportar a PDF">PDF</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="tabla-reservas-canceladas" class="min-w-full text-center border border-gray-300 rounded-lg dark:bg-dark-eval-1">
                <thead>
                    <tr class="bg-gray-200 dark:bg-dark-eval-2">
                        <th class="px-4 py-2 border">Usuario</th>
                        <th class="px-4 py-2 border">Espacio</th>
                        <th class="px-4 py-2 border">Fecha</th>
                        <th class="px-4 py-2 border">Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservasCanceladas as $reserva)
                    <tr class="bg-white dark:bg-dark-eval-1">
                        <td class="border">{{ $reserva['usuario'] }}</td>
                        <td class="border">{{ $reserva['espacio'] }}</td>
                        <td class="border">{{ $reserva['hora'] }}</td>
                        <td class="border">Cancelada</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabla: Reservas Activas Sin Devolución -->
    <div class="p-8 mx-auto mt-8 overflow-hidden bg-white rounded-xl shadow-lg max-w-7xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700">Reservas Activas Sin Devolución <span class="ml-2 cursor-pointer" title="Usuarios que han registrado el ingreso a una sala pero no han registrado la salida.">ℹ️</span></h3>
            <div class="flex gap-2">
                <button class="px-3 py-1 text-white transition bg-green-500 rounded hover:bg-green-600" title="Exportar a Excel">Excel</button>
                <button class="px-3 py-1 text-white transition bg-red-500 rounded hover:bg-red-600" title="Exportar a PDF">PDF</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="tabla-reservas-activas" class="min-w-full text-center border border-gray-300 rounded-lg dark:bg-dark-eval-1">
                <thead>
                    <tr class="bg-gray-200 dark:bg-dark-eval-2">
                        <th class="px-4 py-2 border">Usuario</th>
                        <th class="px-4 py-2 border">Espacio Reservado</th>
                        <th class="px-4 py-2 border">Fecha de Reserva</th>
                        <th class="px-4 py-2 border">Hora de Ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservasSinDevolucion as $reserva)
                    <tr class="bg-white dark:bg-dark-eval-1">
                        <td class="border">{{ $reserva->user->name }}</td>
                        <td class="border">{{ $reserva->espacio->nombre_espacio }} ({{ $reserva->espacio->id_espacio }})</td>
                        <td class="border">{{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }}</td>
                        <td class="border">{{ $reserva->hora }}</td>
                    </tr>
                    @empty
                    <tr class="bg-white dark:bg-dark-eval-1">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No hay reservas activas sin devolución en este momento.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabla: Horarios por día -->
    <div class="p-8 mx-auto mt-8 overflow-hidden bg-white rounded-xl shadow-lg max-w-7xl">
        <h3 class="mb-4 text-lg font-bold text-gray-700">Horarios de la semana - Usuarios asignados por espacio</h3>
        @include('layouts.partials.horarios-semana', ['horariosAgrupados' => $horariosAgrupados])
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de barras: Uso por Día
    window.graficoBarras = new Chart(document.getElementById('grafico-barras'), {
        type: 'bar',
        data: {
            labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            datasets: [{
                label: 'Horas ocupadas',
                data: [4, 5, 3, 6, 2, 1], // ← puedes cambiar estos valores
                backgroundColor: 'rgba(59, 130, 246, 0.7)'
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Gráfico de barras: Top Salas
   window.graficoTopSalas = new Chart(document.getElementById('grafico-top-salas'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($topSalas->pluck('nombre')) !!},
        datasets: [{
            label: 'Uso',
            data: {!! json_encode($topSalas->pluck('uso')) !!},
            backgroundColor: 'rgba(16, 185, 129, 0.7)'
        }]
    },
    options: {
        responsive: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                ticks: {
                    minRotation: 90,
                    maxRotation: 90
                }
            }
        }
    }
});

    // Gráfico de barras: Top Asignaturas
    window.graficoTopAsignaturas = new Chart(document.getElementById('grafico-top-asignaturas'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($topAsignaturas->pluck('nombre')) !!},
            datasets: [{
                label: 'Uso',
                data: {!! json_encode($topAsignaturas->pluck('uso')) !!},
                backgroundColor: 'rgba(251, 191, 36, 0.7)'
            }]
        },
        options: {responsive: false, plugins: {legend: {display: false}}}
    });

    // Gráfico de áreas: Comparativa tipos
    window.graficoComparativaTipos = new Chart(document.getElementById('grafico-comparativa-tipos'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($comparativaTipos->pluck('tipo')) !!},
            datasets: [{
                data: {!! json_encode($comparativaTipos->pluck('total')) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(251, 191, 36, 0.7)',
                    'rgba(239, 68, 68, 0.7)'
                ]
            }]
        },
        options: {responsive: false, plugins: {legend: {position: 'bottom'}}}
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
        options: {responsive: false, plugins: {legend: {position: 'bottom'}}}
    });

    function cambiarPiso(piso) {
        // Mostrar indicador de carga
        const selector = document.getElementById('piso-selector');
        const loadingIndicator = document.getElementById('piso-loading');
        selector.disabled = true;
        loadingIndicator.classList.remove('hidden');
        
        // Mostrar indicador de carga en los widgets
        mostrarCargando();
        
        // Hacer petición AJAX para cambiar el piso
        fetch('/dashboard/set-piso', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ piso: piso })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Obtener los nuevos datos de los widgets
                return fetch('/dashboard/widget-data');
            } else {
                throw new Error('Error al cambiar piso: ' + (data.message || 'Error desconocido'));
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al obtener datos de widgets');
            }
            return response.json();
        })
        .then(widgetData => {
            // Actualizar todos los widgets con los nuevos datos
            actualizarWidgets(widgetData);
            selector.disabled = false;
            loadingIndicator.classList.add('hidden');
            
            // Mostrar notificación de éxito
            mostrarNotificacion('Piso cambiado exitosamente', 'success');
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            selector.disabled = false;
            loadingIndicator.classList.add('hidden');
            ocultarCargando();
            
            // Mostrar notificación de error
            mostrarNotificacion('Error al cambiar piso: ' + error.message, 'error');
        });
    }

    function mostrarCargando() {
        // Agregar clase de carga a los widgets
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
        // Remover clase de carga de los widgets
        const widgets = document.querySelectorAll('.bg-white.rounded-xl.shadow-lg');
        widgets.forEach(widget => {
            widget.classList.remove('opacity-50');
            const loadingDiv = widget.querySelector('.absolute.inset-0');
            if (loadingDiv) {
                loadingDiv.remove();
            }
        });
    }

    function mostrarNotificacion(mensaje, tipo) {
        // Crear notificación temporal
        const notificacion = document.createElement('div');
        notificacion.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 ${
            tipo === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notificacion.textContent = mensaje;
        
        document.body.appendChild(notificacion);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            notificacion.remove();
        }, 3000);
    }

    function actualizarWidgets(data) {
        try {
            // Actualizar KPIs
            actualizarKPI('ocupacion-semanal', data.ocupacionSemanal + '%');
            actualizarKPI('ocupacion-mensual', data.ocupacionMensual + '%');
            actualizarKPI('usuarios-sin-escaneo', data.usuariosSinEscaneo + ' usuarios sin registrar asistencia hoy');
            actualizarKPI('horas-utilizadas', data.horasUtilizadas.utilizadas + ' / ' + data.horasUtilizadas.disponibles);
            actualizarKPI('salas-ocupadas', data.salasOcupadas.ocupadas + ' / ' + data.salasOcupadas.libres);
            actualizarKPI('kpi-promedio-duracion', data.promedioDuracion);
            actualizarKPI('kpi-no-show', data.porcentajeNoShow + '%');
            
            // Actualizar ocupación diaria
            actualizarOcupacionDiaria(data.ocupacionDiaria);
            
            // Actualizar gráficos
            actualizarGraficoBarras(data.usoPorDia);
            actualizarGraficoTopSalas(data.topSalas);
            actualizarGraficoTopAsignaturas(data.topAsignaturas);
            actualizarGraficoComparativaTipos(data.comparativaTipos);
            actualizarGraficoEvolucionMensual(data.evolucionMensual);
            
            // Actualizar tablas
            actualizarTablaReservasCanceladas(data.reservasCanceladas);
            actualizarTablaReservasActivas(data.reservasSinDevolucion);
            actualizarListaCanceladas(data.canceladasPorTipo);
            
            ocultarCargando();
        } catch (error) {
            console.error('Error al actualizar widgets:', error);
            ocultarCargando();
            mostrarNotificacion('Error al actualizar los datos', 'error');
        }
    }

    function actualizarKPI(id, valor) {
        const elemento = document.getElementById(id);
        if (elemento) {
            // Agregar clase de animación
            elemento.classList.add('updating');
            
            // Actualizar el valor
            elemento.textContent = valor;
            
            // Remover la clase de animación después de un tiempo
            setTimeout(() => {
                elemento.classList.remove('updating');
            }, 300);
        }
    }

    function actualizarOcupacionDiaria(ocupacionDiaria) {
        const contenedor = document.querySelector('[data-widget="ocupacion-diaria"]');
        if (contenedor) {
            let html = '';
            for (const [dia, porcentaje] of Object.entries(ocupacionDiaria)) {
                const color = porcentaje > 70 ? 'text-green-700' : (porcentaje > 50 ? 'text-yellow-500' : 'text-red-500');
                html += `<span class="font-bold ${color}">${dia} ${porcentaje}%</span>`;
            }
            contenedor.innerHTML = html;
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

    function actualizarGraficoTopSalas(topSalas) {
        if (window.graficoTopSalas && topSalas) {
            window.graficoTopSalas.data.labels = topSalas.map(sala => sala.nombre);
            window.graficoTopSalas.data.datasets[0].data = topSalas.map(sala => sala.uso);
            window.graficoTopSalas.update('active');
        }
    }

    function actualizarGraficoTopAsignaturas(topAsignaturas) {
        if (window.graficoTopAsignaturas && topAsignaturas) {
            window.graficoTopAsignaturas.data.labels = topAsignaturas.map(asignatura => asignatura.nombre);
            window.graficoTopAsignaturas.data.datasets[0].data = topAsignaturas.map(asignatura => asignatura.uso);
            window.graficoTopAsignaturas.update('active');
        }
    }

    function actualizarGraficoComparativaTipos(comparativaTipos) {
        if (window.graficoComparativaTipos && comparativaTipos) {
            window.graficoComparativaTipos.data.labels = comparativaTipos.map(tipo => tipo.tipo);
            window.graficoComparativaTipos.data.datasets[0].data = comparativaTipos.map(tipo => tipo.total);
            window.graficoComparativaTipos.update('active');
        }
    }

    function actualizarGraficoEvolucionMensual(evolucionMensual) {
        if (window.graficoMensual && evolucionMensual) {
            window.graficoMensual.data.labels = evolucionMensual.dias;
            window.graficoMensual.data.datasets[0].data = evolucionMensual.ocupacion;
            window.graficoMensual.update('active');
        }
    }

    function actualizarTablaReservasCanceladas(reservasCanceladas) {
        const tbody = document.querySelector('#tabla-reservas-canceladas tbody');
        if (tbody) {
            let html = '';
            if (reservasCanceladas && reservasCanceladas.length > 0) {
                reservasCanceladas.forEach(reserva => {
                    html += `
                        <tr class="bg-white dark:bg-dark-eval-1">
                            <td class="border">${reserva.usuario}</td>
                            <td class="border">${reserva.espacio}</td>
                            <td class="border">${reserva.hora}</td>
                            <td class="border">Cancelada</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="4" class="text-center text-gray-500 border">No hay reservas canceladas</td></tr>';
            }
            tbody.innerHTML = html;
        }
    }

    function actualizarTablaReservasActivas(reservas) {
        const tbody = document.querySelector('#tabla-reservas-activas tbody');
        if (tbody) {
            let html = '';
            if (reservas && reservas.length > 0) {
                reservas.forEach(reserva => {
                    const fecha = new Date(reserva.fecha_reserva);
                    const formattedDate = new Date(fecha.valueOf() + fecha.getTimezoneOffset() * 60000).toLocaleDateString('es-CL');
                    html += `
                        <tr class="bg-white dark:bg-dark-eval-1">
                            <td class="border">${reserva.user.name}</td>
                            <td class="border">${reserva.espacio.nombre_espacio} (${reserva.espacio.id_espacio})</td>
                            <td class="border">${formattedDate}</td>
                            <td class="border">${reserva.hora}</td>
                        </tr>
                    `;
                });
            } else {
                html = `
                    <tr class="bg-white dark:bg-dark-eval-1">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No hay reservas activas sin devolución en este momento.
                        </td>
                    </tr>
                `;
            }
            tbody.innerHTML = html;
        }
    }

    function actualizarListaCanceladas(canceladasPorTipo) {
        const contenedor = document.getElementById('lista-canceladas-tipo');
        if (contenedor) {
            let html = '';
            const tipos = Object.keys(canceladasPorTipo);

            if (tipos.length > 0) {
                tipos.forEach(tipo => {
                    const cantidad = canceladasPorTipo[tipo];
                    const tipoFormateado = tipo.replace(/_/g, ' ');
                    html += `
                        <div class="flex justify-between">
                            <span class="capitalize">${tipoFormateado}</span>
                            <span class="font-bold">${cantidad}</span>
                        </div>
                    `;
                });
            } else {
                html = '<p>No hay datos</p>';
            }
            contenedor.innerHTML = html;
        }
    }
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
        0%, 100% {
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
</style>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <!-- Filtros globales compactos + avanzados -->
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-wrap gap-4 items-center justify-center max-w-4xl w-full">
            <!-- Filtros básicos -->
            <div class="flex items-center gap-2">
                <label class="font-semibold">Semana:</label>
                <select class="rounded-md border-gray-300 shadow-sm w-40">
                    <option>Semana actual</option>
                    <option>Semana anterior</option>
                    <option>Hace 2 semanas</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-semibold">Piso:</label>
                <select class="rounded-md border-gray-300 shadow-sm w-32">
                    <option>Todos</option>
                    <option>Piso 1</option>
                    <option>Piso 2</option>
                    <option>Piso 3</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-semibold">Tipo de sala:</label>
                <select class="rounded-md border-gray-300 shadow-sm w-40">
                    <option>Todas</option>
                    <option>Aula</option>
                    <option>Laboratorio</option>
                    <option>Biblioteca</option>
                    <option>Sala de Reuniones</option>
                </select>
            </div>
        </div>
        <!-- Filtros avanzados -->
        <div class="flex flex-wrap gap-4 items-center justify-center">
            <div class="flex items-center gap-2">
                <label class="font-semibold">Rango de fechas:</label>
                <input type="date" class="rounded-md border-gray-300 shadow-sm w-32" />
                <span class="mx-1">-</span>
                <input type="date" class="rounded-md border-gray-300 shadow-sm w-32" />
            </div>
            <div class="flex items-center gap-2">
                <label class="font-semibold">Usuario:</label>
                <select class="rounded-md border-gray-300 shadow-sm w-40">
                    <option>Todos</option>
                    <option>Juan Pérez</option>
                    <option>Ana López</option>
                    <option>Carlos Ruiz</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-semibold">Asignatura:</label>
                <select class="rounded-md border-gray-300 shadow-sm w-40">
                    <option>Todas</option>
                    <option>Matemáticas</option>
                    <option>Lenguaje</option>
                    <option>Historia</option>
                </select>
            </div>
        </div>
    </div>

    <!-- KPIs con tendencia -->
    <div class="p-8 overflow-hidden mb-4 max-w-7xl mx-auto">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-8 w-full">
            <!-- KPI 1: Ocupación semanal -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 relative">
                <img src="https://img.icons8.com/color/48/000000/combo-chart--v2.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">% Ocupación semanal</div>
                <div class="text-3xl font-bold text-primary-600 mt-1 flex items-center gap-2">
                    72%
                    <span class="text-green-500 text-xl" title="Subió respecto a la semana anterior">▲</span>
                </div>
            </div>
            <!-- KPI 2: Ocupación diaria -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 relative">
                <img src="https://img.icons8.com/color/48/000000/calendar--v2.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">% Ocupación diaria</div>
                <div class="flex gap-1 mt-1 text-xs">
                    <span class="font-bold text-green-700">L 80%</span>
                    <span class="font-bold text-green-500">M 70%</span>
                    <span class="font-bold text-yellow-500">X 60%</span>
                    <span class="font-bold text-orange-500">J 50%</span>
                    <span class="font-bold text-red-500">V 40%</span>
                </div>
                <span class="absolute top-2 right-2 text-red-500 text-xl" title="Bajó respecto a ayer">▼</span>
            </div>
            <!-- KPI: Promedio ocupación mensual -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 relative">
                <img src="https://img.icons8.com/color/48/000000/line-chart.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">Promedio ocupación mensual</div>
                <div class="text-3xl font-bold text-primary-600 mt-1 flex items-center gap-2">
                    68%
                    <span class="text-green-500 text-xl" title="Subió respecto al mes anterior">▲</span>
                </div>
            </div>
            <!-- Usuarios sin escaneo (centro) -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6">
                <span class="text-5xl mb-2 text-red-500">❌</span>
                <div class="text-base text-red-700 font-bold mb-1">Usuarios sin escaneo</div>
                <div class="text-xs text-red-600 text-center">3 usuarios sin registrar asistencia hoy</div>
            </div>
            <!-- KPI 3: Horas utilizadas / disponibles -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6">
                <img src="https://img.icons8.com/color/48/000000/hourglass--v2.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">Horas utilizadas / disponibles</div>
                <div class="text-2xl font-bold text-yellow-700 mt-1">32 / 40</div>
                <div class="w-full bg-yellow-200 rounded-full h-2 mt-2" style="max-width:100px;">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 80%"></div>
                </div>
            </div>
            <!-- KPI 4: Salas ocupadas / libres -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6">
                <img src="https://img.icons8.com/color/48/000000/brick-wall.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">Salas ocupadas / libres (hoy)</div>
                <div class="text-2xl font-bold" style="color:#a21caf; margin-top:4px;">8 <span class="text-gray-400">/</span> 4</div>
            </div>
        </div>
    </div>

    <!-- Grid inferior: gráficos y tablas ordenados -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Gráfico de barras: Uso por Día -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative">
            <h4 class="font-semibold mb-4 text-gray-700 flex items-center gap-2">Gráfico de Barras: Uso por Día <span class="ml-2 cursor-pointer" title="Muestra la cantidad de horas ocupadas por día de la semana">ℹ️</span></h4>
            <canvas id="grafico-barras-ejemplo" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de barras horizontal: Top 3 salas más y menos usadas -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative">
            <h4 class="font-semibold mb-4 text-gray-700 flex items-center gap-2">Top 3 Salas más y menos usadas <span class="ml-2 cursor-pointer" title="Ranking de salas según uso mensual">ℹ️</span></h4>
            <canvas id="grafico-top-salas" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de barras: Top asignaturas por uso de espacios -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative">
            <h4 class="font-semibold mb-4 text-gray-700 flex items-center gap-2">Top asignaturas por uso <span class="ml-2 cursor-pointer" title="Asignaturas con mayor uso de espacios">ℹ️</span></h4>
            <canvas id="grafico-top-asignaturas" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de áreas: Comparativa tipos de espacios -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative">
            <h4 class="font-semibold mb-4 text-gray-700 flex items-center gap-2">Comparativa de ocupación por tipo de espacio <span class="ml-2 cursor-pointer" title="Comparación de ocupación entre aulas, laboratorios, etc.">ℹ️</span></h4>
            <canvas id="grafico-comparativa-tipos" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de línea: Promedio mensual (placeholder) -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative">
            <h4 class="font-semibold mb-4 text-gray-700 flex items-center gap-2">Evolución mensual de ocupación <span class="ml-2 cursor-pointer" title="Tendencia del promedio de ocupación mensual">ℹ️</span></h4>
            <canvas id="grafico-mensual" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico circular/donut -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px]">
            <h4 class="font-semibold mb-4 text-gray-700">Gráfico Circular: Ocupado vs Libre</h4>
            <canvas id="grafico-circular-ejemplo" width="220" height="220"></canvas>
        </div>
        <!-- Gráfico de dispersión (scatter) -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px]">
            <h4 class="font-semibold mb-4 text-gray-700">Gráfico de Dispersión: Ocupación vs Hora</h4>
            <canvas id="grafico-dispersion-ejemplo" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de barras apiladas -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px]">
            <h4 class="font-semibold mb-4 text-gray-700">Barras Apiladas: Uso por Día y Módulo</h4>
            <canvas id="grafico-barras-apiladas-ejemplo" width="320" height="220"></canvas>
        </div>
        <!-- Gráfico de línea -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px]">
            <h4 class="font-semibold mb-4 text-gray-700">Gráfico de Línea: Evolución Diaria</h4>
            <canvas id="grafico-linea-ejemplo" width="320" height="220"></canvas>
        </div>
        <!-- Heatmap (simulado con barras apiladas para ejemplo visual) -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px]">
            <h4 class="font-semibold mb-4 text-gray-700">Heatmap: Intensidad de Uso</h4>
            <canvas id="grafico-heatmap-ejemplo" width="320" height="220"></canvas>
        </div>
        <!-- Tabla: Reservas canceladas o no utilizadas (ocupa toda la fila) -->
        <div class="p-8 bg-white rounded-xl shadow-lg col-span-3 flex flex-col min-h-[260px] relative">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-700 flex items-center gap-2">Reservas canceladas o no utilizadas <span class="ml-2 cursor-pointer" title="Reservas que no fueron utilizadas o se cancelaron">ℹ️</span></h3>
                <div class="flex gap-2">
                    <button class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition" title="Exportar a Excel">Excel</button>
                    <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition" title="Exportar a PDF">PDF</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-center border border-gray-300 rounded-lg dark:bg-dark-eval-1">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-dark-eval-2">
                            <th class="px-4 py-2 border">Usuario</th>
                            <th class="px-4 py-2 border">Espacio</th>
                            <th class="px-4 py-2 border">Fecha</th>
                            <th class="px-4 py-2 border">Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white dark:bg-dark-eval-1">
                            <td class="border">Juan Pérez</td>
                            <td class="border">Aula 101</td>
                            <td class="border">2024-06-10</td>
                            <td class="border">No utilizada</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-dark-eval-2">
                            <td class="border">Ana López</td>
                            <td class="border">Laboratorio 202</td>
                            <td class="border">2024-06-11</td>
                            <td class="border">Cancelada</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Tabla: Horarios por día (ocupa toda la fila) -->
        <div class="p-8 bg-white rounded-xl shadow-lg col-span-3 flex flex-col min-h-[260px]">
            <h3 class="text-lg font-bold mb-4 text-gray-700">Horarios por día</h3>
            <div class="mb-6 flex items-center gap-4">
                <label class="text-sm font-medium">Filtrar por día:</label>
                <select class="rounded-md border-gray-300 shadow-sm w-40">
                    <option>Todos los días</option>
                    <option>Lunes</option>
                    <option>Martes</option>
                    <option>Miércoles</option>
                    <option>Jueves</option>
                    <option>Viernes</option>
                    <option>Sábado</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-center border border-gray-300 rounded-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 border border-gray-300">Módulo/Hora</th>
                            <th class="px-4 py-2 border border-gray-300">Día</th>
                            <th class="px-4 py-2 border border-gray-300">Asignatura</th>
                            <th class="px-4 py-2 border border-gray-300">Espacio</th>
                            <th class="px-4 py-2 border border-gray-300">Usuario Asignado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-gray-300">1 (08:00-09:00)</td>
                            <td class="border border-gray-300">Lunes</td>
                            <td class="border border-gray-300">Matemáticas</td>
                            <td class="border border-gray-300">Aula 101</td>
                            <td class="border border-gray-300">Juan Pérez</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="border border-gray-300">2 (09:00-10:00)</td>
                            <td class="border border-gray-300">Martes</td>
                            <td class="border border-gray-300">Lenguaje</td>
                            <td class="border border-gray-300">Aula 102</td>
                            <td class="border border-gray-300">Ana López</td>
                        </tr>
                        <tr class="bg-white">
                            <td class="border border-gray-300">3 (10:00-11:00)</td>
                            <td class="border border-gray-300">Miércoles</td>
                            <td class="border border-gray-300">Historia</td>
                            <td class="border border-gray-300">Aula 103</td>
                            <td class="border border-gray-300">Carlos Ruiz</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Tabla: Usuarios sin escaneo (ocupa toda la fila) -->
        <div class="p-8 bg-white rounded-xl shadow-lg col-span-3 flex flex-col items-center justify-center min-h-[260px]">
            <h3 class="text-lg font-bold mb-4 text-red-700 flex items-center"><span class="text-2xl mr-2">❌</span>Usuarios sin escaneo de asistencia</h3>
            <p class="mb-4 text-red-700">Usuarios con clases asignadas en la semana seleccionada, pero que no han registrado su asistencia mediante escaneo.</p>
            <div class="overflow-x-auto w-full">
                <table class="min-w-full text-center border border-red-200 rounded-lg">
                    <thead>
                        <tr class="bg-red-100">
                            <th class="px-4 py-2 border border-red-200">Usuario</th>
                            <th class="px-4 py-2 border border-red-200">Asignatura</th>
                            <th class="px-4 py-2 border border-red-200">Espacio</th>
                            <th class="px-4 py-2 border border-red-200">Día</th>
                            <th class="px-4 py-2 border border-red-200">Módulo/Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-red-200">Juan Pérez</td>
                            <td class="border border-red-200">Matemáticas</td>
                            <td class="border border-red-200">Aula 101</td>
                            <td class="border border-red-200">Lunes</td>
                            <td class="border border-red-200">1 (08:00-09:00)</td>
                        </tr>
                        <tr class="bg-red-50">
                            <td class="border border-red-200">Ana López</td>
                            <td class="border border-red-200">Lenguaje</td>
                            <td class="border border-red-200">Aula 102</td>
                            <td class="border border-red-200">Martes</td>
                            <td class="border border-red-200">2 (09:00-10:00)</td>
                        </tr>
                        <tr class="bg-white">
                            <td class="border border-red-200">Carlos Ruiz</td>
                            <td class="border border-red-200">Historia</td>
                            <td class="border border-red-200">Aula 103</td>
                            <td class="border border-red-200">Miércoles</td>
                            <td class="border border-red-200">3 (10:00-11:00)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Gráfico de barras
    new Chart(document.getElementById('grafico-barras-ejemplo'), {
        type: 'bar',
        data: {
            labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
            datasets: [{
                label: 'Horas ocupadas',
                data: [8, 7, 6, 5, 4],
                backgroundColor: 'rgba(59, 130, 246, 0.7)'
            }]
        },
        options: {responsive: false, plugins: {legend: {display: false}}}
    });
    // Gráfico de barras apiladas
    new Chart(document.getElementById('grafico-barras-apiladas-ejemplo'), {
        type: 'bar',
        data: {
            labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
            datasets: [
                {label: 'Módulo 1', data: [2, 2, 1, 1, 1], backgroundColor: 'rgba(59,130,246,0.7)'},
                {label: 'Módulo 2', data: [2, 1, 2, 1, 1], backgroundColor: 'rgba(16,185,129,0.7)'},
                {label: 'Módulo 3', data: [1, 2, 1, 2, 1], backgroundColor: 'rgba(251,191,36,0.7)'}
            ]
        },
        options: {responsive: false, plugins: {legend: {position: 'bottom'}}, scales: {x: {stacked: true}, y: {stacked: true}}}
    });
    // Gráfico circular/donut
    new Chart(document.getElementById('grafico-circular-ejemplo'), {
        type: 'doughnut',
        data: {
            labels: ['Ocupado', 'Libre'],
            datasets: [{
                data: [65, 35],
                backgroundColor: ['rgba(239,68,68,0.7)', 'rgba(16,185,129,0.3)']
            }]
        },
        options: {responsive: false, plugins: {legend: {position: 'bottom'}}}
    });
    // Gráfico de dispersión (scatter)
    new Chart(document.getElementById('grafico-dispersion-ejemplo'), {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Ocupación',
                data: [
                    {x: 8, y: 20}, {x: 9, y: 40}, {x: 10, y: 60}, {x: 11, y: 80}, {x: 12, y: 50},
                    {x: 13, y: 30}, {x: 14, y: 20}
                ],
                backgroundColor: 'rgba(59,130,246,0.7)'
            }]
        },
        options: {responsive: false, plugins: {legend: {display: false}}, scales: {x: {title: {display: true, text: 'Hora'}}, y: {title: {display: true, text: '% Ocupación'}}}}
    });
    // Gráfico de línea
    new Chart(document.getElementById('grafico-linea-ejemplo'), {
        type: 'line',
        data: {
            labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
            datasets: [{
                label: 'Ocupación (%)',
                data: [80, 70, 60, 50, 40],
                borderColor: 'rgba(59,130,246,1)',
                backgroundColor: 'rgba(59,130,246,0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {responsive: false, plugins: {legend: {position: 'bottom'}}}
    });
    // Heatmap simulado (barras apiladas)
    new Chart(document.getElementById('grafico-heatmap-ejemplo'), {
        type: 'bar',
        data: {
            labels: ['8:00', '9:00', '10:00', '11:00', '12:00'],
            datasets: [
                {label: 'Lunes', data: [10, 20, 30, 40, 30], backgroundColor: 'rgba(59,130,246,0.7)'},
                {label: 'Martes', data: [20, 30, 40, 30, 20], backgroundColor: 'rgba(16,185,129,0.7)'},
                {label: 'Miércoles', data: [30, 40, 30, 20, 10], backgroundColor: 'rgba(251,191,36,0.7)'}
            ]
        },
        options: {responsive: false, plugins: {legend: {position: 'bottom'}}, scales: {x: {stacked: true}, y: {stacked: true}}}
        });
    </script>
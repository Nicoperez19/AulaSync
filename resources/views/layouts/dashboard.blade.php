<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <!-- Indicadores de Estado de Registro -->
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1 mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Indicador: Estado del Escáner -->
            <div id="indicador-escanner" class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-lg transition-all duration-300">
                <div id="estado-escanner" class="text-3xl mb-2">🔄</div>
                <div class="text-sm text-gray-500">Estado del Escáner</div>
                <div id="estado-escanner-texto" class="text-lg font-semibold">Inicializando...</div>
            </div>

            <!-- Indicador: Usuario Escaneado -->
            <div id="indicador-usuario" class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-lg transition-all duration-300">
                <div id="estado-usuario" class="text-3xl mb-2">👤</div>
                <div class="text-sm text-gray-500">Usuario</div>
                <div id="estado-usuario-texto" class="text-lg font-semibold">Pendiente</div>
            </div>

            <!-- Indicador: Espacio -->
            <div id="indicador-espacio" class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-lg transition-all duration-300">
                <div id="estado-espacio" class="text-3xl mb-2">🏢</div>
                <div class="text-sm text-gray-500">Espacio</div>
                <div id="estado-espacio-texto" class="text-lg font-semibold">Pendiente</div>
            </div>

            <!-- Indicador: Estado del Registro -->
            <div id="indicador-registro" class="flex flex-col items-center justify-center p-4 bg-white rounded-xl shadow-lg transition-all duration-300">
                <div id="estado-registro" class="text-3xl mb-2">📝</div>
                <div class="text-sm text-gray-500">Estado del Registro</div>
                <div id="estado-registro-texto" class="text-lg font-semibold">Pendiente</div>
            </div>
        </div>
    </div>

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
                    @foreach($comparativaTipos as $tipo)
                        <option>{{ $tipo['tipo'] }}</option>
                    @endforeach
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
                    {{ $ocupacionSemanal }}%
                    <span class="text-green-500 text-xl" title="Subió respecto a la semana anterior">▲</span>
                </div>
            </div>
            <!-- KPI 2: Ocupación diaria -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 relative">
                <img src="https://img.icons8.com/color/48/000000/calendar--v2.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">% Ocupación diaria</div>
                <div class="flex gap-1 mt-1 text-xs">
                    @foreach($ocupacionDiaria as $dia => $porcentaje)
                        <span class="font-bold {{ $porcentaje > 70 ? 'text-green-700' : ($porcentaje > 50 ? 'text-yellow-500' : 'text-red-500') }}">{{ $dia }} {{ $porcentaje }}%</span>
                    @endforeach
                </div>
            </div>
            <!-- KPI: Promedio ocupación mensual -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6 relative">
                <img src="https://img.icons8.com/color/48/000000/line-chart.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">Promedio ocupación mensual</div>
                <div class="text-3xl font-bold text-primary-600 mt-1 flex items-center gap-2">
                    {{ $ocupacionMensual }}%
                    <span class="text-green-500 text-xl" title="Subió respecto al mes anterior">▲</span>
                </div>
            </div>
            <!-- Usuarios sin escaneo -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6">
                <span class="text-5xl mb-2 text-red-500">❌</span>
                <div class="text-base text-red-700 font-bold mb-1">Usuarios sin escaneo</div>
                <div class="text-xs text-red-600 text-center">{{ $usuariosSinEscaneo }} usuarios sin registrar asistencia hoy</div>
            </div>
            <!-- KPI 3: Horas utilizadas / disponibles -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6">
                <img src="https://img.icons8.com/color/48/000000/hourglass--v2.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">Horas utilizadas / disponibles</div>
                <div class="text-2xl font-bold text-yellow-700 mt-1">{{ $horasUtilizadas['utilizadas'] }} / {{ $horasUtilizadas['disponibles'] }}</div>
                <div class="w-full bg-yellow-200 rounded-full h-2 mt-2" style="max-width:100px;">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ ($horasUtilizadas['utilizadas'] / $horasUtilizadas['disponibles']) * 100 }}%"></div>
                </div>
            </div>
            <!-- KPI 4: Salas ocupadas / libres -->
            <div class="flex flex-col items-center justify-center min-w-[160px] bg-white rounded-xl shadow-lg p-6">
                <img src="https://img.icons8.com/color/48/000000/brick-wall.png" class="mb-2 w-12 h-12" />
                <div class="text-xs text-gray-500">Salas ocupadas / libres (hoy)</div>
                <div class="text-2xl font-bold" style="color:#a21caf; margin-top:4px;">{{ $salasOcupadas['ocupadas'] }} <span class="text-gray-400">/</span> {{ $salasOcupadas['libres'] }}</div>
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
            <h4 class="font-semibold mb-4 text-gray-700 flex items-center gap-2">Top 3 Salas más usadas <span class="ml-2 cursor-pointer" title="Ranking de salas según uso mensual">ℹ️</span></h4>
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
        <!-- Gráfico de línea: Promedio mensual -->
        <div class="p-8 bg-white rounded-xl shadow-lg flex flex-col items-center min-h-[260px] relative">
            <h4 class="font-semibold mb-4 text-gray-700 flex items-center gap-2">Evolución mensual de ocupación <span class="ml-2 cursor-pointer" title="Tendencia del promedio de ocupación mensual">ℹ️</span></h4>
            <canvas id="grafico-mensual" width="320" height="220"></canvas>
        </div>
        <!-- Tabla: Reservas canceladas o no utilizadas -->
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
                        @foreach($reservasCanceladas as $reserva)
                        <tr class="bg-white dark:bg-dark-eval-1">
                            <td class="border">{{ $reserva['usuario'] }}</td>
                            <td class="border">{{ $reserva['espacio'] }}</td>
                            <td class="border">{{ $reserva['fecha'] }}</td>
                            <td class="border">{{ $reserva['motivo'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Tabla: Horarios por día -->
        <div class="p-8 bg-white rounded-xl shadow-lg col-span-3 flex flex-col min-h-[260px]">
            <h3 class="text-lg font-bold mb-4 text-gray-700">Horarios por día</h3>
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
                        @foreach($horariosPorDia as $horario)
                        <tr class="bg-white">
                            <td class="border border-gray-300">{{ $horario['modulo'] }}</td>
                            <td class="border border-gray-300">{{ $horario['dia'] }}</td>
                            <td class="border border-gray-300">{{ $horario['asignatura'] }}</td>
                            <td class="border border-gray-300">{{ $horario['espacio'] }}</td>
                            <td class="border border-gray-300">{{ $horario['usuario'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de barras: Uso por Día
    new Chart(document.getElementById('grafico-barras-ejemplo'), {
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
   new Chart(document.getElementById('grafico-top-salas'), {
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
    new Chart(document.getElementById('grafico-top-asignaturas'), {
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
    new Chart(document.getElementById('grafico-comparativa-tipos'), {
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
    new Chart(document.getElementById('grafico-mensual'), {
        type: 'line',
        data: {
            labels: {!! json_encode($evolucionMensual['labels']) !!},
            datasets: [{
                label: 'Ocupación (%)',
                data: {!! json_encode($evolucionMensual['data']) !!},
                borderColor: 'rgba(59,130,246,1)',
                backgroundColor: 'rgba(59,130,246,0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {responsive: false, plugins: {legend: {position: 'bottom'}}}
    });

    // Funciones para manejar los indicadores de estado con cambios de color automáticos
    function actualizarEstadoEscanner(estado) {
        const indicador = document.getElementById('indicador-escanner');
        const icono = document.getElementById('estado-escanner');
        const texto = document.getElementById('estado-escanner-texto');
        
        // Remover clases de color anteriores
        indicador.classList.remove('bg-gray-100', 'bg-green-100', 'bg-red-100');
        texto.classList.remove('text-gray-700', 'text-green-700', 'text-red-700');
        
        switch(estado) {
            case 'inicializando':
                icono.textContent = '🔄';
                indicador.classList.add('bg-gray-100');
                texto.textContent = 'Inicializando...';
                texto.classList.add('text-gray-700');
                break;
            case 'activo':
                icono.textContent = '✅';
                indicador.classList.add('bg-green-100');
                texto.textContent = 'Activo';
                texto.classList.add('text-green-700');
                break;
            case 'error':
                icono.textContent = '❌';
                indicador.classList.add('bg-red-100');
                texto.textContent = 'Error';
                texto.classList.add('text-red-700');
                break;
        }
    }

    function actualizarEstadoUsuario(estado, datos = null) {
        const indicador = document.getElementById('indicador-usuario');
        const icono = document.getElementById('estado-usuario');
        const texto = document.getElementById('estado-usuario-texto');
        
        // Remover clases de color anteriores
        indicador.classList.remove('bg-gray-100', 'bg-green-100', 'bg-red-100');
        texto.classList.remove('text-gray-700', 'text-green-700', 'text-red-700');
        
        switch(estado) {
            case 'pendiente':
                icono.textContent = '👤';
                indicador.classList.add('bg-gray-100');
                texto.textContent = 'Pendiente';
                texto.classList.add('text-gray-700');
                break;
            case 'escaneado':
                icono.textContent = '✅';
                indicador.classList.add('bg-green-100');
                texto.textContent = datos?.nombre || 'Usuario Escaneado';
                texto.classList.add('text-green-700');
                break;
            case 'error':
                icono.textContent = '❌';
                indicador.classList.add('bg-red-100');
                texto.textContent = datos?.mensaje || 'Error';
                texto.classList.add('text-red-700');
                break;
        }
    }

    function actualizarEstadoEspacio(estado, datos = null) {
        const indicador = document.getElementById('indicador-espacio');
        const icono = document.getElementById('estado-espacio');
        const texto = document.getElementById('estado-espacio-texto');
        
        // Remover clases de color anteriores
        indicador.classList.remove('bg-gray-100', 'bg-green-100', 'bg-red-100', 'bg-yellow-100');
        texto.classList.remove('text-gray-700', 'text-green-700', 'text-red-700', 'text-yellow-700');
        
        switch(estado) {
            case 'pendiente':
                icono.textContent = '🏢';
                indicador.classList.add('bg-gray-100');
                texto.textContent = 'Pendiente';
                texto.classList.add('text-gray-700');
                break;
            case 'escaneado':
                icono.textContent = '✅';
                indicador.classList.add('bg-green-100');
                texto.textContent = datos?.nombre || 'Espacio Escaneado';
                texto.classList.add('text-green-700');
                break;
            case 'ocupado':
                icono.textContent = '🔒';
                indicador.classList.add('bg-red-100');
                texto.textContent = 'Espacio Ocupado';
                texto.classList.add('text-red-700');
                break;
            case 'reservado':
                icono.textContent = '⏰';
                indicador.classList.add('bg-yellow-100');
                texto.textContent = 'Espacio Reservado';
                texto.classList.add('text-yellow-700');
                break;
            case 'error':
                icono.textContent = '❌';
                indicador.classList.add('bg-red-100');
                texto.textContent = datos?.mensaje || 'Error';
                texto.classList.add('text-red-700');
                break;
        }
    }

    function actualizarEstadoRegistro(estado, datos = null) {
        const indicador = document.getElementById('indicador-registro');
        const icono = document.getElementById('estado-registro');
        const texto = document.getElementById('estado-registro-texto');
        
        // Remover clases de color anteriores
        indicador.classList.remove('bg-gray-100', 'bg-green-100', 'bg-red-100', 'bg-yellow-100');
        texto.classList.remove('text-gray-700', 'text-green-700', 'text-red-700', 'text-yellow-700');
        
        switch(estado) {
            case 'pendiente':
                icono.textContent = '📝';
                indicador.classList.add('bg-gray-100');
                texto.textContent = 'Pendiente';
                texto.classList.add('text-gray-700');
                break;
            case 'completado':
                icono.textContent = '✅';
                indicador.classList.add('bg-green-100');
                texto.textContent = 'Registro Completado';
                texto.classList.add('text-green-700');
                break;
            case 'procesando':
                icono.textContent = '⏳';
                indicador.classList.add('bg-yellow-100');
                texto.textContent = 'Procesando...';
                texto.classList.add('text-yellow-700');
                break;
            case 'error':
                icono.textContent = '❌';
                indicador.classList.add('bg-red-100');
                texto.textContent = datos?.mensaje || 'Error en Registro';
                texto.classList.add('text-red-700');
                break;
        }
    }

    // Función para reiniciar todos los estados
    function reiniciarEstados() {
        actualizarEstadoEscanner('inicializando');
        actualizarEstadoUsuario('pendiente');
        actualizarEstadoEspacio('pendiente');
        actualizarEstadoRegistro('pendiente');
    }

    // Inicializar estados al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
        reiniciarEstados();
    });

    async function initQRScannerSalidaProfesor() {
        if (html5QrcodeScanner === null) {
            try {
                actualizarEstadoEscanner('inicializando');
                document.getElementById('salida-profesor-cargando-msg').textContent =
                    'Cargando escáner, por favor espere...';
                document.getElementById('salida-profesor-cargando-msg').classList.remove('hidden');
                document.getElementById('salida-profesor-error-msg').classList.add('hidden');
                
                // Inicializar el escáner
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "salida-profesor-placeholder",
                    { fps: 10, qrbox: { width: 250, height: 250 } }
                );

                await html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                actualizarEstadoEscanner('activo');
            } catch (error) {
                actualizarEstadoEscanner('error');
                mostrarErrorEscaneoSalida('Error al inicializar el escáner: ' + error.message);
            }
        }
    }

    function mostrarErrorEscaneoSalida(mensaje) {
        actualizarEstadoEscanner('error');
        const errorMsg = document.getElementById('salida-profesor-error-msg');
        const cargandoMsg = document.getElementById('salida-profesor-cargando-msg');
        const btnReintentar = document.getElementById('btn-reintentar-salida-profesor');
        const qrPlaceholder = document.getElementById('salida-profesor-placeholder');
        
        if (errorMsg) {
            errorMsg.textContent = mensaje;
            errorMsg.classList.remove('hidden');
        }
        if (cargandoMsg) cargandoMsg.textContent = '';
        if (btnReintentar) btnReintentar.classList.remove('hidden');
        if (qrPlaceholder) qrPlaceholder.style.display = 'flex';
    }

    function reiniciarEscaneoSalidaProfesor() {
        actualizarEstadoEscanner('inicializando');
        document.getElementById('salida-profesor-error-msg').classList.add('hidden');
        document.getElementById('btn-reintentar-salida-profesor').classList.add('hidden');
        document.getElementById('salida-profesor-cargando-msg').textContent = 'Cargando escáner, por favor espere...';
        document.getElementById('salida-profesor-cargando-msg').classList.remove('hidden');
        initQRScannerSalidaProfesor();
    }

    async function handleScan(event) {
        if (event.key === 'Enter') {
            if (esperandoUsuario) {
                const match = bufferQR.match(/RUN¿(\d+)/);
                if (match) {
                    usuarioEscaneado = match[1];
                    const usuarioInfo = await verificarUsuario(usuarioEscaneado);
                    
                    if (usuarioInfo && usuarioInfo.verificado) {
                        actualizarEstadoUsuario('escaneado', {
                            nombre: usuarioInfo.usuario.nombre
                        });
                        document.getElementById('qr-status').innerHTML = 'Usuario verificado. Escanee el espacio.';
                        document.getElementById('run-escaneado').textContent = usuarioInfo.usuario.run;
                        document.getElementById('nombre-usuario').textContent = usuarioInfo.usuario.nombre;
                        esperandoUsuario = false;
                    } else {
                        actualizarEstadoUsuario('error', {
                            mensaje: usuarioInfo?.mensaje || 'Error de verificación'
                        });
                        document.getElementById('qr-status').innerHTML = usuarioInfo?.mensaje || 'Error de verificación';
                    }
                } else {
                    actualizarEstadoUsuario('error', {
                        mensaje: 'RUN inválido'
                    });
                    document.getElementById('qr-status').innerHTML = 'RUN inválido';
                }
            } else {
                const espacioProcesado = bufferQR.replace(/'/g, '-');
                const espacioInfo = await verificarEspacio(espacioProcesado);
                
                if (espacioInfo?.verificado) {
                    if (espacioInfo.disponible) {
                        actualizarEstadoEspacio('escaneado', {
                            nombre: espacioInfo.espacio.nombre
                        });
                        const confirmar = confirm(`¿Desea utilizar el espacio ${espacioInfo.espacio.nombre}?`);
                        if (confirmar) {
                            const reserva = await crearReserva(usuarioEscaneado, espacioProcesado);
                            if (reserva?.success) {
                                actualizarEstadoRegistro('completado');
                                document.getElementById('qr-status').innerHTML = 'Reserva exitosa';
                                document.getElementById('nombre-espacio').textContent = espacioInfo.espacio.nombre;
                            } else {
                                actualizarEstadoRegistro('error', {
                                    mensaje: reserva?.mensaje || 'Error en reserva'
                                });
                                document.getElementById('qr-status').innerHTML = reserva?.mensaje || 'Error en reserva';
                            }
                        } else {
                            actualizarEstadoRegistro('error', {
                                mensaje: 'Reserva cancelada'
                            });
                            document.getElementById('qr-status').innerHTML = 'Reserva cancelada';
                        }
                    } else {
                        actualizarEstadoEspacio('error', {
                            mensaje: 'Espacio ocupado'
                        });
                        document.getElementById('qr-status').innerHTML = 'Espacio ocupado';
                    }
                } else {
                    actualizarEstadoEspacio('error', {
                        mensaje: espacioInfo?.mensaje || 'Error al verificar espacio'
                    });
                    document.getElementById('qr-status').innerHTML = espacioInfo?.mensaje || 'Error al verificar espacio';
                }
                esperandoUsuario = true;
            }
            bufferQR = '';
                event.target.value = '';
        } else if (event.key.length === 1) {
            bufferQR += event.key;
        }
    }
    </script>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Análisis por tipo de espacio') }}
            </h2>
        </div>
    </x-slot>

    <!-- Filtros -->
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 mb-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">Filtros de búsqueda</h3>
        <form method="GET" action="{{ route('reporteria.tipo-espacio') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- Fecha de inicio -->
            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de inicio
                </label>
                <input type="date" 
                       id="fecha_inicio" 
                       name="fecha_inicio" 
                       value="{{ $fechaInicio }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <!-- Fecha de fin -->
            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de fin
                </label>
                <input type="date" 
                       id="fecha_fin" 
                       name="fecha_fin" 
                       value="{{ $fechaFin }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <!-- Piso -->
            <div>
                <label for="piso" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Piso
                </label>
                <select id="piso" 
                        name="piso" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <option value="">Todos los pisos</option>
                    @foreach($pisos as $numeroPiso => $nombrePiso)
                        <option value="{{ $numeroPiso }}" {{ $piso == $numeroPiso ? 'selected' : '' }}>
                            Piso {{ $nombrePiso }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Tipo de usuario -->
            <div>
                <label for="tipo_usuario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tipo de usuario
                </label>
                <select id="tipo_usuario" 
                        name="tipo_usuario" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <option value="">Todos los tipos</option>
                    @foreach($tiposUsuario as $key => $tipo)
                        <option value="{{ $key }}" {{ $tipoUsuario == $key ? 'selected' : '' }}>
                            {{ $tipo }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Tipo de espacio -->
            <div>
                <label for="tipo_espacio_filtro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tipo de espacio
                </label>
                <select id="tipo_espacio_filtro" 
                        name="tipo_espacio_filtro" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <option value="">Todos los tipos</option>
                    @foreach($tiposEspacioDisponibles as $key => $tipo)
                        <option value="{{ $key }}" {{ $tipoEspacioFiltro == $key ? 'selected' : '' }}>
                            {{ $tipo }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Día -->
            <div>
                <label for="dia_filtro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Día
                </label>
                <select id="dia_filtro" 
                        name="dia_filtro" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <option value="">Todos los días</option>
                    @foreach($diasDisponibles as $key => $dia)
                        <option value="{{ $key }}" {{ $diaFiltro == $key ? 'selected' : '' }}>
                            {{ $dia }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Botones -->
            <div class="flex gap-2 items-end col-span-full">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
                <a href="{{ route('reporteria.tipo-espacio') }}" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de tipos de espacio</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $total_tipos }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
                    <i class="fas fa-user-check text-green-600 dark:text-green-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Promedio utilización</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $promedio_utilizacion }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                    <i class="fas fa-building text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mayor utilización</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $mayor_utilizacion }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                Registro por tipo de espacio ({{ $total_tipos }} tipos)
            </h3>
            <div class="flex gap-2">
                <a href="{{ route('reporteria.tipo-espacio.export', ['format' => 'excel']) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                </a>
                <a href="{{ route('reporteria.tipo-espacio.export', ['format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border">Tipo de sala</th>
                        <th class="py-2 px-4 border">Nivel de utilización</th>
                        <th class="py-2 px-4 border">Comparativa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tiposEspacio as $tipo)
                        <tr>
                            <td class="py-2 px-4 border">{{ $tipo['nombre'] }}</td>
                            <td class="py-2 px-4 border align-middle">
                                <div class="w-full flex items-center gap-2">
                                    <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                                        <div class="bg-green-400 h-4 rounded-full" style="width: {{ $tipo['utilizacion'] }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white min-w-[40px] text-right">{{ $tipo['utilizacion'] }}%</span>
                                </div>
                            </td>
                            <td class="py-2 px-4 border">{{ $tipo['comparativa'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">No hay datos para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Nueva sección: Utilización por días y módulos -->
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                Utilización por días y módulos
                @if($tipoEspacioFiltro || $diaFiltro)
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                        (Filtrado: 
                        @if($tipoEspacioFiltro)
                            {{ $tipoEspacioFiltro }}
                        @endif
                        @if($tipoEspacioFiltro && $diaFiltro)
                            - 
                        @endif
                        @if($diaFiltro)
                            {{ ucfirst($diaFiltro) }}
                        @endif
                        )
                    </span>
                @endif
            </h3>
            <div class="flex gap-2">
                <button onclick="toggleView()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-table mr-2"></i><span id="toggle-text">Vista por Día</span>
                </button>
            </div>
        </div>

        <!-- Resumen estadístico -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @php
                $totalModulos = 0;
                $modulosConUtilizacion = 0;
                $promedioUtilizacion = 0;
                $moduloMasUtilizado = null;
                $maxUtilizacion = 0;
                
                foreach($utilizacionPorDiasModulos as $datosTipo) {
                    foreach($datosTipo['dias'] as $datosDia) {
                        foreach($datosDia['modulos'] as $modulo) {
                            $totalModulos++;
                            $promedioUtilizacion += $modulo['porcentaje'];
                            if ($modulo['porcentaje'] > 0) {
                                $modulosConUtilizacion++;
                            }
                            if ($modulo['porcentaje'] > $maxUtilizacion) {
                                $maxUtilizacion = $modulo['porcentaje'];
                                $moduloMasUtilizado = "M{$modulo['modulo']} - {$datosDia['dia']} - {$datosTipo['tipo']}";
                            }
                        }
                    }
                }
                $promedioUtilizacion = $totalModulos > 0 ? round($promedioUtilizacion / $totalModulos, 1) : 0;
                $porcentajeModulosUtilizados = $totalModulos > 0 ? round(($modulosConUtilizacion / $totalModulos) * 100, 1) : 0;
            @endphp
            
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-800">
                        <i class="fas fa-clock text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total módulos analizados</p>
                        <p class="text-2xl font-semibold text-blue-900 dark:text-blue-100">{{ $totalModulos }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg dark:bg-green-800">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Módulos con utilización</p>
                        <p class="text-2xl font-semibold text-green-900 dark:text-green-100">{{ $porcentajeModulosUtilizados }}%</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-800">
                        <i class="fas fa-chart-line text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Promedio utilización</p>
                        <p class="text-2xl font-semibold text-yellow-900 dark:text-yellow-100">{{ $promedioUtilizacion }}%</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg dark:bg-red-800">
                        <i class="fas fa-fire text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">Máxima utilización</p>
                        <p class="text-lg font-semibold text-red-900 dark:text-red-100">{{ $maxUtilizacion }}%</p>
                        <p class="text-xs text-red-700 dark:text-red-300">{{ $moduloMasUtilizado }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leyenda de módulos -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Leyenda de módulos:</h4>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-2 text-xs">
                @for($i = 1; $i <= 15; $i++)
                    @php
                        $horario = '';
                        if ($i == 1) {
                            $horario = '08:10 - 09:00';
                        } elseif ($i == 2) {
                            $horario = '09:10 - 10:00';
                        } elseif ($i == 3) {
                            $horario = '10:10 - 11:00';
                        } elseif ($i == 4) {
                            $horario = '11:10 - 12:00';
                        } elseif ($i == 5) {
                            $horario = '12:10 - 13:00';
                        } elseif ($i == 6) {
                            $horario = '13:10 - 14:00';
                        } elseif ($i == 7) {
                            $horario = '14:10 - 15:00';
                        } elseif ($i == 8) {
                            $horario = '15:10 - 16:00';
                        } elseif ($i == 9) {
                            $horario = '16:10 - 17:00';
                        } elseif ($i == 10) {
                            $horario = '17:10 - 18:00';
                        } elseif ($i == 11) {
                            $horario = '18:10 - 19:00';
                        } elseif ($i == 12) {
                            $horario = '19:10 - 20:00';
                        } elseif ($i == 13) {
                            $horario = '20:10 - 21:00';
                        } elseif ($i == 14) {
                            $horario = '21:10 - 22:00';
                        } elseif ($i == 15) {
                            $horario = '22:10 - 23:00';
                        }
                    @endphp
                    <div class="flex items-center gap-1">
                        <span class="font-semibold text-gray-600 dark:text-gray-400">M{{ $i }}:</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $horario }}</span>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Vista por tipo de espacio -->
        <div id="vista-por-tipo" class="space-y-6">
            @foreach($utilizacionPorDiasModulos as $datosTipo)
                <div class="border rounded-lg p-4 dark:border-gray-600">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
                        <i class="fas fa-building mr-2 text-blue-600"></i>
                        {{ ucfirst($datosTipo['tipo']) }}
                    </h4>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th class="py-2 px-3 border text-xs font-semibold text-gray-700 dark:text-gray-300">Día</th>
                                    @for($i = 1; $i <= 15; $i++)
                                        <th class="py-2 px-1 border text-xs font-semibold text-gray-700 dark:text-gray-300">M{{ $i }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datosTipo['dias'] as $datosDia)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="py-2 px-3 border text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-calendar-day mr-1 text-green-600"></i>
                                            {{ ucfirst($datosDia['dia']) }}
                                        </td>
                                        @for($i = 1; $i <= 15; $i++)
                                            @php
                                                $modulo = collect($datosDia['modulos'])->firstWhere('modulo', $i);
                                                $porcentaje = $modulo ? $modulo['porcentaje'] : 0;
                                                $reservas = $modulo ? $modulo['reservas'] : 0;
                                                
                                                // Determinar color basado en el porcentaje
                                                $color = 'bg-gray-400';
                                                if ($porcentaje > 0 && $porcentaje <= 25) {
                                                    $color = 'bg-green-400';
                                                } elseif ($porcentaje > 25 && $porcentaje <= 50) {
                                                    $color = 'bg-yellow-400';
                                                } elseif ($porcentaje > 50 && $porcentaje <= 75) {
                                                    $color = 'bg-orange-400';
                                                } elseif ($porcentaje > 75) {
                                                    $color = 'bg-red-400';
                                                }
                                            @endphp
                                            <td class="py-2 px-1 border text-center">
                                                <div class="flex flex-col items-center">
                                                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 mb-1">
                                                        <div class="{{ $color }} h-2 rounded-full transition-all duration-300" style="width: {{ $porcentaje }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $porcentaje }}%</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $reservas }})</span>
                                                </div>
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Vista por día -->
        <div id="vista-por-dia" class="hidden space-y-6">
            @php
                $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
            @endphp
            
            @foreach($dias as $dia)
                <div class="border rounded-lg p-4 dark:border-gray-600">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
                        <i class="fas fa-calendar-week mr-2 text-green-600"></i>
                        {{ ucfirst($dia) }}
                    </h4>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th class="py-2 px-3 border text-xs font-semibold text-gray-700 dark:text-gray-300">Tipo de Espacio</th>
                                    @for($i = 1; $i <= 15; $i++)
                                        <th class="py-2 px-1 border text-xs font-semibold text-gray-700 dark:text-gray-300">M{{ $i }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($utilizacionPorDiasModulos as $datosTipo)
                                    @php
                                        $datosDia = collect($datosTipo['dias'])->firstWhere('dia', $dia);
                                    @endphp
                                    @if($datosDia)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="py-2 px-3 border text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <i class="fas fa-building mr-1 text-blue-600"></i>
                                                {{ ucfirst($datosTipo['tipo']) }}
                                            </td>
                                            @for($i = 1; $i <= 15; $i++)
                                                @php
                                                    $modulo = collect($datosDia['modulos'])->firstWhere('modulo', $i);
                                                    $porcentaje = $modulo ? $modulo['porcentaje'] : 0;
                                                    $reservas = $modulo ? $modulo['reservas'] : 0;
                                                    
                                                    // Determinar color basado en el porcentaje
                                                    $color = 'bg-gray-400';
                                                    if ($porcentaje > 0 && $porcentaje <= 25) {
                                                        $color = 'bg-green-400';
                                                    } elseif ($porcentaje > 25 && $porcentaje <= 50) {
                                                        $color = 'bg-yellow-400';
                                                    } elseif ($porcentaje > 50 && $porcentaje <= 75) {
                                                        $color = 'bg-orange-400';
                                                    } elseif ($porcentaje > 75) {
                                                        $color = 'bg-red-400';
                                                    }
                                                @endphp
                                                <td class="py-2 px-1 border text-center">
                                                    <div class="flex flex-col items-center">
                                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 mb-1">
                                                            <div class="{{ $color }} h-2 rounded-full transition-all duration-300" style="width: {{ $porcentaje }}%"></div>
                                                        </div>
                                                        <span class="text-xs font-semibold text-gray-900 dark:text-white">{{ $porcentaje }}%</span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ $reservas }})</span>
                                                    </div>
                                                </td>
                                            @endfor
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Leyenda de colores -->
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Leyenda de colores:</h4>
            <div class="flex flex-wrap gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-2 bg-gray-400 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">Sin utilización (0%)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-2 bg-green-400 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">Baja utilización (1-25%)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-2 bg-yellow-400 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">Media utilización (26-50%)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-2 bg-orange-400 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">Alta utilización (51-75%)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-2 bg-red-400 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">Muy alta utilización (76-100%)</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentView = 'tipo'; // Vista actual: 'tipo' o 'dia'
        
        function toggleView() {
            const vistaPorTipo = document.getElementById('vista-por-tipo');
            const vistaPorDia = document.getElementById('vista-por-dia');
            const toggleText = document.getElementById('toggle-text');
            
            if (currentView === 'tipo') {
                // Cambiar a vista por día
                vistaPorTipo.classList.add('hidden');
                vistaPorDia.classList.remove('hidden');
                toggleText.textContent = 'Vista por Tipo';
                currentView = 'dia';
            } else {
                // Cambiar a vista por tipo
                vistaPorTipo.classList.remove('hidden');
                vistaPorDia.classList.add('hidden');
                toggleText.textContent = 'Vista por Día';
                currentView = 'tipo';
            }
        }

        // Inicializar el texto del botón al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const toggleText = document.getElementById('toggle-text');
            if (toggleText) {
                toggleText.textContent = 'Vista por Día';
            }
        });
    </script>
</x-app-layout> 
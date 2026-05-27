<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-landmark"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Uso del Auditorio</h2>
                    <p class="text-sm text-gray-500">Reporte de registros de uso en auditorios</p>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('reportes.uso-auditorio.export', ['format' => 'pdf']) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}"
                   class="px-4 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                    <i class="mr-2 fas fa-file-pdf"></i> Exportar PDF
                </a>
                <a href="{{ route('reportes.uso-auditorio.export', ['format' => 'excel']) }}?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}"
                   class="px-4 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                    <i class="mr-2 fas fa-file-excel"></i> Exportar Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="px-4 min-h-[80vh]">
        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-4">
            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <i class="text-blue-600 fas fa-landmark dark:text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Auditorios</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalAuditorios }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
                        <i class="text-green-600 fas fa-chart-line dark:text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Utilización Promedio</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $promedioUtilizacion }}%</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                        <i class="text-yellow-600 fas fa-calendar-check dark:text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Reservas</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalReservas }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-900">
                        <i class="text-purple-600 fas fa-clock dark:text-purple-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Horas de Uso</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ round($horasUtilizadas, 1) }}h</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-4 mb-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-700 dark:text-gray-300">
                <i class="fas fa-filter"></i> Filtros de búsqueda
            </h3>
            <form method="GET" action="{{ route('reportes.uso-auditorio') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="fecha_inicio" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha inicio
                    </label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label for="fecha_fin" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha fin
                    </label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 text-white transition-colors bg-light-cloud-blue rounded-lg hover:bg-[#b10718]">
                        <i class="mr-2 fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('reportes.uso-auditorio') }}"
                       class="px-4 py-2 text-white transition-colors bg-gray-500 rounded-lg hover:bg-gray-600">
                        <i class="mr-2 fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabla de Registros -->
        <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Fecha</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Auditorio</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Usuario</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Asignatura/Motivo</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-center">Entrada</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-center">Salida</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-center">Duración</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($historico as $reserva)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $reserva['fecha'] }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $reserva['espacio'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900 dark:text-white truncate max-w-[200px]" title="{{ $reserva['usuario'] }}">{{ $reserva['usuario'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $reserva['run'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $reserva['asignatura'] ?: 'Uso administrativo / Espontáneo' }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ $reserva['hora_inicio'] }}</td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ $reserva['hora_fin'] }}</td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ $reserva['duracion'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $reserva['estado'] == 'Activa' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                                           ($reserva['estado'] == 'Finalizada' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' :
                                            'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300') }}">
                                        {{ $reserva['estado'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron registros para el período seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

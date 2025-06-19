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
        <form method="GET" action="{{ route('reporteria.tipo-espacio') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
            <!-- Botones -->
            <div class="flex gap-2 items-end">
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
</x-app-layout> 
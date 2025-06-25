<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Accesos registrados (QR)') }}
            </h2>
        </div>
    </x-slot>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg dark:bg-green-900 dark:border-green-700 dark:text-green-300">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg dark:bg-red-900 dark:border-red-700 dark:text-red-300">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 mb-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">Filtros de búsqueda</h3>
        <form method="GET" action="{{ route('reporteria.accesos') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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

            <!-- Espacio -->
            <div>
                <label for="espacio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Espacio
                </label>
                <select id="espacio" 
                        name="espacio" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <option value="">Todos los espacios</option>
                    @foreach($espacios as $nombreEspacio => $nombreCompleto)
                        <option value="{{ $nombreEspacio }}" {{ $espacio == $nombreEspacio ? 'selected' : '' }}>
                            {{ $nombreCompleto }}
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
                <a href="{{ route('reporteria.accesos.limpiar') }}" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de accesos</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $accesos->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
                    <i class="fas fa-user-check text-green-600 dark:text-green-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios únicos</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $accesos->unique('run')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                    <i class="fas fa-building text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Espacios utilizados</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $accesos->unique('espacio')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-900">
                    <i class="fas fa-clock text-purple-600 dark:text-purple-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">En curso</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $accesos->where('hora_salida', 'En curso')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de accesos -->
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                Registro de accesos ({{ $accesos->count() }} registros)
            </h3>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('reporteria.accesos.export.filtros', ['format' => 'excel']) }}" class="inline">
                    @csrf
                    <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                    <input type="hidden" name="piso" value="{{ $piso }}">
                    <input type="hidden" name="tipo_usuario" value="{{ $tipoUsuario }}">
                    <input type="hidden" name="espacio" value="{{ $espacio }}">
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                    </button>
                </form>
                <form method="POST" action="{{ route('reporteria.accesos.export.filtros', ['format' => 'pdf']) }}" class="inline">
                    @csrf
                    <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                    <input type="hidden" name="piso" value="{{ $piso }}">
                    <input type="hidden" name="tipo_usuario" value="{{ $tipoUsuario }}">
                    <input type="hidden" name="espacio" value="{{ $espacio }}">
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg dark:bg-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Espacio
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Hora entrada
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Hora salida
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Duración
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tipo usuario
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Estado
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($accesos as $acceso)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $acceso['usuario'] }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $acceso['run'] }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $acceso['email'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $acceso['espacio'] }} - {{ $acceso['id_espacio'] }} - Piso {{ $acceso['piso'] }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $acceso['facultad'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $acceso['fecha'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $acceso['hora_entrada'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm {{ $acceso['hora_salida'] == 'En curso' ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                                    {{ $acceso['hora_salida'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm {{ $acceso['duracion'] == 'En curso' ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                                    {{ $acceso['duracion'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $acceso['tipo_usuario'] == 'profesor' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                       ($acceso['tipo_usuario'] == 'estudiante' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                       ($acceso['tipo_usuario'] == 'administrativo' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                       'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200')) }}">
                                    {{ ucfirst($acceso['tipo_usuario']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $acceso['estado'] == 'activa' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                       ($acceso['estado'] == 'finalizada' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : 
                                       'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                    {{ ucfirst($acceso['estado']) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-search text-4xl mb-2"></i>
                                    <p class="text-lg font-medium">No se encontraron accesos registrados</p>
                                    <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($accesos->count() > 0)
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Mostrando {{ $accesos->count() }} de {{ $accesos->count() }} registros
                </div>
            </div>
        @endif
    </div>
</x-app-layout> 
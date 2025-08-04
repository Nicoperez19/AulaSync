<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-qrcode"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Accesos registrados (QR)</h2>
                    <p class="text-sm text-gray-500">Revisa los registros de acceso escaneados por código QR</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div
            class="px-4 mb-4 text-green-700 bg-green-100 border border-green-400 rounded-lg dark:bg-green-900 dark:border-green-700 dark:text-green-300">
            <div class="flex items-center">
                <i class="mr-2 fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div
            class="p-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded-lg dark:bg-red-900 dark:border-red-700 dark:text-red-300">
            <div class="flex items-center">
                <i class="mr-2 fas fa-exclamation-triangle"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="px-6 min-h-[80vh]">
        <!-- Filtros -->
        <div class="p-6 mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="flex items-center gap-2 mb-4 text-lg font-semibold text-gray-700 dark:text-gray-300">
                <i class="fas fa-filter"></i> Filtros de búsqueda
            </h3>
            <form method="GET" action="{{ route('reportes.accesos') }}"
                class="flex flex-wrap items-end w-full gap-x-4 gap-y-4">
                <!-- Fecha de inicio -->
                <div class="flex flex-col min-w-[180px] flex-1">
                    <label for="fecha_inicio" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha de inicio
                    </label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}"
                        placeholder="dd-mm-aaaa"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 h-[37px] px-4 appearance-none py-0">
                </div>

                <!-- Fecha de fin -->
                <div class="flex flex-col min-w-[180px] flex-1">
                    <label for="fecha_fin" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha de fin
                    </label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}" placeholder="dd-mm-aaaa"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 h-[37px] px-4 appearance-none py-0">
                </div>

                <!-- Piso -->
                <div class="flex flex-col min-w-[180px] flex-1">
                    <label for="piso" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Piso
                    </label>
                    <select id="piso" name="piso"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 h-[37px] px-4 appearance-none py-0">
                        <option value="">Todos los pisos</option>
                        @foreach($pisos as $numeroPiso => $nombrePiso)
                            <option value="{{ $numeroPiso }}" {{ $piso == $numeroPiso ? 'selected' : '' }}>
                                Piso {{ $nombrePiso }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo de usuario -->
                <div class="flex flex-col min-w-[180px] flex-1">
                    <label for="tipo_usuario" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tipo de usuario
                    </label>
                    <select id="tipo_usuario" name="tipo_usuario"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 h-[37px] px-4 appearance-none py-0">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposUsuario as $key => $tipo)
                            <option value="{{ $key }}" {{ $tipoUsuario == $key ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Espacio -->
                <div class="flex flex-col min-w-[200px] flex-[2]">
                    <label for="espacio" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Espacio
                    </label>
                    <select id="espacio" name="espacio"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 h-[37px] px-4 appearance-none py-0">
                        <option value="">Todos los espacios</option>
                        @foreach($espacios as $nombreEspacio => $nombreCompleto)
                            <option value="{{ $nombreEspacio }}" {{ $espacio == $nombreEspacio ? 'selected' : '' }}>
                                {{ $nombreCompleto }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones -->
                <div class="flex gap-2 items-end min-w-[200px] mt-6">
                    <button type="submit"
                        class="ml-2 px-4 py-2 bg-light-cloud-blue text-white rounded font-semibold text-sm hover:bg-[#b10718] transition">
                        <i class="mr-2 fas fa-search"></i>Filtrar
                    </button>


                    <a href="{{ route('reportes.accesos.limpiar') }}"
                        class="px-4 py-2 ml-2 text-sm font-semibold text-white transition bg-gray-500 rounded hover:bg-gray-600">
                        <i class="mr-2 fas fa-times"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <i class="text-blue-600 fas fa-users dark:text-blue-400"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de accesos</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $accesos->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
                        <i class="text-green-600 fas fa-user-check dark:text-green-400"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios únicos</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $accesos->unique('run')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                        <i class="text-yellow-600 fas fa-building dark:text-yellow-400"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Espacios utilizados</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $accesos->unique('espacio')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-900">
                        <i class="text-purple-600 fas fa-clock dark:text-purple-400"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">En curso</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $accesos->where('hora_salida', 'En curso')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de accesos -->
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Registro de accesos ({{ $accesos->count() }} registros)
                </h3>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('reportes.accesos.export.filtros', ['format' => 'excel']) }}"
                        class="inline">
                        @csrf
                        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                        <input type="hidden" name="piso" value="{{ $piso }}">
                        <input type="hidden" name="tipo_usuario" value="{{ $tipoUsuario }}">
                        <input type="hidden" name="espacio" value="{{ $espacio }}">
                        <button type="submit"
                            class="px-4 py-2 text-white transition-colors bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="mr-2 fas fa-file-excel"></i>Exportar Excel
                        </button>
                    </form>
                    <form method="POST" action="{{ route('reportes.accesos.export.filtros', ['format' => 'pdf']) }}"
                        class="inline">
                        @csrf
                        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                        <input type="hidden" name="piso" value="{{ $piso }}">
                        <input type="hidden" name="tipo_usuario" value="{{ $tipoUsuario }}">
                        <input type="hidden" name="espacio" value="{{ $espacio }}">
                        <button type="submit"
                            class="px-4 py-2 text-white transition-colors bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <i class="mr-2 fas fa-file-pdf"></i>Exportar PDF
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg dark:bg-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Usuario
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Espacio
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Fecha
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Hora entrada
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Hora salida
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Duración
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Tipo usuario
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse($accesos as $acceso)
                                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
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
                                                            {{ $acceso['espacio'] }} - {{ $acceso['id_espacio'] }} - Piso
                                                            {{ $acceso['piso'] }}
                                                        </div>
                                                        <div class="text-xs text-gray-400 dark:text-gray-500">
                                                            {{ $acceso['facultad'] }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ $acceso['fecha'] }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ $acceso['hora_entrada'] }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span
                                                        class="text-sm {{ $acceso['hora_salida'] == 'En curso' ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                                                        {{ $acceso['hora_salida'] }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span
                                                        class="text-sm {{ $acceso['duracion'] == 'En curso' ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-gray-900 dark:text-white' }}">
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
                                        <i class="mb-2 text-4xl fas fa-search"></i>
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
                <div class="flex items-center justify-between mt-4">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando {{ $accesos->count() }} de {{ $accesos->count() }} registros
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
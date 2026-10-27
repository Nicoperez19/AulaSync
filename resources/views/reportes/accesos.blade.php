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

    <div class="px-4 pb-8 space-y-4">
        <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-4">
            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <i class="text-blue-600 fas fa-users dark:text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total de accesos</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $accesos->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
                        <i class="text-green-600 fas fa-user-check dark:text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Usuarios únicos</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $accesos->unique('run')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                        <i class="text-yellow-600 fas fa-building dark:text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Espacios utilizados</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $accesos->unique('espacio')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded-lg shadow-md dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-900">
                        <i class="text-purple-600 fas fa-clock dark:text-purple-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">En curso</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $accesos->where('hora_salida', 'En curso')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sección FILTROS -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-filter text-[#950101]"></i> FILTROS
                </h3>
            </div>

            <form method="GET" action="{{ route('reportes.accesos') }}" id="filtroAccesosForm">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <!-- Semestre -->
                    <div>
                        <label for="periodo_filtro" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Semestre</label>
                        <select name="periodo" 
                                id="periodo_filtro"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white dark:bg-gray-700 dark:text-white">
                            @foreach($periodosDisponibles as $p)
                                <option value="{{ $p->codigo }}" {{ ($periodo ?? '') == $p->codigo ? 'selected' : '' }}>
                                    {{ $p->display_label }}
                                </option>
                            @endforeach
                            <option value="" {{ empty($periodo) ? 'selected' : '' }}>Todos los semestres</option>
                        </select>
                    </div>

                    <!-- Buscar -->
                    <div>
                        <label for="search" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Buscar</label>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search ?? '' }}"
                                   id="search"
                                   class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] dark:bg-gray-700 dark:text-white"
                                   placeholder="Profesor, asignatura, RUN o espacio...">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        </div>
                    </div>

                    <!-- Unidad Académica (UA) -->
                    <div>
                        <label for="ua_filtro" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Unidad Académica</label>
                        <select name="ua" 
                                id="ua_filtro"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white dark:bg-gray-700 dark:text-white">
                            <option value="">Todas las UAs</option>
                            @if(isset($unidadesAcademicas) && count($unidadesAcademicas) > 0)
                                @foreach($unidadesAcademicas as $u)
                                    <option value="{{ $u->id_carrera }}" {{ ($ua ?? '') == $u->id_carrera ? 'selected' : '' }}>
                                        {{ $u->id_carrera }} - {{ $u->nombre }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="estado" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Estado</label>
                        <select name="estado" 
                                id="estado"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] bg-white dark:bg-gray-700 dark:text-white">
                            <option value="">Todos</option>
                            <option value="activa" {{ ($estado ?? '') == 'activa' ? 'selected' : '' }}>En curso (Activa)</option>
                            <option value="finalizada" {{ ($estado ?? '') == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                        </select>
                    </div>

                    <!-- Desde -->
                    <div>
                        <label for="fecha_inicio" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Desde</label>
                        <input type="date" 
                               name="fecha_inicio" 
                               value="{{ $fechaInicio }}"
                               id="fecha_inicio"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Hasta -->
                    <div>
                        <label for="fecha_fin" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Hasta</label>
                        <input type="date" 
                               name="fecha_fin" 
                               value="{{ $fechaFin }}"
                               id="fecha_fin"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-[#D12420]/20 focus:border-[#D12420] dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('reportes.accesos.limpiar') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-undo text-xs"></i> Limpiar
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-semibold text-white bg-[#191919] hover:bg-black active:bg-gray-900 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-filter text-xs"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>


        <!-- Tabla de accesos -->
        <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">
                    Registro de accesos ({{ $accesos->count() }} registros)
                </h3>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('reportes.accesos.export.filtros', ['format' => 'excel']) }}"
                        class="inline">
                        @csrf
                        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                        <input type="hidden" name="periodo" value="{{ $periodo }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="ua" value="{{ $ua }}">
                        <input type="hidden" name="estado" value="{{ $estado }}">
                        <input type="hidden" name="piso" value="{{ $piso }}">
                        <input type="hidden" name="tipo_usuario" value="{{ $tipoUsuario }}">
                        <input type="hidden" name="espacio" value="{{ $espacio }}">
                        <button type="submit"
                            class="px-3 py-1.5 text-xs text-white transition-colors bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="mr-1 fas fa-file-excel"></i>Excel
                        </button>
                    </form>
                    <form method="POST" action="{{ route('reportes.accesos.export.filtros', ['format' => 'pdf']) }}"
                        class="inline">
                        @csrf
                        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                        <input type="hidden" name="periodo" value="{{ $periodo }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="ua" value="{{ $ua }}">
                        <input type="hidden" name="estado" value="{{ $estado }}">
                        <input type="hidden" name="piso" value="{{ $piso }}">
                        <input type="hidden" name="tipo_usuario" value="{{ $tipoUsuario }}">
                        <input type="hidden" name="espacio" value="{{ $espacio }}">
                        <button type="submit"
                            class="px-3 py-1.5 text-xs text-white transition-colors bg-[#D12420] rounded-md hover:bg-[#950101] focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <i class="mr-1 fas fa-file-pdf"></i>PDF
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs bg-white rounded-lg dark:bg-gray-800 table-auto">
                    <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                        <tr>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                Usuario
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                UA
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                Asignatura
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                ID Sala
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                Fecha
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                Entrada
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                Salida
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                Duración
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                Tipo
                            </th>
                            <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-white uppercase dark:text-gray-300">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse($accesos as $acceso)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-2 py-2">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white">
                                        {{ $acceso['usuario'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $acceso['run'] }}
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-xs text-gray-800 dark:text-gray-200">
                                    {{ $acceso['ua'] ?? 'N/A' }}
                                </td>
                                <td class="px-2 py-2 text-xs text-gray-800 dark:text-gray-200">
                                    {{ $acceso['asignatura'] ?? 'N/A' }}
                                </td>
                                <td class="px-2 py-2">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white">
                                        {{ $acceso['id_espacio'] }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        Piso {{ $acceso['piso'] }}
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-xs text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($acceso['fecha'])->format('d/m/Y') }}
                                </td>
                                <td class="px-2 py-2 text-xs text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $acceso['hora_entrada'] }}
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <span class="text-xs {{ $acceso['hora_salida'] == 'En curso' ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                                        {{ $acceso['hora_salida'] }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <span class="text-xs {{ $acceso['duracion'] == 'En curso' ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                                        {{ $acceso['duracion'] }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full whitespace-nowrap
                                        {{ $acceso['tipo_usuario'] == 'profesor' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                           ($acceso['tipo_usuario'] == 'estudiante' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                           ($acceso['tipo_usuario'] == 'administrativo' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' :
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200')) }}">
                                        {{ ucfirst($acceso['tipo_usuario']) }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full whitespace-nowrap
                                        {{ $acceso['estado'] == 'activa' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                           ($acceso['estado'] == 'finalizada' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' :
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                        {{ ucfirst($acceso['estado']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-2 py-4 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <i class="mb-2 text-2xl fas fa-search"></i>
                                        <p class="text-sm font-medium">No se encontraron accesos registrados</p>
                                        <p class="text-xs">Intenta ajustar los filtros de búsqueda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($accesos->count() > 0)
                <div class="flex items-center justify-between mt-3">
                    <div class="text-xs text-gray-700 dark:text-gray-300">
                        Mostrando {{ $accesos->count() }} de {{ $accesos->count() }} registros
                    </div>
                </div>
            @endif
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const periodosLimites = @json($periodosLimites ?? []);
            const periodoSelect = document.getElementById('periodo_filtro');
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');

            if (periodoSelect && fechaInicio && fechaFin) {
                periodoSelect.addEventListener('change', function () {
                    const val = this.value;
                    if (val && periodosLimites[val]) {
                        fechaInicio.value = periodosLimites[val].inicio;
                        fechaFin.value = periodosLimites[val].fin;
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-black">Horarios</h2>
                    <p class="text-base text-gray-500">Directorio de profesores</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="px-6 min-h-[80vh]">
        <!-- Tarjeta de filtros -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 dark:text-gray-200">
                    <i class="fas fa-filter text-[#950101]"></i> FILTROS
                </h3>
            </div>

            <form id="filtro-letra-form" method="GET" action="" class="flex flex-col gap-4" onsubmit="return false;">
                <!-- Fila 1: Filtros principales (Año, Semestre y Búsqueda) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                    <!-- Año -->
                    <div class="sm:col-span-1 lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Año</label>
                        <div class="w-full px-3 py-2 text-sm font-semibold text-center text-gray-700 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            {{ \App\Helpers\SemesterHelper::getCurrentAcademicYear() }}
                        </div>
                    </div>

                    <!-- Semestre -->
                    <div class="sm:col-span-1 lg:col-span-3">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Semestre</label>
                        <select name="semestre" id="semestre-filtro"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @foreach($semestresDisponibles ?? [] as $semestre)
                                <option value="{{ $semestre }}" {{ request('semestre', \App\Helpers\SemesterHelper::getCurrentSemester()) == $semestre ? 'selected' : '' }}>
                                    {{ $semestre }}° Semestre
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Búsqueda rápida por texto -->
                    <div class="col-span-1 sm:col-span-2 lg:col-span-7">
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Buscar profesor</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" name="search" id="search-profesor" value="{{ request('search') }}"
                                placeholder="Buscar por nombre, apellido o RUN..."
                                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white placeholder:text-gray-400" />
                        </div>
                    </div>
                </div>


                <!-- Botones de Acción separados por línea -->
                <div class="flex items-center justify-end gap-2 mt-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button id="limpiar-filtro-btn" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 cursor-pointer">
                        <i class="fas fa-undo text-xs"></i> Limpiar
                    </button>
                    <button id="aplicar-filtro-btn" type="button"
                        class="px-4 py-2 text-sm font-semibold text-white bg-[#191919] hover:bg-black active:bg-gray-900 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-filter text-xs"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
        <!-- Fin tarjeta de filtros -->

        <div id="profesores-lista">
            @php
                $anioActual = \App\Helpers\SemesterHelper::getCurrentAcademicYear();
                $semestre = \App\Helpers\SemesterHelper::getCurrentSemester();
                $periodoActual = \App\Helpers\SemesterHelper::getCurrentPeriod();

                // Usar el año actual y el semestre seleccionado o el actual
                $anioMostrar = $anioActual;
                $semestreMostrar = request('semestre', $semestre);
            @endphp
            <div class="mb-4">
                <p class="text-sm text-gray-500">{{ $profesores->total() }} profesores encontrados</p>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6">
                @foreach ($profesores as $profesor)
                                    <div class="flex flex-col gap-3 p-5 transition-all duration-300 ease-in-out bg-white border border-gray-200 shadow-sm cursor-pointer rounded-xl hover:shadow-2xl hover:scale-105 hover:-translate-y-2 hover:border-light-cloud-blue profesor-card"
                        data-run="{{ $profesor->run_profesor }}">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center p-3 bg-gray-100 rounded-full">
                                <i class="text-2xl text-gray-400 fa-solid fa-user"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-base font-bold text-gray-900 uppercase">{{ $profesor->name }}</span>
                            </div>
                        </div>


                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $profesores->appends(request()->query())->links() }}
            </div>
        </div>

        {{-- Modal --}}
        <div id="horarioModal"
            class="fixed inset-0 z-[150] flex items-center justify-center hidden bg-black bg-opacity-50">
            <div
                class="flex flex-col w-full max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg max-w-7xl md:mx-8 md:flex-row">
                <!-- Columna izquierda: Información personal -->
                <div
                    class="flex flex-col items-center justify-center w-full p-6 border-b border-red-100 bg-red-50 md:w-1/5 md:border-b-0 md:border-r">
                    <div class="p-4 mb-4 bg-red-100 rounded-full">
                        <i class="text-3xl text-red-600 fa-solid fa-user"></i>
                    </div>
                    <h3 class="mb-2 text-base font-bold text-center text-black" id="modalNombreProfesor">Profesor</h3>
                    <p class="mb-1 text-xs text-center text-gray-700" id="modalCorreoProfesor">Correo: </p>
                    <p class="mb-1 text-xs text-center text-gray-600" id="modalPeriodo">
                        <i class="mr-1 fa-solid fa-calendar-days"></i>
                        Período: <span id="modalSemestre">{{ $semestreMostrar }}</span>° Semestre <span
                            id="modalAnio">{{ $anioMostrar }}</span>
                    </p>
                </div>
                <!-- Columna derecha: Horario -->
                <div class="flex flex-col flex-1 bg-white">
                    <!-- Encabezado colorido -->
                    <div class="relative flex items-center justify-between p-8 bg-red-700">
                        <h3 class="flex items-center gap-2 text-2xl font-bold text-white" id="modalTitle">
                            <i class="text-white fa-solid fa-calendar-days"></i> Horario del Profesor
                        </h3>
                        <button onclick="cerrarModal()"
                            class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                        <!-- Círculos decorativos -->
                        <span
                            class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                        <span
                            class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                    </div>
                    <div class="overflow-y-auto max-h-[70vh] p-6">
                        <table class="min-w-full overflow-hidden bg-white rounded-lg">
                            <thead class="sticky top-0 z-10 bg-gray-100 shadow">
                                <tr>
                                    <th class="px-4 py-3 font-semibold text-center border-b">Hora</th>
                                    <th class="px-4 py-3 font-semibold text-center border-b">Lunes</th>
                                    <th class="px-4 py-3 font-semibold text-center border-b">Martes</th>
                                    <th class="px-4 py-3 font-semibold text-center border-b">Miércoles</th>
                                    <th class="px-4 py-3 font-semibold text-center border-b">Jueves</th>
                                    <th class="px-4 py-3 font-semibold text-center border-b">Viernes</th>
                                    <th class="px-4 py-3 font-semibold text-center border-b">Sábado</th>
                                </tr>
                            </thead>
                            <tbody id="horarioBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spinner -->
        <div id="profesores-spinner"
            class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-10">
            <div class="flex flex-col items-center">
                <svg class="w-10 h-10 mb-2 text-black animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-sm font-semibold text-black">Cargando...</span>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <div data-page-config="HorariosProfesoresConfig" data-config="{{ json_encode(['currentYear' => \App\Helpers\SemesterHelper::getCurrentAcademicYear(), 'currentSemester' => \App\Helpers\SemesterHelper::getCurrentSemester()]) }}" class="hidden"></div>
    @vite(['resources/js/views/horarios/profesores.js'])
</x-app-layout>

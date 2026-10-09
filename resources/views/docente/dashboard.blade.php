<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue text-white shadow-md">
                    <i class="text-2xl fa-solid fa-graduation-cap"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                     Asignaturas
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Docente: <strong class="text-gray-700 dark:text-gray-200">{{ $docente?->name ?? Auth::user()->name }}</strong> &bull; RUN: {{ $docenteRun }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 text-right shadow-sm">
                    <div class="text-xs text-gray-400 font-medium">Total de Clases</div>
                    <div class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $clases->count() }}</div>
                </div>

                @if($esSuperadmin)
                    <a href="{{ route('docente.supervision') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                        <i class="fa-solid fa-users"></i>
                        <span>Cambiar Docente</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Banner de Superadmin Simulando --}}
        @if($esSuperadmin)
            <div class="p-4 rounded-lg bg-amber-50 border-l-4 border-amber-500 text-amber-900 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm font-medium">
                    <i class="fa-solid fa-user-secret text-base text-amber-600"></i>
                    <span><strong>Modo Supervisión:</strong> Estás navegando como <strong>{{ $docente?->name ?? $docenteRun }}</strong>. Puedes tomar asistencia en modo prueba sin necesidad de escanear el QR físico.</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('docente.supervision') }}" class="px-3 py-1 rounded bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition">
                        Cambiar Docente
                    </a>
                    <form method="POST" action="{{ route('docente.supervision.restablecer') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold transition">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        @endif



        @if(session('error'))
            <div class="p-4 rounded-lg bg-rose-50 border-l-4 border-rose-500 text-rose-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        {{-- Contenedor de Clases --}}
        @if($clases->isEmpty())
            <div class="p-12 bg-white dark:bg-dark-eval-1 rounded-lg shadow-lg text-center">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-light-cloud-blue flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">No tienes asignaturas registradas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto mt-1">
                    Actualmente no registras asignaturas ni colaboraciones activas para esta sede y período académico. Si crees que es un error, por favor contacta a la coordinación académica.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($clases as $clase)
                    <div class="bg-white dark:bg-dark-eval-1 rounded-lg border border-gray-200 dark:border-gray-700 shadow-md hover:shadow-lg transition-shadow flex flex-col justify-between overflow-hidden">
                        {{-- Cabecera de la tarjeta --}}
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <span class="font-mono text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-dark-eval-2 px-2.5 py-1 rounded-md">
                                    {{ $clase['codigo'] }} @if($clase['seccion'] !== 'N/A') &bull; Sec {{ $clase['seccion'] }} @endif
                                </span>

                                {{-- Badge de Rol --}}
                                @if($clase['rol'] === 'titular')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-award mr-1"></i> Titular
                                    </span>
                                @elseif($clase['rol'] === 'reemplazo')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-repeat mr-1"></i> Reemplazo
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                        <i class="fa-solid fa-handshake mr-1"></i> Colaborador
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 hover:text-light-cloud-blue transition leading-snug">
                                {{ $clase['nombre'] }}
                            </h3>

                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5 flex-wrap">
                                <i class="fa-solid fa-building-columns text-gray-400"></i>
                                <span>{{ $clase['carrera'] }}</span>
                                @if(!empty($clase['id_carrera']))
                                    <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-dark-eval-2 text-gray-600 dark:text-gray-300">
                                        UA: {{ $clase['id_carrera'] }}
                                    </span>
                                @endif
                            </p>

                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2 text-xs">
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                    <span class="text-gray-400">Clase de hoy:</span>
                                    @if($clase['tiene_clase_hoy'])
                                        <span class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-1">
                                            <i class="fa-regular fa-clock text-light-cloud-blue"></i>
                                            {{ $clase['modulo_planificado'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">Sin horario programado hoy</span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                    <span class="text-gray-400">Espacio asignado:</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $clase['espacio_planificado'] ?? 'No asignado' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                    <span class="text-gray-400">Estudiantes:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-dark-eval-2 px-2 py-0.5 rounded">
                                        {{ $clase['total_inscritos'] }} inscritos
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Footer de Acción --}}
                        <div class="p-4 bg-gray-50 dark:bg-dark-eval-2 border-t border-gray-100 dark:border-gray-700">
                            @if($clase['en_sala'])
                                <div class="flex items-center gap-2 text-xs text-emerald-700 dark:text-emerald-400 font-semibold mb-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>En sala: <strong>{{ $clase['espacio_actual'] }}</strong></span>
                                </div>
                            @endif

                            <x-button href="{{ route('docente.asistencia.show', $clase['id']) }}" 
                                      variant="ucsc" 
                                      class="w-full justify-center gap-2 shadow-sm font-semibold">
                                <i class="fa-solid fa-list-check"></i>
                                <span>Pasar Asistencia</span>
                            </x-button>


                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-amber-500 text-white shadow-md">
                    <i class="text-2xl fa-solid fa-user-shield"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                        Supervisión del Portal Docente
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Selecciona un docente para ingresar a su portal y probar la toma de asistencia en modo prueba.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('docente.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 dark:bg-dark-eval-1 dark:hover:bg-dark-eval-2 border border-slate-200 hover:border-slate-300 dark:border-gray-600 text-slate-700 dark:text-gray-200 text-sm font-semibold rounded-xl shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    <svg class="w-4 h-4 shrink-0 text-slate-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Ir al Portal Docente</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Buscador y Filtros --}}
        <div class="p-4 bg-white dark:bg-dark-eval-1 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('docente.supervision') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar docente por nombre, RUN o correo..." 
                           class="w-full pl-10 pr-4 py-2 text-xs rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-light-cloud-blue outline-none">
                </div>
                <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-md bg-gray-800 hover:bg-gray-900 text-white transition">
                    Filtrar
                </button>
                @if($search)
                    <a href="{{ route('docente.supervision') }}" class="px-4 py-2 text-xs font-semibold rounded-md bg-gray-100 hover:bg-gray-200 text-gray-600 text-center transition">
                        Limpiar
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabla de Docentes --}}
        <div class="bg-white dark:bg-dark-eval-1 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-dark-eval-2 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4">RUN</th>
                            <th class="py-3 px-4">Docente</th>
                            <th class="py-3 px-4">Correo</th>
                            <th class="py-3 px-4 text-center">Asignaturas</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($profesores as $prof)
                            @php $esActual = ($docenteSimuladoRun === (string)$prof->run_profesor); @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-dark-eval-2 transition {{ $esActual ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}">
                                <td class="py-3 px-4 font-mono text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $prof->run_profesor }}
                                </td>
                                <td class="py-3 px-4 font-bold text-gray-900 dark:text-gray-100">
                                    {{ $prof->name }}
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $prof->email ?? 'Sin correo' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                                        {{ $prof->asignaturas_count }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($esActual)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-200 text-amber-900 animate-pulse">
                                            Seleccionado
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Inactivo</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('docente.supervision.seleccionar', $prof->run_profesor) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-md text-xs font-semibold {{ $esActual ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-gray-100 dark:bg-dark-eval-2 hover:bg-blue-50 hover:text-light-cloud-blue text-gray-700 dark:text-gray-200' }} transition">
                                            {{ $esActual ? 'Supervisando (Ver)' : 'Ver como este docente' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-xs text-gray-400">
                                    No se encontraron profesores registrados en esta sede.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($profesores->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $profesores->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

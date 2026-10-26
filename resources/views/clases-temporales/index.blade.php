<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Profesores Colaboradores</h2>
                    <p class="text-sm text-gray-500">Gestión de asignaturas temporales y profesores colaboradores</p>
                </div>
            </div>
            <div>
                <x-button href="{{ route('clases-temporales.create') }}" variant="add">
                    <i class="mr-2 fa-solid fa-plus"></i>
                    <span>Nueva Asignatura Temporal</span>
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="w-full pb-6">
        <!-- Mensajes de éxito/error -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Sección FILTROS -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 dark:text-gray-200">
                        <i class="fas fa-filter text-[#950101]"></i> FILTROS
                    </h3>
                </div>

                <form method="GET" action="{{ route('clases-temporales.index') }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <!-- Buscar -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Buscar</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Profesor, asignatura o RUN..."
                                       class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Estado</label>
                            <select name="estado" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <!-- Vigencia -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 dark:text-gray-300">Vigencia</label>
                            <select name="vigencia" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#950101]/20 focus:border-[#950101] bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Todas las vigencias</option>
                                <option value="vigentes" {{ request('vigencia') === 'vigentes' ? 'selected' : '' }}>Vigentes</option>
                                <option value="vencidos" {{ request('vigencia') === 'vencidos' ? 'selected' : '' }}>Vencidos</option>
                            </select>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('clases-temporales.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors inline-flex items-center gap-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                            <i class="fas fa-undo text-xs"></i> Limpiar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-semibold text-white bg-[#191919] hover:bg-black active:bg-gray-900 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                            <i class="fas fa-filter text-xs"></i> Aplicar filtros
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-[#950101] text-white border-b border-[#7B0101]">
                            <tr>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Profesor</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Asignatura</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Horarios</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider min-w-[240px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($profesoresColaboradores as $pc)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ mb_strtoupper(mb_substr($pc->profesor->name ?? 'NA', 0, 2, 'UTF-8'), 'UTF-8') }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $pc->profesor->name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ $pc->run_profesor_colaborador }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $pc->nombre_asignatura }}</div>
                                        @if($pc->asignatura)
                                            <div class="text-xs text-gray-500">Código: {{ $pc->asignatura->codigo_asignatura }}</div>
                                        @else
                                            <div class="text-xs text-purple-600"><i class="fa-solid fa-star mr-1"></i>Temporal</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <div><i class="fa-solid fa-calendar-day mr-1 text-green-600"></i>{{ $pc->fecha_inicio->format('d/m/Y') }}</div>
                                            <div><i class="fa-solid fa-calendar-xmark mr-1 text-red-600"></i>{{ $pc->fecha_termino->format('d/m/Y') }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">
                                            {{ $pc->planificaciones->count() }} horario(s) asignado(s)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            @if($pc->estado === 'activo')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                    <i class="fa-solid fa-check-circle mr-1"></i>Activo
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                                    <i class="fa-solid fa-times-circle mr-1"></i>Inactivo
                                                </span>
                                            @endif
                                            
                                            @if($pc->estaVigente())
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                                    <i class="fa-solid fa-calendar-check mr-1"></i>Vigente
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">
                                                    <i class="fa-solid fa-calendar-times mr-1"></i>Vencido
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-medium">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('clases-temporales.show', $pc) }}" 
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg border border-blue-200 transition-colors shadow-xs" title="Ver detalles">
                                                <i class="fa-solid fa-eye"></i>
                                                <span>Ver</span>
                                            </a>
                                            <a href="{{ route('clases-temporales.edit', $pc) }}" 
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-[#950101] rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors shadow-xs" title="Editar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                <span>Editar</span>
                                            </a>
                                            <form action="{{ route('clases-temporales.destroy', $pc) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este profesor colaborador?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#D12420] bg-red-50 hover:bg-[#D12420] hover:text-white rounded-lg border border-[#EDB9B9] transition-colors shadow-xs" title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i>
                                                    <span>Eliminar</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300"></i>
                                        <p class="text-lg font-medium">No hay profesores colaboradores registrados</p>
                                        <p class="text-sm">Comienza creando uno nuevo usando el botón superior</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($profesoresColaboradores->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $profesoresColaboradores->links() }}
                    </div>
                @endif
            </div>
    </div>
</x-app-layout>

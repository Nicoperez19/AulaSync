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
                <a href="{{ route('clases-temporales.create') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors duration-150 border border-transparent rounded-lg bg-light-cloud-blue hover:bg-[#b10718] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="mr-2 fa-solid fa-plus"></i>
                    Nueva Asignatura Temporal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
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

            <!-- Filtros -->
            <div class="mb-6 p-6 bg-white rounded-lg shadow">
                <form method="GET" action="{{ route('clases-temporales.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Nombre profesor o asignatura..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vigencia</label>
                        <select name="vigencia" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            <option value="vigentes" {{ request('vigencia') === 'vigentes' ? 'selected' : '' }}>Vigentes</option>
                            <option value="vencidos" {{ request('vigencia') === 'vencidos' ? 'selected' : '' }}>Vencidos</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                            <i class="fa-solid fa-filter mr-2"></i>Filtrar
                        </button>
                        <a href="{{ route('clases-temporales.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                            <i class="fa-solid fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horarios</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($profesoresColaboradores as $pc)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ substr($pc->profesor->name ?? 'N/A', 0, 2) }}
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
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('clases-temporales.show', $pc) }}" 
                                               class="text-indigo-600 hover:text-indigo-900" title="Ver detalles">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('clases-temporales.edit', $pc) }}" 
                                               class="text-blue-600 hover:text-blue-900" title="Editar">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <form action="{{ route('clases-temporales.destroy', $pc) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este profesor colaborador?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i>
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
    </div>
</x-app-layout>

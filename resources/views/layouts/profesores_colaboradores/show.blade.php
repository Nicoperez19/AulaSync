<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600">
                    <i class="text-2xl text-white fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Detalles Profesor Colaborador</h2>
                    <p class="text-sm text-gray-500">Información completa de la asignatura temporal</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('profesores-colaboradores.edit', $profesorColaborador) }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    <i class="mr-2 fa-solid fa-edit"></i>Editar
                </a>
                <a href="{{ route('profesores-colaboradores.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="mr-2 fa-solid fa-arrow-left"></i>Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Información General -->
            <div class="mb-6 bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fa-solid fa-info-circle mr-2"></i>Información General
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Profesor</label>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                    {{ substr($profesorColaborador->profesor->name ?? 'N/A', 0, 2) }}
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-gray-900">{{ $profesorColaborador->profesor->name ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500">RUN: {{ $profesorColaborador->run_profesor_colaborador }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Asignatura</label>
                            <p class="text-base font-semibold text-gray-900">{{ $profesorColaborador->nombre_asignatura }}</p>
                            @if($profesorColaborador->asignatura)
                                <p class="text-sm text-gray-500">Código: {{ $profesorColaborador->asignatura->codigo_asignatura }}</p>
                            @else
                                <p class="text-sm text-purple-600"><i class="fa-solid fa-star mr-1"></i>Asignatura Temporal</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Período de Vigencia</label>
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2 text-sm">
                                    <i class="fa-solid fa-calendar-day text-green-600"></i>
                                    <span class="font-medium">Inicio:</span>
                                    <span class="text-gray-700">{{ $profesorColaborador->fecha_inicio->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <i class="fa-solid fa-calendar-xmark text-red-600"></i>
                                    <span class="font-medium">Término:</span>
                                    <span class="text-gray-700">{{ $profesorColaborador->fecha_termino->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
                            <div class="flex gap-2">
                                @if($profesorColaborador->estado === 'activo')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold text-green-800 bg-green-100 rounded-full">
                                        <i class="fa-solid fa-check-circle mr-1"></i>Activo
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold text-red-800 bg-red-100 rounded-full">
                                        <i class="fa-solid fa-times-circle mr-1"></i>Inactivo
                                    </span>
                                @endif
                                
                                @if($profesorColaborador->estaVigente())
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold text-blue-800 bg-blue-100 rounded-full">
                                        <i class="fa-solid fa-calendar-check mr-1"></i>Vigente
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold text-gray-800 bg-gray-100 rounded-full">
                                        <i class="fa-solid fa-calendar-times mr-1"></i>Vencido
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($profesorColaborador->descripcion)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Descripción</label>
                            <p class="text-sm text-gray-700 p-3 bg-gray-50 rounded border border-gray-200">
                                {{ $profesorColaborador->descripcion }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Horarios Asignados -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fa-solid fa-calendar-alt mr-2"></i>Horarios Asignados ({{ $profesorColaborador->planificaciones->count() }})
                    </h3>
                </div>
                <div class="p-6">
                    @if($profesorColaborador->planificaciones->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <i class="fa-solid fa-calendar-xmark text-4xl mb-3 text-gray-300"></i>
                            <p>No hay horarios asignados</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Día</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espacio</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Piso</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        $diasMap = [
                                            'lunes' => 'Lunes',
                                            'martes' => 'Martes',
                                            'miercoles' => 'Miércoles',
                                            'jueves' => 'Jueves',
                                            'viernes' => 'Viernes',
                                            'sabado' => 'Sábado'
                                        ];
                                    @endphp
                                    @foreach($profesorColaborador->planificaciones->sortBy(function($p) {
                                        $parts = explode('.', $p->id_modulo);
                                        $diaOrden = ['LU' => 1, 'MA' => 2, 'MI' => 3, 'JU' => 4, 'VI' => 5, 'SA' => 6];
                                        return ($diaOrden[$parts[0]] ?? 0) * 100 + (int)($parts[1] ?? 0);
                                    }) as $plan)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    <i class="fa-solid fa-calendar-day mr-1"></i>
                                                    {{ $diasMap[$plan->modulo->dia] ?? $plan->modulo->dia }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $plan->id_modulo }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                <i class="fa-solid fa-clock mr-1 text-gray-400"></i>
                                                {{ substr($plan->modulo->hora_inicio, 0, 5) }} - {{ substr($plan->modulo->hora_termino, 0, 5) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-solid fa-door-open text-indigo-500"></i>
                                                    <span class="text-sm font-medium text-gray-900">{{ $plan->espacio->nombre }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $plan->espacio->piso->nombre ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

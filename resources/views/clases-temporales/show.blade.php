<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl" style="background-color: #cd1627;">
                    <i class="text-2xl text-white fa-solid fa-book-open"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">{{ $profesorColaborador->nombre_asignatura_temporal }}</h2>
                    <p class="text-sm text-gray-500">Detalles de la clase temporal</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('clases-temporales.edit', $profesorColaborador) }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90"
                   style="background-color: #cd1627;">
                    <i class="mr-2 fa-solid fa-edit"></i>Editar
                </a>
                <a href="{{ route('clases-temporales.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="mr-2 fa-solid fa-arrow-left"></i>Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna izquierda: Información -->
                <div class="lg:col-span-2">
                    <!-- Datos Básicos -->
                    <div class="mb-6 bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <h3 class="text-lg font-semibold text-white">
                                <i class="fa-solid fa-file-alt mr-2"></i>Datos Básicos
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Nombre de la Asignatura</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $profesorColaborador->nombre_asignatura_temporal }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Cantidad de Inscritos</label>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-users text-blue-600 text-lg"></i>
                                        <p class="text-lg font-semibold text-gray-900">{{ $profesorColaborador->cantidad_inscritos }} estudiantes</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Fecha de Inicio</label>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-day text-green-600"></i>
                                        <p class="text-gray-900">{{ $profesorColaborador->fecha_inicio->format('d/m/Y') }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Fecha de Término</label>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-xmark text-red-600"></i>
                                        <p class="text-gray-900">{{ $profesorColaborador->fecha_termino->format('d/m/Y') }}</p>
                                    </div>
                                </div>

                                @if($profesorColaborador->descripcion)
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Descripción</label>
                                        <p class="text-gray-700 leading-relaxed">{{ $profesorColaborador->descripcion }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Profesor -->
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <h3 class="text-lg font-semibold text-white">
                                <i class="fa-solid fa-user mr-2"></i>Profesor
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 h-16 w-16 rounded-full flex items-center justify-center text-white font-bold text-xl"
                                     style="background-color: #cd1627;">
                                    {{ strtoupper(substr($profesorColaborador->profesor->name ?? '?', 0, 2)) }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-lg font-semibold text-gray-900">{{ $profesorColaborador->profesor->name ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">RUN:</span> {{ $profesorColaborador->run_profesor_colaborador }}
                                    </p>
                                    @if($profesorColaborador->profesor?->email)
                                        <p class="text-sm text-gray-600">
                                            <i class="fa-solid fa-envelope mr-1" style="color: #cd1627;"></i>
                                            {{ $profesorColaborador->profesor->email }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Horarios Agrupados por Día -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <h3 class="text-lg font-semibold text-white">
                                <i class="fa-solid fa-calendar-alt mr-2"></i>Horarios de Clase
                            </h3>
                        </div>
                        <div class="p-6">
                            @php
                                // Agrupar planificaciones por día
                                $planificacionesPorDia = [];
                                foreach ($profesorColaborador->planificaciones as $plan) {
                                    $dia = $plan->modulo->dia;
                                    if (!isset($planificacionesPorDia[$dia])) {
                                        $planificacionesPorDia[$dia] = [];
                                    }
                                    $planificacionesPorDia[$dia][] = $plan;
                                }

                                // Ordenar por día
                                $diasOrden = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                                $planificacionesOrdenadas = [];
                                foreach ($diasOrden as $dia) {
                                    if (isset($planificacionesPorDia[$dia])) {
                                        $planificacionesOrdenadas[$dia] = $planificacionesPorDia[$dia];
                                    }
                                }
                            @endphp

                            <div class="space-y-4">
                                @forelse($planificacionesOrdenadas as $dia => $planificaciones)
                                    @php
                                        // Ordenar por hora
                                        usort($planificaciones, function($a, $b) {
                                            return strtotime($a->modulo->hora_inicio) - strtotime($b->modulo->hora_inicio);
                                        });

                                        $horaInicio = $planificaciones[0]->modulo->hora_inicio;
                                        $horaFin = $planificaciones[count($planificaciones)-1]->modulo->hora_termino;
                                        $modulosInfo = collect($planificaciones)->pluck('id_modulo')->map(fn($id) => 
                                            intval(explode('.', $id)[1])
                                        )->implode(', ');

                                        $diaTexto = ucfirst(str_replace('miercoles', 'Miércoles', $dia));
                                    @endphp
                                    <div class="border-l-4 pl-4 py-3" style="border-color: #cd1627; background-color: #fafafa;">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-bold text-gray-900 mb-1">
                                                    {{ $diaTexto }} de {{ date('H:i', strtotime($horaInicio)) }} a {{ date('H:i', strtotime($horaFin)) }}hrs
                                                </h4>
                                                <p class="text-xs text-gray-600 mb-2">Módulos: {{ $modulosInfo }}</p>
                                                <div class="space-y-1">
                                                    @foreach($planificaciones as $plan)
                                                        <div class="flex items-center gap-2 text-xs">
                                                            <i class="fa-solid fa-door-open text-blue-600"></i>
                                                            <span class="font-medium text-gray-700">{{ $plan->espacio->nombre_espacio }}</span>
                                                            <span class="text-gray-500">(Piso {{ $plan->espacio->piso?->numero_piso }})</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                                                      style="background-color: rgba(205, 22, 39, 0.1); color: #cd1627;">
                                                    {{ count($planificaciones) }} módulo{{ count($planificaciones) > 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <i class="fa-solid fa-calendar-xmark text-3xl text-gray-300 mb-2"></i>
                                        <p class="text-gray-500">No hay horarios asignados</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Estado -->
                <div class="lg:col-span-1">
                    <!-- Estado -->
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <h3 class="text-lg font-semibold text-white">
                                <i class="fa-solid fa-status mr-2"></i>Estado
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-3">Disponibilidad</label>
                                    @if($profesorColaborador->estado === 'activo')
                                        <div class="flex items-center gap-3 p-3 rounded-lg bg-green-50 border border-green-200">
                                            <i class="fa-solid fa-check-circle text-green-600 text-xl"></i>
                                            <span class="font-semibold text-green-700">Activo</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-3 p-3 rounded-lg bg-red-50 border border-red-200">
                                            <i class="fa-solid fa-times-circle text-red-600 text-xl"></i>
                                            <span class="font-semibold text-red-700">Inactivo</span>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-3">Vigencia</label>
                                    @if($profesorColaborador->estaVigente())
                                        <div class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 border border-blue-200">
                                            <i class="fa-solid fa-calendar-check text-blue-600 text-xl"></i>
                                            <span class="font-semibold text-blue-700">Vigente</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 border border-gray-200">
                                            <i class="fa-solid fa-calendar-times text-gray-600 text-xl"></i>
                                            <span class="font-semibold text-gray-700">Vencido</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Registro -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4" style="background-color: #cd1627;">
                            <h3 class="text-lg font-semibold text-white">
                                <i class="fa-solid fa-info-circle mr-2"></i>Información del Registro
                            </h3>
                        </div>
                        <div class="p-6 space-y-3 text-sm">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Creado</label>
                                <p class="text-gray-700">{{ $profesorColaborador->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Actualizado</label>
                                <p class="text-gray-700">{{ $profesorColaborador->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

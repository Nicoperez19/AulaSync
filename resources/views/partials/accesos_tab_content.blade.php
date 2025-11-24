<div class="flex flex-col gap-6 md:flex-row">
    <!-- Reservas Pendientes -->
    <div class="w-full p-8 bg-white shadow-lg rounded-xl md:w-1/2">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 text-orange-600 bg-orange-100 rounded-full">
                    <i class="fas fa-exclamation-triangle"></i>
                </span>
                <h3 class="text-lg font-bold text-gray-700">Reservas Activas Pendientes</h3>
            </div>
            <span class="px-3 py-1 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full">
                {{ $reservasSinDevolucion->count() }} pendiente
            </span>
        </div>
        <div class="mb-4 text-xs text-gray-500">Reservas activas que requieren atención (sin devolver)</div>
        <div class="flex flex-col gap-4">
            @forelse($reservasSinDevolucion as $reserva)
                <div class="flex flex-row items-center gap-6 p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 text-gray-400 bg-gray-100 rounded-full">
                            <i class="fas fa-user"></i>
                        </span>
                        <div>
                            @if($reserva->run_profesor)
                                <div class="font-semibold text-gray-800">
                                    {{ $reserva->profesor->name ?? 'Profesor no encontrado' }}
                                </div>
                                <div class="text-xs text-gray-500">RUN: {{ $reserva->profesor->run_profesor ?? 'N/A' }}</div>
                                <div class="text-xs text-blue-600">Tipo: Profesor</div>
                            @elseif($reserva->run_solicitante)
                                <div class="font-semibold text-gray-800">
                                    {{ $reserva->solicitante->nombre ?? 'Solicitante no encontrado' }}
                                </div>
                                <div class="text-xs text-gray-500">RUN: {{ $reserva->solicitante->run_solicitante ?? 'N/A' }}</div>
                                <div class="text-xs text-green-600">Tipo: Solicitante</div>
                            @else
                                <div class="font-semibold text-gray-800">Usuario no identificado</div>
                                <div class="text-xs text-gray-500">RUN: N/A</div>
                            @endif
                        </div>
                    </div>

                    <!-- Detalles de la reserva -->
                    <div class="flex flex-wrap gap-6 text-xs text-gray-600">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $reserva->espacio->id_espacio }}
                            <span class="ml-1 text-gray-400">{{ $reserva->espacio->nombre_espacio }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="fas fa-calendar-alt"></i>
                            {{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }}
                            <span class="ml-1 text-gray-400">Fecha reserva</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="fas fa-clock"></i>
                            {{ $reserva->hora }}
                            <span class="ml-1 text-gray-400">Hora ingreso</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-gray-500">No hay reservas activas que requieran atención.</div>
            @endforelse
        </div>
    </div>

    <!-- Registro de Accesos -->
    <div class="w-full p-8 bg-white shadow-lg rounded-xl md:w-1/2">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 text-blue-600 bg-blue-100 rounded-full">
                    <i class="fas fa-eye"></i>
                </span>
                <h3 class="text-lg font-bold text-gray-700">Registro de Accesos</h3>
            </div>
            <x-button class="inline-flex items-center gap-2 px-4 py-2 mt-3 text-sm font-medium hover:bg-red-700"
                variant="primary" href="{{ route('reportes.accesos') }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd"
                        d="M12.97 3.97a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06l6.22-6.22H3a.75.75 0 0 1 0-1.5h16.19l-6.22-6.22a.75.75 0 0 1 0-1.06Z"
                        clip-rule="evenodd" />
                </svg>
                Ver detalles
            </x-button>
        </div>
        <div class="flex flex-col gap-4">
            @forelse($accesosActuales as $acceso)
                <div class="flex flex-col gap-2 p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 text-gray-400 bg-gray-100 rounded-full">
                            <i class="fas fa-user"></i>
                        </span>
                        <div>
                            @if($acceso->run_profesor)
                                <div class="font-semibold text-gray-800">
                                    {{ $acceso->profesor->name ?? 'Profesor no encontrado' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    <span class="mx-1">•</span> 
                                    <span class="text-blue-700">{{ $acceso->profesor->email ?? 'N/A' }}</span>
                                </div>
                                <div class="text-xs text-blue-600">Tipo: Profesor</div>
                            @elseif($acceso->run_solicitante)
                                <div class="font-semibold text-gray-800">
                                    {{ $acceso->solicitante->nombre ?? 'Solicitante no encontrado' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    <span class="mx-1">•</span> 
                                    <span class="text-blue-700">{{ $acceso->solicitante->correo ?? 'N/A' }}</span>
                                </div>
                                <div class="text-xs text-green-600">Tipo: Solicitante</div>
                            @else
                                <div class="font-semibold text-gray-800">Usuario no identificado</div>
                                <div class="text-xs text-gray-500">
                                    <span class="mx-1">•</span> 
                                    <span class="text-blue-700">N/A</span>
                                </div>
                            @endif
                        </div>
                        <span class="flex items-center gap-1 ml-auto text-xs text-green-600">
                            <span class="w-2 h-2 bg-green-400 rounded-full"></span> En curso
                        </span>
                    </div>
                    <div class="flex flex-col gap-4 p-3 mb-2 text-xs text-gray-700 rounded-md md:flex-row md:items-start md:justify-between bg-gray-50">
                        <!-- Bloque: Información del espacio -->
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="font-semibold">{{ $acceso->espacio->id_espacio }}</span> -
                                {{ $acceso->espacio->nombre_espacio }}
                            </div>
                            <div class="text-gray-500">
                                Piso {{ $acceso->espacio->piso->numero_piso ?? '-' }},
                                {{ $acceso->espacio->piso->facultad->nombre_facultad ?? '' }}
                            </div>
                        </div>

                        <!-- Bloque: Fechas y horas -->
                        <div class="flex flex-wrap gap-6 text-xs text-gray-600">
                            <div>
                                <span class="block text-gray-400">Fecha</span>
                                <span class="font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::parse($acceso->fecha_reserva)->format('d/m/Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Entrada</span>
                                <span class="font-semibold text-gray-800">{{ $acceso->hora }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Salida</span>
                                <span class="font-semibold text-gray-800">{{ $acceso->hora_salida ?? 'En curso' }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Tipo</span>
                                <span class="font-semibold text-gray-800">{{ ucfirst($acceso->tipo_reserva) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-gray-500 bg-white ">
                    <i class="fas fa-info-circle text-blue-500 mb-2"></i>
                    <p class="font-medium">No hay usuarios actualmente en espacios.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

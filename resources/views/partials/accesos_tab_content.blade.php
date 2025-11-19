<div class="space-y-6">
    <!-- Reservas sin devolución de llaves -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Reservas sin devolución de llaves ({{ $reservasSinDevolucion->count() }})
            </h3>

            @if($reservasSinDevolucion->isEmpty())
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>No hay reservas pendientes de devolución</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Espacio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hora Inicio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hora Fin</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tiempo Transcurrido</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($reservasSinDevolucion as $reserva)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $reserva->espacio->nombre }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $reserva->user->name }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($reserva->fecha_reserva . ' ' . $reserva->modulo->hora_inicio)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($reserva->fecha_reserva . ' ' . $reserva->modulo->hora_fin)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        @php
                                            $horaFin = \Carbon\Carbon::parse($reserva->fecha_reserva . ' ' . $reserva->modulo->hora_fin);
                                            $minutos = $horaFin->diffInMinutes(now());
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            @if($minutos < 15) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($minutos < 30) bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @endif">
                                            {{ $minutos }} min
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Accesos actuales en espacios -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Accesos actuales en espacios ({{ $accesosActuales->count() }})
            </h3>

            @if($accesosActuales->isEmpty())
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p>No hay accesos registrados en este momento</p>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($accesosActuales as $acceso)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $acceso->espacio->nombre }}
                                </h4>
                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-full">
                                    Activo
                                </span>
                            </div>
                            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <p>
                                    <span class="font-medium">Usuario:</span> {{ $acceso->user->name }}
                                </p>
                                <p>
                                    <span class="font-medium">Entrada:</span>
                                    {{ \Carbon\Carbon::parse($acceso->hora_entrada)->format('d/m/Y H:i') }}
                                </p>
                                @if($acceso->modulo)
                                    <p>
                                        <span class="font-medium">Horario:</span>
                                        {{ \Carbon\Carbon::parse($acceso->modulo->hora_inicio)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($acceso->modulo->hora_fin)->format('H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

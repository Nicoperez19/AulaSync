<div class="p-6">
    @if (count($pisos) > 0)
        <div class="relative w-full bg-white rounded-lg shadow-sm border border-gray-200">
            @if (count($this->getTodosLosEspacios()) > 0)
                <!-- Tabla de dos columnas -->
                <div class="flex w-full">
                    <!-- Columna 1 - Primera mitad -->
                    <div class="flex-1 border-r border-gray-200">
                        <table class="w-full table-fixed">
                            <thead>
                                <tr class="bg-light-cloud-blue text-white border-b border-gray-200">
                                    <th class="w-1/3 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Espacio
                                    </th>
                                    <th class="w-2/5 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="w-1/5 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Responsable
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach (array_slice($this->getTodosLosEspacios(), 0, ceil(count($this->getTodosLosEspacios()) / 2)) as $index => $espacio)
                                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors duration-200 h-10">
                                        <td class="px-3 py-1 text-sm align-middle">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full {{ $this->getEstadoColor($espacio['estado'], $espacio['tiene_clase'], $espacio['tiene_reserva_solicitante']) }} flex-shrink-0"></span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="font-semibold text-blue-700 text-sm">{{ $espacio['id_espacio'] }}</span>
                                                        <span class="font-medium text-gray-900 text-sm">{{ $espacio['nombre_espacio'] }}</span>
                                                        <span class="text-sm text-gray-500">({{ $espacio['piso'] }})</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-1 text-sm align-middle">
                                            @if ($espacio['tiene_reserva_solicitante'] && $espacio['datos_solicitante'])
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-sm font-medium text-purple-700">
                                                            Espacio Solicitado
                                                        </span>
                                                    </div>
                                                </div>
                                            @elseif ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-sm">
                                                            {{ $espacio['datos_clase']['nombre_asignatura'] }}
                                                        </span>
                                                        @if ($espacio['datos_clase']['seccion'])
                                                            <span class="text-sm text-gray-500 flex-shrink-0">(Sección {{ $espacio['datos_clase']['seccion'] }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-sm">Sin clases</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-1 text-sm align-middle">
                                            @if ($espacio['tiene_reserva_solicitante'] && $espacio['datos_solicitante'])
                                                <span class="font-medium text-purple-700 text-sm">{{ $this->getPrimerApellidoSolicitante($espacio['datos_solicitante']['nombre']) }}</span>
                                            @elseif ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                <span class="font-medium text-gray-900 text-sm">{{ $this->getPrimerApellido($espacio['datos_clase']['profesor']['name']) }}</span>
                                            @else
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Columna 2 - Segunda mitad -->
                    <div class="flex-1">
                        <table class="w-full table-fixed">
                            <thead>
                                <tr class="bg-light-cloud-blue text-white border-b border-gray-200">
                                    <th class="w-1/3 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Espacio
                                    </th>
                                    <th class="w-2/5 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="w-1/5 px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                        Responsable
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach (array_slice($this->getTodosLosEspacios(), ceil(count($this->getTodosLosEspacios()) / 2)) as $index => $espacio)
                                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors duration-200 h-10">
                                        <td class="px-3 py-1 text-sm align-middle">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full {{ $this->getEstadoColor($espacio['estado'], $espacio['tiene_clase'], $espacio['tiene_reserva_solicitante']) }} flex-shrink-0"></span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="font-semibold text-blue-700 text-sm">{{ $espacio['id_espacio'] }}</span>
                                                        <span class="font-medium text-gray-900 text-sm">{{ $espacio['nombre_espacio'] }}</span>
                                                        <span class="text-sm text-gray-500">({{ $espacio['piso'] }})</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-1 text-sm align-middle">
                                            @if ($espacio['tiene_reserva_solicitante'] && $espacio['datos_solicitante'])
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-sm font-medium text-purple-700">
                                                            Espacio Solicitado
                                                        </span>
                                                    </div>
                                                </div>
                                            @elseif ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-sm">
                                                            {{ $espacio['datos_clase']['nombre_asignatura'] }}
                                                        </span>
                                                        @if ($espacio['datos_clase']['seccion'])
                                                            <span class="text-sm text-gray-500 flex-shrink-0">(Sección {{ $espacio['datos_clase']['seccion'] }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-sm">Sin clases</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-1 text-sm align-middle">
                                            @if ($espacio['tiene_reserva_solicitante'] && $espacio['datos_solicitante'])
                                                <span class="font-medium text-purple-700 text-sm">{{ $this->getPrimerApellidoSolicitante($espacio['datos_solicitante']['nombre']) }}</span>
                                            @elseif ($espacio['tiene_clase'] && $espacio['datos_clase'])
                                                <span class="font-medium text-gray-900 text-sm">{{ $this->getPrimerApellido($espacio['datos_clase']['profesor']['name']) }}</span>
                                            @else
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
    
    <!-- Leyenda de colores -->
    <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200 text-center">
        <h4 class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Leyenda de Estados</h4>
        <div class="flex flex-wrap gap-4 text-xs justify-center">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                <span class="text-gray-600">Disponible</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-yellow-400 flex-shrink-0"></span>
                <span class="text-gray-600">Reservado / Clase Programada</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500 flex-shrink-0"></span>
                <span class="text-gray-600">Ocupado (Interacción Física)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0"></span>
                <span class="text-gray-600">Próxima Clase</span>
            </div>
        </div>
    </div>

    <script>
        // Actualizar datos cada 10 segundos
        setInterval(function() {
            @this.actualizarAutomaticamente();
        }, 10000);
    </script>
</div>

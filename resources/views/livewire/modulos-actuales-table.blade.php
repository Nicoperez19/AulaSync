<div class="p-6">
    @if (count($pisos) > 0)
        <div class="relative w-full bg-white rounded-lg shadow-sm border border-gray-200">
            @if (count($this->todosLosEspacios) > 0)
                <div class="flex w-full">

                    {{-- Columna izquierda - DISPONIBLES --}}
                    <div class="flex-1 border-r border-gray-200">
                        <table class="w-full table-fixed">
                            <thead>
                                <tr class="bg-green-600 text-white border-b border-gray-200">
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">N° Sala</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Nombre</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Piso</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach (array_slice(array_filter($this->todosLosEspacios, function($espacio) { return strtolower($espacio['estado']) === 'disponible'; }), 0, 11) as $espacio)
                                    <tr class="bg-white hover:bg-green-50 transition-colors h-10">
                                        <td class="px-3 py-1 text-sm font-semibold text-blue-700">{{ $espacio['id_espacio'] }}</td>
                                        <td class="px-3 py-1 text-sm">{{ $espacio['nombre_espacio'] }}</td>
                                        <td class="px-3 py-1 text-sm text-gray-500">{{ $espacio['piso'] }}</td>
                                        <td class="px-3 py-1 text-sm text-green-600 font-medium">Disponible</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Columna derecha - OCUPADOS / RESERVADOS / CLASES --}}
                    <div class="flex-1">
                        <div>
                            <table class="w-full table-fixed">
                                <thead>
                                    <tr class="text-white border-b border-gray-200"
                                        @if($slideCarrusel === 'ocupados') style="background-color: #dc2626;" @elseif($slideCarrusel === 'reservados') style="background-color: #facc15; color: #92400e;" @endif>
                                        <th class="px-3 py-1 text-left text-sm font-semibold uppercase">N° Sala</th>
                                        <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Nombre</th>
                                        <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Piso</th>
                                        <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Estado</th>
                                        <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Responsable</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @php
                                        $filtrados = array_filter($this->todosLosEspacios, function($espacio) use ($slideCarrusel) {
                                            $tieneReservaSolicitante = $espacio['tiene_reserva_solicitante'] ?? false;
                                            $tieneClase = $espacio['tiene_clase'] ?? false;
                                            $esOcupado = strtolower($espacio['estado']) === 'ocupado';
                                            $esReservado = strtolower($espacio['estado']) === 'reservado' || $tieneReservaSolicitante || $tieneClase;
                                            return ($slideCarrusel === 'ocupados' && $esOcupado) || ($slideCarrusel === 'reservados' && $esReservado && !$esOcupado);
                                        });
                                    @endphp
                                    @foreach (array_slice($filtrados, 0, 11) as $espacio)
                                        @php
                                            $tieneReservaSolicitante = $espacio['tiene_reserva_solicitante'] ?? false;
                                            $tieneClase = $espacio['tiene_clase'] ?? false;
                                            $datosSolicitante = $espacio['datos_solicitante'] ?? null;
                                            $datosClase = $espacio['datos_clase'] ?? null;
                                            $esOcupado = strtolower($espacio['estado']) === 'ocupado';
                                        @endphp
                                        <tr class="bg-white hover:bg-blue-50 transition-colors h-10">
                                            <td class="px-3 py-1 text-sm font-semibold text-blue-700">{{ $espacio['id_espacio'] }}</td>
                                            <td class="px-3 py-1 text-sm">{{ $espacio['nombre_espacio'] }}</td>
                                            <td class="px-3 py-1 text-sm text-gray-500">{{ $espacio['piso'] }}</td>
                                            <td class="px-3 py-1 text-sm font-medium @if($esOcupado) text-red-600 @else text-yellow-600 @endif">
                                                @if($esOcupado)
                                                    Ocupado
                                                @elseif($tieneReservaSolicitante && $datosSolicitante)
                                                    Espacio Solicitado
                                                @elseif($tieneClase && $datosClase)
                                                    {{ $datosClase['nombre_asignatura'] }}
                                                @else
                                                    Reservado
                                                @endif
                                            </td>
                                            <td class="px-3 py-1 text-sm align-middle">
                                                @if($esOcupado)
                                                    {{-- Aquí podrías mostrar responsable si lo tienes --}}
                                                    -
                                                @elseif($tieneReservaSolicitante && $datosSolicitante)
                                                    {{ $this->getPrimerApellidoSolicitante($datosSolicitante['nombre']) }}
                                                @elseif($tieneClase && $datosClase)
                                                    {{ $this->getPrimerApellido($datosClase['profesor']['name']) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            @endif
        </div>
    @endif

    {{-- Slide label solo sobre la segunda tabla --}}
    <div class="flex justify-end mt-4 mb-1">
        <span class="px-3 py-1 rounded-full text-xs font-semibold"
            :class="@js($slideCarrusel === 'ocupados' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')">
            @if($slideCarrusel === 'ocupados') Ocupados @else Reservados @endif
        </span>
    </div>
    {{-- Leyenda de colores --}}
    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-center">
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

    {{-- Carrusel automático y actualización automática --}}
    <script>
        window.slideCarrusel = window.slideCarrusel || 'ocupados';
        function alternarCarrusel() {
            window.slideCarrusel = window.slideCarrusel === 'ocupados' ? 'reservados' : 'ocupados';
            @this.set('slideCarrusel', window.slideCarrusel);
        }
        setInterval(function() {
            alternarCarrusel();
            @this.actualizarAutomaticamente();
        }, 5000);
    </script>
</div>
<div class="p-6">
<div style="padding:10px; background:#eee; margin-bottom:10px;">
    slideCarruselDisponibles actual: <strong>{{ $slideCarruselDisponibles }}</strong>
</div>


    @if (count($pisos) > 0)
        <div class="relative w-full bg-white rounded-lg shadow-sm border border-gray-200">
            @if (count($this->todosLosEspacios) > 0)
                <div class="flex w-full">

@php
    $filtradosLimitados = $this->espaciosFiltrados;
@endphp

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
            @foreach ($this->espaciosFiltrados as $espacio)
                <tr class="bg-white hover:bg-green-50 transition-colors h-10">
                    <td class="px-3 py-1 text-sm font-semibold text-blue-700">{{ $espacio['id_espacio'] }}</td>
                    <td class="px-3 py-1 text-sm">{{ $espacio['nombre_espacio'] }}</td>
                    <td class="px-3 py-1 text-sm text-gray-500">{{ $espacio['piso'] ?? 'N/A' }}</td>
                    <td class="px-3 py-1 text-sm text-green-600 font-medium">Disponible</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        const compEl = document.querySelector('[wire\\:id]');
        if (!compEl) return;

        const comp = Livewire.find(compEl.getAttribute('wire:id'));

        window.debeHacerSlide = @json($this->debeHacerSlide);

        setInterval(() => {
            if (!window.debeHacerSlide) return;

            comp.call('avanzarCarruselDisponibles');
        }, 5000);
    });
</script>
@endpush

</div>




                    {{-- Columna derecha - OCUPADOS / RESERVADOS / CLASES --}}
              <div class="flex-1">
                <div>
                    <table class="w-full table-fixed">
                        <thead>
                            @php
                            $hayEntreModulos = collect($this->todosLosEspacios)->contains('es_entre_modulos', true);
                            $mostrandoProximasClases = $slideCarrusel === 'reservados' && $hayEntreModulos;
                            @endphp
                            <tr class="text-white border-b border-gray-200"
                                @if($slideCarrusel==='ocupados' )
                                style="background-color: #dc2626;"
                                @elseif($mostrandoProximasClases)
                                style="background-color: #2563eb;"
                                @else
                                style="background-color: #facc15; color: #92400e;"
                                @endif>
                                <th class="px-3 py-1 text-left text-sm font-semibold uppercase w-20">N° Sala</th>
                                <th class="px-3 py-1 text-left text-sm font-semibold uppercase w-32">Nombre</th>
                                <th class="px-3 py-1 text-left text-sm font-semibold uppercase w-16">Piso</th>
                                <th class="px-3 py-1 text-left text-sm font-semibold uppercase w-28">Estado</th>
                                <th class="px-3 py-1 text-left text-sm font-semibold uppercase">Responsable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php
                            $filtrados = array_filter($this->todosLosEspacios, function($espacio) use ($slideCarrusel) {
                            $tieneReservaSolicitante = $espacio['tiene_reserva_solicitante'] ?? false;
                            $tieneReservaProfesor = $espacio['tiene_reserva_profesor'] ?? false;
                            $tieneClase = $espacio['tiene_clase'] ?? false;
                            $esOcupado = strtolower($espacio['estado']) === 'ocupado';
                            $esEntreModulos = $espacio['es_entre_modulos'] ?? false;

                            // Si estamos entre módulos, solo mostrar ocupados y próximas clases
                            if ($esEntreModulos) {
                            return ($slideCarrusel === 'ocupados' && $esOcupado) ||
                            ($slideCarrusel === 'reservados' && $tieneClase && !$esOcupado);
                            }

                            // Lógica normal cuando hay módulo activo
                            $esReservado = strtolower($espacio['estado']) === 'reservado' || $tieneReservaSolicitante || $tieneReservaProfesor || $tieneClase;
                            return ($slideCarrusel === 'ocupados' && $esOcupado) || ($slideCarrusel === 'reservados' && $esReservado && !$esOcupado);
                            });
                            @endphp
                            @foreach (array_slice($filtrados, 0, 10) as $espacio)
                            @php
                            $tieneReservaSolicitante = $espacio['tiene_reserva_solicitante'] ?? false;
                            $tieneClase = $espacio['tiene_clase'] ?? false;
                            $tieneReservaProfesor = $espacio['tiene_reserva_profesor'] ?? false;
                            $datosSolicitante = $espacio['datos_solicitante'] ?? null;
                            $datosClase = $espacio['datos_clase'] ?? null;
                            $datosProfesor = $espacio['datos_profesor'] ?? null;
                            $esOcupado = strtolower($espacio['estado']) === 'ocupado';
                            $esEntreModulos = $espacio['es_entre_modulos'] ?? false;
                            $esProximaClase = isset($datosClase['es_proxima']) && $datosClase['es_proxima'];
                            @endphp
                            <tr class="bg-white hover:bg-blue-50 transition-colors h-10">
                                <td class="px-3 py-1 text-sm font-semibold text-blue-700">{{ $espacio['id_espacio'] }}</td>
                                <td class="px-3 py-1 text-sm">{{ $espacio['nombre_espacio'] }}</td>
                                <td class="px-3 py-1 text-sm text-gray-500">{{ $espacio['piso'] }}</td>
                                <td class="px-3 py-1 text-sm font-medium @if($esOcupado) text-red-600 @elseif($esProximaClase) text-blue-600 @else text-yellow-600 @endif">
                                    @if($esOcupado)
                                    Ocupado
                                    @elseif($esProximaClase)
                                    Próxima Clase
                                    @elseif($tieneReservaSolicitante && $datosSolicitante)
                                    Espacio Solicitado
                                    @elseif($tieneReservaProfesor && $datosProfesor)
                                    Reservado por Profesor
                                    @elseif($tieneClase && $datosClase)
                                    {{ $datosClase['nombre_asignatura'] ?? 'Clase Programada' }}
                                    @else
                                    Reservado
                                    @endif
                                </td>
                                <td class="px-3 py-1 text-sm align-middle">
                                    @if($esOcupado)
                                        @if($datosClase && isset($datosClase['profesor']['name']))
                                            {{ $this->getNombreCompleto($datosClase['profesor']['name']) }}
                                        @elseif($datosProfesor)
                                            {{ $this->getNombreCompleto($datosProfesor['nombre']) }}
                                        @else
                                            -
                                        @endif

                                    @elseif($esProximaClase && $datosClase && isset($datosClase['profesor']['name']))
                                        {{ $this->getNombreCompleto($datosClase['profesor']['name']) }}
                                        <div class="text-xs text-gray-500">
                                            {{ $datosClase['hora_inicio'] ?? '' }}
                                        </div>
                                    @elseif($tieneReservaSolicitante && $datosSolicitante)
                                        {{ $this->getPrimerApellidoSolicitante($datosSolicitante['nombre']) }}
                                    @elseif($tieneReservaProfesor && $datosProfesor)
                                        {{ $this->getNombreCompleto($datosProfesor['nombre']) }}
                                    @elseif($tieneClase && $datosClase && isset($datosClase['profesor']['name']))
                                        {{ $this->getNombreCompleto($datosClase['profesor']['name']) }}
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
        @php
            $hayEntreModulos = collect($this->todosLosEspacios)->contains('es_entre_modulos', true);
            $mostrandoProximasClases = $slideCarrusel === 'reservados' && $hayEntreModulos;
        @endphp
        <span class="px-3 py-1 rounded-full text-xs font-semibold
            @if($slideCarrusel==='ocupados')
                bg-red-100 text-red-700
            @elseif($mostrandoProximasClases)
                bg-blue-100 text-blue-700
            @else
                bg-yellow-100 text-yellow-700
            @endif">
            @if($slideCarrusel === 'ocupados')
                Ocupados
            @else
                @if($hayEntreModulos)
                    Próximas Clases
                @else
                    Reservados
                @endif
            @endif
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
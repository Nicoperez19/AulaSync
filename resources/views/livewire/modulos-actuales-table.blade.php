<div class="p-6" x-data="{
        pagina: 0,
        paginaAnterior: -1,
        totalPaginas: Math.ceil({{ count($this->getTodosLosEspacios()) }} / 13),
        transicionando: false
    }"
    x-init="
        // Emitir página inicial
        window.dispatchEvent(new CustomEvent('actualizar-pagina', { detail: { pagina: pagina + 1, total: totalPaginas } }));

        // Emitir información de feriado
        window.dispatchEvent(new CustomEvent('actualizar-feriado', {
            detail: {
                esFeriado: {{ $esFeriado ? 'true' : 'false' }},
                nombreFeriado: '{{ $nombreFeriado }}'
            }
        }));

        // Animación inicial
        setTimeout(() => {
            const initialPage = document.querySelector('[data-pagina=\'0\']');
            if (initialPage) {
                Array.from(initialPage.querySelectorAll('tbody tr')).forEach((row, idx) => {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-100%)';
                    setTimeout(() => {
                        row.style.opacity = '1';
                        row.style.transform = 'translateX(0)';
                    }, idx * 150);
                });
            }
        }, 100);

        // Actualizar página cada 12 segundos con animación
        setInterval(() => {
            if (totalPaginas > 1) {
                paginaAnterior = pagina;
                pagina = (pagina + 1) % totalPaginas;
                window.dispatchEvent(new CustomEvent('actualizar-pagina', { detail: { pagina: pagina + 1, total: totalPaginas } }));
            }
        }, 12000)
    ">

    @if (count($pisos) > 0)

        <div class="relative w-full bg-gray-100 rounded-lg shadow-sm border border-gray-300 overflow-hidden">
            @if (count($this->getTodosLosEspacios()) > 0)
                @php $totalPaginas = ceil(count($this->getTodosLosEspacios()) / 13); @endphp
                <table class="w-full table-fixed">
                    <colgroup>
                        <col style="width: 16.66%">
                        <col style="width: 8.33%">
                        <col style="width: 35%">
                        <col style="width: 18%">
                        <col style="width: 12%">
                        <col style="width: 10%">
                    </colgroup>
                    <thead>
                        <tr class="bg-red-600 text-white border-b border-gray-300">
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-clock mr-2"></i>Modulo
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-door-open mr-2"></i>Espacio
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-book mr-2"></i>Clase
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-graduation-cap mr-2"></i>Carrera
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300">
                                <i class="fas fa-users mr-2"></i>Asistencia
                            </th>
                            <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider">
                                <i class="fas fa-circle-info mr-2"></i>Status
                            </th>
                        </tr>
                    </thead>
                </table>
                <div class="relative">
                    @for ($i = 0; $i < $totalPaginas; $i++)
                        <div x-show="pagina === {{ $i }}"
                             data-pagina="{{ $i }}"
                             x-init="$watch('pagina', value => {
                                 if (value === {{ $i }}) {
                                     $nextTick(() => {
                                         Array.from($el.querySelectorAll('tbody tr')).forEach((row, idx) => {
                                             row.style.opacity = '0';
                                             row.style.transform = 'translateX(-100%)';
                                             setTimeout(() => {
                                                 row.style.opacity = '1';
                                                 row.style.transform = 'translateX(0)';
                                             }, idx * 150);
                                         });
                                     });
                                 }
                             })"
                             x-transition:leave="transition-opacity ease-in-out duration-500 absolute inset-0"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             @transitionstart.self="
                                 if ($event.propertyName === 'opacity' && getComputedStyle($el).opacity === '1') {
                                     Array.from($el.querySelectorAll('tbody tr')).forEach((row, idx) => {
                                         setTimeout(() => {
                                             row.style.transform = 'translateX(150%)';
                                             row.style.opacity = '0';
                                         }, idx * 120);
                                     });
                                 }
                             "
                             class="w-full">
                            <table class="w-full table-fixed">
                                <colgroup>
                                    <col style="width: 16.66%">
                                    <col style="width: 8.33%">
                                    <col style="width: 35%">
                                    <col style="width: 18%">
                                    <col style="width: 12%">
                                    <col style="width: 10%">
                                </colgroup>
                                <tbody class="divide-y divide-gray-200">
                                @foreach (array_slice($this->getTodosLosEspacios(), $i * 13, 13) as $index => $espacio)
                                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 h-10 border-b border-gray-200 transition-all duration-1000 ease-in-out">
                                        <!-- Columna 1: Modulo -->
                                            <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                                @if($espacio['estado'] === 'Disponible' && !empty($espacio['rango_disponibilidad']))
                                                    {{-- Mostrar rango de disponibilidad cuando el espacio está disponible --}}
                                                    <div class="font-medium text-gray-900 text-sm">
                                                        <div class="flex items-center gap-2 text-base font-semibold text-green-600">
                                                            {{ $espacio['rango_disponibilidad']['desde'] }} - {{ $espacio['rango_disponibilidad']['hasta'] }}
                                                        </div>
                                                        <div class="text-gray-600">
                                                            {{ substr($espacio['rango_disponibilidad']['hora_desde'], 0, 5) }} - {{ substr($espacio['rango_disponibilidad']['hora_hasta'], 0, 5) }}
                                                        </div>
                                                    </div>
                                                @elseif(($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']) && !empty($espacio['datos_clase']['modulo_inicio']) && !empty($espacio['datos_clase']['modulo_fin']))
                                                    <div class="font-medium text-gray-900 text-sm">
                                                        <div class="flex items-center gap-2 text-base font-semibold">

                                                           {{ preg_replace('/^[A-Z]{2}\./', '', $espacio['datos_clase']['modulo_inicio']) }} - {{ preg_replace('/^[A-Z]{2}\./', '', $espacio['datos_clase']['modulo_fin']) }}

                                                        </div>
                                                        <div class="text-gray-600">

                                                            {{ $espacio['datos_clase']['hora_inicio'] ?? '--:--' }} - {{ $espacio['datos_clase']['hora_fin'] ?? '--:--' }}
                                                        </div>
                                                    </div>
                                                @elseif(($espacio['tiene_reserva_profesor'] ?? false) && !empty($espacio['datos_profesor']))
                                                    <div class="font-medium text-gray-900 text-sm">
                                                        <div class="text-gray-600">
                                                            Reserva Profesor
                                                        </div>
                                                    </div>
                                                @elseif(!empty($espacio['proxima_clase']) && is_array($espacio['proxima_clase']))
                                                     <div class="flex items-center gap-2 text-base font-semibold">

                                                           {{ preg_replace('/^[A-Z]{2}\./', '', $espacio['proxima_clase']['modulo_inicio'] ?? '--') }} - {{ preg_replace('/^[A-Z]{2}\./', '', $espacio['proxima_clase']['modulo_fin'] ?? '--') }}
                                                        </div>
                                                        <div class="text-gray-600">
                                                            {{ $espacio['proxima_clase']['hora_inicio'] ?? '--:--' }} - {{ $espacio['proxima_clase']['hora_fin'] ?? '--:--' }}
                                                        </div>
                                                    </div>
                                                @elseif($this->moduloActual && !empty($this->moduloActual['numero']))
                                                    @if(($this->moduloActual['tipo'] ?? 'modulo') === 'break')
                                                        <span class="text-base font-semibold text-gray-600">
                                                          ------
                                                        </span>
                                                    @else
                                                        <div class="font-medium text-gray-900 text-sm">
                                                            @if(!empty($espacio['rango_disponibilidad']))
                                                                <div class="flex items-center gap-2 text-base font-semibold text-green-600">
                                                                    {{ $espacio['rango_disponibilidad']['desde'] }} - {{ $espacio['rango_disponibilidad']['hasta'] }}
                                                                </div>
                                                                <div class="text-gray-600">
                                                                    {{ substr($espacio['rango_disponibilidad']['hora_desde'], 0, 5) }} - {{ substr($espacio['rango_disponibilidad']['hora_hasta'], 0, 5) }}
                                                                </div>
                                                            @else
                                                                <div class="flex items-center gap-2 text-base font-semibold">
                                                                    {{ $this->moduloActual['numero'] }}
                                                                </div>
                                                                <div class="text-gray-600">
                                                                    {{ substr($this->moduloActual['inicio'], 0, 5) }} - {{ substr($this->moduloActual['fin'], 0, 5) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>
                                        <!-- Columna 2: Espacio -->
                                        <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            <div class="font-semibold text-blue-700 text-sm">{{ $espacio['id_espacio'] }}</div>
                                            <div class="text-gray-600 text-xs">Piso {{ $espacio['piso'] ?? 'N/A' }}</div>
                                        </td>
                                        <!-- Columna 3: Estado -->
                                         <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            @php
                                                $asignatura = $espacio['datos_clase']['nombre_asignatura'] ?? $espacio['proxima_clase']['nombre_asignatura'] ?? null;
                                                $esColaborador = $espacio['datos_clase']['es_colaborador'] ?? false;
                                            @endphp
                                            @if (($espacio['tiene_reserva_solicitante'] ?? false) && !empty($espacio['datos_solicitante']))
                                                <span class="font-medium text--700 text-sm">Solicitante: {{ $espacio['datos_solicitante']['nombre'] ?? 'N/A' }}</span>
                                            @elseif (($espacio['tiene_reserva_profesor'] ?? false) && !empty($espacio['datos_profesor']) && !empty($espacio['datos_profesor']['nombre']))
                                                <span class="font-medium text-gray-700 text-sm">
                                                    <div>
                                                        @if(!empty($espacio['datos_profesor']['codigo_asignatura']))
                                                            <span class="font-semibold text-blue-600">[{{ $espacio['datos_profesor']['codigo_asignatura'] }}]</span>
                                                        @endif
                                                        {{ $espacio['datos_profesor']['nombre_asignatura'] ?? $asignatura ?? 'Sin asignatura' }}
                                                    </div>
                                                    <div>Profesor: {{ $espacio['datos_profesor']['nombre'] ?? 'N/A' }}</div>
                                                </span>
                                            @elseif (($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']))
                                                @if($esColaborador)
                                                    <!-- Clase Colaboradora: mostrar información básica con etiqueta a la derecha -->
                                                    @php
                                                        $tipoClase = $espacio['datos_clase']['tipo_clase'] ?? 'temporal';
                                                        $etiquetaConfig = [
                                                            'temporal' => ['bg' => 'bg-purple-200', 'text' => 'text-purple-700', 'label' => 'TEMPORAL'],
                                                            'reforzamiento' => ['bg' => 'bg-orange-200', 'text' => 'text-orange-700', 'label' => 'REFORZAMIENTO'],
                                                            'recuperacion' => ['bg' => 'bg-green-200', 'text' => 'text-green-700', 'label' => 'RECUPERACIÓN'],
                                                        ];
                                                        $etiqueta = $etiquetaConfig[$tipoClase] ?? $etiquetaConfig['temporal'];
                                                    @endphp
                                                    <div class="font-medium text-gray-900 text-sm flex items-start justify-between gap-2">
                                                        <div class="flex-1">
                                                            <div>{{ $asignatura ?? 'Clase Temporal' }}</div>
                                                            <div>Prof: {{ $espacio['datos_clase']['profesor']['name'] ?? 'N/A' }}</div>
                                                        </div>
                                                        <span class="px-2 py-0.5 {{ $etiqueta['bg'] }} {{ $etiqueta['text'] }} text-xs font-semibold rounded whitespace-nowrap">{{ $etiqueta['label'] }}</span>
                                                    </div>
                                                @else
                                                    <!-- Clase Regular -->
                                                    <div class="font-medium text-gray-900 text-sm">
                                                        @if(!empty($espacio['es_recuperacion']) && $espacio['es_recuperacion'])
                                                            <div class="flex items-center gap-1">
                                                                <i class="fas fa-redo text-blue-600"></i>
                                                                <span class="text-blue-600 font-semibold">Recuperación de clase</span>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            @if(!empty($espacio['datos_clase']['codigo_asignatura']))
                                                                <span class="font-semibold ">{{ $espacio['datos_clase']['codigo_asignatura'] }} - </span>
                                                            @endif
                                                            {{ $asignatura ?? 'Sin asignatura' }}
                                                        </div>
                                                        <div>Prof: {{ $espacio['datos_clase']['profesor']['name'] ?? 'N/A' }}</div>
                                                        @if(!empty($espacio['es_recuperacion']) && !empty($espacio['datos_clase']['fecha_original']))
                                                            <div class="text-xs text-gray-600">Original: {{ $espacio['datos_clase']['fecha_original'] }}</div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @elseif(!empty($espacio['proxima_clase']) && is_array($espacio['proxima_clase']))
                                                <div class="font-medium text-gray-700 text-sm">
                                                    <div>
                                                        @if(!empty($espacio['proxima_clase']['codigo_asignatura']))
                                                            <span class="font-semibold ">{{ $espacio['proxima_clase']['codigo_asignatura'] }} - </span>
                                                        @endif
                                                        Próxima: {{ $asignatura ?? 'Clase programada' }}
                                                    </div>
                                                    <div>Prof: {{ $espacio['proxima_clase']['profesor'] ?? '-' }}</div>
                                                </div>
                                            @elseif($asignatura)
                                                <div class="font-medium text-gray-900 text-sm">
                                                    <div>{{ $asignatura }}</div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            @endif
                                        </td>

                                        <!-- Columna 4: Carrera -->
                                        <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            @php
                                                $esColaborador = $espacio['datos_clase']['es_colaborador'] ?? false;
                                            @endphp
                                            @if($esColaborador)
                                                <!-- No mostrar carrera para clases colaboradoras -->
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            @elseif (($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']['carrera']))
                                                <span class="font-medium text-gray-700 text-sm">{{ $espacio['datos_clase']['carrera'] }}</span>
                                            @elseif (($espacio['tiene_reserva_profesor'] ?? false) && !empty($espacio['datos_profesor']['carrera']))
                                                <span class="font-medium text-gray-700 text-sm">{{ $espacio['datos_profesor']['carrera'] }}</span>
                                            @else
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            @endif
                                        </td>

                                        <!-- Columna 5: Capacidad -->
                                        <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            @php
                                                $capacidadMax = $espacio['capacidad_maxima'] ?? 0;
                                                $puestosDisponibles = $espacio['puestos_disponibles'] ?? 0;
                                                $capacidadUtilizada = max(0, $capacidadMax - $puestosDisponibles);
                                                $porcentaje = $capacidadMax > 0 ? round(($capacidadUtilizada / $capacidadMax) * 100) : 0;

                                                // Determinar color según ocupación
                                                $colorClase = '';
                                                if ($porcentaje >= 90) {
                                                    $colorClase = 'text-red-600 font-bold';
                                                } elseif ($porcentaje >= 70) {
                                                    $colorClase = 'text-orange-600 font-semibold';
                                                } elseif ($porcentaje >= 50) {
                                                    $colorClase = 'text-yellow-600 font-medium';
                                                } else {
                                                    $colorClase = 'text-green-600';
                                                }
                                            @endphp

                                            @if($capacidadMax > 0)
                                                <div class="flex flex-col gap-1">
                                                    <div class="{{ $colorClase }} text-sm">
                                                        {{ $capacidadUtilizada }}/{{ $capacidadMax }}
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="h-2 rounded-full {{ $porcentaje >= 90 ? 'bg-red-600' : ($porcentaje >= 70 ? 'bg-orange-500' : ($porcentaje >= 50 ? 'bg-yellow-500' : 'bg-green-500')) }}"
                                                             style="width: {{ $porcentaje }}%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-sm italic">Sin datos</span>
                                            @endif
                                        </td>

                                        <!-- Columna 6: Status -->
                                      <td class="px-3 py-1 text-sm align-middle">
                                            <span class="w-4 h-4 rounded-full {{ $this->getEstadoColor($espacio['estado'], $espacio['tiene_clase'] ?? false, $espacio['tiene_reserva_solicitante'] ?? false, $espacio['tiene_reserva_profesor'] ?? false) }} flex-shrink-0 inline-block mr-2"></span>
                                            <span class="font-medium text-gray-900 text-sm">{{ $espacio['estado'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endfor
                </div>
            @endif
        </div>
    @endif

    <!-- Leyenda de colores -->

    </div>

    <script>
        // Actualizar datos cada 60 segundos para evitar sobrecarga del servidor
        setInterval(function() {
            @this.actualizarAutomaticamente();
        }, 60000); // Aumentado a 60 segundos

        // Escuchar eventos de Livewire para actualizar el feriado cuando se recarguen los datos
        document.addEventListener('livewire:load', function() {
            Livewire.on('datosActualizados', function() {
                window.dispatchEvent(new CustomEvent('actualizar-feriado', {
                    detail: {
                        esFeriado: {{ $esFeriado ? 'true' : 'false' }},
                        nombreFeriado: '{{ $nombreFeriado }}'
                    }
                }));
            });
        });
    </script>
</div>

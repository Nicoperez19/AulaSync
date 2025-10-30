<div class="p-6" x-data="{ 
        pagina: 0, 
        totalPaginas: Math.ceil({{ count($this->getTodosLosEspacios()) }} / 13) 
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
        
        // Actualizar página cada 5 segundos
        setInterval(() => { 
            pagina = (pagina + 1) % totalPaginas;
            window.dispatchEvent(new CustomEvent('actualizar-pagina', { detail: { pagina: pagina + 1, total: totalPaginas } }));
        }, 5000)
    ">
    
    @if (count($pisos) > 0)
    
        <div class="relative w-full fixed bg-gray-100 rounded-lg shadow-sm border border-gray-300 overflow-hidden">
            @if (count($this->getTodosLosEspacios()) > 0)
                @php $totalPaginas = ceil(count($this->getTodosLosEspacios()) / 13); @endphp
                @for ($i = 0; $i < $totalPaginas; $i++)
                    <div x-show="pagina === {{ $i }}" class="transition-all duration-500">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-red-600 text-white border-b border-gray-300">
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300 w-1/6">Modulo</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300 w-1/12">Espacio</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300 w-1/3">Clase</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300 w-1/6">Carrera</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300 w-1/12">Capacidad</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider w-1/12">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach (array_slice($this->getTodosLosEspacios(), $i * 13, 13) as $index => $espacio)
                                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors duration-200 h-10 border-b border-gray-200">
                                        <!-- Columna 1: Modulo -->
                                            <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                                @if(($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']) && !empty($espacio['datos_clase']['modulo_inicio']) && !empty($espacio['datos_clase']['modulo_fin']))
                                                    <div class="font-medium text-gray-900 text-sm">
                                                        <div class="flex items-center gap-2 text-base font-semibold">
                                                                         <i class="fas fa-clock"></i>
                                                           {{ preg_replace('/^[A-Z]{2}\./', '', $espacio['datos_clase']['modulo_inicio']) }} - {{ preg_replace('/^[A-Z]{2}\./', '', $espacio['datos_clase']['modulo_fin']) }}
                                                          
                                                        </div>
                                                        <div class="text-gray-600">
                                                             
                                                            {{ $espacio['datos_clase']['hora_inicio'] ?? '--:--' }} - {{ $espacio['datos_clase']['hora_fin'] ?? '--:--' }}
                                                        </div>
                                                    </div>
                                                @elseif(!empty($espacio['proxima_clase']) && is_array($espacio['proxima_clase']))
                                                     <div class="flex items-center gap-2 text-base font-semibold">
                                                                         <i class="fas fa-clock"></i>
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
                                                        <span class="flex items-center gap-2 text-base font-semibold">
                                                            <i class="fas fa-clock"></i>
                                                            {{ $this->moduloActual['numero'] }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>
                                        <!-- Columna 2: Espacio -->
                                        <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            <span class="font-semibold text-blue-700 text-sm">{{ $espacio['id_espacio'] }}</span>
                                        </td>
                                        <!-- Columna 3: Estado -->
                                         <td class="px-3 py-1 text-sm align-middle border-r border-gray-200">
                                            @php
                                                $asignatura = $espacio['datos_clase']['nombre_asignatura'] ?? $espacio['proxima_clase']['nombre_asignatura'] ?? null;
                                            @endphp
                                            @if (($espacio['tiene_reserva_solicitante'] ?? false) && !empty($espacio['datos_solicitante']))
                                                <span class="font-medium text--700 text-sm">Solicitante: {{ $espacio['datos_solicitante']['nombre'] ?? 'N/A' }}</span>
                                            @elseif (($espacio['tiene_reserva_profesor'] ?? false) && !empty($espacio['datos_profesor']) && !empty($espacio['datos_profesor']['nombre']))
                                                <span class="font-medium text-gray-700 text-sm">
                                                    <div>{{ $espacio['datos_profesor']['nombre_asignatura'] ?? $asignatura ?? 'Sin asignatura' }}</div>
                                                    <div>Profesor: {{ $espacio['datos_profesor']['nombre'] ?? 'N/A' }}</div>
                                                </span>
                                            @elseif (($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']) && isset($espacio['datos_clase']['profesor']) && !empty($espacio['datos_clase']['profesor']['name']))
                                                <div class="font-medium text-gray-900 text-sm">
                                                    <div>{{ $asignatura ?? 'Sin asignatura' }}</div>
                                                    <div>Prof: {{ $espacio['datos_clase']['profesor']['name'] ?? 'N/A' }}</div>
                                                </div>
                                            @elseif(!empty($espacio['proxima_clase']) && is_array($espacio['proxima_clase']))
                                                <div class="font-medium text-gray-700 text-sm">
                                                    <div>Próxima: {{ $asignatura ?? 'Clase programada' }}</div>
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
                                            @if (($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']['carrera']))
                                                <span class="font-medium text-gray-700 text-sm">{{ $espacio['datos_clase']['carrera'] }}</span>
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
                                                <span class="text-gray-400 text-sm">N/A</span>
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
<div class="p-6" x-data="{ pagina: 0, totalPaginas: Math.ceil({{ count($this->getTodosLosEspacios()) }} / 13) }" x-init="setInterval(() => { pagina = (pagina + 1) % totalPaginas }, 5000)">
    @if (count($pisos) > 0)
        
    <!-- Indicador de página -->
    @if (count($this->getTodosLosEspacios()) > 13)
        <div class="mt-1 text-center p-2">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg border">
                <span class="text-sm font-medium text-gray-600">Página</span>
                <span class="px-2 py-1 bg-blue-600 text-white text-sm font-bold rounded" x-text="pagina + 1"></span>
                <span class="text-sm text-gray-600">de</span>
                <span class="px-2 py-1 bg-gray-200 text-gray-700 text-sm font-medium rounded" x-text="totalPaginas"></span>
            </div>
        </div>
    @endif
    
        <div class="relative w-full fixed bg-gray-100 rounded-lg shadow-sm border border-gray-300 overflow-hidden">
            @if (count($this->getTodosLosEspacios()) > 0)
                @php $totalPaginas = ceil(count($this->getTodosLosEspacios()) / 13); @endphp
                @for ($i = 0; $i < $totalPaginas; $i++)
                    <div x-show="pagina === {{ $i }}" class="transition-all duration-500">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-red-600 text-white border-b border-gray-300">
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300 w-1/4">Modulo</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300 w-1/6">Espacio</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider border-r border-gray-300 w-5/12">Clase</th>
                                    <th class="px-3 py-1 text-left text-sm font-semibold uppercase tracking-wider w-1/6">Status</th>
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
                                                           {{ $espacio['datos_clase']['modulo_inicio'] }} - {{ $espacio['datos_clase']['modulo_fin'] }}
                                                          
                                                        </div>
                                                        <div class="text-gray-600">
                                                             
                                                            {{ $espacio['datos_clase']['hora_inicio'] ?? '--:--' }} - {{ $espacio['datos_clase']['hora_fin'] ?? '--:--' }}
                                                        </div>
                                                    </div>
                                                @elseif(!empty($espacio['proxima_clase']))
                                                     <div class="flex items-center gap-2 text-base font-semibold">
                                                                         <i class="fas fa-clock"></i>
                                                           {{ $espacio['proxima_clase']['modulo_inicio'] }} - {{ $espacio['proxima_clase']['modulo_fin'] }}
                                                        </div>
                                                        <div class="text-gray-600">
                                                            {{ $espacio['proxima_clase']['hora_inicio'] }} - {{ $espacio['proxima_clase']['hora_fin'] }}
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
                                            @if (($espacio['tiene_reserva_solicitante'] ?? false) && !empty($espacio['datos_solicitante']))
                                                <span class="font-medium text--700 text-sm">Solicitante: {{ $espacio['datos_solicitante']['nombre'] }}</span>
                                            @elseif (($espacio['tiene_reserva_profesor'] ?? false) && !empty($espacio['datos_profesor']) && !empty($espacio['datos_profesor']['nombre']))
                                                <span class="font-medium text-gray-700 text-sm">
                                                    <div><div>{{ $espacio['datos_clase']['nombre_asignatura'] }}</div>
                                                    <div>Profesor: {{ $espacio['datos_profesor']['nombre'] }}</div>

                                                </span>
                                            @elseif (($espacio['tiene_clase'] ?? false) && !empty($espacio['datos_clase']) && isset($espacio['datos_clase']['profesor']) && !empty($espacio['datos_clase']['profesor']['name']))
                                                <div class="font-medium text-gray-900 text-sm">
                                                    <div>{{ $espacio['datos_clase']['nombre_asignatura'] }}</div>
                                                    <div>Prof: {{ $espacio['datos_clase']['profesor']['name'] }}</div>
                                                </div>
                                            @elseif(!empty($espacio['proxima_clase']))
                                                <div class="font-medium text-gray-700 text-sm">
                                                    <div>Próxima: {{ $espacio['proxima_clase']['nombre_asignatura'] ?? 'Clase programada' }}</div>
                                                    <div>Prof: {{ $espacio['proxima_clase']['profesor'] ?? '-' }}</div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-sm">-</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Columna 4: Status -->
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
        // Actualizar datos cada 10 segundos
        setInterval(function() {
            @this.actualizarAutomaticamente();
        }, 10000);
    </script>
</div>
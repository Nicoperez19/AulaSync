<div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-16 px-2 py-3 font-medium text-left text-gray-500 uppercase text-m">Módulo</th>
                        <th class="w-20 px-2 py-3 font-medium text-center text-gray-500 uppercase text-m">Código</th>
                        <th class="w-32 px-2 py-3 font-medium text-center text-gray-500 uppercase text-m">Asignatura</th>
                        <th class="w-16 px-2 py-3 font-medium text-center text-gray-500 uppercase text-m">Sección</th>
                        <th class="w-24 px-2 py-3 font-medium text-center text-gray-500 uppercase text-m">Sala</th>
                        <th class="px-2 py-3 font-medium text-center text-gray-500 uppercase text-m w-28">Profesor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-300">
                    @if(count($planificaciones) > 0)
                        @foreach($planificaciones as $planificacion)
                                                    @php
                            $estadoEspacio = $planificacion['espacio']['estado'] ?? 'Disponible';
                            $colorFila = '';
                            $colorTexto = '';
                            
                            switch($estadoEspacio) {
                                case 'Ocupado':
                                    $colorFila = 'bg-red-50';
                                    $colorTexto = 'text-red-700';
                                    break;
                                case 'Reservado':
                                    $colorFila = 'bg-yellow-50';
                                    $colorTexto = 'text-yellow-700';
                                    break;
                                case 'Disponible':
                                default:
                                    $colorFila = 'bg-green-50';
                                    $colorTexto = 'text-green-700';
                                    break;
                            }
                        @endphp
                        <tr class="{{ $colorFila }} hover:{{ str_replace('bg-50', 'bg-100', $colorFila) }} transition-colors duration-200 border-b border-gray-200">
                            <td class="px-2 py-3 font-bold text-left text-blue-700">
                                    {{ $planificacion['modulo']['numero_modulo'] }}
                                    <div class="flex items-center mt-1 text-center text-gray-500 text-m">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $planificacion['modulo']['hora_inicio'] }} - {{ $planificacion['modulo']['hora_termino'] }}
                                    </div>
                                </td>

                                <td class="px-2 py-3 font-bold text-center text-blue-700 text-m">
                                    {{ $planificacion['asignatura']['codigo_asignatura'] }}
                                </td>

                                <td class="px-2 py-3 text-center text-m">
                                    {{ $planificacion['asignatura']['nombre_asignatura'] }}
                                </td>

                                <td class="px-2 py-3 text-center text-m">
                                    {{ $planificacion['asignatura']['seccion'] }}
                                </td>
                                
                                <td class="px-2 py-3 font-bold text-center text-blue-700 text-m">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="inline-block w-2 h-2 rounded-full 
                                            {{ $estadoEspacio === 'Ocupado' ? 'bg-red-500' : 
                                               ($estadoEspacio === 'Reservado' ? 'bg-yellow-500' : 'bg-green-500') }}">
                                        </span>
                                        {{ $planificacion['espacio']['nombre_espacio'] }} ({{ $planificacion['espacio']['id_espacio'] }})
                                    </div>
                                </td>

                                <td class="px-2 py-3">
                                    {{ $planificacion['asignatura']['profesor']['name'] }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500 text-m">
                                No hay actividades disponibles en el módulo actual.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    
    <!-- Leyenda de estados -->
    <div class="mt-4 pt-3 border-t border-gray-200">
        <div class="flex items-center justify-center gap-4 text-xs">
            <div class="flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                <span class="text-gray-600">Disponible</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span>
                <span class="text-gray-600">Reservado</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                <span class="text-gray-600">Ocupado</span>
            </div>
        </div>
    </div>

    <script>
        // Actualizar datos cada 30 segundos
        setInterval(function() {
            @this.actualizarAutomaticamente();
        }, 30000);
    </script>
</div> 
@if(!empty($horariosAgrupados))
    <div class="space-y-6">
        @foreach($horariosAgrupados as $dia => $horarios)
            <div class="p-4 border rounded-lg" style="background-color: #f3f4f6;">
                <h4 class="flex items-center mb-3 text-lg font-semibold text-black">
                    <i class="mr-2 fas fa-calendar-day" style="color: #8C0303;"></i>
                    Día: {{ $dia }}
                </h4>
                
                @foreach($horarios as $hora => $datosModulo)
                    <div class="mb-4">
                        <h5 class="flex items-center mb-2 font-medium text-md text-black">
                            <i class="mr-2 fas fa-clock" style="color: #8C0303;"></i>
                            Módulo: {{ $datosModulo['numero_modulo'] }} ({{ $hora }})
                        </h5>
                        
                        @if(count($datosModulo['espacios']) > 0)
                        <div class="grid grid-cols-1 gap-3 text-center md:grid-cols-2 lg:grid-cols-4">
                            @foreach($datosModulo['espacios'] as $espacio)
                                <div class="p-3 transition-shadow border rounded-lg shadow-sm hover:shadow-md bg-white border-gray-400">
                                    <div class="mb-2 text-lg font-semibold text-black">
                                        <i class="mr-1 fas fa-building" style="color: #8C0303;"></i>
                                        {{ $espacio['espacio'] }}
                                    </div>
                                    <div class="mb-1 text-sm text-black">
                                        <i class="mr-1 fas fa-book" style="color: #8C0303;"></i>
                                        <span class="font-medium">{{ $espacio['asignatura'] }}</span>
                                    </div>
                                    <div class="mb-1 text-sm text-black">
                                        <i class="mr-1 fas fa-user" style="color: #8C0303;"></i>
                                        <span class="font-medium">{{ $espacio['profesor'] }}</span>
                                    </div>
                                    @if($espacio['email'] !== 'No disponible')
                                        <div class="text-xs text-gray-700">
                                            <i class="mr-1 fas fa-envelope" style="color: #8C0303;"></i>
                                            {{ $espacio['email'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @else
                        <div class="p-4 mt-2 text-center bg-white border rounded-lg text-gray-600 border-gray-400">
                            <i class="mr-2 fas fa-info-circle" style="color: #8C0303;"></i>
                            No hay horarios asignados al módulo actual.
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@else
    <div class="py-8 text-center text-gray-500">
        <i class="mb-4 text-4xl fas fa-calendar-times"></i>
        <p class="text-lg">No hay horarios programados para esta semana.</p>
        <p class="mt-2 text-sm">Los horarios se mostrarán aquí cuando se programen asignaturas para los espacios.</p>
    </div>
@endif

@if(isset($horariosPorTipoDiaModulo) && !empty($horariosPorTipoDiaModulo))
    <div class="overflow-x-auto">
        <table class="min-w-full text-center border border-gray-300 rounded-lg">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-2 py-1 border">Tipo de Espacio</th>
                    @php
                        $dias = array_keys(reset($horariosPorTipoDiaModulo));
                        $modulos = [];
                        foreach ($dias as $dia) {
                            $modulos = array_unique(array_merge($modulos, array_keys(reset($horariosPorTipoDiaModulo)[$dia])));
                        }
                        sort($modulos);
                    @endphp
                    @foreach($dias as $dia)
                        @foreach($modulos as $modulo)
                            <th class="px-2 py-1 border">{{ $dia }}<br>Mód {{ $modulo }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($horariosPorTipoDiaModulo as $tipo => $diasData)
                    <tr>
                        <td class="font-bold border bg-gray-50">{{ $tipo }}</td>
                        @foreach($dias as $dia)
                            @foreach($modulos as $modulo)
                                <td class="border {{ ($diasData[$dia][$modulo] ?? 0) > 70 ? 'bg-green-200' : (($diasData[$dia][$modulo] ?? 0) > 40 ? 'bg-yellow-200' : 'bg-red-100') }}">
                                    {{ $diasData[$dia][$modulo] ?? 0 }}%
                                </td>
                            @endforeach
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif 
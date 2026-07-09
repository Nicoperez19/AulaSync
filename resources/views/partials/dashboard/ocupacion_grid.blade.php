<table class="w-full border-collapse table-fixed">
    <thead>
        <tr class="text-sm font-extrabold text-slate-500 uppercase tracking-wider border-b border-gray-100 pb-2">
            <th class="py-3 px-1 text-center w-24">Módulo</th>
            <th class="py-3 px-1 text-center">Lunes</th>
            <th class="py-3 px-1 text-center">Martes</th>
            <th class="py-3 px-1 text-center">Miércoles</th>
            <th class="py-3 px-1 text-center">Jueves</th>
            <th class="py-3 px-1 text-center">Viernes</th>
            <th class="py-3 px-1 text-center">Sábado</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++)
            <tr class="hover:bg-slate-50/50 transition duration-150">
                <!-- Información del Módulo -->
                <td class="py-1.5 px-1 text-center">
                    <div class="h-16 w-full bg-slate-50 border border-slate-200 rounded-xl flex flex-col items-center justify-center shadow-xs">
                        <span class="text-sm md:text-base font-extrabold text-slate-700">Mod.{{ $moduloNum }}</span>
                        <span class="text-[10px] md:text-xs font-semibold text-slate-400 mt-0.5">
                            {{ substr(\App\Helpers\ModulosHelper::getHorarioModulo('lunes', $moduloNum)['inicio'] ?? '00:00', 0, 5) }}-{{ substr(\App\Helpers\ModulosHelper::getHorarioModulo('lunes', $moduloNum)['fin'] ?? '00:00', 0, 5) }}
                        </span>
                    </div>
                </td>
                
                <!-- Días de la semana -->
                @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia)
                    @php
                        $prefijoDia = [
                            'lunes' => 'LU',
                            'martes' => 'MA',
                            'miercoles' => 'MI',
                            'jueves' => 'JU',
                            'viernes' => 'VI',
                            'sabado' => 'SA',
                        ][$dia];
                        
                        $isSaturdayNA = ($dia === 'sabado' && $moduloNum > 5);
                        $porcentaje = $isSaturdayNA ? null : ($ocupacion[$dia][$moduloNum] ?? 0);
                        
                        if ($porcentaje !== null) {
                            if ($porcentaje <= 35) {
                                $colorClass = 'text-emerald-500';
                            } elseif ($porcentaje <= 75) {
                                $colorClass = 'text-amber-500';
                            } else {
                                $colorClass = 'text-red-500';
                            }
                        }
                    @endphp
                    
                    <td class="py-1.5 px-1 text-center">
                        @if ($porcentaje === null)
                            <div class="h-16 w-full border border-dashed border-gray-200 rounded-xl flex flex-col items-center justify-center bg-gray-50/50 text-gray-300">
                                <span class="text-xs md:text-sm font-bold">N/A</span>
                            </div>
                        @else
                            <div class="h-16 w-full border border-gray-200 rounded-xl flex flex-col items-center justify-center bg-white shadow-xs hover:border-blue-200 transition duration-150">
                                <span class="text-[10px] md:text-xs font-bold text-gray-400 uppercase">{{ $prefijoDia }}.{{ $moduloNum }}</span>
                                <span class="text-base md:text-xl font-extrabold {{ $colorClass }} mt-0.5">{{ $porcentaje }}%</span>
                            </div>
                        @endif
                    </td>
                @endforeach
            </tr>
        @endfor
    </tbody>
</table>

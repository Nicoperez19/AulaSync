<div class="overflow-x-auto w-full">
    <table class="w-full table-fixed border-separate border-spacing-1">
        <thead>
            <tr class="text-xs font-extrabold text-slate-600 uppercase tracking-wider">
                <th class="py-2 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[14.28%]">Módulo</th>
                <th class="py-2 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[14.28%]">Lunes</th>
                <th class="py-2 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[14.28%]">Martes</th>
                <th class="py-2 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[14.28%]">Miércoles</th>
                <th class="py-2 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[14.28%]">Jueves</th>
                <th class="py-2 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[14.28%]">Viernes</th>
                <th class="py-2 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[14.28%]">Sábado</th>
            </tr>
        </thead>
        <tbody>
            @for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++)
                <tr>
                    <!-- Información del Módulo -->
                    <td class="p-0 text-center w-[14.28%]">
                        <div class="h-10 w-full bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center px-1">
                            <span class="text-[11px] md:text-xs font-bold text-slate-700 whitespace-nowrap">
                                Mód. {{ $moduloNum }} ({{ substr(\App\Helpers\ModulosHelper::getHorarioModulo('lunes', $moduloNum)['inicio'] ?? '00:00', 0, 5) }} - {{ substr(\App\Helpers\ModulosHelper::getHorarioModulo('lunes', $moduloNum)['fin'] ?? '00:00', 0, 5) }})
                            </span>
                        </div>
                    </td>
                    
                    <!-- Días de la semana -->
                    @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia)
                        @php
                            $isSaturdayNA = ($dia === 'sabado' && $moduloNum > 5);
                            $porcentaje = $isSaturdayNA ? null : ($ocupacion[$dia][$moduloNum] ?? 0);
                            
                            if ($porcentaje !== null) {
                                if ($porcentaje <= 35) {
                                    // Verde suave / leve con texto verde oscuro
                                    $cellBg = 'bg-emerald-100 border border-emerald-300/70 text-emerald-800';
                                } elseif ($porcentaje <= 75) {
                                    // Ámbar suave / leve con texto ámbar oscuro
                                    $cellBg = 'bg-amber-100 border border-amber-300/70 text-amber-900';
                                } else {
                                    // Rojo suave / leve con texto rojo oscuro
                                    $cellBg = 'bg-red-100 border border-red-300/70 text-red-800';
                                }
                            }
                        @endphp
                        
                        <td class="p-0 text-center w-[14.28%]">
                            @if ($porcentaje === null)
                                <div class="h-10 w-full rounded-lg flex items-center justify-center bg-slate-50 border border-dashed border-slate-200 text-slate-400">
                                    <span class="text-xs font-medium">N/A</span>
                                </div>
                            @else
                                <div class="h-10 w-full rounded-lg flex items-center justify-center {{ $cellBg }} transition duration-150 transform hover:scale-[1.01] cursor-default">
                                    <span class="text-sm md:text-base font-extrabold tracking-tight">{{ $porcentaje }}%</span>
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endfor
        </tbody>
    </table>
</div>

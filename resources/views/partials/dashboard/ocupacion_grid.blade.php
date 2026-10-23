<div class="overflow-x-auto w-full pb-2">
    <table class="w-full min-w-[720px] md:min-w-[780px] table-fixed border-separate border-spacing-1.5">
        <thead>
            <tr class="text-xs font-extrabold text-slate-600 uppercase tracking-wider">
                <th class="py-2.5 px-2 text-center bg-slate-100 border border-slate-200 rounded-lg w-[19%] sm:w-[17%] md:w-[16%]">Módulo</th>
                <th class="py-2.5 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[13.5%] sm:w-[13.83%] md:w-[14%]">Lunes</th>
                <th class="py-2.5 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[13.5%] sm:w-[13.83%] md:w-[14%]">Martes</th>
                <th class="py-2.5 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[13.5%] sm:w-[13.83%] md:w-[14%]">Miércoles</th>
                <th class="py-2.5 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[13.5%] sm:w-[13.83%] md:w-[14%]">Jueves</th>
                <th class="py-2.5 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[13.5%] sm:w-[13.83%] md:w-[14%]">Viernes</th>
                <th class="py-2.5 px-1 text-center bg-slate-100 border border-slate-200 rounded-lg w-[13.5%] sm:w-[13.83%] md:w-[14%]">Sábado</th>
            </tr>
        </thead>
        <tbody>
            @for ($moduloNum = 1; $moduloNum <= 15; $moduloNum++)
                @php
                    $horario = \App\Helpers\ModulosHelper::getHorarioModulo('lunes', $moduloNum);
                    $inicio = substr($horario['inicio'] ?? '00:00', 0, 5);
                    $fin = substr($horario['fin'] ?? '00:00', 0, 5);
                @endphp
                <tr>
                    <!-- Información del Módulo -->
                    <td class="p-0 text-center">
                        <div class="h-10 w-full bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center px-1.5 shadow-2xs">
                            <span class="text-[11px] sm:text-xs font-bold text-slate-700 whitespace-nowrap">
                                Mód. {{ $moduloNum }} <span class="text-slate-500 font-medium text-[10px] sm:text-[11px]">({{ $inicio }} - {{ $fin }})</span>
                            </span>
                        </div>
                    </td>
                    
                    <!-- Días de la semana -->
                    @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia)
                        @php
                            $isSaturdayNA = ($dia === 'sabado' && $moduloNum > 6);
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
                        
                        <td class="p-0 text-center">
                            @if ($porcentaje === null)
                                <div class="h-10 w-full rounded-lg flex items-center justify-center bg-slate-50 border border-dashed border-slate-200 text-slate-400">
                                    <span class="text-xs font-medium">N/A</span>
                                </div>
                            @else
                                <div class="h-10 w-full rounded-lg flex items-center justify-center {{ $cellBg }} shadow-2xs transition duration-150 transform hover:scale-[1.01] cursor-default">
                                    <span class="text-xs sm:text-sm md:text-base font-extrabold tracking-tight">{{ $porcentaje }}%</span>
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endfor
        </tbody>
    </table>
</div>

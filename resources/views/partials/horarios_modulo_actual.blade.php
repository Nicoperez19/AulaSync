@php
// Colores por tipo de espacio (puedes ajustar estos valores)
$coloresTipo = [
    'Aula' => 'bg-blue-500',
    'Laboratorio' => 'bg-orange-500',
    'Auditorio' => 'bg-green-500',
    'Sala' => 'bg-purple-500',
    'Otro' => 'bg-gray-400',
];
@endphp
@if(!$moduloActualNum)
    @php
        // Durante break, intentar obtener el próximo módulo
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diaActual = $dias[Carbon\Carbon::now()->dayOfWeek];
        $horaActual = Carbon\Carbon::now()->format('H:i:s');
        
        $horariosDelDia = [
            'lunes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'martes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'miercoles' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'jueves' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'viernes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ]
        ];
        
        $proximoModulo = null;
        $proximoHorario = null;
        
        if (isset($horariosDelDia[$diaActual])) {
            foreach ($horariosDelDia[$diaActual] as $numeroModulo => $horario) {
                if ($horaActual < $horario['inicio']) {
                    $proximoModulo = $numeroModulo;
                    $proximoHorario = $horario;
                    break;
                }
            }
        }
    @endphp
    
    @if($proximoModulo)
        <div class="mb-2 flex items-center gap-2 text-lg font-semibold text-orange-600">
            <i class="fas fa-coffee"></i>
            {{ ucfirst($diaActual) }} - En Break - Próximo Módulo {{ $proximoModulo }} ({{ substr($proximoHorario['inicio'],0,5) }} - {{ substr($proximoHorario['fin'],0,5) }})
        </div>
        <div class="text-gray-500 mb-4">Preparándose para el próximo módulo</div>
    @else
        <div class="text-gray-500 text-center py-8">No hay módulo actual en este momento.</div>
    @endif

    @else
    <div class="mb-2 flex items-center gap-2 text-lg font-semibold">
        <i class="fas fa-clock"></i>
        {{ ucfirst($diaActual) }} - Módulo Actual {{ $moduloActualNum }} ({{ substr($moduloActualHorario['inicio'],0,5) }} - {{ substr($moduloActualHorario['fin'],0,5) }})
    </div>
    <div class="text-gray-500 mb-4">Usuarios asignados por espacio</div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($asignaciones as $asig)
            @php
                $tipo = $asig->espacio->tipo_espacio ?? 'Otro';
                $color = $coloresTipo[$tipo] ?? $coloresTipo['Otro'];
            @endphp
            <div class="rounded-lg border border-gray-200 bg-white p-4 flex flex-col gap-2 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block w-3 h-3 rounded-full {{ $color }}"> aaa< /span>
                    <span class="font-bold text-base">{{ $asig->espacio->id_espacio }}</span>
                    <span class="text-xs text-gray-500 ml-2">Piso {{ $asig->espacio->piso->numero_piso ?? '-' }}</span>
                </div>
                <div class="font-semibold text-sm text-gray-800">{{ $asig->asignatura->nombre_asignatura ?? '-' }}</div>
                <div class="text-xs text-gray-500">{{ $asig->asignatura->profesor->name ?? '-' }}</div>
                <div class="flex items-center gap-1 text-xs text-gray-500">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $asig->asignatura->profesor->email ?? '-' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 py-8">No hay asignaciones para este módulo.</div>
        @endforelse
    </div>
@endif 
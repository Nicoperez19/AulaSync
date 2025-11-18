<?php

namespace App\Console\Commands;

use App\Helpers\SemesterHelper;
use App\Models\ClaseNoRealizada;
use App\Models\Notificacion;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DetectarClasesNoRealizadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clases:detectar-no-realizadas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta automáticamente las clases que no fueron realizadas cuando todos los módulos programados han finalizado';

    // Horarios de módulos
    private $horariosModulos = [
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando detección de clases no realizadas...');

        $hoy = Carbon::now();
        $diaActual = $hoy->locale('es')->isoFormat('dddd');
        $horaActual = $hoy->format('H:i:s');
        $fechaActual = $hoy->toDateString();

        // Solo ejecutar en días laborales
        if ($diaActual === 'sábado' || $diaActual === 'domingo') {
            $this->info('Hoy es fin de semana, no se ejecuta la detección.');

            return 0;
        }

        // Obtener el período actual
        $periodo = SemesterHelper::getCurrentPeriod();

        // Mapear el día a su prefijo
        $prefijoDia = $this->obtenerPrefijoDia($diaActual);
        if (! $prefijoDia) {
            $this->error('No se pudo determinar el prefijo del día.');

            return 1;
        }

        // Obtener todas las planificaciones del día actual
        $planificaciones = Planificacion_Asignatura::with(['asignatura.profesor', 'espacio'])
            ->where('id_modulo', 'LIKE', $prefijoDia.'.%')
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get()
            ->groupBy('id_asignatura');

        $clasesDetectadas = 0;

        foreach ($planificaciones as $idAsignatura => $planificacionesAsignatura) {
            // Ordenar por módulo para obtener el último
            $planificacionesOrdenadas = $planificacionesAsignatura->sortBy(function ($plan) {
                $moduloParts = explode('.', $plan->id_modulo);

                return isset($moduloParts[1]) ? (int) $moduloParts[1] : 0;
            });

            $ultimaPlanificacion = $planificacionesOrdenadas->last();
            $primeraPlanificacion = $planificacionesOrdenadas->first();

            if (! $ultimaPlanificacion || ! $primeraPlanificacion) {
                continue;
            }

            // Obtener el número del último módulo
            $ultimoModuloParts = explode('.', $ultimaPlanificacion->id_modulo);
            $numeroUltimoModulo = isset($ultimoModuloParts[1]) ? (int) $ultimoModuloParts[1] : 0;

            if (! $numeroUltimoModulo) {
                continue;
            }

            // Verificar si el último módulo ya terminó
            $diaKey = $this->normalizarDia($diaActual);
            $horariosDelDia = $this->horariosModulos[$diaKey] ?? null;

            if (! $horariosDelDia || ! isset($horariosDelDia[$numeroUltimoModulo])) {
                continue;
            }

            $horaFinUltimoModulo = $horariosDelDia[$numeroUltimoModulo]['fin'];

            // Si el último módulo aún no ha terminado, saltar
            if ($horaActual < $horaFinUltimoModulo) {
                continue;
            }

            // Verificar si el profesor registró entrada en algún espacio
            $runProfesor = $ultimaPlanificacion->asignatura->run_profesor ?? null;
            if (! $runProfesor) {
                continue;
            }

            $tuvoEntrada = Reserva::where('fecha_reserva', $fechaActual)
                ->where('run_profesor', $runProfesor)
                ->whereNotNull('hora')
                ->exists();

            // Si el profesor NO registró entrada, marcar como clase no realizada
            if (! $tuvoEntrada) {
                // Registrar la clase no realizada
                $claseNoRealizada = ClaseNoRealizada::registrarClaseNoRealizada([
                    'id_asignatura' => $idAsignatura,
                    'id_espacio' => $primeraPlanificacion->id_espacio,
                    'id_modulo' => $primeraPlanificacion->id_modulo,
                    'run_profesor' => $runProfesor,
                    'fecha_clase' => $fechaActual,
                    'periodo' => $periodo,
                    'motivo' => 'No se registró ingreso del profesor durante toda la clase (detección automática)',
                ]);

                if ($claseNoRealizada && $claseNoRealizada->wasRecentlyCreated) {
                    // Crear notificación para supervisores y administradores
                    Notificacion::crearNotificacionClaseNoRealizada($claseNoRealizada);
                    $clasesDetectadas++;

                    $this->info(sprintf(
                        'Clase no realizada detectada: %s - Profesor: %s',
                        $ultimaPlanificacion->asignatura->nombre_asignatura ?? 'Desconocida',
                        $ultimaPlanificacion->asignatura->profesor->name ?? 'Desconocido'
                    ));
                }
            }
        }

        $this->info(sprintf('Detección finalizada. Total de clases no realizadas detectadas: %d', $clasesDetectadas));
        Log::info(sprintf('Comando DetectarClasesNoRealizadas ejecutado. Clases detectadas: %d', $clasesDetectadas));

        return 0;
    }

    /**
     * Obtener el prefijo del día
     */
    private function obtenerPrefijoDia($diaActual)
    {
        $mapaDias = [
            'lunes' => 'LU',
            'martes' => 'MA',
            'miércoles' => 'MI',
            'miercoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
        ];

        return $mapaDias[strtolower($diaActual)] ?? null;
    }

    /**
     * Normalizar el nombre del día
     */
    private function normalizarDia($diaActual)
    {
        $mapaDias = [
            'lunes' => 'lunes',
            'martes' => 'martes',
            'miércoles' => 'miercoles',
            'miercoles' => 'miercoles',
            'jueves' => 'jueves',
            'viernes' => 'viernes',
        ];

        return $mapaDias[strtolower($diaActual)] ?? strtolower($diaActual);
    }
}

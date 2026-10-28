<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ClaseNoRealizada extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected $connection = 'tenant';

    protected $table = 'clases_no_realizadas';

    protected $fillable = [
        'id_asignatura',
        'id_espacio',
        'id_modulo',
        'run_profesor',
        'fecha_clase',
        'periodo',
        'motivo',
        'observaciones',
        'estado',
        'hora_deteccion',
    ];

    protected $casts = [
        'fecha_clase' => 'date',
        'hora_deteccion' => 'datetime',
    ];

    // Relaciones
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    // Scopes
    public function scopePorProfesor($query, $runProfesor)
    {
        return $query->where('run_profesor', $runProfesor);
    }

    public function scopePorPeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_clase', [$fechaInicio, $fechaFin]);
        }

        return $query->whereDate('fecha_clase', $fechaInicio);
    }

    public function scopeNoRealizadas($query)
    {
        return $query->where('estado', 'no_realizada');
    }

    // Métodos estáticos
    public static function registrarClaseNoRealizada($datosClase)
    {
        // Verificar si la fecha es un día feriado o sin actividades
        if (\App\Models\DiaFeriado::esFeriado($datosClase['fecha_clase'])) {
            // Si es feriado, registrar como justificado automáticamente
            $registroExistente = static::where('id_asignatura', $datosClase['id_asignatura'])
                ->where('id_espacio', $datosClase['id_espacio'])
                ->where('id_modulo', $datosClase['id_modulo'])
                ->where('fecha_clase', $datosClase['fecha_clase'])
                ->where('run_profesor', $datosClase['run_profesor'])
                ->first();

            if (! $registroExistente) {
                $feriado = \App\Models\DiaFeriado::obtenerFeriadoEnFecha($datosClase['fecha_clase']);

                return static::create([
                    'id_asignatura' => $datosClase['id_asignatura'],
                    'id_espacio' => $datosClase['id_espacio'],
                    'id_modulo' => $datosClase['id_modulo'],
                    'fecha_clase' => $datosClase['fecha_clase'],
                    'run_profesor' => $datosClase['run_profesor'],
                    'periodo' => $datosClase['periodo'],
                    'motivo' => $feriado ? $feriado->nombre : 'Día sin actividades',
                    'observaciones' => 'Justificado automáticamente - '.($feriado ? ($feriado->descripcion ?? 'Sin descripción') : 'Periodo sin actividad universitaria'),
                    'estado' => 'justificado',
                    'hora_deteccion' => Carbon::now(),
                ]);
            }

            return $registroExistente;
        }

        // Verificar si ya existe un registro para esta clase HOY
        $registroExistente = static::where('id_asignatura', $datosClase['id_asignatura'])
            ->where('id_espacio', $datosClase['id_espacio'])
            ->where('id_modulo', $datosClase['id_modulo'])
            ->where('fecha_clase', $datosClase['fecha_clase'])
            ->where('run_profesor', $datosClase['run_profesor'])
            ->first();

        // Si ya existe, no crear duplicado
        if ($registroExistente) {
            return $registroExistente;
        }

        // Antes de crear, verificar si es un espacio externo o si ya tiene lista de asistencia o reserva en sala paralela/co-docente
        $esRetiroAnticipado = str_contains(strtolower($datosClase['motivo'] ?? ''), 'retiro anticipado');
        $ignorarEntrada = $datosClase['ignorar_entrada'] ?? false;

        if (!empty($datosClase['id_espacio'])) {
            $espacioModel = \App\Models\Espacio::where('id_espacio', $datosClase['id_espacio'])->first();
            if ($espacioModel && method_exists($espacioModel, 'esEspacioExterno') && $espacioModel->esEspacioExterno()) {
                return null;
            }
        }

        if (!$esRetiroAnticipado && !$ignorarEntrada) {
            $runBuscado = $datosClase['run_profesor'] ?? null;
            $idAsigBuscada = $datosClase['id_asignatura'] ?? null;

            // Recopilar RUNs válidos (docente titular y colaboradores de la misma asignatura)
            $runsValidos = $runBuscado ? [$runBuscado] : [];
            if ($idAsigBuscada) {
                $asigObj = \App\Models\Asignatura::where('id_asignatura', $idAsigBuscada)->first();
                if ($asigObj && $asigObj->run_profesor) {
                    $runsValidos[] = $asigObj->run_profesor;
                }
                if ($asigObj && $asigObj->run_profesor_reemplazo) {
                    $runsValidos[] = $asigObj->run_profesor_reemplazo;
                }
                $colabsRuns = \App\Models\ProfesorColaborador::where('id_asignatura', $idAsigBuscada)
                    ->pluck('run_profesor_colaborador')
                    ->filter()
                    ->all();
                $runsValidos = array_values(array_unique(array_merge($runsValidos, $colabsRuns)));
            }

            // 1. Verificar entrada en el mismo espacio o en sala paralela/alternativa para la misma asignatura/docente
            $tuvoEntrada = \App\Models\Reserva::where('fecha_reserva', $datosClase['fecha_clase'])
                ->whereNotNull('hora')
                ->where(function ($q) use ($datosClase, $runsValidos, $idAsigBuscada) {
                    // En el mismo espacio por cualquiera de los docentes de la asignatura
                    $q->where(function ($sub) use ($datosClase, $runsValidos) {
                        $sub->where('id_espacio', $datosClase['id_espacio']);
                        if (!empty($runsValidos)) {
                            $sub->where(function ($rq) use ($runsValidos) {
                                $rq->whereIn('run_profesor', $runsValidos)
                                   ->orWhereIn('run_solicitante', $runsValidos);
                            });
                        } else {
                            $sub->whereNotNull('run_profesor');
                        }
                    });

                    // O en otro espacio (ej. sala paralela 215/212 o laboratorio alternativo) para la misma asignatura
                    if ($idAsigBuscada && !empty($runsValidos)) {
                        $q->orWhere(function ($alt) use ($idAsigBuscada, $runsValidos) {
                            $alt->where('id_asignatura', $idAsigBuscada)
                                ->where(function ($rq) use ($runsValidos) {
                                    $rq->whereIn('run_profesor', $runsValidos)
                                       ->orWhereIn('run_solicitante', $runsValidos);
                                });
                        });
                    }
                })
                ->exists();

            if ($tuvoEntrada) {
                return null;
            }

            // 2. Verificar si existe respaldo de lista de asistencia en el Portal Docente
            try {
                if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('sesiones_asistencia')) {
                    $tieneSesionAsistencia = \App\Models\SesionAsistencia::whereDate('fecha', $datosClase['fecha_clase'])
                        ->where(function ($sq) use ($idAsigBuscada, $runsValidos) {
                            if ($idAsigBuscada) {
                                $sq->where('id_asignatura', $idAsigBuscada);
                            } elseif (!empty($runsValidos)) {
                                $sq->whereIn('run_profesor', $runsValidos);
                            }
                        })
                        ->exists();

                    if ($tieneSesionAsistencia) {
                        return null;
                    }
                }
            } catch (\Throwable $e) {
                // Ignorar si no está disponible la tabla
            }
        }

        // Crear el nuevo registro
        return static::create([
            'id_asignatura' => $datosClase['id_asignatura'],
            'id_espacio' => $datosClase['id_espacio'],
            'id_modulo' => $datosClase['id_modulo'],
            'fecha_clase' => $datosClase['fecha_clase'],
            'run_profesor' => $datosClase['run_profesor'],
            'periodo' => $datosClase['periodo'],
            'motivo' => $datosClase['motivo'] ?? 'No se registró ingreso en el primer módulo',
            'observaciones' => $datosClase['observaciones'] ?? null,
            'estado' => 'no_realizada',
            'hora_deteccion' => Carbon::now(),
        ]);
    }

    /**
     * Limpiar registros incorrectos cuando un profesor registra entrada tarde
     * Se llama cuando se crea una reserva de profesor
     * Ahora mueve los registros a profesor_atrasos en lugar de marcar como justificado
     * 
     * Elimina TODOS los registros de clases_no_realizadas para esa clase/fecha/espacio
     * porque si el profesor llegó (aunque tarde), la clase SÍ se realizó.
     */
    public static function limpiarRegistrosIncorrectos($idEspacio, $fechaReserva, $horaEntrada = null, $runProfesor = null)
    {
        // Primero ejecutar sincronización integral de recuperaciones con reservas para la fecha
        try {
            static::sincronizarRecuperacionesConReservas($fechaReserva, $fechaReserva);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Error en sincronizarRecuperacionesConReservas: " . $e->getMessage());
        }

        // Buscar registros de hoy para este espacio o salas paralelas del mismo docente en esa fecha.
        $query = static::whereDate('fecha_clase', $fechaReserva);

        if ($runProfesor) {
            $runLimpio = preg_replace('/[^0-9kK]/', '', $runProfesor);
            $query->where(function($q) use ($runProfesor, $runLimpio) {
                $q->where('run_profesor', $runProfesor)
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
            });
        } else {
            $query->where('id_espacio', $idEspacio);
        }

        $registros = $query->get();

        // Identificar qué módulos corresponden a una recuperación reagendada en esta fecha
        $modulosRecuperacionFecha = [];
        try {
            $rcQuery = \App\Models\RecuperacionClase::whereDate('fecha_reagendada', $fechaReserva)
                ->whereIn('estado', ['reagendada', 'realizada']);
            if ($runProfesor) {
                $runLimpio = preg_replace('/[^0-9kK]/', '', $runProfesor);
                $rcQuery->where(function($q) use ($runProfesor, $runLimpio) {
                    $q->where('run_profesor', $runProfesor)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                });
            }
            foreach ($rcQuery->get() as $rc) {
                foreach (explode(',', (string) $rc->id_modulo_reagendado) as $m) {
                    $mTrim = trim($m);
                    if ($mTrim !== '') {
                        $modulosRecuperacionFecha[$mTrim] = $rc;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        $fechaCarbon = Carbon::parse($fechaReserva);
        $diasSemana = [0 => 'domingo', 1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado'];
        $diaNorm = \App\Helpers\ModulosHelper::normalizarDia($diasSemana[$fechaCarbon->dayOfWeek] ?? 'lunes');

        // Determinar bloque de módulos de recuperación consecutivos que cubren $horaEntrada
        $bloqueModulosActivos = [];
        if ($horaEntrada) {
            $infoRec = static::obtenerRecuperacionProgramada($idEspacio, $runProfesor, $fechaReserva, $horaEntrada, 40);
            if ($infoRec && !empty($infoRec['modulos_ids'])) {
                foreach ($infoRec['modulos_ids'] as $mId) {
                    $bloqueModulosActivos[$mId] = true;
                }
            }
        }

        $contadorMovidos = 0;

        foreach ($registros as $registro) {
            $modulosRegistro = array_values(array_filter(array_map('trim', explode(',', (string) $registro->id_modulo))));
            $primerModuloId = $modulosRegistro[0] ?? $registro->id_modulo;
            $ultimoModuloId = end($modulosRegistro) ?: $primerModuloId;

            $numIni = \App\Helpers\ModulosHelper::getNumeroModulo((string) $primerModuloId);
            $numFin = \App\Helpers\ModulosHelper::getNumeroModulo((string) $ultimoModuloId);
            $horarioIni = $numIni ? \App\Helpers\ModulosHelper::getHorarioModulo($diaNorm, $numIni) : null;
            $horarioFin = $numFin ? \App\Helpers\ModulosHelper::getHorarioModulo($diaNorm, $numFin) : null;

            $horaInicioReg = $registro->modulo?->hora_inicio ?? ($horarioIni['inicio'] ?? null);
            $horaTerminoReg = $registro->modulo?->hora_termino ?? ($horarioFin['fin'] ?? null);

            // Si tenemos horaEntrada, verificar que el registro pertenezca al bloque horario actual (o sea parte del bloque consecutivo de recuperación)
            if ($horaEntrada && $horaInicioReg && $horaTerminoReg) {
                $perteneceABloqueRec = false;
                foreach ($modulosRegistro as $mReg) {
                    if (isset($bloqueModulosActivos[$mReg])) {
                        $perteneceABloqueRec = true;
                        break;
                    }
                }

                if (!$perteneceABloqueRec) {
                    $hEntradaCarbon = Carbon::parse($horaEntrada);
                    $hIniMargen = Carbon::parse($horaInicioReg)->subMinutes(40);
                    $hFinMargen = Carbon::parse($horaTerminoReg)->addMinutes(30);
                    if ($hEntradaCarbon->lt($hIniMargen) || $hEntradaCarbon->gt($hFinMargen)) {
                        continue;
                    }
                }
            }

            $esRecuperacion = $registro->estado === 'pendiente'
                || $registro->estado === 'recuperada'
                || str_contains(mb_strtolower($registro->observaciones ?? '', 'UTF-8'), 'reagendad')
                || str_contains(mb_strtolower($registro->observaciones ?? '', 'UTF-8'), 'recupera');

            if (!$esRecuperacion) {
                foreach ($modulosRegistro as $mReg) {
                    if (isset($modulosRecuperacionFecha[$mReg])) {
                        $esRecuperacion = true;
                        break;
                    }
                }
            }

            // Solo mover a atrasos si era un registro de "no_realizada" o "justificado" con autocorregido
            $debeRegistrarAtraso = !$esRecuperacion && (
                $registro->estado === 'no_realizada' || 
                ($registro->estado === 'justificado' && str_contains($registro->observaciones ?? '', 'Auto-corregido'))
            );

            $minutosAtraso = 0;
            if ($debeRegistrarAtraso) {
                // Intentar obtener la planificación correspondiente
                $planificacion = \App\Models\Planificacion_Asignatura::where('id_asignatura', $registro->id_asignatura)
                    ->where('id_espacio', $registro->id_espacio)
                    ->where('id_modulo', $primerModuloId)
                    ->first();

                // Calcular minutos de atraso si tenemos hora de entrada
                $horaProgramada = $horaInicioReg;
                $esRealmenteAtraso = false;
                
                if ($horaEntrada && $horaProgramada) {
                    $horaLlegadaCarbon = Carbon::parse($horaEntrada);
                    $horaProgramadaCarbon = Carbon::parse($horaProgramada);
                    
                    // Solo es atraso si llegó DESPUÉS de la hora programada
                    if ($horaLlegadaCarbon->gt($horaProgramadaCarbon)) {
                        $minutosAtraso = $horaProgramadaCarbon->diffInMinutes($horaLlegadaCarbon);
                        $esRealmenteAtraso = true;
                    }
                }

                // Solo registrar en profesor_atrasos si realmente llegó tarde
                if ($esRealmenteAtraso) {
                    $existeAtraso = \Illuminate\Support\Facades\DB::connection('tenant')->table('profesor_atrasos')
                        ->where('id_asignatura', $registro->id_asignatura)
                        ->where('id_espacio', $registro->id_espacio)
                        ->where('id_modulo', $primerModuloId)
                        ->where('fecha', $registro->fecha_clase)
                        ->exists();

                    if (!$existeAtraso) {
                        try {
                            \Illuminate\Support\Facades\DB::connection('tenant')->table('profesor_atrasos')->insert([
                                'id_planificacion' => $planificacion ? $planificacion->id : 0,
                                'id_asignatura' => $registro->id_asignatura,
                                'id_espacio' => $registro->id_espacio,
                                'id_modulo' => $primerModuloId,
                                'run_profesor' => $runProfesor ?? $registro->run_profesor,
                                'fecha' => $registro->fecha_clase,
                                'hora_programada' => $horaProgramada,
                                'hora_llegada' => $horaEntrada,
                                'minutos_atraso' => $minutosAtraso,
                                'periodo' => $registro->periodo,
                                'observaciones' => 'Profesor llegó tarde pero realizó la clase',
                                'justificado' => false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $contadorMovidos++;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::warning("No se pudo crear registro de atraso: " . $e->getMessage());
                        }
                    }
                }
            }

            try {
                $horaStr = $horaEntrada ?? now()->format('H:i:s');
                if ($esRecuperacion) {
                    $obsBase = $registro->observaciones ?? '';
                    $notaIngreso = "✓ CLASE RECUPERADA - Profesor registró ingreso a las {$horaStr}";
                    $nuevaObs = !empty($obsBase) && !str_contains($obsBase, 'Profesor registró ingreso')
                        ? "{$obsBase} | {$notaIngreso}"
                        : ($obsBase ?: $notaIngreso);

                    $registro->update([
                        'estado' => 'recuperada',
                        'observaciones' => $nuevaObs,
                    ]);

                    // Sincronizar también en recuperacion_clases
                    foreach ($modulosRegistro as $mReg) {
                        \App\Models\RecuperacionClase::whereDate('fecha_reagendada', $fechaReserva)
                            ->where('id_asignatura', $registro->id_asignatura)
                            ->where(function ($q) use ($mReg, $registro) {
                                $q->where('id_modulo_reagendado', $mReg)
                                  ->orWhere('id_modulo_reagendado', 'LIKE', "%{$mReg}%")
                                  ->orWhere('id_clase_no_realizada', $registro->id);
                            })
                            ->where('estado', 'reagendada')
                            ->update(['estado' => 'realizada']);
                    }
                } else {
                    $registro->update([
                        'estado' => 'realizada',
                        'motivo' => ($minutosAtraso ?? 0) > 0 ? "Clase realizada con atraso de {$minutosAtraso} min" : "Clase realizada",
                        'observaciones' => "Profesor registró ingreso a las {$horaStr}",
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("No se pudo actualizar registro: " . $e->getMessage());
            }
        }

        return $contadorMovidos;
    }

    /**
     * Busca si el docente tiene una recuperación agendada en la fecha/espacio y hora indicadas.
     * Soporta tanto registros en recuperacion_clases (estado reagendada/realizada) como en
     * clases_no_realizadas (estado pendiente/recuperada) y agrupa bloques de módulos consecutivos.
     */
    public static function obtenerRecuperacionProgramada(
        ?string $idEspacio,
        ?string $runProfesor,
        string $fecha,
        ?string $horaActual = null,
        int $minutosAnticipacion = 30
    ): ?array {
        if (empty($runProfesor) && empty($idEspacio)) {
            return null;
        }

        $fechaCarbon = Carbon::parse($fecha);
        $fechaStr = $fechaCarbon->toDateString();
        $diasSemana = [0 => 'domingo', 1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado'];
        $prefijosDias = [0 => 'DO', 1 => 'LU', 2 => 'MA', 3 => 'MI', 4 => 'JU', 5 => 'VI', 6 => 'SA'];
        $diaNorm = \App\Helpers\ModulosHelper::normalizarDia($diasSemana[$fechaCarbon->dayOfWeek] ?? 'lunes');
        $prefijoDia = $prefijosDias[$fechaCarbon->dayOfWeek] ?? 'LU';
        $horariosDia = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaNorm] ?? [];

        $runLimpio = $runProfesor ? strtoupper(preg_replace('/[^0-9Kk]/', '', $runProfesor)) : null;
        $runSinDv = ($runLimpio && strlen($runLimpio) > 1) ? substr($runLimpio, 0, -1) : $runLimpio;
        $espacioLimpio = $idEspacio ? strtoupper(str_replace(['-', ' '], '', $idEspacio)) : null;

        $itemsPorAsignatura = [];

        // 1. Consultar RecuperacionClase para esa fecha_reagendada
        try {
            $rcQuery = \App\Models\RecuperacionClase::with(['asignatura', 'moduloReagendado'])
                ->whereDate('fecha_reagendada', $fechaStr)
                ->whereIn('estado', ['reagendada', 'realizada']);

            if ($runLimpio) {
                $rcQuery->where(function ($q) use ($runProfesor, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $runProfesor)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                });
            }

            foreach ($rcQuery->get() as $rc) {
                $espRc = $rc->id_espacio_reagendado ?: $rc->id_espacio;
                if ($espacioLimpio && strtoupper(str_replace(['-', ' '], '', (string) $espRc)) !== $espacioLimpio) {
                    continue;
                }
                $idAsig = $rc->id_asignatura ?: 'RECUPERACION';
                foreach (explode(',', (string) $rc->id_modulo_reagendado) as $mRaw) {
                    $mTrim = trim($mRaw);
                    if ($mTrim === '') continue;
                    $numMod = \App\Helpers\ModulosHelper::getNumeroModulo($mTrim);
                    if ($numMod <= 0 && is_numeric($mTrim)) {
                        $numMod = (int) $mTrim;
                        $mTrim = "{$prefijoDia}.{$numMod}";
                    }
                    if ($numMod > 0) {
                        $itemsPorAsignatura[$idAsig]['asignatura'] = $rc->asignatura;
                        $itemsPorAsignatura[$idAsig]['id_asignatura'] = $rc->id_asignatura;
                        $itemsPorAsignatura[$idAsig]['id_espacio'] = $espRc;
                        $itemsPorAsignatura[$idAsig]['run_profesor'] = $rc->run_profesor;
                        $itemsPorAsignatura[$idAsig]['modulos'][$numMod] = $mTrim;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        // 2. Consultar ClaseNoRealizada en estado pendiente / recuperada para esa fecha
        try {
            $cnrQuery = static::with(['asignatura', 'modulo'])
                ->whereDate('fecha_clase', $fechaStr)
                ->where(function ($q) {
                    $q->whereIn('estado', ['pendiente', 'recuperada'])
                      ->orWhere('observaciones', 'LIKE', '%Reagendada%');
                });

            if ($runLimpio) {
                $cnrQuery->where(function ($q) use ($runProfesor, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $runProfesor)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                });
            }

            foreach ($cnrQuery->get() as $cnr) {
                if ($espacioLimpio && strtoupper(str_replace(['-', ' '], '', (string) $cnr->id_espacio)) !== $espacioLimpio) {
                    continue;
                }
                $idAsig = $cnr->id_asignatura ?: 'RECUPERACION';
                foreach (explode(',', (string) $cnr->id_modulo) as $mRaw) {
                    $mTrim = trim($mRaw);
                    if ($mTrim === '') continue;
                    $numMod = \App\Helpers\ModulosHelper::getNumeroModulo($mTrim);
                    if ($numMod <= 0 && is_numeric($mTrim)) {
                        $numMod = (int) $mTrim;
                        $mTrim = "{$prefijoDia}.{$numMod}";
                    }
                    if ($numMod > 0) {
                        $itemsPorAsignatura[$idAsig]['asignatura'] = $itemsPorAsignatura[$idAsig]['asignatura'] ?? $cnr->asignatura;
                        $itemsPorAsignatura[$idAsig]['id_asignatura'] = $cnr->id_asignatura;
                        $itemsPorAsignatura[$idAsig]['id_espacio'] = $cnr->id_espacio;
                        $itemsPorAsignatura[$idAsig]['run_profesor'] = $cnr->run_profesor;
                        $itemsPorAsignatura[$idAsig]['modulos'][$numMod] = $mTrim;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        if (empty($itemsPorAsignatura)) {
            return null;
        }

        // Agrupar en secuencias de módulos consecutivos y evaluar contra $horaActual
        $bloquesCandidatos = [];
        foreach ($itemsPorAsignatura as $idAsig => $data) {
            if (empty($data['modulos'])) continue;
            ksort($data['modulos']);

            $secuencias = [];
            $actual = [];
            $prevNum = null;
            foreach ($data['modulos'] as $num => $mId) {
                if ($prevNum === null || $num === $prevNum + 1) {
                    $actual[$num] = $mId;
                } else {
                    $secuencias[] = $actual;
                    $actual = [$num => $mId];
                }
                $prevNum = $num;
            }
            if (!empty($actual)) {
                $secuencias[] = $actual;
            }

            foreach ($secuencias as $sec) {
                $nums = array_keys($sec);
                $modIni = reset($nums);
                $modFin = end($nums);
                $hIni = $horariosDia[$modIni]['inicio'] ?? '08:10:00';
                $hFin = $horariosDia[$modFin]['fin'] ?? '09:00:00';

                if ($horaActual !== null) {
                    $hActualCarbon = Carbon::parse($horaActual);
                    $hIniConMargen = Carbon::parse($hIni)->subMinutes($minutosAnticipacion);
                    $hFinCarbon = Carbon::parse($hFin);

                    if ($hActualCarbon->lt($hIniConMargen) || $hActualCarbon->gt($hFinCarbon)) {
                        continue;
                    }
                }

                $asigObj = $data['asignatura'];
                $bloquesCandidatos[] = [
                    'id_asignatura' => $data['id_asignatura'],
                    'asignatura' => $asigObj,
                    'nombre_asignatura' => $asigObj?->nombre_asignatura ?? 'Recuperación de Clase',
                    'id_espacio' => $data['id_espacio'],
                    'run_profesor' => $data['run_profesor'],
                    'id_modulo' => reset($sec),
                    'modulos_ids' => array_values($sec),
                    'modulo_inicio' => (int) $modIni,
                    'modulo_fin' => (int) $modFin,
                    'cantidad_modulos' => count($sec),
                    'hora_inicio' => $hIni,
                    'hora_termino' => $hFin,
                    'es_recuperacion' => true,
                ];
            }
        }

        return $bloquesCandidatos[0] ?? null;
    }

    /**
     * Reconcilia automáticamente los registros de recuperacion_clases y clases_no_realizadas
     * con las reservas ya registradas por el lector QR / tótem:
     *  1. Normaliza y elimina filas duplicadas con módulos separados por coma (ej. "MI.5,MI.6" cuando existen "MI.5" y "MI.6").
     *  2. Si existe una Reserva del docente en la ventana del bloque de recuperación, vincula la asignatura,
     *     extiende el bloque de módulos consecutivos (ej. módulos 5 y 6 de 12:10 a 14:00),
     *     y actualiza clases_no_realizadas -> 'recuperada' y recuperacion_clases -> 'realizada'.
     */
    public static function sincronizarRecuperacionesConReservas(?string $fechaInicio = null, ?string $fechaFin = null): void
    {
        try {
            $fIni = $fechaInicio ? Carbon::parse($fechaInicio)->toDateString() : Carbon::today()->subDays(14)->toDateString();
            $fFin = $fechaFin ? Carbon::parse($fechaFin)->toDateString() : Carbon::today()->addDays(7)->toDateString();

            $diasSemana = [0 => 'domingo', 1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado'];
            $prefijosDias = [0 => 'DO', 1 => 'LU', 2 => 'MA', 3 => 'MI', 4 => 'JU', 5 => 'VI', 6 => 'SA'];

            // Paso 1: Normalizar registros en clases_no_realizadas con módulos separados por comas (ej. "MI.5,MI.6")
            $cnrsConComa = static::whereBetween('fecha_clase', [$fIni, $fFin])
                ->where('id_modulo', 'LIKE', '%,%')
                ->get();

            foreach ($cnrsConComa as $cnrComa) {
                $fechaCnrStr = Carbon::parse($cnrComa->fecha_clase)->toDateString();
                $fechaCnrCarbon = Carbon::parse($fechaCnrStr);
                $prefijo = $prefijosDias[$fechaCnrCarbon->dayOfWeek] ?? 'LU';
                $partes = array_values(array_filter(array_map('trim', explode(',', (string) $cnrComa->id_modulo))));

                if (count($partes) <= 1) {
                    continue;
                }

                foreach ($partes as $parte) {
                    $modId = str_contains($parte, '.') ? $parte : "{$prefijo}.{$parte}";
                    $existenteIndividual = static::whereDate('fecha_clase', $fechaCnrStr)
                        ->where('id_espacio', $cnrComa->id_espacio)
                        ->where('run_profesor', $cnrComa->run_profesor)
                        ->where('id_asignatura', $cnrComa->id_asignatura)
                        ->where('id_modulo', $modId)
                        ->where('id', '!=', $cnrComa->id)
                        ->first();

                    if (!$existenteIndividual) {
                        static::create([
                            'id_asignatura' => $cnrComa->id_asignatura,
                            'id_espacio' => $cnrComa->id_espacio,
                            'id_modulo' => $modId,
                            'run_profesor' => $cnrComa->run_profesor,
                            'fecha_clase' => $fechaCnrStr,
                            'periodo' => $cnrComa->periodo,
                            'motivo' => $cnrComa->motivo,
                            'observaciones' => $cnrComa->observaciones,
                            'estado' => $cnrComa->estado,
                            'hora_deteccion' => $cnrComa->hora_deteccion ?? now(),
                        ]);
                    }
                }

                // Eliminar el registro combinado redundante ("MI.5,MI.6") para evitar las 3 filas (5-5, 5-6, 6-6)
                $cnrComa->delete();
            }

            // Paso 2: Recopilar todos los bloques de recuperación en el rango de fechas
            $recuperaciones = \App\Models\RecuperacionClase::whereBetween('fecha_reagendada', [$fIni, $fFin])
                ->whereIn('estado', ['reagendada', 'realizada'])
                ->get();

            $cnrsPendientes = static::whereBetween('fecha_clase', [$fIni, $fFin])
                ->where(function ($q) {
                    $q->whereIn('estado', ['pendiente', 'recuperada'])
                      ->orWhere('observaciones', 'LIKE', '%Reagendada%');
                })
                ->get();

            if ($recuperaciones->isEmpty() && $cnrsPendientes->isEmpty()) {
                return;
            }

            // Agrupar por fecha + run_profesor_limpio + espacio_limpio + id_asignatura
            $grupos = [];

            foreach ($recuperaciones as $rc) {
                $fStr = Carbon::parse($rc->fecha_reagendada)->toDateString();
                $runNorm = strtoupper(preg_replace('/[^0-9Kk]/', '', (string) $rc->run_profesor));
                $esp = $rc->id_espacio_reagendado ?: $rc->id_espacio;
                $espNorm = strtoupper(str_replace(['-', ' '], '', (string) $esp));
                $asig = $rc->id_asignatura ?: '';
                $key = "{$fStr}_{$runNorm}_{$espNorm}_{$asig}";

                $grupos[$key]['fecha'] = $fStr;
                $grupos[$key]['run_norm'] = $runNorm;
                $grupos[$key]['run_raw'] = $rc->run_profesor;
                $grupos[$key]['espacio'] = $esp;
                $grupos[$key]['esp_norm'] = $espNorm;
                $grupos[$key]['id_asignatura'] = $rc->id_asignatura;
                $grupos[$key]['rcs'][] = $rc;

                foreach (explode(',', (string) $rc->id_modulo_reagendado) as $m) {
                    $num = \App\Helpers\ModulosHelper::getNumeroModulo(trim($m));
                    if ($num > 0) {
                        $grupos[$key]['modulos'][$num] = trim($m);
                    }
                }
            }

            foreach ($cnrsPendientes as $cnr) {
                $fStr = Carbon::parse($cnr->fecha_clase)->toDateString();
                $runNorm = strtoupper(preg_replace('/[^0-9Kk]/', '', (string) $cnr->run_profesor));
                $esp = $cnr->id_espacio;
                $espNorm = strtoupper(str_replace(['-', ' '], '', (string) $esp));
                $asig = $cnr->id_asignatura ?: '';
                $key = "{$fStr}_{$runNorm}_{$espNorm}_{$asig}";

                $grupos[$key]['fecha'] = $fStr;
                $grupos[$key]['run_norm'] = $runNorm;
                $grupos[$key]['run_raw'] = $cnr->run_profesor;
                $grupos[$key]['espacio'] = $esp;
                $grupos[$key]['esp_norm'] = $espNorm;
                $grupos[$key]['id_asignatura'] = $cnr->id_asignatura;
                $grupos[$key]['cnrs'][] = $cnr;

                foreach (explode(',', (string) $cnr->id_modulo) as $m) {
                    $num = \App\Helpers\ModulosHelper::getNumeroModulo(trim($m));
                    if ($num > 0) {
                        $grupos[$key]['modulos'][$num] = trim($m);
                    }
                }
            }

            foreach ($grupos as $grupo) {
                if (empty($grupo['modulos'])) {
                    continue;
                }

                $fStr = $grupo['fecha'];
                $fCarbon = Carbon::parse($fStr);
                $diaNorm = \App\Helpers\ModulosHelper::normalizarDia($diasSemana[$fCarbon->dayOfWeek] ?? 'lunes');
                $horariosDia = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaNorm] ?? [];

                ksort($grupo['modulos']);

                // Agrupar en secuencias consecutivas de módulos
                $secuencias = [];
                $actual = [];
                $prev = null;
                foreach ($grupo['modulos'] as $num => $mId) {
                    if ($prev === null || $num === $prev + 1) {
                        $actual[$num] = $mId;
                    } else {
                        $secuencias[] = $actual;
                        $actual = [$num => $mId];
                    }
                    $prev = $num;
                }
                if (!empty($actual)) {
                    $secuencias[] = $actual;
                }

                // Buscar reservas del docente en esa fecha
                $runNorm = $grupo['run_norm'];
                $runSinDv = strlen($runNorm) > 1 ? substr($runNorm, 0, -1) : $runNorm;

                $reservasDocente = \App\Models\Reserva::whereDate('fecha_reserva', $fStr)
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->where(function ($q) use ($grupo, $runNorm, $runSinDv) {
                        $q->where('run_profesor', $grupo['run_raw'])
                          ->orWhere('run_profesor', $runNorm)
                          ->orWhere('run_profesor', $runSinDv)
                          ->orWhere('run_solicitante', $grupo['run_raw'])
                          ->orWhere('run_solicitante', $runNorm)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runNorm])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    })
                    ->get();

                if ($reservasDocente->isEmpty()) {
                    continue;
                }

                foreach ($secuencias as $sec) {
                    $nums = array_keys($sec);
                    $modIni = (int) reset($nums);
                    $modFin = (int) end($nums);
                    $cantMods = count($sec);
                    $hIniBloque = $horariosDia[$modIni]['inicio'] ?? '08:10:00';
                    $hFinBloque = $horariosDia[$modFin]['fin'] ?? '09:00:00';
                    $hFinPrimerMod = $horariosDia[$modIni]['fin'] ?? $hFinBloque;

                    $margenIni = Carbon::parse($hIniBloque)->subMinutes(40)->format('H:i:s');

                    // Buscar reserva que haya ingresado en la ventana de este bloque de recuperación
                    $reservaMatch = $reservasDocente->first(function ($r) use ($modIni, $modFin, $margenIni, $hFinBloque) {
                        if (!empty($r->modulo_inicio) && !empty($r->modulo_fin)) {
                            if ((int) $r->modulo_inicio <= $modFin && (int) $r->modulo_fin >= $modIni) {
                                return true;
                            }
                        }
                        if (!empty($r->hora)) {
                            return $r->hora >= $margenIni && $r->hora <= $hFinBloque;
                        }
                        return false;
                    });

                    if (!$reservaMatch) {
                        continue;
                    }

                    // 1. Actualizar la Reserva del lector QR si quedó como espontánea o de 1 solo módulo
                    $reservaCambios = false;
                    if (empty($reservaMatch->id_asignatura) && !empty($grupo['id_asignatura'])) {
                        $reservaMatch->id_asignatura = $grupo['id_asignatura'];
                        $reservaCambios = true;
                    }
                    if (in_array($reservaMatch->tipo_reserva, ['espontanea', null, ''], true)) {
                        $reservaMatch->tipo_reserva = 'recuperacion';
                        $reservaCambios = true;
                    }
                    if (empty($reservaMatch->modulo_inicio) || (int) $reservaMatch->modulo_inicio > $modIni) {
                        $reservaMatch->modulo_inicio = $modIni;
                        $reservaCambios = true;
                    }
                    if (empty($reservaMatch->modulo_fin) || (int) $reservaMatch->modulo_fin < $modFin) {
                        $reservaMatch->modulo_fin = $modFin;
                        $reservaMatch->modulos = max((int) ($reservaMatch->modulos ?? 1), $cantMods);
                        $reservaCambios = true;
                    }
                    // Si la hora_salida quedó con el fin del primer módulo (por reserva espontánea de 1 módulo) y no fue cerrada anticipadamente
                    if (
                        !$reservaMatch->clase_finalizada_anticipadamente
                        && ($reservaMatch->hora_salida === $hFinPrimerMod || empty($reservaMatch->hora_salida))
                        && $hFinBloque > $hFinPrimerMod
                    ) {
                        $reservaMatch->hora_salida = $hFinBloque;
                        $reservaCambios = true;
                    }
                    if ($reservaCambios) {
                        $reservaMatch->save();
                    }

                    // 2. Marcar RecuperacionClase correspondientes como 'realizada'
                    foreach ($grupo['rcs'] ?? [] as $rc) {
                        $rcMods = array_map('trim', explode(',', (string) $rc->id_modulo_reagendado));
                        $intersect = array_intersect($rcMods, array_values($sec));
                        if (!empty($intersect) && $rc->estado !== 'realizada') {
                            $rc->estado = 'realizada';
                            $rc->save();
                        }
                    }

                    // 3. Asegurar y marcar ClaseNoRealizada correspondientes como 'recuperada'
                    foreach ($sec as $numMod => $modIdStr) {
                        $cnrMod = static::whereDate('fecha_clase', $fStr)
                            ->where('id_modulo', $modIdStr)
                            ->where('id_asignatura', $grupo['id_asignatura'])
                            ->where(function ($q) use ($grupo, $runNorm, $runSinDv) {
                                $q->where('run_profesor', $grupo['run_raw'])
                                  ->orWhere('run_profesor', $runNorm)
                                  ->orWhere('run_profesor', $runSinDv)
                                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runNorm]);
                            })
                            ->first();

                        if ($cnrMod && $cnrMod->estado !== 'recuperada') {
                            $obs = $cnrMod->observaciones ?? '';
                            $notaQr = "✓ CLASE RECUPERADA - Ingreso QR registrado a las " . substr((string) $reservaMatch->hora, 0, 5);
                            $cnrMod->estado = 'recuperada';
                            if (!str_contains($obs, 'CLASE RECUPERADA')) {
                                $cnrMod->observaciones = $obs ? "{$obs} | {$notaQr}" : $notaQr;
                            }
                            $cnrMod->save();
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Error en sincronizarRecuperacionesConReservas: " . $e->getMessage());
        }
    }

    public static function obtenerEstadisticasPorProfesor($periodo = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = static::with(['asignatura', 'profesor'])
            ->selectRaw('run_profesor, COUNT(*) as total_ausencias')
            ->groupBy('run_profesor');

        if ($periodo) {
            $query->where('periodo', $periodo);
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_clase', [$fechaInicio, $fechaFin]);
        } elseif ($fechaInicio) {
            $query->whereDate('fecha_clase', '>=', $fechaInicio);
        }

        return $query->get();
    }
}

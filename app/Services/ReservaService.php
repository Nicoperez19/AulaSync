<?php

namespace App\Services;

use App\Helpers\ModulosHelper;
use App\Helpers\SemesterHelper;
use App\Models\Asignatura;
use App\Models\Espacio;
use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use App\Models\Profesor;
use App\Models\Reserva;
use App\Models\Solicitante;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservaService
{
    protected EspacioService $espacioService;

    public function __construct(EspacioService $espacioService)
    {
        $this->espacioService = $espacioService;
    }

    /**
     * Obtener listado de reservas con relaciones ansiosas y datos procesados.
     */
    public function getReservas(array $filtros = []): array
    {
        $tenant = null;
        if (session()->has('tenant_id')) {
            $tenant = Tenant::find(session('tenant_id'));
        }

        if (!$tenant) {
            $tenant = Tenant::where('is_active', true)->first();
            if ($tenant) {
                $tenant->makeCurrent();
            }
        }

        if (!$tenant) {
            return [
                'success' => false,
                'message' => 'No se encontró tenant configurado',
                'reservas' => [],
                'total' => 0,
            ];
        }

        if ($tenant->database) {
            config(['database.connections.tenant.database' => $tenant->database]);
            app('db')->purge('tenant');
            app('db')->disconnect('tenant');
        }

        $query = Reserva::on('tenant')
            ->with(['profesor', 'solicitante', 'espacio', 'asignatura'])
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora');

        $reservasRaw = $query->get();

        $reservas = $reservasRaw->map(function ($reserva) {
            $nombreResponsable = 'Sin asignar';
            $runResponsable = 'N/A';
            $tipoResponsable = 'desconocido';

            if ($reserva->run_profesor) {
                $profesor = $reserva->profesor;
                $nombreResponsable = $profesor ? ($profesor->name ?? ($profesor->nombres . ' ' . $profesor->apellidos)) : $reserva->run_profesor;
                $runResponsable = $reserva->run_profesor;
                $tipoResponsable = 'profesor';
            } elseif ($reserva->run_solicitante) {
                $solicitante = $reserva->solicitante;
                $nombreResponsable = $solicitante ? $solicitante->nombre : $reserva->run_solicitante;
                $runResponsable = $reserva->run_solicitante;
                $tipoResponsable = 'solicitante';
            }

            $espacio = $reserva->espacio;
            $nombreEspacio = $espacio ? $espacio->nombre_espacio : 'Espacio desconocido';

            $asignaturaInfo = 'Sin asignatura';
            if ($reserva->asignatura) {
                $asignaturaInfo = $reserva->asignatura->codigo_asignatura . ' - ' . $reserva->asignatura->nombre_asignatura;
            } elseif ($reserva->id_asignatura) {
                $asignaturaInfo = $reserva->id_asignatura;
            }

            $modulosInfo = $this->procesarModulosYHorarios($reserva);

            $fueEditada = $reserva->created_at
                && $reserva->updated_at
                && $reserva->created_at->diffInSeconds($reserva->updated_at) > 5;

            return [
                'id' => $reserva->id_reserva,
                'id_reserva' => $reserva->id_reserva,
                'espacio' => $nombreEspacio,
                'codigo_espacio' => $reserva->id_espacio,
                'id_espacio' => $reserva->id_espacio,
                'fecha' => $reserva->fecha_reserva ? ($reserva->fecha_reserva instanceof Carbon ? $reserva->fecha_reserva->format('Y-m-d') : Carbon::parse($reserva->fecha_reserva)->format('Y-m-d')) : 'N/A',
                'hora' => $reserva->hora ? substr($reserva->hora, 0, 5) : 'N/A',
                'hora_salida' => $reserva->hora_salida ? substr($reserva->hora_salida, 0, 5) : null,
                'modulos' => $reserva->modulos ?? 1,
                'modulos_info' => $modulosInfo,
                'modulo_inicial' => $modulosInfo['modulo_inicial'],
                'modulo_final' => $modulosInfo['modulo_final'],
                'rango_horario' => $modulosInfo['rango_horario'],
                'horario_completo' => $modulosInfo['texto_completo'],
                'tipo_responsable' => $tipoResponsable,
                'run_responsable' => $runResponsable,
                'nombre_responsable' => $nombreResponsable,
                'asignatura' => $asignaturaInfo,
                'id_asignatura' => $reserva->id_asignatura,
                'nombre_actividad' => $reserva->nombre_actividad,
                'descripcion_actividad' => $reserva->descripcion_actividad,
                'tipo_reserva' => $reserva->tipo_reserva ?? 'directa',
                'estado' => $reserva->estado ?? 'activa',
                'observaciones' => $reserva->observaciones,
                'creado_por' => $reserva->creado_por,
                'created_at' => $reserva->created_at ? $reserva->created_at->format('d/m/Y H:i') : 'N/A',
                'fue_editada' => $fueEditada,
                'updated_at' => $fueEditada ? $reserva->updated_at->format('d/m/Y H:i') : null,
            ];
        });

        return [
            'success' => true,
            'reservas' => $reservas->values()->all(),
            'total' => $reservas->count(),
        ];
    }

    /**
     * Buscar personas (profesores y solicitantes).
     */
    public function buscarPersonas(string $termino): array
    {
        if (strlen($termino) < 2) {
            return [];
        }

        $personas = [];

        $profesores = Profesor::where('run_profesor', 'LIKE', '%' . $termino . '%')
            ->orWhere('name', 'LIKE', '%' . $termino . '%')
            ->limit(10)
            ->get();

        foreach ($profesores as $profesor) {
            $personas[] = [
                'run' => $profesor->run_profesor,
                'nombre' => $profesor->name,
                'email' => $profesor->email ?? '',
                'telefono' => $profesor->celular ?? '',
                'tipo' => 'profesor',
                'display' => $profesor->run_profesor . ' - ' . $profesor->name . ' (Profesor)',
            ];
        }

        $solicitantes = Solicitante::on('tenant')
            ->where('run_solicitante', 'LIKE', '%' . $termino . '%')
            ->orWhere('nombre', 'LIKE', '%' . $termino . '%')
            ->where('activo', true)
            ->limit(10)
            ->get();

        foreach ($solicitantes as $solicitante) {
            $personas[] = [
                'run' => $solicitante->run_solicitante,
                'nombre' => $solicitante->nombre,
                'email' => $solicitante->correo ?? '',
                'telefono' => $solicitante->telefono ?? '',
                'tipo' => 'solicitante',
                'display' => $solicitante->run_solicitante . ' - ' . $solicitante->nombre . ' (Solicitante)',
            ];
        }

        return $personas;
    }

    /**
     * Buscar asignaturas por código o nombre.
     */
    public function buscarAsignaturas(string $termino): array
    {
        if (strlen($termino) < 2) {
            return [];
        }

        $asignaturas = Asignatura::where('codigo_asignatura', 'LIKE', '%' . $termino . '%')
            ->orWhere('nombre_asignatura', 'LIKE', '%' . $termino . '%')
            ->limit(20)
            ->get();

        return $asignaturas->map(function ($asignatura) {
            return [
                'id_asignatura' => $asignatura->id_asignatura,
                'codigo_asignatura' => $asignatura->codigo_asignatura,
                'nombre_asignatura' => $asignatura->nombre_asignatura,
                'display' => $asignatura->codigo_asignatura . ' - ' . $asignatura->nombre_asignatura,
            ];
        })->toArray();
    }

    /**
     * Verificar conflictos con clases programadas y reservas existentes.
     */
    public function verificarConflictos(array $params): array
    {
        $espacioCodigo = $params['espacio'];
        $fechaStr = $params['fecha'];
        $moduloInicial = (int) $params['modulo_inicial'];
        $moduloFinal = (int) $params['modulo_final'];

        $conflictos = [];

        $fechaReserva = Carbon::parse($fechaStr);
        $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        $prefijoDia = $prefijosDias[$fechaReserva->dayOfWeek];
        $periodo = SemesterHelper::getCurrentPeriod($fechaReserva);

        for ($modulo = $moduloInicial; $modulo <= $moduloFinal; ++$modulo) {
            $idModulo = $prefijoDia . '.' . $modulo;

            $planificacion = Planificacion_Asignatura::with(['asignatura.profesor', 'asignatura.carrera', 'modulo'])
                ->where('id_espacio', $espacioCodigo)
                ->where('id_modulo', $idModulo)
                ->whereHas('horario', function ($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->first();

            if ($planificacion) {
                $conflictos[] = [
                    'tipo' => 'planificacion',
                    'modulo' => $modulo,
                    'id_modulo' => $idModulo,
                    'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin nombre',
                    'codigo' => $planificacion->asignatura->codigo_asignatura ?? '-',
                    'profesor' => $planificacion->horario->profesor->name ?? 'Sin profesor',
                    'carrera' => $planificacion->asignatura->carrera->nombre ?? '-',
                ];
            }
        }

        $reservaExistente = Reserva::with(['profesor', 'solicitante', 'asignatura'])
            ->where('id_espacio', $espacioCodigo)
            ->where('fecha_reserva', $fechaStr)
            ->whereIn('estado', ['activa', 'programada'])
            ->where(function ($q) use ($moduloInicial, $moduloFinal) {
                $q->where(function ($inner) use ($moduloInicial, $moduloFinal) {
                    $inner->where('modulo_inicio', '<=', $moduloFinal)
                          ->where('modulo_fin', '>=', $moduloInicial);
                });
            })
            ->get();

        foreach ($reservaExistente as $reserva) {
            $conflictos[] = [
                'tipo' => 'reserva',
                'id_reserva' => $reserva->id_reserva,
                'estado' => $reserva->estado,
                'modulo_inicio' => $reserva->modulo_inicio,
                'modulo_fin' => $reserva->modulo_fin,
                'responsable' => $reserva->profesor->name ?? $reserva->solicitante->nombre ?? 'Desconocido',
                'asignatura' => $reserva->asignatura->nombre_asignatura ?? $reserva->nombre_actividad ?? '-',
            ];
        }

        return [
            'tiene_conflictos' => count($conflictos) > 0,
            'conflictos' => $conflictos,
        ];
    }

    /**
     * Crear una reserva (puntual o recurrente).
     */
    public function crearReserva(array $data, ?User $usuario = null): array
    {
        $runNormalizado = $this->normalizeRun($data['run']);

        $idAsignatura = $data['id_asignatura'] ?? null;
        if ($idAsignatura === 'otro') {
            $idAsignatura = null;
        }

        if ($idAsignatura && !Asignatura::where('id_asignatura', $idAsignatura)->exists()) {
            return [
                'success' => false,
                'status' => 422,
                'mensaje' => 'La asignatura seleccionada no existe en este tenant',
            ];
        }

        if (($data['tipo'] ?? '') === 'colaborador' && !$idAsignatura) {
            return [
                'success' => false,
                'status' => 400,
                'mensaje' => 'Debe seleccionar una asignatura para las reservas de profesor colaborador',
            ];
        }

        if (($data['tipo'] ?? '') === 'solicitante' && empty($data['nombre_actividad'])) {
            return [
                'success' => false,
                'status' => 400,
                'mensaje' => 'Debe indicar el nombre de la actividad para reservas de solicitantes externos',
            ];
        }

        $moduloInicial = (int) $data['modulo_inicial'];
        $moduloFinal = (int) $data['modulo_final'];

        if ($moduloInicial > $moduloFinal) {
            return [
                'success' => false,
                'status' => 400,
                'mensaje' => 'El módulo inicial no puede ser mayor al módulo final',
            ];
        }

        $espacio = Espacio::where('id_espacio', $data['espacio'])->first();
        if (!$espacio) {
            return [
                'success' => false,
                'status' => 400,
                'mensaje' => 'El espacio seleccionado no existe',
            ];
        }

        $diaNormalizado = ModulosHelper::normalizarDia(Carbon::parse($data['fecha'])->locale('es')->isoFormat('dddd'));
        $horariosModulos = ModulosHelper::getHorariosModulos()[$diaNormalizado] ?? [];

        $duracionModulos = $moduloFinal - $moduloInicial + 1;

        $fechasASerReservadas = [];
        $esRecurrente = (($data['tipo_frecuencia'] ?? '') === 'recurrente' && !empty($data['fecha_fin']));

        if ($esRecurrente) {
            $inicio = Carbon::parse($data['fecha']);
            $fin = Carbon::parse($data['fecha_fin']);
            $curr = $inicio->copy();
            while ($curr->lte($fin)) {
                $fechasASerReservadas[] = $curr->format('Y-m-d');
                $curr->addWeek();
            }
        } else {
            $fechasASerReservadas[] = $data['fecha'];
        }

        $runProfesor = null;
        $runSolicitante = null;

        if ($data['tipo'] === 'profesor' || $data['tipo'] === 'colaborador') {
            $tipoProfesor = $data['tipo'] === 'colaborador' ? 'Colaborador' : 'Invitado';

            $profesor = Profesor::updateOrCreate(
                ['run_profesor' => $runNormalizado],
                [
                    'name' => $data['nombre'],
                    'email' => $data['correo'],
                    'celular' => $data['telefono'] ?? null,
                    'tipo_profesor' => $tipoProfesor,
                ]
            );

            $runProfesor = $profesor->run_profesor;
        } else {
            $solicitante = Solicitante::on('tenant')->updateOrCreate(
                ['run_solicitante' => $runNormalizado],
                [
                    'nombre' => $data['nombre'],
                    'correo' => $data['correo'],
                    'telefono' => $data['telefono'] ?? null,
                    'tipo_solicitante' => 'visitante',
                    'activo' => true,
                    'fecha_registro' => now(),
                ]
            );

            $runSolicitante = $solicitante->run_solicitante;
        }

        $reservasCreadas = [];
        $esForzado = !empty($data['forzar']);

        foreach ($fechasASerReservadas as $indexFecha => $fechaObjStr) {
            $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
            $prefijoReserva = $prefijosDias[Carbon::parse($fechaObjStr)->dayOfWeek];

            $modulosReserva = [];
            for ($i = $moduloInicial; $i <= $moduloFinal; ++$i) {
                $modulosReserva[] = $prefijoReserva . '.' . $i;
            }
            $idModuloString = implode(',', $modulosReserva);
            $idModuloInicial = $prefijoReserva . '.' . $moduloInicial;
            $idModuloFinal = $prefijoReserva . '.' . $moduloFinal;

            $moduloInicialObj = Modulo::where('id_modulo', $idModuloInicial)->first();
            $moduloFinalObj = Modulo::where('id_modulo', $idModuloFinal)->first();

            $horaInicio = $moduloInicialObj ? $moduloInicialObj->hora_inicio : ($horariosModulos[$moduloInicial]['inicio'] ?? '08:10:00');
            $horaFin = $moduloFinalObj ? $moduloFinalObj->hora_termino : ($horariosModulos[$moduloFinal]['fin'] ?? '09:00:00');

            // Validar reservas activas
            $reservaExistente = Reserva::where('id_espacio', $data['espacio'])
                ->where('fecha_reserva', $fechaObjStr)
                ->whereIn('estado', ['activa', 'programada'])
                ->where(function ($q) use ($moduloInicial, $moduloFinal) {
                    $q->where(function ($inner) use ($moduloInicial, $moduloFinal) {
                        $inner->where('modulo_inicio', '<=', $moduloFinal)
                              ->where('modulo_fin', '>=', $moduloInicial);
                    });
                })
                ->first();

            if ($reservaExistente && !$esForzado) {
                if (!$esRecurrente) {
                    return [
                        'success' => false,
                        'status' => 409,
                        'tipo_error' => 'reserva_existente',
                        'mensaje' => 'Ya existe una reserva activa para el espacio ' . $data['espacio'] . ' el día ' . $fechaObjStr . ' en los módulos solicitados.',
                        'reserva_conflicto' => $reservaExistente->id_reserva,
                    ];
                }
                continue;
            }

            // Validar clases programadas
            if (!$esForzado) {
                $periodoActual = SemesterHelper::getCurrentPeriod(Carbon::parse($fechaObjStr));
                $claseConflicto = Planificacion_Asignatura::with(['asignatura', 'modulo'])
                    ->where('id_espacio', $data['espacio'])
                    ->whereIn('id_modulo', $modulosReserva)
                    ->whereHas('horario', fn ($q) => $q->where('periodo', $periodoActual))
                    ->first();

                if ($claseConflicto) {
                    $nombreAsig = $claseConflicto->asignatura->nombre_asignatura ?? 'Clase programada';
                    $idMod = $claseConflicto->modulo->id_modulo ?? '-';

                    if (!$esRecurrente) {
                        return [
                            'success' => false,
                            'status' => 409,
                            'tipo_error' => 'clase_programada',
                            'mensaje' => "No es posible realizar la reserva: la sala {$espacio->nombre_espacio} tiene una clase programada ({$nombreAsig}) en el módulo {$idMod} del período {$periodoActual}.",
                            'clase' => [
                                'asignatura' => $nombreAsig,
                                'modulo' => $idMod,
                                'periodo' => $periodoActual,
                            ],
                        ];
                    }
                    continue;
                }
            }

            $idReserva = 'RES-' . strtoupper(uniqid());

            $rangoModulos = 'Módulos: ' . $moduloInicial . '-' . $moduloFinal . ' | ';
            $forzadoTag = $esForzado ? '⚠️ RESERVA FORZADA | ' : '';
            $tipoTag = $esRecurrente ? '🔁 RESERVA RECURRENTE SEMESTRAL | ' : '';
            $observacionesAutomaticas = $tipoTag . $forzadoTag . 'RESERVA CREADA MANUALMENTE por ' . ($usuario->name ?? 'Administrador') . ' el ' . now()->format('d/m/Y H:i:s') . ' | ' . $rangoModulos;
            $observacionesCompletas = $observacionesAutomaticas . ($data['observaciones'] ?? '');

            $fechaActual = now()->format('Y-m-d');
            $horaActualStr = now()->format('H:i:s');
            $esMismoDia = ($fechaObjStr === $fechaActual);

            $estaEnFranjaActual = false;
            if ($esMismoDia) {
                $horaInicioModulo = $horariosModulos[$moduloInicial]['inicio'] ?? null;
                $horaFinModulo = $horariosModulos[$moduloFinal]['fin'] ?? null;
                if ($horaInicioModulo && $horaFinModulo) {
                    $estaEnFranjaActual = ($horaActualStr >= $horaInicioModulo && $horaActualStr <= $horaFinModulo);
                }
            }

            $estadoReserva = ($esMismoDia && $estaEnFranjaActual) ? 'activa' : 'programada';
            $tipoReserva = $esRecurrente ? 'recurrente' : ((($data['tipo'] === 'profesor' || $data['tipo'] === 'colaborador') && $idAsignatura) ? 'clase' : 'directa');

            $datosReserva = [
                'id_reserva' => $idReserva,
                'fecha_reserva' => $fechaObjStr,
                'id_espacio' => $data['espacio'],
                'id_asignatura' => $idAsignatura,
                'modulos' => $duracionModulos,
                'modulo_inicio' => $moduloInicial,
                'modulo_fin' => $moduloFinal,
                'hora' => $horaInicio,
                'hora_salida' => $horaFin,
                'tipo_reserva' => $tipoReserva,
                'estado' => $estadoReserva,
                'id_modulo' => $idModuloString,
                'observaciones' => $observacionesCompletas,
                'nombre_actividad' => $data['nombre_actividad'] ?? null,
                'descripcion_actividad' => $data['descripcion_actividad'] ?? null,
                'run_profesor' => $runProfesor,
                'run_solicitante' => $runSolicitante,
                'creado_por' => $usuario ? $usuario->name . ' (' . ($usuario->run ?? 'Admin') . ')' : 'Sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $reservaCreated = Reserva::create($datosReserva);
            $reservasCreadas[] = $reservaCreated;

            if ($indexFecha === 0) {
                $this->enviarCorreoReserva($reservaCreated);
            }

            if ($indexFecha === 0) {
                $this->espacioService->ocuparEspacioSiEsReservaActual($reservaCreated);
            }
        }

        $this->espacioService->limpiarCacheEstados();

        if (empty($reservasCreadas)) {
            return [
                'success' => false,
                'status' => 409,
                'mensaje' => 'No se pudo crear ninguna reserva porque todas las fechas tenían conflictos.',
            ];
        }

        $primera = $reservasCreadas[0];
        $totalCreadas = count($reservasCreadas);
        $mensajeExito = $esRecurrente
            ? "Se crearon exitosamente {$totalCreadas} reservas recurrentes para el espacio {$data['espacio']}."
            : 'Reserva creada exitosamente.';

        return [
            'success' => true,
            'status' => 200,
            'mensaje' => $mensajeExito,
            'total_creadas' => $totalCreadas,
            'reserva' => [
                'id' => $primera->id_reserva,
                'espacio' => $data['espacio'],
                'fecha' => $primera->fecha_reserva,
                'modulos' => $duracionModulos,
                'estado' => $primera->estado,
            ],
        ];
    }

    /**
     * Cambiar estado de una reserva.
     */
    public function cambiarEstadoReserva(string $id, string $nuevoEstado): array
    {
        $reserva = Reserva::where('id_reserva', $id)->first();

        if (!$reserva) {
            return [
                'success' => false,
                'status' => 404,
                'mensaje' => 'Reserva no encontrada',
            ];
        }

        $estadoAnterior = $reserva->estado ?? 'activa';
        $reserva->estado = $nuevoEstado;

        $espacioLiberado = false;
        $espacioId = $reserva->id_espacio;

        if ($nuevoEstado === 'finalizada') {
            $reserva->hora_salida = now()->format('H:i:s');
            $espacioLiberado = $this->espacioService->liberarEspacioSiEsReservaActual($reserva);
        }

        $reserva->save();
        $this->espacioService->limpiarCacheEstados();

        $mensaje = "Reserva {$id} {$nuevoEstado} correctamente";
        if ($nuevoEstado === 'finalizada') {
            $mensaje .= " (hora de salida: {$reserva->hora_salida})";
            if ($espacioLiberado) {
                $mensaje .= ". Espacio {$espacioId} liberado automáticamente";
            }
        }

        return [
            'success' => true,
            'status' => 200,
            'mensaje' => $mensaje,
            'reserva' => [
                'id' => $reserva->id_reserva,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'hora_salida' => $reserva->hora_salida,
                'espacio' => $reserva->espacio->nombre_espacio ?? 'Sin espacio',
                'usuario' => $reserva->nombreUsuario ?? 'Sin usuario',
                'espacio_liberado' => $espacioLiberado,
            ],
        ];
    }

    /**
     * Actualizar una reserva existente.
     */
    public function actualizarReserva(string $id, array $data, ?User $usuario = null): array
    {
        $reserva = Reserva::where('id_reserva', $id)->first();

        if (!$reserva) {
            return [
                'success' => false,
                'status' => 404,
                'mensaje' => 'Reserva no encontrada',
            ];
        }

        if ($reserva->estado !== 'activa' && $reserva->estado !== 'programada') {
            return [
                'success' => false,
                'status' => 400,
                'mensaje' => 'Solo se pueden editar reservas activas o programadas',
            ];
        }

        $espacio = Espacio::where('id_espacio', $data['id_espacio'])->first();
        if (!$espacio) {
            return [
                'success' => false,
                'status' => 404,
                'mensaje' => 'Espacio no encontrado',
            ];
        }

        $moduloInicio = $data['modulo_inicio'] ?? 1;
        $moduloFin = $data['modulo_fin'] ?? 1;

        $conflicto = Reserva::where('id_espacio', $data['id_espacio'])
            ->where('fecha_reserva', $data['fecha'])
            ->where('id_reserva', '!=', $id)
            ->whereIn('estado', ['activa', 'programada'])
            ->where(function ($q) use ($moduloInicio, $moduloFin) {
                $q->where('modulo_inicio', '<=', $moduloFin)
                  ->where('modulo_fin', '>=', $moduloInicio);
            })
            ->exists();

        if ($conflicto) {
            return [
                'success' => false,
                'status' => 400,
                'mensaje' => 'El espacio ya está reservado para el horario seleccionado',
            ];
        }

        $adminNota = '';
        if (!empty($data['nuevo_run_profesor'])) {
            $nuevoRun = $data['nuevo_run_profesor'];
            $nuevoProfesor = Profesor::where('run_profesor', $nuevoRun)->first()
                ?? User::where('run', $nuevoRun)->first();

            if (!$nuevoProfesor) {
                return [
                    'success' => false,
                    'status' => 422,
                    'mensaje' => 'No se encontró un docente con el RUN indicado.',
                ];
            }

            $anteriorRun = $reserva->run_profesor ?: $reserva->run_solicitante;
            $reserva->run_profesor = $nuevoRun;
            $reserva->run_solicitante = null;

            $admin = $usuario ?? auth()->user();
            $adminNota = "\n[ADMIN] Docente reasignado de {$anteriorRun} a {$nuevoRun} por " . ($admin->name ?? 'Admin') . ' (' . ($admin->run ?? '') . ') el ' . now()->format('d/m/Y H:i') . '.';
        }

        $reserva->id_espacio = $espacio->id_espacio;
        $reserva->fecha_reserva = $data['fecha'];
        $reserva->hora = $data['hora'];
        $reserva->modulos = $data['modulos'];
        if (!empty($data['modulo_inicio'])) {
            $reserva->modulo_inicio = $data['modulo_inicio'];
        }
        if (!empty($data['modulo_fin'])) {
            $reserva->modulo_fin = $data['modulo_fin'];
        }
        $reserva->observaciones = ($data['observaciones'] ?? '') . $adminNota;

        $reserva->touch();
        $reserva->save();

        $this->espacioService->limpiarCacheEstados();

        return [
            'success' => true,
            'status' => 200,
            'mensaje' => 'Reserva actualizada correctamente',
            'reserva' => [
                'id' => $reserva->id_reserva,
                'espacio' => $espacio->id_espacio,
                'fecha' => $reserva->fecha_reserva,
                'hora' => $reserva->hora,
                'modulos' => $reserva->modulos,
            ],
        ];
    }

    /**
     * Procesar información de módulos y horarios para mostrar en frontend.
     */
    public function procesarModulosYHorarios($reserva): array
    {
        $moduloInicio = null;
        $moduloFin = null;
        $cantidadModulos = 1;

        if ($reserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reserva->observaciones, $matches)) {
            $moduloInicio = (int) $matches[1];
            $moduloFin = (int) $matches[2];
            $cantidadModulos = $moduloFin - $moduloInicio + 1;
        } elseif ($reserva->modulos && preg_match('/(\d+)\s*-\s*(\d+)/', $reserva->modulos, $matches)) {
            $moduloInicio = (int) $matches[1];
            $moduloFin = (int) $matches[2];
            $cantidadModulos = $moduloFin - $moduloInicio + 1;
        } elseif (is_numeric($reserva->modulos)) {
            $cantidadModulos = (int) $reserva->modulos;
        }

        $diaNormalizado = ModulosHelper::normalizarDia(Carbon::parse($reserva->fecha_reserva)->locale('es')->isoFormat('dddd'));
        $horariosModulos = ModulosHelper::getHorariosModulos()[$diaNormalizado] ?? [];

        if (!$moduloInicio && $reserva->hora) {
            $horaReserva = substr($reserva->hora, 0, 5);
            foreach ($horariosModulos as $modulo => $horario) {
                if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                    $moduloInicio = $modulo;
                    $cantidadModulos = is_numeric($reserva->modulos) ? (int) $reserva->modulos : 1;
                    $moduloFin = $moduloInicio + $cantidadModulos - 1;
                    break;
                }
            }
        }

        if ($moduloInicio && $moduloFin && isset($horariosModulos[$moduloInicio]) && isset($horariosModulos[$moduloFin])) {
            $horaInicio = $horariosModulos[$moduloInicio]['inicio'];
            $horaFin = $horariosModulos[$moduloFin]['fin'];

            return [
                'modulo_inicial' => $moduloInicio,
                'modulo_final' => $moduloFin,
                'cantidad_modulos' => $cantidadModulos,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'rango_horario' => "{$horaInicio} - {$horaFin}",
                'texto_completo' => "Módulos {$moduloInicio}-{$moduloFin} ({$horaInicio} - {$horaFin}) • {$cantidadModulos} módulo" . ($cantidadModulos > 1 ? 's' : ''),
            ];
        }

        return [
            'modulo_inicial' => null,
            'modulo_final' => null,
            'cantidad_modulos' => $cantidadModulos,
            'hora_inicio' => substr($reserva->hora, 0, 5),
            'hora_fin' => 'Desconocido',
            'rango_horario' => substr($reserva->hora, 0, 5),
            'texto_completo' => 'Hora: ' . substr($reserva->hora, 0, 5) . " • Duración: {$cantidadModulos} módulo" . ($cantidadModulos > 1 ? 's' : ''),
        ];
    }

    /**
     * Enviar comprobante de reserva por correo.
     */
    public function enviarCorreoReserva(Reserva $reserva): void
    {
        try {
            $destinatario = $this->resolverEmailReserva($reserva);

            if (!$destinatario) {
                Log::info("ℹ️ Reserva {$reserva->id_reserva}: no se encontró correo para enviar comprobante.");
                return;
            }

            Mail::to($destinatario)->queue(new \App\Mail\ComprobanteReservaMailable($reserva));
            Log::info("📧 Comprobante de reserva {$reserva->id_reserva} encolado para {$destinatario}");
        } catch (\Exception $e) {
            Log::warning("⚠️ No se pudo enviar el correo de la reserva {$reserva->id_reserva}: " . $e->getMessage());
        }
    }

    /**
     * Resolver la dirección de correo del responsable de la reserva.
     */
    public function resolverEmailReserva(Reserva $reserva): ?string
    {
        if ($reserva->run_profesor) {
            $profesor = Profesor::where('run_profesor', $reserva->run_profesor)->first();
            if ($profesor && !empty($profesor->email)) {
                return $profesor->email;
            }
        }

        if ($reserva->run_solicitante) {
            $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reserva->run_solicitante)->first();
            if ($solicitante && !empty($solicitante->correo)) {
                return $solicitante->correo;
            }
        }

        return null;
    }

    /**
     * Normalizar RUN chileno (quitar puntos, guiones y espacios).
     */
    public function normalizeRun(?string $run): string
    {
        if (!$run) {
            return '';
        }

        return strtoupper(preg_replace('/[^0-9kK]/', '', $run));
    }
}

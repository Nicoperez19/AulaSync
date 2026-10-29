<?php

namespace App\Http\Controllers;

use App\Helpers\ModulosHelper;
use App\Helpers\SemesterHelper;
use App\Helpers\EspacioAliasHelper;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Reserva;
use App\Models\ClaseNoRealizada;
use App\Models\DiaFeriado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\RunNormalizer;
use App\Services\OcupacionSemanalService;
use App\Services\TodasClasesService;
use App\Exports\OcupacionSemanalExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    use RunNormalizer;

    public function index(Request $request)
    {
        return view('layouts.dashboard');
    }

    public function horariosActualAjax(Request $request)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
        $horaAhora = date('H:i:s');
        $moduloActualNum = null;
        $moduloActualHorario = null;

        $horariosModulos = ModulosHelper::getHorariosModulos();
        $diaNormalizado = ModulosHelper::normalizarDia($diaActual);

        if (isset($horariosModulos[$diaNormalizado])) {
            foreach ($horariosModulos[$diaNormalizado] as $num => $horario) {
                if ($horaAhora >= $horario['inicio'] && $horaAhora < $horario['fin']) {
                    $moduloActualNum = $num;
                    $moduloActualHorario = $horario;
                    break;
                }
            }

            // Si estamos en intervalo (break) entre módulos, considerar el próximo módulo
            if (!$moduloActualNum) {
                $moduloActualNum = ModulosHelper::obtenerModuloActual($horaAhora, $diaNormalizado);
                if ($moduloActualNum && isset($horariosModulos[$diaNormalizado][$moduloActualNum])) {
                    $moduloActualHorario = $horariosModulos[$diaNormalizado][$moduloActualNum];
                }
            }
        }

        // Determinar el período actual y fecha de hoy
        $periodo = SemesterHelper::getCurrentPeriod();
        $fechaHoy = now()->format('Y-m-d');

        // Obtener los usuarios asignados por espacio para el módulo actual
        $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        $diasArray = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $indexDia = array_search($diaActual, $diasArray);
        $prefijo = $indexDia !== false ? $prefijosDias[$indexDia] : 'LU';
        $idModulo = $prefijo.'.'.$moduloActualNum;

        $asignaciones = Planificacion_Asignatura::with(['espacio.piso', 'asignatura', 'horario.profesor'])
            ->where('id_modulo', $idModulo)
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();

        $asignacionesColaboradores = PlanificacionProfesorColaborador::with(['espacio.piso', 'profesorColaborador.profesor', 'profesorColaborador.asignatura'])
            ->where('id_modulo', $idModulo)
            ->whereHas('profesorColaborador', function ($q) use ($fechaHoy) {
                $q->where('estado', 'activo')
                  ->where('fecha_inicio', '<=', $fechaHoy)
                  ->where('fecha_termino', '>=', $fechaHoy);
            })
            ->get();

        // Obtener todas las reservas de hoy (activas y finalizadas) por espacio para optimizar consultas
        $reservasHoy = Reserva::with(['profesor', 'asignatura', 'solicitante', 'espacio.piso'])
            ->where('fecha_reserva', $fechaHoy)
            ->whereIn('estado', ['activa', 'finalizada'])
            ->get()
            ->groupBy('id_espacio');

        // Filtrar reservas activas en curso
        $reservasActivasHoy = $reservasHoy->map(function ($reservas) use ($horaAhora) {
            return $reservas->filter(function ($r) use ($horaAhora) {
                return $r->estado === 'activa'
                    && $r->hora <= $horaAhora
                    && ($r->hora_salida === null || $r->hora_salida >= $horaAhora);
            });
        })->filter(function ($group) {
            return $group->isNotEmpty();
        });

        // Pre-cargar clases no realizadas de hoy agrupadas por espacio
        $clasesNoRealizadasHoy = ClaseNoRealizada::where('fecha_clase', $fechaHoy)
            ->where('estado', 'no_realizada')
            ->get()
            ->groupBy('id_espacio');

        // Horario y tolerancia del módulo actual
        $inicioModulo = $moduloActualHorario['inicio'] ?? null;
        $finModulo = $moduloActualHorario['fin'] ?? null;
        $margenMinutos = $moduloActualNum ? ModulosHelper::getMargenIngresoMinutos($idModulo) : 15;
        $horaLimiteTolerancia = ($inicioModulo && $moduloActualNum)
            ? Carbon::parse($fechaHoy . ' ' . $inicioModulo)->addMinutes($margenMinutos)->format('H:i:s')
            : null;

        $asignacionesMapeadas = collect();

        foreach ($asignaciones as $asig) {
            if (!$asig->espacio) {
                continue;
            }

            $runProfesor = $asig->horario->run_profesor ?? $asig->asignatura->run_profesor ?? null;
            $runProfesorNorm = $runProfesor ? $this->normalizeRun($runProfesor) : null;

            $espaciosEquiv = EspacioAliasHelper::obtenerEquivalentes($asig->espacio->id_espacio);
            $reservasEspacio = collect();
            foreach ($espaciosEquiv as $espId) {
                if ($reservasHoy->has($espId)) {
                    $reservasEspacio = $reservasEspacio->concat($reservasHoy->get($espId));
                }
            }

            $nombreAsignatura = $asig->asignatura->nombre_asignatura ?? '-';
            $profesorName = $asig->horario->profesor->name ?? $asig->asignatura->profesor->name ?? '-';
            $profesorEmail = $asig->horario->profesor->email ?? $asig->asignatura->profesor->email ?? '-';
            $estadoPresencia = 'ausente';
            $profesorPresente = false;
            $horaSalida = null;

            // 1. Buscar reserva activa en curso para este espacio/asignatura
            $reservaActiva = $reservasEspacio->first(function ($r) use ($horaAhora, $runProfesorNorm, $asig) {
                if ($r->estado !== 'activa') return false;
                if ($r->hora > $horaAhora) return false;
                if ($r->hora_salida !== null && $r->hora_salida < $horaAhora) return false;

                if ($r->id_asignatura && $asig->id_asignatura && $r->id_asignatura == $asig->id_asignatura) {
                    return true;
                }
                if ($runProfesorNorm && $r->run_profesor) {
                    return $this->normalizeRun($r->run_profesor) === $runProfesorNorm;
                }
                return true;
            });

            if ($reservaActiva) {
                $estadoPresencia = 'en_sala';
                $profesorPresente = true;
                if ($reservaActiva->asignatura && !empty($reservaActiva->asignatura->nombre_asignatura)) {
                    $nombreAsignatura = $reservaActiva->asignatura->nombre_asignatura;
                }
                if ($reservaActiva->profesor) {
                    $profesorName = $reservaActiva->profesor->name;
                    $profesorEmail = $reservaActiva->profesor->email ?? $profesorEmail;
                }
            } else {
                // 2. Buscar si la clase ya fue impartida y finalizada hoy
                $reservaFinalizada = $reservasEspacio->first(function ($r) use ($asig, $runProfesorNorm, $inicioModulo, $finModulo) {
                    if ($r->estado !== 'finalizada') return false;

                    // Coincidencia directa por asignatura
                    if ($r->id_asignatura && $asig->id_asignatura && $r->id_asignatura == $asig->id_asignatura) {
                        return true;
                    }

                    // Coincidencia por profesor y solapamiento horario con este módulo
                    $esMismoProfesor = $runProfesorNorm && $r->run_profesor && ($this->normalizeRun($r->run_profesor) === $runProfesorNorm);
                    $solapaHorario = (!$inicioModulo || !$finModulo) || ($r->hora <= $finModulo && ($r->hora_salida === null || $r->hora_salida >= $inicioModulo));

                    if ($esMismoProfesor && $solapaHorario) {
                        return true;
                    }

                    if ($r->clase_finalizada_anticipadamente && $solapaHorario) {
                        return true;
                    }

                    return false;
                });

                if ($reservaFinalizada) {
                    $estadoPresencia = 'finalizada';
                    $profesorPresente = true;
                    $horaSalida = $reservaFinalizada->hora_salida;
                    if ($reservaFinalizada->asignatura && !empty($reservaFinalizada->asignatura->nombre_asignatura)) {
                        $nombreAsignatura = $reservaFinalizada->asignatura->nombre_asignatura;
                    }
                    if ($reservaFinalizada->profesor) {
                        $profesorName = $reservaFinalizada->profesor->name;
                        $profesorEmail = $reservaFinalizada->profesor->email ?? $profesorEmail;
                    }
                } else {
                    // 3. Verificar si está registrada como ClaseNoRealizada
                    $esCNR = false;
                    foreach ($espaciosEquiv as $espId) {
                        $cnrs = $clasesNoRealizadasHoy->get($espId, collect());
                        foreach ($cnrs as $cnr) {
                            if ($cnr->id_asignatura == $asig->id_asignatura || str_contains($cnr->id_modulo, $idModulo)) {
                                $esCNR = true;
                                break 2;
                            }
                        }
                    }

                    if ($esCNR) {
                        $estadoPresencia = 'ausente';
                        $profesorPresente = false;
                    } elseif ($horaLimiteTolerancia && $horaAhora < $horaLimiteTolerancia) {
                        // Aún dentro del margen de tolerancia inicial
                        $estadoPresencia = 'espera';
                        $profesorPresente = false;
                    } else {
                        $estadoPresencia = 'ausente';
                        $profesorPresente = false;
                    }
                }
            }

            $asignacionesMapeadas->push((object) [
                'espacio' => $asig->espacio,
                'nombre_asignatura' => $nombreAsignatura,
                'profesor_name' => $profesorName,
                'profesor_email' => $profesorEmail,
                'profesor_presente' => $profesorPresente,
                'estado_presencia' => $estadoPresencia,
                'hora_salida' => $horaSalida,
            ]);
        }

        foreach ($asignacionesColaboradores as $asig) {
            if (!$asig->espacio) {
                continue;
            }

            $runProfesor = $asig->profesorColaborador->run_profesor_colaborador ?? null;
            $runProfesorNorm = $runProfesor ? $this->normalizeRun($runProfesor) : null;
            $idAsignaturaColab = $asig->profesorColaborador->id_asignatura ?? null;

            $espaciosEquiv = EspacioAliasHelper::obtenerEquivalentes($asig->espacio->id_espacio);
            $reservasEspacio = collect();
            foreach ($espaciosEquiv as $espId) {
                if ($reservasHoy->has($espId)) {
                    $reservasEspacio = $reservasEspacio->concat($reservasHoy->get($espId));
                }
            }

            $nombreAsignatura = $asig->profesorColaborador->nombre_asignatura ?? '-';
            $profesorName = $asig->profesorColaborador->profesor->name ?? '-';
            $profesorEmail = $asig->profesorColaborador->profesor->email ?? '-';
            $estadoPresencia = 'ausente';
            $profesorPresente = false;
            $horaSalida = null;

            // 1. Buscar reserva activa en curso
            $reservaActiva = $reservasEspacio->first(function ($r) use ($horaAhora, $runProfesorNorm, $idAsignaturaColab) {
                if ($r->estado !== 'activa') return false;
                if ($r->hora > $horaAhora) return false;
                if ($r->hora_salida !== null && $r->hora_salida < $horaAhora) return false;

                if ($r->id_asignatura && $idAsignaturaColab && $r->id_asignatura == $idAsignaturaColab) {
                    return true;
                }
                if ($runProfesorNorm && $r->run_profesor) {
                    return $this->normalizeRun($r->run_profesor) === $runProfesorNorm;
                }
                return true;
            });

            if ($reservaActiva) {
                $estadoPresencia = 'en_sala';
                $profesorPresente = true;
                if ($reservaActiva->asignatura && !empty($reservaActiva->asignatura->nombre_asignatura)) {
                    $nombreAsignatura = $reservaActiva->asignatura->nombre_asignatura;
                }
                if ($reservaActiva->profesor) {
                    $profesorName = $reservaActiva->profesor->name;
                    $profesorEmail = $reservaActiva->profesor->email ?? $profesorEmail;
                }
            } else {
                // 2. Buscar si ya fue finalizada hoy
                $reservaFinalizada = $reservasEspacio->first(function ($r) use ($idAsignaturaColab, $runProfesorNorm, $inicioModulo, $finModulo) {
                    if ($r->estado !== 'finalizada') return false;

                    if ($r->id_asignatura && $idAsignaturaColab && $r->id_asignatura == $idAsignaturaColab) {
                        return true;
                    }

                    $esMismoProfesor = $runProfesorNorm && $r->run_profesor && ($this->normalizeRun($r->run_profesor) === $runProfesorNorm);
                    $solapaHorario = (!$inicioModulo || !$finModulo) || ($r->hora <= $finModulo && ($r->hora_salida === null || $r->hora_salida >= $inicioModulo));

                    if ($esMismoProfesor && $solapaHorario) {
                        return true;
                    }

                    if ($r->clase_finalizada_anticipadamente && $solapaHorario) {
                        return true;
                    }

                    return false;
                });

                if ($reservaFinalizada) {
                    $estadoPresencia = 'finalizada';
                    $profesorPresente = true;
                    $horaSalida = $reservaFinalizada->hora_salida;
                    if ($reservaFinalizada->asignatura && !empty($reservaFinalizada->asignatura->nombre_asignatura)) {
                        $nombreAsignatura = $reservaFinalizada->asignatura->nombre_asignatura;
                    }
                    if ($reservaFinalizada->profesor) {
                        $profesorName = $reservaFinalizada->profesor->name;
                        $profesorEmail = $reservaFinalizada->profesor->email ?? $profesorEmail;
                    }
                } else {
                    $esCNR = false;
                    foreach ($espaciosEquiv as $espId) {
                        $cnrs = $clasesNoRealizadasHoy->get($espId, collect());
                        foreach ($cnrs as $cnr) {
                            if (($idAsignaturaColab && $cnr->id_asignatura == $idAsignaturaColab) || str_contains($cnr->id_modulo, $idModulo)) {
                                $esCNR = true;
                                break 2;
                            }
                        }
                    }

                    if ($esCNR) {
                        $estadoPresencia = 'ausente';
                        $profesorPresente = false;
                    } elseif ($horaLimiteTolerancia && $horaAhora < $horaLimiteTolerancia) {
                        $estadoPresencia = 'espera';
                        $profesorPresente = false;
                    } else {
                        $estadoPresencia = 'ausente';
                        $profesorPresente = false;
                    }
                }
            }

            $asignacionesMapeadas->push((object) [
                'espacio' => $asig->espacio,
                'nombre_asignatura' => $nombreAsignatura,
                'profesor_name' => $profesorName,
                'profesor_email' => $profesorEmail,
                'profesor_presente' => $profesorPresente,
                'estado_presencia' => $estadoPresencia,
                'hora_salida' => $horaSalida,
            ]);
        }

        // Agregar espacios que no tienen planificación en este módulo pero sí una reserva activa en curso
        $espaciosConAsignacion = $asignacionesMapeadas->pluck('espacio.id_espacio')->filter()->toArray();
        foreach ($reservasActivasHoy as $idEspacio => $reservas) {
            if (!in_array($idEspacio, $espaciosConAsignacion)) {
                $reservaExtra = $reservas->first();
                $espacioExtra = $reservaExtra->espacio ?? \App\Models\Espacio::with('piso')->where('id_espacio', $idEspacio)->first();
                if ($espacioExtra) {
                    $asignacionesMapeadas->push((object) [
                        'espacio' => $espacioExtra,
                        'nombre_asignatura' => $reservaExtra->asignatura->nombre_asignatura ?? ($reservaExtra->tipo_reserva === 'espontanea' ? 'Reserva Espontánea' : 'En Uso'),
                        'profesor_name' => $reservaExtra->profesor->name ?? $reservaExtra->solicitante->nombre ?? '-',
                        'profesor_email' => $reservaExtra->profesor->email ?? $reservaExtra->solicitante->correo ?? '-',
                        'profesor_presente' => true,
                        'estado_presencia' => 'en_sala',
                        'hora_salida' => null,
                        'hora_inicio_proxima' => null,
                    ]);
                }
            }
        }

        // Ventana de 15 minutos para clases próximas (coherente con Plano Digital)
        $horaLimiteProxima = Carbon::parse($fechaHoy . ' ' . $horaAhora)->addMinutes(15)->format('H:i:s');
        $diasPosibles = array_unique([$diaActual, $diaNormalizado, 'miércoles', 'miercoles', 'sábado', 'sabado']);

        $planificacionesRegularesProximas = Planificacion_Asignatura::with(['horario.profesor', 'asignatura.profesor', 'modulo', 'espacio'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaAhora, $horaLimiteProxima, $diasPosibles) {
                $query->whereIn('dia', $diasPosibles)
                    ->where('hora_inicio', '>=', $horaAhora)
                    ->where('hora_inicio', '<=', $horaLimiteProxima);
            })
            ->get();

        $colaboradoresProximos = PlanificacionProfesorColaborador::with(['modulo', 'espacio', 'profesorColaborador.profesor', 'profesorColaborador.asignatura'])
            ->whereHas('modulo', function ($query) use ($horaAhora, $horaLimiteProxima, $diasPosibles) {
                $query->whereIn('dia', $diasPosibles)
                    ->where('hora_inicio', '>=', $horaAhora)
                    ->where('hora_inicio', '<=', $horaLimiteProxima);
            })
            ->whereHas('profesorColaborador', fn($q) => $q->where('estado', 'activo')
                ->where('fecha_inicio', '<=', $fechaHoy)
                ->where('fecha_termino', '>=', $fechaHoy))
            ->get();

        // Mapear identificadores de espacios ya procesados (incluyendo alias equivalentes)
        $espaciosProcesados = [];
        foreach ($asignacionesMapeadas as $asigItem) {
            if (!empty($asigItem->espacio?->id_espacio)) {
                foreach (EspacioAliasHelper::obtenerEquivalentes($asigItem->espacio->id_espacio) as $alias) {
                    $espaciosProcesados[strtoupper($alias)] = true;
                }
            }
        }

        // Obtener todos los espacios del campus/sede actual para mostrar disponibilidad total
        $todosLosEspacios = Espacio::with('piso')->get();

        foreach ($todosLosEspacios as $esp) {
            $equivs = EspacioAliasHelper::obtenerEquivalentes($esp->id_espacio);
            $yaEsta = false;
            foreach ($equivs as $eq) {
                if (isset($espaciosProcesados[strtoupper($eq)])) {
                    $yaEsta = true;
                    break;
                }
            }
            if ($yaEsta) {
                continue;
            }

            foreach ($equivs as $eq) {
                $espaciosProcesados[strtoupper($eq)] = true;
            }

            // 1. Estado Mantención
            $estadoEspLower = strtolower($esp->estado ?? '');
            if (in_array($estadoEspLower, ['mantención', 'mantenimiento', 'mantencion'])) {
                $asignacionesMapeadas->push((object) [
                    'espacio' => $esp,
                    'nombre_asignatura' => 'Espacio en Mantención',
                    'profesor_name' => 'Fuera de Servicio',
                    'profesor_email' => 'Mantenimiento preventivo / correctivo',
                    'profesor_presente' => false,
                    'estado_presencia' => 'mantencion',
                    'hora_salida' => null,
                    'hora_inicio_proxima' => null,
                ]);
                continue;
            }

            // 2. Clase Regular Próxima (en los siguientes 15 minutos)
            $proxReg = $planificacionesRegularesProximas->first(function ($p) use ($equivs) {
                return in_array(strtoupper($p->id_espacio ?? ''), array_map('strtoupper', $equivs));
            });

            if ($proxReg) {
                $horaIni = !empty($proxReg->modulo?->hora_inicio) ? substr($proxReg->modulo->hora_inicio, 0, 5) : '';
                $asignacionesMapeadas->push((object) [
                    'espacio' => $esp,
                    'nombre_asignatura' => $proxReg->asignatura->nombre_asignatura ?? 'Clase Programada',
                    'profesor_name' => $proxReg->horario->profesor->name ?? $proxReg->asignatura->profesor->name ?? '-',
                    'profesor_email' => $proxReg->horario->profesor->email ?? $proxReg->asignatura->profesor->email ?? '-',
                    'profesor_presente' => false,
                    'estado_presencia' => 'proxima',
                    'hora_salida' => null,
                    'hora_inicio_proxima' => $horaIni,
                ]);
                continue;
            }

            // 3. Clase Colaborador Próxima (en los siguientes 15 minutos)
            $proxColab = $colaboradoresProximos->first(function ($p) use ($equivs) {
                return in_array(strtoupper($p->id_espacio ?? ''), array_map('strtoupper', $equivs));
            });

            if ($proxColab) {
                $horaIni = !empty($proxColab->modulo?->hora_inicio) ? substr($proxColab->modulo->hora_inicio, 0, 5) : '';
                $asignacionesMapeadas->push((object) [
                    'espacio' => $esp,
                    'nombre_asignatura' => $proxColab->profesorColaborador->nombre_asignatura ?? 'Clase Programada',
                    'profesor_name' => $proxColab->profesorColaborador->profesor->name ?? '-',
                    'profesor_email' => $proxColab->profesorColaborador->profesor->email ?? '-',
                    'profesor_presente' => false,
                    'estado_presencia' => 'proxima',
                    'hora_salida' => null,
                    'hora_inicio_proxima' => $horaIni,
                ]);
                continue;
            }

            // 4. Espacio Libre / Disponible
            $asignacionesMapeadas->push((object) [
                'espacio' => $esp,
                'nombre_asignatura' => 'Disponible',
                'profesor_name' => 'Sin clase programada',
                'profesor_email' => 'Espacio libre para uso',
                'profesor_presente' => false,
                'estado_presencia' => 'disponible',
                'hora_salida' => null,
                'hora_inicio_proxima' => null,
            ]);
        }

        // Ordenar por número de piso y luego orden natural por código de espacio
        $asignacionesOrdenadas = $asignacionesMapeadas->sort(function ($a, $b) {
            $pisoA = (int) ($a->espacio->piso->numero_piso ?? 99);
            $pisoB = (int) ($b->espacio->piso->numero_piso ?? 99);
            if ($pisoA !== $pisoB) {
                return $pisoA <=> $pisoB;
            }
            return strnatcasecmp($a->espacio->id_espacio ?? '', $b->espacio->id_espacio ?? '');
        })->values();

        return view('partials.dashboard.horarios_modulo_actual', [
            'diaActual' => $diaActual,
            'moduloActualNum' => $moduloActualNum,
            'moduloActualHorario' => $moduloActualHorario,
            'asignaciones' => $asignacionesOrdenadas,
        ])->render();
    }

    public function ocupacionDatosAjax(Request $request, OcupacionSemanalService $service)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        try {
            $tipoFilter = strtolower($request->query('tipo', 'todos'));
            $fechaParam = $request->query('fecha');
            $lunesDate = $fechaParam ? Carbon::parse($fechaParam)->startOfWeek() : now()->startOfWeek();

            $datos = $service->calcularOcupacion($lunesDate, $tipoFilter, true);

            return view('partials.dashboard.ocupacion_grid', [
                'ocupacion' => $datos['ocupacion'],
                'tipoFilter' => $tipoFilter,
                'resumen' => $datos['resumen'],
                'totalEspacios' => $datos['total_espacios'],
            ])->render();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error en ocupacionDatosAjax: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response(
                '<div class="p-6 text-center text-red-600 bg-red-50 border border-red-200 rounded-xl my-4">' .
                '<i class="fas fa-exclamation-triangle text-2xl mb-2 text-red-500 block"></i>' .
                '<p class="font-bold">Error al calcular la ocupación:</p>' .
                '<p class="text-xs text-red-700 mt-1 font-mono">' . htmlspecialchars($e->getMessage()) . '</p>' .
                '</div>',
                500
            );
        }
    }

    /**
     * Exportar ocupación semanal en Excel o PDF
     */
    public function exportOcupacion(Request $request, string $format, OcupacionSemanalService $service)
    {
        $tipoFilter = strtolower($request->query('tipo', 'todos'));
        $fechaParam = $request->query('fecha');
        $lunesDate = $fechaParam ? Carbon::parse($fechaParam)->startOfWeek() : now()->startOfWeek();

        $datos = $service->calcularOcupacion($lunesDate, $tipoFilter, true);

        $semanaStr = $datos['semana_inicio'] . '_al_' . $datos['semana_fin'];
        $tipoStr = str_replace('_', '-', $tipoFilter);
        $fileName = "ocupacion_semanal_{$tipoStr}_{$semanaStr}";

        if (strtolower($format) === 'excel' || strtolower($format) === 'xlsx') {
            return Excel::download(
                new OcupacionSemanalExport($datos, "Semana {$datos['semana_iso']}"),
                "{$fileName}.xlsx"
            );
        }

        if (strtolower($format) === 'pdf') {
            $pdf = Pdf::loadView('pdf.ocupacion-semanal', ['datos' => $datos]);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download("{$fileName}.pdf");
        }

        abort(404, 'Formato no soportado');
    }

    /**
     * Endpoint AJAX para el Estado de Clases (Gráfico 2D y Métricas de Asistencia por Fechas)
     * Basado en la planificación académica real (Planificacion_Asignatura) y asistencia verificada.
     */
    public function statusClasesAjax(Request $request)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $rango = $request->query('rango', 'semana');
        $fechaInicioStr = $request->query('fecha_inicio');
        $fechaFinStr = $request->query('fecha_fin');

        if (!$fechaInicioStr || !$fechaFinStr) {
            if ($rango === 'hoy') {
                $fechaInicio = now()->startOfDay();
                $fechaFin = now()->endOfDay();
            } elseif ($rango === 'mes') {
                $fechaInicio = now()->startOfMonth();
                $fechaFin = now()->endOfMonth();
            } else { // 'semana' por defecto
                $fechaInicio = now()->startOfWeek();
                $fechaFin = now()->endOfWeek();
            }
        } else {
            $fechaInicio = Carbon::parse($fechaInicioStr)->startOfDay();
            $fechaFin = Carbon::parse($fechaFinStr)->endOfDay();
        }

        $fechaInicioYmd = $fechaInicio->format('Y-m-d');
        $fechaFinYmd = $fechaFin->format('Y-m-d');

        $tenantId = \App\Models\Tenant::current()?->id ?? 'default';
        try {
            $ultimaActualizacionCnr = ClaseNoRealizada::whereBetween('fecha_clase', [$fechaInicioYmd, $fechaFinYmd])
                ->max('updated_at') ?? 'sin-cambios';
        } catch (\Throwable $e) {
            $ultimaActualizacionCnr = 'sin-cambios';
        }
        $cacheKey = "dash_status_{$tenantId}_{$rango}_{$fechaInicioYmd}_{$fechaFinYmd}_{$ultimaActualizacionCnr}";

        $computeStatusData = function () use (
        $rango, $fechaInicio, $fechaFin, $fechaInicioYmd, $fechaFinYmd, $tenantId
    ) {
        $periodo = SemesterHelper::resolverPeriodoParaFecha($fechaInicioYmd) ?: SemesterHelper::getCurrentPeriod();
        $todasClasesService = new TodasClasesService();
        $todasLasClases = $todasClasesService->obtenerTodasLasClases(
            $fechaInicioYmd,
            $fechaFinYmd,
            $periodo
        );

        // Agrupar por bloque de clase (fecha + espacio + run_profesor + asignatura)
        // Exactamente como hace Control de Clases en ClasesNoRealizadasTable para garantizar concordancia matematica
        $clasesAgrupadas = collect($todasLasClases)->groupBy(function ($item) {
            $fecha = ($item['fecha'] instanceof Carbon)
                ? $item['fecha']->format('Y-m-d')
                : Carbon::parse($item['fecha'])->format('Y-m-d');
            return $fecha . '_' . ($item['espacio'] ?? '') . '_' . ($item['run_profesor'] ?? '') . '_' . ($item['id_asignatura'] ?? '');
        });

        $realizadas = 0;
        $recuperadas = 0;
        $justificadas = 0;
        $noRegistradas = 0;
        $futurasPendientes = 0;

        foreach ($clasesAgrupadas as $bloqueItems) {
            $estados = $bloqueItems->pluck('estado')->unique()->values()->toArray();

            if (in_array('No Registrada', $estados, true)) {
                $noRegistradas++;
            } elseif (in_array('Pendiente de Recuperación', $estados, true) || in_array('Recuperada', $estados, true)) {
                $recuperadas++;
            } elseif (in_array('Justificada', $estados, true) || in_array('Feriado/Justificado', $estados, true)) {
                $justificadas++;
            } elseif (in_array('Planificada', $estados, true)) {
                $futurasPendientes++;
            } else {
                $realizadas++;
            }
        }

        $totalImpartidas = $realizadas + $recuperadas + $justificadas;
        $totalClasesEvaluadas = $totalImpartidas + $noRegistradas;

        $pctImpartidas    = $totalClasesEvaluadas > 0 ? round(($totalImpartidas / $totalClasesEvaluadas) * 100, 1) : 0;
        $pctRealizadas    = $totalClasesEvaluadas > 0 ? round(($realizadas / $totalClasesEvaluadas) * 100, 1) : 0;
        $pctRecuperadas   = $totalClasesEvaluadas > 0 ? round(($recuperadas / $totalClasesEvaluadas) * 100, 1) : 0;
        $pctJustificadas  = $totalClasesEvaluadas > 0 ? round(($justificadas / $totalClasesEvaluadas) * 100, 1) : 0;
        $pctNoRegistradas = $totalClasesEvaluadas > 0 ? round(($noRegistradas / $totalClasesEvaluadas) * 100, 1) : 0;

        return [
            'rango'              => $rango,
            'fecha_inicio'       => $fechaInicioYmd,
            'fecha_fin'          => $fechaFinYmd,
            'total_clases'       => $totalClasesEvaluadas,
            'total_impartidas'   => $totalImpartidas,
            'realizadas'         => $realizadas,
            'recuperadas'        => $recuperadas,
            'justificadas'       => $justificadas,
            'no_registradas'     => $noRegistradas,
            'futuras_pendientes' => $futurasPendientes,
            'pct_impartidas'     => $pctImpartidas,
            'pct_realizadas'     => $pctRealizadas,
            'pct_recuperadas'    => $pctRecuperadas,
            'pct_justificadas'   => $pctJustificadas,
            'pct_no_registradas' => $pctNoRegistradas,
        ];
    };

        try {
            $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, $computeStatusData);
        } catch (\Throwable $e) {
            $data = $computeStatusData();
        }

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($data);
        }

        return view('partials.dashboard.status_clases_chart', $data)->render();
    }
}

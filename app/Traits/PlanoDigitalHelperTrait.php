<?php

namespace App\Traits;

use App\Mail\ConfirmacionDevolucion;
use App\Mail\ConfirmacionReserva;
use App\Models\Tenant;
use App\Models\ClaseNoRealizada;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Planificacion_Asignatura;
use App\Models\Mapa;
use App\Models\Modulo;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionReserva;
use App\Mail\NotificacionDevolucion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Helpers\SemesterHelper;

trait PlanoDigitalHelperTrait
{

    private function obtenerMapa($id)
    {
        return Mapa::withoutGlobalScopes()
            ->with(['bloques.espacio', 'piso.facultad.sede.universidad'])
            ->where('id_mapa', $id)
            ->firstOrFail();
    }


    private function obtenerEstadoActual(Carbon $horaActual)
    {
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $horaActualStr = $horaActual->format('H:i:s');
        $fechaActual = $horaActual->format('Y-m-d');

        return [
            'hora' => $horaActualStr,
            'dia' => $diaActual,
            'fecha' => $fechaActual,
            'codigo_dia' => $this->obtenerCodigoDia($diaActual)
        ];
    }


    private function obtenerCodigoDia(string $diaActual): ?string
    {
        return match ($diaActual) {
            'lunes' => 'LU',
            'martes' => 'MA',
            'miércoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sábado' => 'SA',
            default => null
        };
    }


    private function prepararBloques(Mapa $mapa, array $estadoActual): array
    {
        $moduloActual = $this->obtenerModuloActual($estadoActual);
        $planificacionesActivas = $this->obtenerPlanificacionesActivas($mapa, $moduloActual);
        $planificacionesProximas = $this->obtenerPlanificacionesProximas($mapa, $estadoActual);

        // USAR EL MISMO $estadoActual para consistencia
        $horaActualStr = $estadoActual['hora'];
        $fechaActual = $estadoActual['fecha'];

        // Obtener reservas próximas (próximos 10 minutos) - MISMO CÁLCULO DE HORA
        $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActualStr);
        $horaLimite = $horaActualCarbon->copy()->addMinutes(10)->format('H:i:s');

        $reservasProximas = Reserva::with(['asignatura', 'profesor', 'solicitante'])
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->where('hora', '>', $horaActualStr)
            ->where('hora', '<=', $horaLimite)
            ->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'))
            ->get();

        return $mapa->bloques->map(function ($bloque) use ($planificacionesActivas, $planificacionesProximas, $reservasProximas, $mapa, $horaActualStr, $fechaActual, $moduloActual) {
            $idEspacio = $bloque->id_espacio;
            $espacio = $bloque->espacio;

            // Verificar si hay una reserva activa en este espacio
            // Obtener la reserva completa con relaciones para mostrar la información correcta
            $reservaActiva = Reserva::with(['profesor', 'asignatura.carrera', 'solicitante'])
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->where('fecha_reserva', $fechaActual)
                ->first();

            // Verificar si hay una reserva PROGRAMADA (creada con antelación, aún no activada)
            $reservaProgramada = null;
            if (!$reservaActiva) {
                $reservaProgramada = Reserva::with(['profesor', 'asignatura.carrera', 'solicitante'])
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'programada')
                    ->where('fecha_reserva', $fechaActual)
                    ->first();

                // Solo considerar la reserva programada si el módulo actual está dentro de su rango
                if ($reservaProgramada && $moduloActual) {
                    $numModActual = (int) (explode('.', $moduloActual->id_modulo)[1] ?? 0);
                    $modIniProg = (int) ($reservaProgramada->modulo_inicio ?? 0);
                    $modFinProg = (int) ($reservaProgramada->modulo_fin ?? 0);
                    if ($numModActual && $modIniProg && $modFinProg) {
                        if ($numModActual < $modIniProg || $numModActual > $modFinProg) {
                            $reservaProgramada = null;  // Fuera de franja → ignorar
                        }
                    }
                }
            }

            // Verificar si hay una clase no realizada en la tabla clases_no_realizadas para hoy
            $clasesNoRealizadasHoy = ClaseNoRealizada::with('modulo')
                ->where('id_espacio', $idEspacio)
                ->where('fecha_clase', $fechaActual)
                ->get();

            // Verificar si hay alguna clase no realizada cuyo módulo AÚN NO HA TERMINADO
            $claseSinAsistentesActiva = false;
            foreach ($clasesNoRealizadasHoy as $clase) {
                if ($clase->modulo && $clase->modulo->hora_termino > $horaActualStr) {
                    // El módulo aún no ha terminado, mostrar como no realizada
                    $claseSinAsistentesActiva = true;
                    break;
                }
            }

            // Determinar estado basado en actividad real (no en BD)
            if ($espacio->estado === 'Mantención' || $espacio->estado === 'Mantenimiento') {
                // Espacio en mantención - no permitir reservas
                $estadoFinal = 'Mantención';
            } elseif ($reservaActiva) {
                // 1. Hay reserva activa = Ocupado
                $estadoFinal = 'Ocupado';

                // Corregir BD si está inconsistente
                if ($espacio->estado !== 'Ocupado') {
                    $espacio->estado = 'Ocupado';
                    $espacio->save();
                }
            } elseif ($reservaProgramada) {
                // 1.5. Hay reserva PROGRAMADA y el módulo actual está en su rango
                $estadoFinal = 'Programado';
            } elseif ($claseSinAsistentesActiva) {
                // 2. Hubo una clase sin asistentes y el módulo aún no termina
                $estadoFinal = 'ClaseSinAsistentes';

                // Corregir BD si está inconsistente
                if ($espacio->estado !== 'Disponible') {
                    $espacio->estado = 'Disponible';
                    $espacio->save();
                }
            } else {
                // 3. Verificar planificaciones (actuales y próximas)
                $planificacionActiva = $planificacionesActivas->firstWhere('id_espacio', $idEspacio);
                $planificacionProxima = $planificacionesProximas->firstWhere('id_espacio', $idEspacio);
                $reservaProxima = $reservasProximas->firstWhere('id_espacio', $idEspacio);

                // Verificar que la planificación próxima realmente esté en rango (validación EXTRA agresiva)
                if ($planificacionProxima && isset($planificacionProxima->modulo->hora_inicio)) {
                    // Calcular diferencia de minutos
                    $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActualStr);
                    $horaProximaCarbon = Carbon::createFromFormat('H:i:s', $planificacionProxima->modulo->hora_inicio);
                    $diferencia = $horaProximaCarbon->diffInMinutes($horaActualCarbon, false);

                    // Si la diferencia es > 10 min, NO es próxima, es futura

                }

                if ($planificacionActiva) {
                    // Hay clase en curso = Reservado (naranja)
                    $estadoFinal = 'Reservado';
                } elseif ($planificacionProxima || $reservaProxima) {
                    // Solo hay clase próxima (próximos 10 min) = Reservado (naranja)
                    $estadoFinal = 'Reservado';
                } else {
                    // 4. No hay actividad = Disponible
                    $estadoFinal = 'Disponible';

                    // Corregir BD si está marcado como Ocupado sin actividad
                        $espacio->estado = 'Disponible';
                        $espacio->save();
                }
            }

            return [
                'id' => $idEspacio,
                'nombre' => $bloque->espacio->nombre_espacio,
                'x' => $bloque->posicion_x,
                'y' => $bloque->posicion_y,
                'estado' => $estadoFinal,
                'tipo' => $espacio->tipo_espacio,
                'capacidad' => $espacio->capacidad_maxima,
                'piso' => $mapa->piso->numero_piso,
                'detalles' => array_merge(
                    $this->prepararDetallesBloque(
                        $bloque->espacio,
                        $planificacionActiva ?? null,
                        $reservaActiva ?? $reservaProgramada,  // Pasar la reserva (activa o programada)
                        $planificacionProxima ?? null
                    ),
                    [
                        'estado' => $espacio->estado,
                        'facultad' => $mapa->piso->facultad->nombre_facultad,
                        'clase_sin_asistentes' => $claseSinAsistentesActiva,
                        'clases_no_realizadas' => $clasesNoRealizadasHoy,
                        'es_programada' => ($estadoFinal === 'Programado'),
                        'nombre_actividad' => $reservaProgramada->nombre_actividad ?? null,
                    ]
                )
            ];
        })->toArray();
    }


    private function obtenerModuloActual(array $estadoActual): ?Modulo
    {
        $modulo = Modulo::where('dia', $estadoActual['dia'])
            ->where('hora_inicio', '<=', $estadoActual['hora'])
            ->where('hora_termino', '>=', $estadoActual['hora'])
            ->first();

        // Si no hay módulo en curso (fuera de horario), asumir módulo 1 del día
        if (!$modulo) {
            $codigoDia = $this->obtenerCodigoDia($estadoActual['dia']);
            if ($codigoDia) {
                $modulo = Modulo::where('id_modulo', $codigoDia . '.1')->first();
            }
        }

        return $modulo;
    }


    private function obtenerPlanificacionesActivas(Mapa $mapa, ?Modulo $moduloActual)
    {
        if (!$moduloActual) {
            return collect([]);
        }

        $periodo = SemesterHelper::getCurrentPeriod();

        // Obtener la hora actual
        $horaActual = Carbon::now()->format('H:i:s');

        // Obtener planificaciones de asignaturas regulares
        $planificacionesRegulares = Planificacion_Asignatura::with(['horario.profesor', 'asignatura.profesor', 'modulo', 'espacio'])

            ->where('id_modulo', $moduloActual->id_modulo)
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaActual) {
                $query->where('hora_termino', '>=', $horaActual);  // Solo módulos que no han terminado
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        // Obtener planificaciones de profesores colaboradores
        $planificacionesTemporales = PlanificacionProfesorColaborador::with(['profesorColaborador', 'modulo', 'espacio'])
            ->where('id_modulo', $moduloActual->id_modulo)
            ->whereHas('modulo', function ($query) use ($horaActual) {
                $query->where('hora_termino', '>=', $horaActual);  // Solo módulos que no han terminado
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        return $planificacionesRegulares->merge($planificacionesTemporales);
    }


    private function obtenerPlanificacionesProximas(Mapa $mapa, array $estadoActual)
    {
        $horaActual = Carbon::parse($estadoActual['hora']);
        $diaActual = $estadoActual['dia'];

        $periodo = SemesterHelper::getCurrentPeriod();

        // Verificar si estamos en el rango especial de 05:00 a 05:10
        $horaActualStr = $horaActual->format('H:i:s');
        $esRangoEspecial = ($horaActualStr >= '05:00:00' && $horaActualStr <= '05:10:00');

        if ($esRangoEspecial) {
            // Para el rango 05:00-05:10, buscar clases que empiecen a las 05:10
            $horaInicioBusqueda = '05:00:00';
            $horaFinBusqueda = '05:10:00';
        } else {
            // Para otros horarios, usar la lógica normal (15 minutos después)
            $horaInicioBusqueda = $horaActual->format('H:i:s');
            $horaFinBusqueda = $horaActual->copy()->addMinutes(15)->format('H:i:s');
        }

        // Obtener planificaciones regulares próximas
        $planificacionesRegularesProximas = Planificacion_Asignatura::with(['horario.profesor', 'asignatura.profesor', 'modulo', 'espacio'])

            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaInicioBusqueda, $horaFinBusqueda, $diaActual, $esRangoEspecial) {
                $query->where('dia', $diaActual);

                if ($esRangoEspecial) {
                    // Para el rango 05:00-05:10, buscar módulos que empiecen a las 05:10
                    $query->where('hora_inicio', '=', '05:10:00');
                } else {
                    // Para otros horarios, buscar módulos que empiecen en los próximos 15 minutos
                    // IMPORTANTE: >= para incluir clases que comienzan AHORA también
                    $query
                        ->where('hora_inicio', '>=', $horaInicioBusqueda)
                        ->where('hora_inicio', '<=', $horaFinBusqueda);
                }
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        // Obtener planificaciones de profesores colaboradores próximas
        $planificacionesTemporalesProximas = PlanificacionProfesorColaborador::with(['profesorColaborador', 'modulo', 'espacio'])
            ->whereHas('modulo', function ($query) use ($horaInicioBusqueda, $horaFinBusqueda, $diaActual, $esRangoEspecial) {
                $query->where('dia', $diaActual);

                if ($esRangoEspecial) {
                    // Para el rango 05:00-05:10, buscar módulos que empiecen a las 05:10
                    $query->where('hora_inicio', '=', '05:10:00');
                } else {
                    // Para otros horarios, buscar módulos que empiecen en los próximos 10 minutos
                    // IMPORTANTE: >= para incluir clases que comienzan AHORA también
                    $query
                        ->where('hora_inicio', '>=', $horaInicioBusqueda)
                        ->where('hora_inicio', '<=', $horaFinBusqueda);
                }
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        return $planificacionesRegularesProximas->merge($planificacionesTemporalesProximas);
    }


    private function prepararDetallesBloque($espacio, $planificacion, $reserva, $planificacionProxima): array
    {
        $detalles = [
            'tipo_espacio' => $espacio->tipo_espacio ?? 'No especificado',
            'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
            'planificacion' => null,
            'reserva' => null,
            'planificacion_proxima' => null
        ];

        // PRIORIDAD 1: Si hay reserva activa de profesor con asignatura Y es programada/cambio de sala
        // Esto asegura que se muestre la clase que realmente se está dando, no la programada
        // Las reservas espontáneas NO deben mostrar información de clase
        if ($reserva && $reserva->run_profesor && $reserva->asignatura && $reserva->tipo_reserva !== 'espontanea') {
            // El profesor está dando esta asignatura (puede ser diferente a la planificada)
            $detalles['planificacion'] = [
                'asignatura' => $reserva->asignatura->nombre_asignatura ?? 'Sin asignatura',
                'codigo_asignatura' => $reserva->asignatura->codigo_asignatura ?? '-',
                'profesor' => ucwords($reserva->profesor->name ?? 'No asignado'),
                'carrera' => $reserva->asignatura->carrera->nombre ?? '-',
                'es_reserva_activa' => true,  // Flag para identificar que viene de reserva
                'modulos' => []  // No mostrar módulos ya que es la clase actual
            ];
        } elseif ($planificacion && $planificacion->asignatura) {
            // PRIORIDAD 2: Si NO hay reserva, mostrar la planificación del espacio
            $detalles['planificacion'] = [
                'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin asignatura',
                'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? '-',
                'profesor' => ucwords($planificacion->horario->profesor->name ?? 'No asignado'),
                'carrera' => $planificacion->asignatura->carrera->nombre ?? '-',
                'es_reserva_activa' => false,
                'modulos' => $planificacion
                    ->asignatura
                    ->planificaciones()
                    ->where('id_espacio', $espacio->id_espacio)
                    ->with('modulo')
                    ->get()
                    ->map(function ($plan) {
                        return [
                            'dia' => $plan->modulo->dia ?? 'No especificado',
                            'hora_inicio' => $plan->modulo->hora_inicio ?? '00:00:00',
                            'hora_termino' => $plan->modulo->hora_termino ?? '00:00:00'
                        ];
                    })
                    ->toArray()
            ];
        }

        if ($planificacionProxima && $planificacionProxima->asignatura) {
            $detalles['planificacion_proxima'] = [
                'asignatura' => $planificacionProxima->asignatura->nombre_asignatura ?? 'Sin asignatura',
                'profesor' => ucwords($planificacionProxima->horario->profesor->name ?? 'No asignado'),
                'hora_inicio' => substr($planificacionProxima->modulo->hora_inicio ?? '00:00', 0, 5),
                'hora_termino' => substr($planificacionProxima->modulo->hora_termino ?? '00:00', 0, 5),
                'modulo' => explode('.', $planificacionProxima->modulo->id_modulo ?? '')[1] ?? 'No especificado'
            ];
        }

        if ($reserva) {
            $detalles['reserva'] = [
                'fecha_reserva' => $reserva->fecha_reserva ?? 'No especificada',
                'hora' => $reserva->hora ?? '00:00:00',
                'hora_salida' => $reserva->hora_salida ?? null
            ];
            // Incluir el nombre del usuario que ocupa el espacio
            $detalles['usuario_ocupando'] = $reserva->user ? $reserva->user->name : null;

            // Incluir información adicional del usuario si está disponible
            if ($reserva->user) {
                $detalles['usuario_info'] = [
                    'nombre' => $reserva->user->name ?? 'No especificado',
                    'email' => $reserva->user->email ?? 'No especificado',
                    'run' => $reserva->user->run ?? 'No especificado'
                ];
            }
        } else {
            $detalles['usuario_ocupando'] = null;
            $detalles['usuario_info'] = null;
        }

        return $detalles;
    }

    private function enviarCorreoReserva(Reserva $reserva): void
    {
        try {
            $reserva->load(['profesor', 'solicitante', 'espacio', 'asignatura']);
            $email = $this->resolverEmailReserva($reserva);
            if ($email) {
                Mail::to($email)->send(new ConfirmacionReserva($reserva));
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de confirmación de reserva: ' . $e->getMessage(), [
                'id_reserva' => $reserva->id_reserva,
            ]);
        }
    }

    private function enviarCorreoDevolucion(Reserva $reserva): void
    {
        try {
            $reserva->load(['profesor', 'solicitante', 'espacio', 'asignatura']);
            $email = $this->resolverEmailReserva($reserva);
            if ($email) {
                Mail::to($email)->send(new ConfirmacionDevolucion($reserva));
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de confirmación de devolución: ' . $e->getMessage(), [
                'id_reserva' => $reserva->id_reserva,
            ]);
        }
    }

    private function resolverEmailReserva(Reserva $reserva): ?string
    {
        if ($reserva->run_profesor && $reserva->profesor) {
            return $reserva->profesor->email ?: null;
        }
        if ($reserva->run_solicitante && $reserva->solicitante) {
            return $reserva->solicitante->correo ?: null;
        }
        return null;
    }

    private function establecerContextoTenant()
    {
        $tenantId = tenant_id() ?? session('tenant_id') ?? null;

        if (!$tenantId) {
            $host = request()->getHost();
            $tenant = Tenant::where('domain', $host)
                ->orWhere('domain', 'LIKE', '%' . $host . '%')
                ->first();

            if ($tenant) {
                $tenant->makeCurrent();

            }
        } elseif (session()->has('tenant_id')) {
            $tenant = Tenant::find(session('tenant_id'));
            if ($tenant) {
                $tenant->makeCurrent();

            }
        }
    }

    private function obtenerMapaHorariosModulos(): array
    {
        return [
            1 => ['inicio' => '08:10:00', 'fin' => '08:55:00'],
            2 => ['inicio' => '08:55:00', 'fin' => '09:40:00'],
            3 => ['inicio' => '09:55:00', 'fin' => '10:40:00'],
            4 => ['inicio' => '10:40:00', 'fin' => '11:25:00'],
            5 => ['inicio' => '11:35:00', 'fin' => '12:20:00'],
            6 => ['inicio' => '12:20:00', 'fin' => '13:05:00'],
            7 => ['inicio' => '13:30:00', 'fin' => '14:15:00'],
            8 => ['inicio' => '14:15:00', 'fin' => '15:00:00'],
            9 => ['inicio' => '15:15:00', 'fin' => '16:00:00'],
            10 => ['inicio' => '16:00:00', 'fin' => '16:45:00'],
            11 => ['inicio' => '16:55:00', 'fin' => '17:40:00'],
            12 => ['inicio' => '17:40:00', 'fin' => '18:25:00'],
            13 => ['inicio' => '18:30:00', 'fin' => '19:15:00'],
            14 => ['inicio' => '19:15:00', 'fin' => '20:00:00'],
        ];
    }

    private function limpiarCacheEstadosEspacios()
    {
        try {
            $tenantId = Tenant::current()?->id ?? 'default';
            \Illuminate\Support\Facades\Cache::forget("estados_espacios_{$tenantId}");
            Log::info("Caché de estados de espacios limpiado para tenant: {$tenantId}");
        } catch (\Exception $e) {
            Log::error('Error al limpiar caché de estados: ' . $e->getMessage());
        }
    }

    private function normalizeEspacioId(?string $idEspacio): ?string
    {
        if (empty($idEspacio)) {
            return $idEspacio;
        }

        // Convertir a mayúsculas y quitar espacios
        $idEspacio = strtoupper(trim(str_replace(' ', '', $idEspacio)));

        // Obtener el prefijo del tenant actual (ej: "CH", "LA", "CT", "TH")
        $tenant = Tenant::current();
        if (!$tenant) {
            return $idEspacio;
        }
        $prefix = strtoupper($tenant->domain);

        // Remover todos los guiones temporalmente para unificar el formato
        $normalizedInput = str_replace('-', '', $idEspacio);

        // Si el código normalizado comienza con el prefijo del tenant, lo removemos para obtener el "core"
        if (strpos($normalizedInput, $prefix) === 0) {
            $normalizedInput = substr($normalizedInput, strlen($prefix));
        }

        // El ID final en base de datos tiene la estructura: PREFIX-CORE
        return $prefix . '-' . $normalizedInput;
    }
}

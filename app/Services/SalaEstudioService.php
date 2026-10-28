<?php

namespace App\Services;

use App\Models\Espacio;
use App\Models\Notificacion;
use App\Models\Profesor;
use App\Models\Reserva;
use App\Models\Solicitante;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VetoSalaEstudio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SalaEstudioService
{
    /**
     * Obtener reservas de salas de estudio con datos enriquecidos.
     */
    public function getReservas(array $filtros = []): array
    {
        $tenant = session()->has('tenant_id') ? Tenant::find(session('tenant_id')) : Tenant::where('is_active', true)->first();
        if ($tenant && $tenant->database) {
            config(['database.connections.tenant.database' => $tenant->database]);
            app('db')->purge('tenant');
            app('db')->disconnect('tenant');
        }

        $espaciosEstudio = Espacio::on('tenant')
            ->where(function ($q) {
                $q->where('tipo_espacio', 'like', '%estudio%')
                  ->orWhere('tipo_espacio', 'like', '%Estudio%')
                  ->orWhere('nombre_espacio', 'like', '%estudio%')
                  ->orWhere('nombre_espacio', 'like', '%Estudio%');
            })
            ->pluck('id_espacio');

        $query = Reserva::on('tenant')
            ->whereIn('id_espacio', $espaciosEstudio)
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora', 'desc');

        if (!empty($filtros['estado']) && $filtros['estado'] !== 'todos') {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['espacio']) && $filtros['espacio'] !== 'todos') {
            $query->where('id_espacio', $filtros['espacio']);
        }

        if (!empty($filtros['fecha'])) {
            $query->whereDate('fecha_reserva', $filtros['fecha']);
        }

        $reservasRaw = $query->get();

        $reservas = $reservasRaw->map(function ($reserva) {
            $nombreResponsable = 'Sin asignar';
            $runResponsable = 'N/A';

            if ($reserva->run_solicitante) {
                $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reserva->run_solicitante)->first();
                $nombreResponsable = $solicitante ? $solicitante->nombre : $reserva->run_solicitante;
                $runResponsable = $reserva->run_solicitante;
            } elseif ($reserva->run_profesor) {
                $profesor = Profesor::on('tenant')->where('run_profesor', $reserva->run_profesor)->first();
                $nombreResponsable = $profesor ? $profesor->name : $reserva->run_profesor;
                $runResponsable = $reserva->run_profesor;
            }

            $espacio = Espacio::on('tenant')->where('id_espacio', $reserva->id_espacio)->first();
            $nombreEspacio = $espacio ? ($espacio->nombre_espacio ?? $espacio->id_espacio) : $reserva->id_espacio;

            $minutosTranscurridos = 0;
            $vencida = false;
            $proximaVencer = false;
            $sancionado = false;
            if ($reserva->estado === 'activa' && $reserva->fecha_reserva && $reserva->hora) {
                $inicioStr = ($reserva->fecha_reserva instanceof Carbon ? $reserva->fecha_reserva->format('Y-m-d') : $reserva->fecha_reserva) . ' ' . $reserva->hora;
                $inicio = Carbon::parse($inicioStr);
                $minutosTranscurridos = (int) $inicio->diffInMinutes(now(), false);

                if ($minutosTranscurridos >= 120) {
                    $vencida = true;
                    if ($runResponsable !== 'N/A' && VetoSalaEstudio::estaVetado($runResponsable)) {
                        $sancionado = true;
                    }
                } elseif ($minutosTranscurridos >= 105) {
                    $proximaVencer = true;
                }
            }

            return [
                'id_reserva' => $reserva->id_reserva,
                'id_espacio' => $reserva->id_espacio,
                'nombre_espacio' => $nombreEspacio,
                'run_responsable' => $runResponsable,
                'nombre_responsable' => $nombreResponsable,
                'fecha_reserva' => $reserva->fecha_reserva ? ($reserva->fecha_reserva instanceof Carbon ? $reserva->fecha_reserva->format('d/m/Y') : Carbon::parse($reserva->fecha_reserva)->format('d/m/Y')) : 'N/A',
                'hora_inicio' => $reserva->hora ? substr($reserva->hora, 0, 5) : 'N/A',
                'hora_salida' => $reserva->hora_salida ? substr($reserva->hora_salida, 0, 5) : null,
                'estado' => $reserva->estado,
                'minutos_transcurridos' => max(0, $minutosTranscurridos),
                'vencida' => $vencida,
                'proxima_vencer' => $proximaVencer,
                'sancionado' => $sancionado,
                'observaciones' => $reserva->observaciones,
            ];
        });

        return [
            'reservas' => $reservas->values()->all(),
            'total' => $reservas->count(),
            'vigentes' => $reservas->where('estado', 'activa')->count(),
        ];
    }

    /**
     * Procesar escaneo de carnet / QR para salas de estudio.
     */
    public function procesarEscaneo(array $validated): array
    {
        $idEspacioRaw = $validated['id_espacio'] ?? null;
        $rawRun = $validated['run'];
        $paso = $validated['paso'] ?? ($idEspacioRaw && $idEspacioRaw !== 'DESCONOCIDO' ? 'asignar_sala' : 'validar_usuario');

        if (preg_match('#[?&]run=([^&/]+)#i', $rawRun, $m)) {
            $runClean = preg_replace('/[^0-9kK]/', '', $m[1]);
        } elseif (preg_match('#RUN[^0-9]*([0-9.Kk-]+)#i', $rawRun, $m)) {
            $runClean = preg_replace('/[^0-9kK]/', '', $m[1]);
        } elseif (preg_match('#([0-9]{1,2}(?:\.[0-9]{3}){2}-?[0-9Kk]|[0-9]{7,9}-?[0-9Kk]?)#i', $rawRun, $m)) {
            $runClean = preg_replace('/[^0-9kK]/', '', $m[1]);
        } else {
            $runClean = preg_replace('/[^0-9kK]/', '', $rawRun);
        }

        if (empty($runClean)) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'RUN no válido.',
            ];
        }

        $runBase = preg_replace('/[^0-9]/', '', $runClean);
        $dv = strtoupper(substr($runClean, -1));
        $runConGuion = (is_numeric($dv) ? $runClean : (strlen($runBase) >= 7 ? substr($runBase, 0, -1) . '-' . $dv : $runClean));
        $runSinGuion = $runBase . (is_numeric($dv) ? '' : $dv);

        $usuario = User::whereIn('run', [$runClean, $runBase, $runConGuion, $runSinGuion])->first();
        $solicitante = Solicitante::on('tenant')->whereIn('run_solicitante', [$runClean, $runBase, $runConGuion, $runSinGuion])->first();
        $profesor = Profesor::on('tenant')->whereIn('run_profesor', [$runClean, $runBase, $runConGuion, $runSinGuion])->first();

        // Si el usuario existe en users o profesors pero aún no está en la tabla solicitantes del tenant,
        // sincronizarlo automáticamente en solicitantes para respetar la clave foránea reservas_run_solicitante_foreign.
        if (!$solicitante && ($usuario || $profesor)) {
            $runSync = $usuario ? (string) $usuario->run : (string) $profesor->run_profesor;
            $nombreSync = $usuario ? $usuario->name : $profesor->name;
            $correoSync = ($usuario ? $usuario->email : $profesor->email) ?: ($runSync . '@temp.com');
            $telefonoSync = ($usuario ? ($usuario->celular ?? null) : ($profesor->celular ?? null)) ?: '000000000';
            $tipoSync = $profesor ? 'personal' : 'estudiante';

            if (Solicitante::on('tenant')->where('correo', $correoSync)->where('run_solicitante', '!=', $runSync)->exists()) {
                $correoSync = $runSync . '@temp.com';
            }

            $solicitante = Solicitante::on('tenant')->firstOrCreate(
                ['run_solicitante' => $runSync],
                [
                    'nombre' => $nombreSync ?: 'Usuario ' . $runSync,
                    'correo' => $correoSync,
                    'telefono' => $telefonoSync,
                    'tipo_solicitante' => $tipoSync,
                    'activo' => true,
                    'fecha_registro' => now(),
                ]
            );
        }

        $run = $solicitante ? $solicitante->run_solicitante : ($usuario ? $usuario->run : ($profesor ? $profesor->run_profesor : $runClean));
        $nombreAlumno = $solicitante ? $solicitante->nombre : ($usuario ? $usuario->name : ($profesor ? $profesor->name : 'Usuario'));

        // 1. PRIMERO: Verificar si ya hay una reserva activa del responsable en cualquier sala de estudio => DEVOLUCIÓN
        // Importante: la devolución/liberación SIEMPRE debe permitirse aunque el estudiante ya haya superado las 2 horas o esté vetado.
        $reservaActivaQuery = Reserva::on('tenant')
            ->where('estado', 'activa')
            ->where(function ($q) use ($runClean, $runBase, $runConGuion, $runSinGuion) {
                $q->whereIn('run_solicitante', [$runClean, $runBase, $runConGuion, $runSinGuion])
                  ->orWhereIn('run_profesor', [$runClean, $runBase, $runConGuion, $runSinGuion]);
            });

        if (!empty($idEspacioRaw) && $idEspacioRaw !== 'DESCONOCIDO') {
            $reservaEnEspacio = (clone $reservaActivaQuery)->where('id_espacio', $idEspacioRaw)->orderBy('fecha_reserva', 'desc')->first();
            $reservaActiva = $reservaEnEspacio ?? $reservaActivaQuery->orderBy('fecha_reserva', 'desc')->first();
        } else {
            $reservaActiva = $reservaActivaQuery->orderBy('fecha_reserva', 'desc')->first();
        }

        if ($reservaActiva) {
            $inicioStr = ($reservaActiva->fecha_reserva instanceof Carbon ? $reservaActiva->fecha_reserva->format('Y-m-d') : $reservaActiva->fecha_reserva) . ' ' . $reservaActiva->hora;
            $inicio = Carbon::parse($inicioStr);
            $minutosTranscurridos = max(0, (int) $inicio->diffInMinutes(now(), false));

            $sancionado = false;
            $fechaFinVetoTxt = null;

            // Primero liberar la reserva y el espacio para garantizar que NUNCA quede bloqueada la sala
            if ($minutosTranscurridos > 120) {
                $sancionado = true;
                $fechaFinVetoTxt = now()->addDays(7)->format('d/m/Y');
                $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . " | Devuelta con atraso ({$minutosTranscurridos} min) - Bloqueo 1 semana aplicado");
            } else {
                $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . ' | Sala devuelta mediante escáner a tiempo');
            }

            $reservaActiva->estado = 'finalizada';
            $reservaActiva->hora_salida = now()->format('H:i:s');
            $reservaActiva->save();

            $espacioLiberado = Espacio::on('tenant')->where('id_espacio', $reservaActiva->id_espacio)->first();
            if ($espacioLiberado) {
                // Verificar si quedan otras reservas activas en el espacio
                $otrasActivas = Reserva::on('tenant')
                    ->where('id_espacio', $espacioLiberado->id_espacio)
                    ->where('estado', 'activa')
                    ->where('id_reserva', '!=', $reservaActiva->id_reserva)
                    ->exists();

                if (!$otrasActivas) {
                    $espacioLiberado->estado = 'Disponible';
                    $espacioLiberado->save();
                }
            }

            // Si se pasó de las 2 horas (120 min), aplicar bloqueo de 1 semana (de forma segura, después de liberar la sala)
            if ($sancionado) {
                VetoSalaEstudio::aplicarVetoPorTiempoExcedido($run, $reservaActiva->id_reserva, $minutosTranscurridos, 7);
            }

            $mensaje = $sancionado
                ? "Devolución registrada para {$nombreAlumno} en la sala {$reservaActiva->id_espacio}. ⚠️ SANCIÓN APLICADA: Superó el límite de 2 horas ({$minutosTranscurridos} min). Usuario bloqueado por 1 semana hasta el {$fechaFinVetoTxt}."
                : "Devolución registrada exitosamente para {$nombreAlumno} en la sala {$reservaActiva->id_espacio} ({$minutosTranscurridos} min de uso).";

            return [
                'success' => true,
                'status' => 200,
                'devolucion' => true,
                'sancionado' => $sancionado,
                'minutos_transcurridos' => $minutosTranscurridos,
                'fecha_fin_veto' => $fechaFinVetoTxt,
                'nombre_estudiante' => $nombreAlumno,
                'id_espacio' => $reservaActiva->id_espacio,
                'message' => $mensaje,
            ];
        }

        // 2. SEGUNDO: Si NO está devolviendo una sala, verificar si el usuario tiene bloqueo/veto activo para impedir NUEVAS reservas
        $veto = VetoSalaEstudio::vetoActivo($runClean) ?? VetoSalaEstudio::vetoActivo($runBase);
        if ($veto) {
            $fechaFinObj = $veto->fecha_fin_veto ?? ($veto->fecha_veto ? $veto->fecha_veto->copy()->addDays(7) : null);
            $hastaTxt = $fechaFinObj ? ' hasta el ' . $fechaFinObj->format('d/m/Y H:i') : '';
            return [
                'success' => false,
                'status' => 403,
                'message' => "🚫 Acceso Denegado: Usuario bloqueado{$hastaTxt}. Motivo: {$veto->observacion}",
                'vetado' => true,
                'fecha_fin_veto' => $fechaFinObj ? $fechaFinObj->format('d/m/Y H:i') : null,
            ];
        }

        if (!$usuario && !$solicitante && !empty($validated['nombre']) && !empty($validated['correo'])) {
            $solicitante = Solicitante::on('tenant')->create([
                'run_solicitante' => $runClean,
                'nombre' => $validated['nombre'],
                'correo' => $validated['correo'],
                'telefono' => $validated['telefono'] ?? null,
                'tipo_solicitante' => $validated['tipo_solicitante'] ?? 'estudiante',
            ]);
            $run = $solicitante->run_solicitante;
            $nombreAlumno = $solicitante->nombre;
        }

        if (!$usuario && !$solicitante) {
            return [
                'success' => false,
                'status' => 200,
                'requiere_registro' => true,
                'run' => $runClean,
                'message' => 'El usuario no se encuentra registrado. Por favor complete el registro.',
            ];
        }

        if ($paso === 'validar_usuario' || empty($idEspacioRaw) || $idEspacioRaw === 'DESCONOCIDO') {
            return [
                'success' => true,
                'status' => 200,
                'requiere_escaneo_sala' => true,
                'nombre_estudiante' => $nombreAlumno,
                'run' => $runClean,
            ];
        }

        $countReservasActivas = Reserva::on('tenant')
            ->where('estado', 'activa')
            ->where(function ($q) use ($runClean, $runBase, $runConGuion, $runSinGuion) {
                $q->whereIn('run_solicitante', [$runClean, $runBase, $runConGuion, $runSinGuion])
                  ->orWhereIn('run_profesor', [$runClean, $runBase, $runConGuion, $runSinGuion]);
            })
            ->whereDate('fecha_reserva', today())
            ->count();

        if ($countReservasActivas >= 2) {
            return [
                'success' => false,
                'status' => 422,
                'message' => "🚫 Límite alcanzado: El usuario {$nombreAlumno} ya posee 2 salas de estudio reservadas activas.",
            ];
        }

        $idEspacioLimpio = strtoupper(str_replace("'", "-", $idEspacioRaw));

        $espacio = Espacio::on('tenant')->whereIn('id_espacio', [$idEspacioRaw, $idEspacioLimpio])->first();
        if (!$espacio) {
            $idSoloAlfa = preg_replace('/[^A-Z0-9]/i', '', $idEspacioRaw);
            $espacio = Espacio::on('tenant')->get()->first(function ($e) use ($idSoloAlfa) {
                return preg_replace('/[^A-Z0-9]/i', '', $e->id_espacio) === $idSoloAlfa;
            });
        }

        if (!$espacio) {
            return [
                'success' => false,
                'status' => 404,
                'message' => "El espacio '{$idEspacioRaw}' no existe.",
            ];
        }

        $idEspacio = $espacio->id_espacio;
        $ahora = now();

        // Garantizar que el registro exista en tenant.solicitantes justo antes de insertar en reservas
        if (!Solicitante::on('tenant')->where('run_solicitante', $run)->exists()) {
            $correoGarantizado = ($solicitante->correo ?? $usuario?->email ?? $profesor?->email) ?: ($run . '@temp.com');
            if (Solicitante::on('tenant')->where('correo', $correoGarantizado)->where('run_solicitante', '!=', $run)->exists()) {
                $correoGarantizado = $run . '_' . uniqid() . '@temp.com';
            }
            $solicitante = Solicitante::on('tenant')->create([
                'run_solicitante' => $run,
                'nombre' => $nombreAlumno ?: ('Usuario ' . $run),
                'correo' => $correoGarantizado,
                'telefono' => ($solicitante->telefono ?? $usuario?->celular ?? $profesor?->celular) ?: '000000000',
                'tipo_solicitante' => $profesor ? 'personal' : 'estudiante',
                'activo' => true,
                'fecha_registro' => $ahora,
            ]);
            $run = $solicitante->run_solicitante;
        }

        $nuevaReserva = Reserva::on('tenant')->create([
            'id_reserva' => Reserva::generarIdUnico(),
            'id_espacio' => $idEspacio,
            'run_solicitante' => $run,
            'fecha_reserva' => $ahora->toDateString(),
            'hora' => $ahora->format('H:i:s'),
            'hora_salida' => $ahora->copy()->addHours(2)->format('H:i:s'), // Límite de 2 horas
            'estado' => 'activa',
            'tipo_reserva' => 'espontanea',
            'observaciones' => 'Sala de Estudio - Lector QR (2h max)',
        ]);

        $espacio->estado = 'Ocupado';
        $espacio->save();

        return [
            'success' => true,
            'status' => 200,
            'reserva_creada' => true,
            'nombre_estudiante' => $nombreAlumno,
            'id_espacio' => $idEspacio,
            'message' => "Reserva iniciada exitosamente por 2 horas para {$nombreAlumno} en la sala {$idEspacio}.",
            'reserva' => $nuevaReserva,
        ];
    }

    /**
     * Verificar en segundo plano reservas activas de salas de estudio para notificaciones y bloqueos.
     */
    public function verificarNotificaciones(): array
    {
        $espaciosEstudio = Espacio::on('tenant')
            ->where('tipo_espacio', 'like', '%estudio%')
            ->orWhere('tipo_espacio', 'Sala de Estudio')
            ->pluck('id_espacio');

        $reservasActivas = Reserva::on('tenant')
            ->where('estado', 'activa')
            ->where(function ($q) use ($espaciosEstudio) {
                $q->whereIn('id_espacio', $espaciosEstudio)
                  ->orWhere('observaciones', 'like', '%Sala de estudio%');
            })
            ->whereDate('fecha_reserva', today())
            ->get();

        $advertenciasEnviadas = 0;
        $vencidosEnviadas = 0;
        $bloqueosAplicados = 0;

        foreach ($reservasActivas as $reserva) {
            if ($reserva->fecha_reserva && $reserva->hora) {
                $inicioStr = ($reserva->fecha_reserva instanceof Carbon ? $reserva->fecha_reserva->format('Y-m-d') : $reserva->fecha_reserva) . ' ' . $reserva->hora;
                $inicio = Carbon::parse($inicioStr);
                $minutosTranscurridos = (int) $inicio->diffInMinutes(now(), false);

                $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reserva->run_solicitante)->first();
                $nombreAlumno = $solicitante ? $solicitante->nombre : ($reserva->run_solicitante ?? 'Alumno');
                $runResponsable = $reserva->run_solicitante ?? $reserva->run_profesor;

                if ($minutosTranscurridos >= 120) {
                    Notificacion::crearNotificacionSalaEstudioVencida($reserva, $nombreAlumno);
                    $vencidosEnviadas++;

                    // Aplicar sanción de 1 semana si aún no está vetado
                    if ($runResponsable && !VetoSalaEstudio::estaVetado($runResponsable)) {
                        VetoSalaEstudio::aplicarVetoPorTiempoExcedido($runResponsable, $reserva->id_reserva, $minutosTranscurridos, 7);
                        $bloqueosAplicados++;
                    }
                } elseif ($minutosTranscurridos >= 105) {
                    Notificacion::crearNotificacionSalaEstudioAdvertencia($reserva, $nombreAlumno);
                    $advertenciasEnviadas++;
                }
            }
        }

        return [
            'advertencias_enviadas' => $advertenciasEnviadas,
            'vencidos_enviadas' => $vencidosEnviadas,
            'bloqueos_aplicados' => $bloqueosAplicados,
        ];
    }
}

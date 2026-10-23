<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Models\Tenant;

class Notificacion extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'notificaciones';

    protected $fillable = [
        'run_usuario',
        'tipo',
        'titulo',
        'mensaje',
        'url',
        'leida',
        'fecha_lectura',
        'datos_adicionales',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'fecha_lectura' => 'datetime',
        'datos_adicionales' => 'array',
    ];

    /**
     * Catálogo de tipos de notificación (etiqueta, ícono Font Awesome y color lógico).
     * Los colores se traducen a clases Tailwind en la vista del Centro de Notificaciones.
     */
    public const TIPOS = [
        'clase_no_realizada' => ['label' => 'Clases no realizadas', 'icon' => 'fa-triangle-exclamation', 'color' => 'red'],
        'clase_reagendada' => ['label' => 'Reagendamientos', 'icon' => 'fa-calendar-check', 'color' => 'emerald'],
        'sala_estudio_advertencia' => ['label' => 'Sala de estudio por vencer', 'icon' => 'fa-hourglass-half', 'color' => 'amber'],
        'sala_estudio_vencida' => ['label' => 'Sala de estudio vencida', 'icon' => 'fa-clock', 'color' => 'orange'],
        'soporte_respuesta' => ['label' => 'Soporte', 'icon' => 'fa-headset', 'color' => 'blue'],
    ];

    /**
     * Metadatos del tipo (con valores por defecto para tipos desconocidos).
     */
    public static function metaTipo(?string $tipo): array
    {
        return static::TIPOS[$tipo] ?? [
            'label' => $tipo ? ucfirst(str_replace('_', ' ', $tipo)) : 'General',
            'icon' => 'fa-circle-info',
            'color' => 'slate',
        ];
    }

    /**
     * URL de destino calculada dinámicamente según el tipo (evita URLs guardadas obsoletas).
     */
    public function urlDestino(): string
    {
        $datos = $this->datos_adicionales ?? [];

        if (in_array($this->tipo, ['sala_estudio_advertencia', 'sala_estudio_vencida'])) {
            return route('quick-actions.gestionar-salas-estudio');
        }

        if ($this->tipo === 'soporte_respuesta' && ! empty($datos['id_ticket'])) {
            return route('soporte.show', $datos['id_ticket']);
        }

        return route('recuperacion-clases.index');
    }

    /**
     * Mensaje a mostrar. Reconstruye el texto con datos adicionales cuando están disponibles.
     */
    public function mensajeFormateado(): string
    {
        $datos = $this->datos_adicionales ?? [];

        if ($this->tipo === 'clase_no_realizada' && isset($datos['id_espacio'])) {
            return sprintf(
                'El profesor no realizó la clase en %s el %s',
                $datos['id_espacio'] ?? 'Desconocido',
                $datos['fecha_clase'] ?? 'fecha desconocida'
            );
        }

        if ($this->tipo === 'clase_reagendada' && isset($datos['id_espacio_reagendado'])) {
            return sprintf(
                'Clase reagendada para el %s en %s',
                $datos['fecha_reagendada'] ?? 'fecha pendiente',
                $datos['id_espacio_reagendado'] ?? 'Espacio pendiente'
            );
        }

        return (string) $this->mensaje;
    }

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'run_usuario', 'run');
    }

    /**
     * Scope para notificaciones no leídas
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    /**
     * Scope para notificaciones leídas
     */
    public function scopeLeidas($query)
    {
        return $query->where('leida', true);
    }

    /**
     * Scope para notificaciones de un tipo específico
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para notificaciones recientes
     */
    public function scopeRecientes($query, $dias = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    /**
     * Marcar como leída
     */
    public function marcarComoLeida()
    {
        $this->update([
            'leida' => true,
            'fecha_lectura' => now(),
        ]);
    }

    /**
     * Marcar como no leída
     */
    public function marcarComoNoLeida()
    {
        $this->update([
            'leida' => false,
            'fecha_lectura' => null,
        ]);
    }

    /**
     * Crear notificación para clase no realizada
     */
    public static function crearNotificacionClaseNoRealizada($claseNoRealizada)
    {
        try {
            // Obtener usuarios supervisores y administradores
            $usuarios = User::role(['Supervisor', 'Administrador'])->get();

            try {
                \Log::info('Creando notificaciones de clase no realizada - Usuarios encontrados: ' . $usuarios->count());
            } catch (\Throwable $logEx) {
                // Ignorar errores de log — no bloquear el flujo por permisos de archivo
            }

            // Cargar relaciones necesarias con eager loading
            $claseNoRealizada->load(['asignatura', 'profesor', 'espacio', 'modulo']);
            
            $asignatura = $claseNoRealizada->asignatura;
            $profesor = $claseNoRealizada->profesor;
            $espacio = $claseNoRealizada->espacio;
            $modulo = $claseNoRealizada->modulo;
            
            // Obtener nombre completo del profesor con fallback a múltiples campos
            $nombreProfesor = 'Desconocido';
            if ($profesor) {
                if (isset($profesor->name) && !empty($profesor->name)) {
                    $nombreProfesor = $profesor->name;
                } else if (isset($profesor->nombres) && isset($profesor->apellido_paterno)) {
                    $nombreProfesor = trim($profesor->nombres . ' ' . $profesor->apellido_paterno);
                } else if (isset($profesor->run)) {
                    $nombreProfesor = 'Profesor RUN: ' . $profesor->run;
                }
            }
            
            // Construir hora del módulo
            $horaModulo = 'hora no especificada';
            if ($modulo && $modulo->hora_inicio) {
                $horaModulo = substr($modulo->hora_inicio, 0, 5); // HH:MM
            }

            $titulo = 'Clase no realizada';
            $mensaje = sprintf(
                'El profesor %s no registró clase de %s en %s el %s a las %s',
                $nombreProfesor,
                $asignatura->nombre_asignatura ?? 'Desconocida',
                $espacio->id_espacio ?? 'Desconocido',
                $claseNoRealizada->fecha_clase->format('d/m/Y'),
                $horaModulo
            );

            foreach ($usuarios as $usuario) {
                static::create([
                    'run_usuario' => $usuario->run,
                    'tipo' => 'clase_no_realizada',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'url' => route('clases-no-realizadas.index'),
                    'datos_adicionales' => [
                        'id_clase_no_realizada' => $claseNoRealizada->id,
                        'id_asignatura' => $claseNoRealizada->id_asignatura,
                        'run_profesor' => $claseNoRealizada->run_profesor,
                        'nombre_profesor' => $nombreProfesor,
                        'nombre_asignatura' => $asignatura->nombre_asignatura ?? 'Desconocida',
                        'fecha_clase' => $claseNoRealizada->fecha_clase->toDateString(),
                        'id_espacio' => $claseNoRealizada->id_espacio,
                        'hora_modulo' => $horaModulo,
                        'id_modulo' => $claseNoRealizada->id_modulo,
                    ],
                ]);

                try {
                    \Log::info('Notificación creada para usuario: ' . $usuario->run);
                } catch (\Throwable $logEx) {
                    // Ignorar errores de log — no bloquear el flujo por permisos de archivo
                }
            }
        } catch (\Exception $e) {
            try {
                \Log::error('Error al crear notificación de clase no realizada: ' . $e->getMessage());
            } catch (\Throwable $logEx) {
                // Ignorar errores de log — no bloquear el flujo por permisos de archivo
            }
        }
    }

    /**
     * Crear notificación para clase reagendada
     */
    public static function crearNotificacionClaseReagendada($recuperacion)
    {
        // Obtener usuarios supervisores y administradores
        $usuarios = User::role(['Supervisor', 'Administrador'])->get();

        $asignatura = $recuperacion->asignatura;
        $profesor = $recuperacion->profesor;

        $titulo = 'Clase reagendada';
        $mensaje = sprintf(
            'Se ha reagendado la clase de %s (profesor: %s) para el %s en %s',
            $asignatura->nombre_asignatura ?? 'Desconocida',
            $profesor->name ?? 'Desconocido',
            $recuperacion->fecha_reagendada ? $recuperacion->fecha_reagendada->format('d/m/Y') : 'fecha pendiente',
            $recuperacion->id_espacio_reagendado ?? 'Espacio pendiente'
        );

        foreach ($usuarios as $usuario) {
            static::create([
                'run_usuario' => $usuario->run,
                'tipo' => 'clase_reagendada',
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url' => route('recuperacion-clases.index'),
                'datos_adicionales' => [
                    'id_recuperacion' => $recuperacion->id_recuperacion,
                    'id_asignatura' => $recuperacion->id_asignatura,
                    'run_profesor' => $recuperacion->run_profesor,
                    'fecha_reagendada' => $recuperacion->fecha_reagendada ? $recuperacion->fecha_reagendada->toDateString() : null,
                ],
            ]);
        }
    }

    /**
     * Crear notificación de advertencia (15 minutos antes de 2h) en sala de estudio
     */
    public static function crearNotificacionSalaEstudioAdvertencia($reserva, $nombreAlumno = 'Alumno')
    {
        try {
            $usuarios = User::role(['Supervisor', 'Administrador'])->get();
            $titulo = '⚠️ Reserva por Vencer (Salas de Estudio)';
            $espacioId = $reserva->id_espacio ?? 'Sala de estudio';
            $mensaje = sprintf('La reserva de la sala %s por %s vence en 15 minutos.', $espacioId, $nombreAlumno);

            foreach ($usuarios as $usuario) {
                // Evitar notificaciones duplicadas para la misma reserva y tipo
                $existe = static::where('run_usuario', $usuario->run)
                    ->where('tipo', 'sala_estudio_advertencia')
                    ->whereJsonContains('datos_adicionales->id_reserva', $reserva->id_reserva)
                    ->exists();

                if (!$existe) {
                    static::create([
                        'run_usuario' => $usuario->run,
                        'tipo' => 'sala_estudio_advertencia',
                        'titulo' => $titulo,
                        'mensaje' => $mensaje,
                        'url' => route('quick-actions.gestionar-salas-estudio'),
                        'datos_adicionales' => [
                            'id_reserva' => $reserva->id_reserva,
                            'id_espacio' => $espacioId,
                            'nombre_alumno' => $nombreAlumno,
                        ],
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de advertencia en sala de estudio: ' . $e->getMessage());
        }
    }

    /**
     * Crear notificación de vencimiento (2 horas cumplidas) en sala de estudio
     */
    public static function crearNotificacionSalaEstudioVencida($reserva, $nombreAlumno = 'Alumno')
    {
        try {
            $usuarios = User::role(['Supervisor', 'Administrador'])->get();
            $titulo = '🚨 Reserva Vencida (2 Horas Cumplidas)';
            $espacioId = $reserva->id_espacio ?? 'Sala de estudio';
            $mensaje = sprintf('Se han cumplido las 2 horas de la reserva en %s (%s). Por favor solicitar la devolución del espacio.', $espacioId, $nombreAlumno);

            foreach ($usuarios as $usuario) {
                $existe = static::where('run_usuario', $usuario->run)
                    ->where('tipo', 'sala_estudio_vencida')
                    ->whereJsonContains('datos_adicionales->id_reserva', $reserva->id_reserva)
                    ->exists();

                if (!$existe) {
                    static::create([
                        'run_usuario' => $usuario->run,
                        'tipo' => 'sala_estudio_vencida',
                        'titulo' => $titulo,
                        'mensaje' => $mensaje,
                        'url' => route('quick-actions.gestionar-salas-estudio'),
                        'datos_adicionales' => [
                            'id_reserva' => $reserva->id_reserva,
                            'id_espacio' => $espacioId,
                            'nombre_alumno' => $nombreAlumno,
                        ],
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de vencimiento en sala de estudio: ' . $e->getMessage());
        }
    }


    /**
     * Crear una notificación de Soporte (respuesta a un ticket) en la BD de la sede del ticket.
     *
     * Los tickets viven en la BD central y pueden pertenecer a una sede distinta a la que
     * el usuario que responde tiene activa, por lo que se cambia temporalmente de tenant
     * para escribir en la base de datos correcta y luego se restaura el contexto original.
     */
    public static function crearNotificacionSoporte($ticket, $runDestino, string $titulo, string $mensaje): void
    {
        $tenantActual = app()->bound('tenant') ? Tenant::current() : null;

        try {
            $tenantDestino = $ticket->id_sede
                ? Tenant::where('sede_id', $ticket->id_sede)->where('is_active', true)->first()
                : null;

            ($tenantDestino ?? $tenantActual)?->makeCurrent();

            static::create([
                'run_usuario' => $runDestino,
                'tipo' => 'soporte_respuesta',
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url' => route('soporte.show', $ticket->id),
                'datos_adicionales' => [
                    'id_ticket' => $ticket->id,
                    'titulo_ticket' => $ticket->title,
                ],
            ]);
        } catch (\Throwable $e) {
            try {
                \Log::error('Error al crear notificación de soporte: ' . $e->getMessage());
            } catch (\Throwable $logEx) {
                // No bloquear el flujo por errores de log
            }
        } finally {
            $tenantActual?->makeCurrent();
        }
    }

    /**
     * Eliminar todas las notificaciones leídas de un usuario
     */
    public static function eliminarLeidas($runUsuario): int
    {
        return static::where('run_usuario', $runUsuario)
            ->leidas()
            ->delete();
    }

    /**
     * Obtener contador de notificaciones no leídas para un usuario
     */
    public static function contadorNoLeidas($runUsuario)
    {
        return static::where('run_usuario', $runUsuario)
            ->noLeidas()
            ->count();
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public static function marcarTodasComoLeidas($runUsuario)
    {
        return static::where('run_usuario', $runUsuario)
            ->noLeidas()
            ->update([
                'leida' => true,
                'fecha_lectura' => now(),
            ]);
    }
}

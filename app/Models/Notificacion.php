<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

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

            \Log::info('Creando notificaciones de clase no realizada - Usuarios encontrados: ' . $usuarios->count());

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

                \Log::info('Notificación creada para usuario: ' . $usuario->run);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de clase no realizada: ' . $e->getMessage());
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
                    'id_espacio_reagendado' => $recuperacion->id_espacio_reagendado,
                ],
            ]);
        }
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

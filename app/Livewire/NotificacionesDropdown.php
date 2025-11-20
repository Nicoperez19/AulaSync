<?php

namespace App\Livewire;

use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificacionesDropdown extends Component
{
    public $notificaciones = [];

    public $contadorNoLeidas = 0;

    public $mostrarDropdown = false;

    protected $listeners = ['notificacionCreada' => 'cargarNotificaciones'];

    public function mount()
    {
        $this->cargarNotificaciones();
    }

    public function cargarNotificaciones()
    {
        if (! Auth::check()) {
            return;
        }

        $usuario = Auth::user();

        // Solo cargar notificaciones para supervisores y administradores
        if (! $usuario->hasAnyRole(['Supervisor', 'Administrador'])) {
            return;
        }

        $this->notificaciones = Notificacion::where('run_usuario', $usuario->run)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($notificacion) {
                // Regenerar URL dinámicamente para evitar localhost
                $notificacion->url = route('recuperacion-clases.index');
                
                // Reconstruir mensaje con ID de espacio si está disponible
                if ($notificacion->tipo === 'clase_no_realizada' && isset($notificacion->datos_adicionales['id_espacio'])) {
                    $datos = $notificacion->datos_adicionales;
                    $notificacion->mensaje = sprintf(
                        'El profesor no realizó la clase en %s el %s',
                        $datos['id_espacio'] ?? 'Desconocido',
                        $datos['fecha_clase'] ?? 'fecha desconocida'
                    );
                } elseif ($notificacion->tipo === 'clase_reagendada' && isset($notificacion->datos_adicionales['id_espacio_reagendado'])) {
                    $datos = $notificacion->datos_adicionales;
                    $notificacion->mensaje = sprintf(
                        'Clase reagendada para el %s en %s',
                        $datos['fecha_reagendada'] ?? 'fecha pendiente',
                        $datos['id_espacio_reagendado'] ?? 'Espacio pendiente'
                    );
                }
                
                return $notificacion;
            });

        $this->contadorNoLeidas = Notificacion::contadorNoLeidas($usuario->run);
    }

    public function marcarComoLeida($notificacionId)
    {
        $notificacion = Notificacion::find($notificacionId);

        if ($notificacion && $notificacion->run_usuario === Auth::user()->run) {
            $notificacion->marcarComoLeida();
            $this->cargarNotificaciones();
        }
    }

    public function marcarTodasComoLeidas()
    {
        if (Auth::check()) {
            Notificacion::marcarTodasComoLeidas(Auth::user()->run);
            $this->cargarNotificaciones();
        }
    }

    public function toggleDropdown()
    {
        $this->mostrarDropdown = ! $this->mostrarDropdown;
    }

    public function render()
    {
        return view('livewire.notificaciones-dropdown');
    }
}

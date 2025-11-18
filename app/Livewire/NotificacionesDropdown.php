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
            ->get();

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

<?php

namespace App\Livewire;

use App\Models\Notificacion;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Centro de Notificaciones.
 *
 * Funciona en todas las sedes: las notificaciones se leen siempre de la conexión
 * "tenant" (BD de la sede activa en sesión) y se filtran por el usuario autenticado.
 */
class NotificacionesCenter extends Component
{
    public string $search = '';

    public string $tipo = '';

    /** '' = todas | 'no_leidas' | 'leidas' */
    public string $estado = '';

    public ?string $desde = null;

    public ?string $hasta = null;

    /** Cantidad de notificaciones visibles (botón "Mostrar más"). */
    public int $limite = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'tipo' => ['except' => ''],
        'estado' => ['except' => ''],
        'desde' => ['except' => null],
        'hasta' => ['except' => null],
    ];

    // ─────────────────────────────────────────────────────────────
    // Hooks de filtros
    // ─────────────────────────────────────────────────────────────

    public function updated($property): void
    {
        if (in_array($property, ['search', 'tipo', 'estado', 'desde', 'hasta'], true)) {
            $this->limite = 10;
        }

        if (in_array($property, ['desde', 'hasta'], true)) {
            $this->normalizarFechas();
        }
    }

    /**
     * Descarta fechas inválidas y, si el rango viene invertido, lo ordena.
     */
    protected function normalizarFechas(): void
    {
        $this->desde = $this->fechaValida($this->desde);
        $this->hasta = $this->fechaValida($this->hasta);

        if ($this->desde && $this->hasta && $this->desde > $this->hasta) {
            [$this->desde, $this->hasta] = [$this->hasta, $this->desde];
        }
    }

    protected function fechaValida(?string $valor): ?string
    {
        if (! $valor) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $valor)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'tipo', 'estado', 'desde', 'hasta']);
        $this->limite = 10;
    }

    public function filtrarPorTipo(string $tipo): void
    {
        $this->tipo = $this->tipo === $tipo ? '' : $tipo;
        $this->limite = 10;
    }

    /**
     * Atajos de rango: hoy, 7 días, 30 días.
     */
    public function rangoRapido(string $rango): void
    {
        $hoy = now()->toDateString();

        $this->hasta = $hoy;
        $this->desde = match ($rango) {
            'hoy' => $hoy,
            '7d' => now()->subDays(6)->toDateString(),
            '30d' => now()->subDays(29)->toDateString(),
            default => null,
        };

        if ($this->desde === null) {
            $this->hasta = null;
        }

        $this->limite = 10;
    }

    public function mostrarMas(): void
    {
        $this->limite += 10;
    }

    // ─────────────────────────────────────────────────────────────
    // Acciones
    // ─────────────────────────────────────────────────────────────

    protected function mias()
    {
        return Notificacion::where('run_usuario', Auth::user()->run);
    }

    /**
     * Refresca la campana del navbar (NotificacionesDropdown escucha este evento).
     */
    protected function refrescarCampana(): void
    {
        $this->dispatch('notificacionCreada');
    }

    public function marcarComoLeida(int $id): void
    {
        $notificacion = $this->mias()->find($id);

        if ($notificacion && ! $notificacion->leida) {
            $notificacion->marcarComoLeida();
            $this->refrescarCampana();
        }
    }

    public function marcarComoNoLeida(int $id): void
    {
        $notificacion = $this->mias()->find($id);

        if ($notificacion && $notificacion->leida) {
            $notificacion->marcarComoNoLeida();
            $this->refrescarCampana();
        }
    }

    public function abrir(int $id): void
    {
        $notificacion = $this->mias()->find($id);

        if (! $notificacion) {
            return;
        }

        if (! $notificacion->leida) {
            $notificacion->marcarComoLeida();
        }

        $this->redirect($notificacion->urlDestino());
    }

    public function eliminar(int $id): void
    {
        $this->mias()->where('id', $id)->delete();
        $this->refrescarCampana();
    }

    public function marcarTodasComoLeidas(): void
    {
        Notificacion::marcarTodasComoLeidas(Auth::user()->run);
        $this->refrescarCampana();
    }

    public function eliminarLeidas(): void
    {
        Notificacion::eliminarLeidas(Auth::user()->run);
        $this->refrescarCampana();
    }

    // ─────────────────────────────────────────────────────────────
    // Consulta con filtros
    // ─────────────────────────────────────────────────────────────

    protected function consultaFiltrada()
    {
        $query = $this->mias();

        $busqueda = trim($this->search);
        if ($busqueda !== '') {
            // Escapar comodines de LIKE para que "%" o "_" se busquen literalmente
            $like = '%' . addcslashes($busqueda, '%_\\') . '%';

            $query->where(function ($q) use ($like) {
                $q->where('titulo', 'like', $like)
                    ->orWhere('mensaje', 'like', $like)
                    // Datos como espacio, profesor o asignatura también son buscables
                    ->orWhere('datos_adicionales', 'like', $like);
            });
        }

        if ($this->tipo !== '') {
            $query->where('tipo', $this->tipo);
        }

        if ($this->estado === 'no_leidas') {
            $query->where('leida', false);
        } elseif ($this->estado === 'leidas') {
            $query->where('leida', true);
        }

        if ($this->fechaValida($this->desde)) {
            $query->whereDate('created_at', '>=', $this->desde);
        }

        if ($this->fechaValida($this->hasta)) {
            $query->whereDate('created_at', '<=', $this->hasta);
        }

        return $query;
    }

    protected function etiquetaDia(Carbon $fecha): string
    {
        if ($fecha->isToday()) {
            return 'Hoy';
        }

        if ($fecha->isYesterday()) {
            return 'Ayer';
        }

        return ucfirst($fecha->copy()->locale('es')->translatedFormat('l d \d\e F, Y'));
    }

    public function render()
    {
        $usuario = Auth::user();

        // ── Totales (sin filtros) para las tarjetas de resumen ──
        $total = $this->mias()->count();
        $sinLeer = $this->mias()->where('leida', false)->count();
        $leidas = $total - $sinLeer;

        $porTipo = $this->mias()
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->all();

        // ── Lista filtrada ──
        $consulta = $this->consultaFiltrada();
        $coincidencias = (clone $consulta)->count();

        $notificaciones = $consulta
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($this->limite)
            ->get();

        $grupos = $notificaciones->groupBy(fn ($n) => $this->etiquetaDia($n->created_at));

        // Opciones del selector: tipos conocidos + cualquier tipo presente en BD
        $tiposDisponibles = collect(Notificacion::TIPOS)->map(fn ($m) => $m['label'])->all();
        foreach (array_keys($porTipo) as $t) {
            $tiposDisponibles[$t] ??= Notificacion::metaTipo($t)['label'];
        }

        $hayFiltros = $this->search !== '' || $this->tipo !== '' || $this->estado !== ''
            || $this->desde || $this->hasta;

        return view('livewire.notificaciones-center', [
            'grupos' => $grupos,
            'total' => $total,
            'sinLeer' => $sinLeer,
            'leidas' => $leidas,
            'porTipo' => $porTipo,
            'coincidencias' => $coincidencias,
            'mostradas' => $notificaciones->count(),
            'tiposDisponibles' => $tiposDisponibles,
            'hayFiltros' => $hayFiltros,
            'sedeNombre' => app()->bound('tenant')
                ? (Tenant::current()?->sede?->nombre_sede ?? Tenant::current()?->name)
                : null,
            'nombreUsuario' => $usuario->name,
        ]);
    }
}

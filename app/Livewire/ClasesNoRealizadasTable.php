<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PeriodoAcademico;
use App\Models\ClaseNoRealizada;
use App\Models\RecuperacionClase;
use App\Models\AuditoriaClase;
use App\Models\Asignatura;
use App\Models\Profesor;
use App\Models\Carrera;
use App\Helpers\SemesterHelper;
use App\Helpers\ModulosHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\ClasesNoRealizadasReportService;
use App\Services\AuditoriaService;

class ClasesNoRealizadasTable extends Component
{
    use WithPagination;

    public $search = '';
    public $estado = '';
    public $ua = '';
    public $escuela = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $periodo = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Cache para estadísticas y colecciones filtradas
    private $cachedEstadisticas = null;
    private $todasLasClasesFiltradasCache = null;
    private $todasLasClasesBaseCache = null;

    // Propiedades de selección múltiple (Acciones Masivas)
    public $selectedClases = [];
    public $excludedClases = [];
    public $selectAllPage = false;
    public $selectAllFiltered = false;

    public $reagendar_id = null; // ID de clase para abrir modal automáticamente

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'ua' => ['except' => ''],
        'escuela' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'periodo' => ['except' => ''],
        'reagendar_id' => ['except' => ''],
    ];

    protected $listeners = [
        'updateClase',
        'confirmDelete',
        'reagendarClase',
        'marcarComoRecuperada',
        'ejecutarCambioEstadoMasivo',
        'ejecutarJustificacionMasiva',
        'ejecutarRegistroMasivo',
    ];

    public function mount()
    {
        $this->periodo = request()->query('periodo', SemesterHelper::getCurrentPeriod());
        $limites = SemesterHelper::getLimitesPeriodo($this->periodo);
        $defaultInicio = $limites['inicio']->format('Y-m-d');
        $defaultFin = $limites['fin']->gt(Carbon::today())
            ? Carbon::today()->format('Y-m-d')
            : $limites['fin']->format('Y-m-d');

        $this->fecha_inicio = request()->query('fecha_inicio', $this->fecha_inicio ?: $defaultInicio);
        $this->fecha_fin = request()->query('fecha_fin', $this->fecha_fin ?: $defaultFin);
        $this->estado = request()->query('estado', $this->estado);
        $this->search = request()->query('search', $this->search);
        $this->ua = request()->query('ua', $this->ua);
        $this->escuela = request()->query('escuela', $this->escuela);
        
        // Si viene un reagendar_id desde URL, abrir modal automáticamente
        if ($this->reagendar_id) {
            $this->dispatch('auto-open-reagendar', ['id' => $this->reagendar_id]);
        }
    }

    public function updatingSearch()
    {
        $this->limpiarCacheMemoria();
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->limpiarCacheMemoria();
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    public function updatingUa()
    {
        $this->limpiarCacheMemoria();
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    public function updatingEscuela()
    {
        $this->limpiarCacheMemoria();
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    public function updatingPeriodo()
    {
        $this->limpiarCacheMemoria(true);
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    public function updatedPeriodo($value)
    {
        $this->limpiarCacheMemoria(true);
        $this->limpiarSeleccion();
        $this->resetPage();

        if ($value) {
            $limites = SemesterHelper::getLimitesPeriodo($value);
            $this->fecha_inicio = $limites['inicio']->format('Y-m-d');
            $this->fecha_fin = $limites['fin']->gt(Carbon::today())
                ? Carbon::today()->format('Y-m-d')
                : $limites['fin']->format('Y-m-d');
        } else {
            $this->fecha_inicio = '';
            $this->fecha_fin = Carbon::today()->format('Y-m-d');
        }
    }

    public function updatingFechaInicio()
    {
        $this->limpiarCacheMemoria(true);
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->limpiarCacheMemoria(true);
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    protected function getTenantCachePrefix(): string
    {
        $tenantId = class_exists(\App\Models\Tenant::class) ? (\App\Models\Tenant::current()->id ?? 'default') : 'default';
        return (string) $tenantId;
    }

    protected function getBaseCacheKey(): string
    {
        return 'clases_no_realizadas_base_v1_' . $this->getTenantCachePrefix() . '_' . md5(json_encode([
            $this->fecha_inicio,
            $this->fecha_fin,
            $this->periodo,
        ]));
    }

    protected function getMetadataCacheKey(): string
    {
        return 'clases_no_realizadas_meta_v1_' . $this->getTenantCachePrefix();
    }

    protected function getStatsCacheKey(): string
    {
        return 'clases_no_realizadas_stats_v1_' . $this->getTenantCachePrefix() . '_' . md5(json_encode([
            $this->fecha_inicio,
            $this->fecha_fin,
            $this->periodo,
            $this->ua,
            $this->escuela,
            trim((string) $this->search),
        ]));
    }

    protected function safeCacheGet(string $key, mixed $default = null): mixed
    {
        try {
            return \Illuminate\Support\Facades\Cache::get($key, $default);
        } catch (\Throwable $e) {
            Log::warning('No se pudo leer cache en Control Docente', ['key' => $key, 'error' => $e->getMessage()]);
            return $default;
        }
    }

    protected function safeCachePut(string $key, mixed $value, int $ttlSeconds = 600): void
    {
        try {
            \Illuminate\Support\Facades\Cache::put($key, $value, $ttlSeconds);
        } catch (\Throwable $e) {
            Log::warning('No se pudo escribir cache en Control Docente', ['key' => $key, 'error' => $e->getMessage()]);
        }
    }

    protected function safeCacheForget(string $key): void
    {
        try {
            \Illuminate\Support\Facades\Cache::forget($key);
        } catch (\Throwable $e) {
            Log::warning('No se pudo invalidar cache en Control Docente', ['key' => $key, 'error' => $e->getMessage()]);
        }
    }

    protected function limpiarCacheMemoria(bool $incluirBase = false): void
    {
        $this->cachedEstadisticas = null;
        $this->todasLasClasesFiltradasCache = null;
        if ($incluirBase) {
            $this->todasLasClasesBaseCache = null;
        }
    }

    protected function estadoToDisplay(string $estadoDb): string
    {
        return match ($estadoDb) {
            'no_realizada' => 'No Registrada',
            'realizada', 'registrada' => 'Realizada',
            'justificado', 'justificada' => 'Justificada',
            'recuperada' => 'Recuperada',
            'pendiente', 'reagendada', 'programada', 'confirmada' => 'Pendiente de Recuperación',
            'feriado' => 'Feriado/Justificado',
            default => $estadoDb,
        };
    }

    /**
     * Actualiza elementos concretos dentro del caché base en memoria y disco para evitar recalcular todo el semestre.
     */
    protected function actualizarItemsEnCache(array $cambiosPorUniqueKey = [], array $cambiosPorId = []): void
    {
        $this->limpiarCacheMemoria(false);

        if (empty($cambiosPorUniqueKey) && empty($cambiosPorId)) {
            return;
        }

        $baseKey = $this->getBaseCacheKey();
        $baseCollection = $this->todasLasClasesBaseCache ?? $this->safeCacheGet($baseKey);

        if (!$baseCollection instanceof \Illuminate\Support\Collection) {
            $this->todasLasClasesBaseCache = null;
            return;
        }

        $actualizada = $baseCollection->map(function ($item) use ($cambiosPorUniqueKey, $cambiosPorId) {
            $uk = $item['unique_key'] ?? null;
            $itemId = $item['id'] ?? null;

            if ($uk !== null && isset($cambiosPorUniqueKey[$uk])) {
                return array_merge($item, $cambiosPorUniqueKey[$uk]);
            }

            if ($itemId !== null && isset($cambiosPorId[$itemId])) {
                return array_merge($item, $cambiosPorId[$itemId]);
            }

            return $item;
        });

        $this->todasLasClasesBaseCache = $actualizada;
        $this->safeCachePut($baseKey, $actualizada, 600);
        $this->safeCacheForget($this->getStatsCacheKey());
    }

    /**
     * Ajusta la página actual solo si tras filtrar/modificar registros queda fuera de rango.
     */
    protected function ajustarPaginaSiVacia(): void
    {
        $total = $this->getClasesFiltradasCollection()->count();
        $maxPage = max(1, (int) ceil($total / max(1, (int) $this->perPage)));
        $currentPage = method_exists($this, 'getPage')
            ? $this->getPage()
            : (\Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);

        if ($currentPage > $maxPage) {
            $this->setPage($maxPage);
        }
    }

    public function limpiarCacheFiltradas()
    {
        $this->limpiarCacheMemoria(true);
        $this->safeCacheForget($this->getBaseCacheKey());
        $this->safeCacheForget($this->getMetadataCacheKey());
        $this->safeCacheForget($this->getStatsCacheKey());
    }

    public function getEstadoNombreProperty()
    {
        return match($this->estado) {
            'no_realizada' => 'No registradas',
            'realizada' => 'Registradas',
            'recuperada' => 'Recuperadas',
            'impartidas' => 'Impartidas / Efectivas',
            'justificado' => 'Justificadas',
            'pendiente', 'reagendada' => 'Pendientes de recuperación',
            default => 'Todos los estados'
        };
    }

    public function refresh()
    {
        // Método para refrescar manualmente los datos
        $this->limpiarCacheFiltradas();
        $this->resetPage();
    }

    public function aplicarFiltros()
    {
        // Validar fechas si ambas están establecidas
        if ($this->fecha_inicio && $this->fecha_fin) {
            if (Carbon::parse($this->fecha_inicio)->gt(Carbon::parse($this->fecha_fin))) {
                $this->dispatch('show-error', ['message' => 'La fecha inicio no puede ser mayor que la fecha fin']);
                return;
            }
        }
        
        $this->limpiarCacheFiltradas();
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->estado = '';
        $this->ua = '';
        $this->escuela = '';
        $this->periodo = SemesterHelper::getCurrentPeriod();
        $limites = SemesterHelper::getLimitesPeriodo($this->periodo);
        $this->fecha_inicio = $limites['inicio']->format('Y-m-d');
        $this->fecha_fin = $limites['fin']->gt(Carbon::today())
            ? Carbon::today()->format('Y-m-d')
            : $limites['fin']->format('Y-m-d');

        $this->limpiarCacheMemoria(true);
        $this->limpiarSeleccion();
        $this->resetPage();
    }

    // ==========================================
    // MÉTODOS DE SELECCIÓN Y JUSTIFICACIÓN MASIVA
    // ==========================================

    public function calcularCantidadSeleccionada(): int
    {
        $todas = $this->getClasesFiltradasCollection();

        if ($this->selectAllFiltered) {
            $total = $todas->count();
            if (empty($this->excludedClases)) {
                return $total;
            }
            $excluidasValidas = $todas->whereIn('unique_key', $this->excludedClases)->count();
            return max(0, $total - $excluidasValidas);
        }

        if (empty($this->selectedClases)) {
            return 0;
        }

        return $todas->whereIn('unique_key', $this->selectedClases)->count();
    }

    public function getCantidadSeleccionadaProperty(): int
    {
        return $this->calcularCantidadSeleccionada();
    }

    public function updatedSelectAllPage($value)
    {
        if ($value) {
            if ($this->selectAllFiltered) {
                $currentPage = method_exists($this, 'getPage')
                    ? $this->getPage()
                    : (\Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);
                $currentPageKeys = $this->getClasesFiltradasCollection()
                    ->forPage($currentPage, $this->perPage)
                    ->pluck('unique_key')
                    ->filter()
                    ->values()
                    ->toArray();

                $this->excludedClases = array_values(array_diff($this->excludedClases, $currentPageKeys));
                $this->selectedClases = $currentPageKeys;
                $this->selectAllPage = true;
            } else {
                $this->seleccionarTodoElFiltro();
            }
        } else {
            $this->limpiarSeleccion();
        }
    }

    public function updatedSelectedClases()
    {
        $todasLasClases = $this->getClasesFiltradasCollection();
        $currentPage = method_exists($this, 'getPage')
            ? $this->getPage()
            : (\Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);
        $currentPageKeys = $todasLasClases
            ->forPage($currentPage, $this->perPage)
            ->pluck('unique_key')
            ->filter()
            ->values()
            ->toArray();

        if ($this->selectAllFiltered) {
            $desmarcados = array_diff($currentPageKeys, $this->selectedClases);
            $this->excludedClases = array_values(array_unique(array_merge(
                array_diff($this->excludedClases, $this->selectedClases),
                $desmarcados
            )));

            if ($todasLasClases->count() > 0 && count($this->excludedClases) >= $todasLasClases->count()) {
                $this->limpiarSeleccion();
            } else {
                $this->selectAllPage = !empty($currentPageKeys) && empty($desmarcados);
            }
            return;
        }

        $totalFiltradas = $todasLasClases->count();
        $totalSeleccionadas = $todasLasClases->whereIn('unique_key', $this->selectedClases)->count();

        if ($totalFiltradas > 0 && $totalSeleccionadas === $totalFiltradas) {
            $this->seleccionarTodoElFiltro();
        } else {
            $this->selectAllFiltered = false;
            $this->selectAllPage = !empty($currentPageKeys) && empty(array_diff($currentPageKeys, $this->selectedClases));
        }
    }

    public function seleccionarTodoElFiltro()
    {
        $this->selectAllFiltered = true;
        $this->excludedClases = [];
        $this->selectAllPage = true;
        $currentPage = method_exists($this, 'getPage')
            ? $this->getPage()
            : (\Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);
        $this->selectedClases = $this->getClasesFiltradasCollection()
            ->forPage($currentPage, $this->perPage)
            ->pluck('unique_key')
            ->filter()
            ->values()
            ->toArray();
    }

    public function seleccionarSoloPaginaActual()
    {
        $this->selectAllFiltered = false;
        $this->excludedClases = [];
        $currentPage = method_exists($this, 'getPage')
            ? $this->getPage()
            : (\Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);
        $this->selectedClases = $this->getClasesFiltradasCollection()
            ->forPage($currentPage, $this->perPage)
            ->pluck('unique_key')
            ->filter()
            ->values()
            ->toArray();
        $this->selectAllPage = !empty($this->selectedClases);
    }

    public function limpiarSeleccion()
    {
        $this->selectedClases = [];
        $this->excludedClases = [];
        $this->selectAllPage = false;
        $this->selectAllFiltered = false;
    }

    public function abrirModalAccionMasiva(?string $estadoInicial = 'justificado')
    {
        if (!$this->puedeGestionarClases) {
            $this->dispatch('show-error', ['message' => 'No tienes permisos para modificar o cambiar estados de clases.']);
            return;
        }

        $cantidad = $this->calcularCantidadSeleccionada();

        if ($cantidad === 0) {
            $this->dispatch('show-error', ['message' => 'No hay clases seleccionadas para gestionar.']);
            return;
        }

        $this->dispatch('show-bulk-action-modal', [
            'cantidad' => $cantidad,
            'tipo' => $estadoInicial ?: 'justificado',
        ]);
    }

    public function abrirModalJustificarMasivo()
    {
        $this->abrirModalAccionMasiva('justificado');
    }

    public function abrirModalRegistrarMasivo()
    {
        $this->abrirModalAccionMasiva('realizada');
    }

    public function ejecutarCambioEstadoMasivo($estadoObjetivo, $motivo = '', $observaciones = '', $sobrescribir = false)
    {
        if (!$this->puedeGestionarClases) {
            $this->dispatch('show-error', ['message' => 'No tienes permisos para modificar o cambiar estados de clases.']);
            return;
        }

        $estadoObjetivo = $estadoObjetivo === 'registrada' ? 'realizada' : trim((string) $estadoObjetivo);
        $configEstados = [
            'justificado' => [
                'accion' => 'justificar_masivo',
                'motivo_default' => 'Supervisión de Prácticas / Terreno',
                'etiqueta' => 'Justificada',
                'verbo' => 'justificaron',
                'prefijo_nota' => 'Justificación Masiva',
            ],
            'realizada' => [
                'accion' => 'registrar_masivo',
                'motivo_default' => 'Docente dictó la clase (asistencia regularizada)',
                'etiqueta' => 'Realizada',
                'verbo' => 'registraron como Realizada',
                'prefijo_nota' => 'Registro Masivo',
            ],
            'no_realizada' => [
                'accion' => 'marcar_no_realizada_masivo',
                'motivo_default' => 'Cambio manual masivo a No Registrada',
                'etiqueta' => 'No Registrada',
                'verbo' => 'marcaron como No Registrada',
                'prefijo_nota' => 'Cambio Masivo a No Registrada',
            ],
            'pendiente' => [
                'accion' => 'marcar_pendiente_masivo',
                'motivo_default' => 'Pendiente de recuperación',
                'etiqueta' => 'Pendiente de Recuperación',
                'verbo' => 'marcaron como Pendiente de Recuperación',
                'prefijo_nota' => 'Cambio Masivo a Pendiente',
            ],
            'recuperada' => [
                'accion' => 'marcar_recuperada_masivo',
                'motivo_default' => 'Clase recuperada confirmada',
                'etiqueta' => 'Recuperada',
                'verbo' => 'marcaron como Recuperada',
                'prefijo_nota' => 'Recuperación Masiva',
            ],
        ];

        if (!isset($configEstados[$estadoObjetivo])) {
            $this->dispatch('show-error', ['message' => 'El estado masivo seleccionado no es válido.']);
            return;
        }

        $cfg = $configEstados[$estadoObjetivo];

        @ini_set('memory_limit', '512M');
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');

        $motivo = trim((string) $motivo);
        if (empty($motivo)) {
            $motivo = $cfg['motivo_default'];
        }

        $observaciones = trim((string) $observaciones);

        try {
            $clasesFiltradas = $this->getClasesFiltradasCollection();

            if ($this->selectAllFiltered) {
                $candidatas = empty($this->excludedClases)
                    ? $clasesFiltradas
                    : $clasesFiltradas->whereNotIn('unique_key', $this->excludedClases);
            } else {
                $candidatas = $clasesFiltradas->whereIn('unique_key', $this->selectedClases);
            }

            if ($candidatas->isEmpty()) {
                $this->dispatch('show-error', ['message' => 'No se encontraron clases válidas seleccionadas para actualizar.']);
                return;
            }

            $usuarioActual = auth()->user()->name ?? 'Administrador';
            $fechaHora = Carbon::now()->format('d/m/Y H:i');
            $notaAuditoria = "✓ {$cfg['prefijo_nota']} aplicada por {$usuarioActual} el {$fechaHora}. Motivo: {$motivo}";
            if (!empty($observaciones)) {
                $notaAuditoria .= " - Detalle: {$observaciones}";
            }

            $itemsMeta = [];
            $idsParaBuscar = [];
            $fechasParaBuscar = [];
            $asigsParaBuscar = [];

            foreach ($candidatas as $claseData) {
                $datos = $this->resolverDatosNaturalesClase((array) $claseData);
                $uk = $claseData['unique_key'] ?? $this->generarUniqueKey($claseData);
                $clave4 = $this->claveNatural4($datos['id_asignatura'], $datos['id_espacio'], $datos['id_modulo'], $datos['fecha_clase']);

                if (!empty($claseData['id'])) {
                    $idsParaBuscar[] = (int) $claseData['id'];
                } else {
                    $fechasParaBuscar[] = $datos['fecha_clase'];
                    $asigsParaBuscar[] = $datos['id_asignatura'];
                }

                $itemsMeta[] = [
                    'datos' => $datos,
                    'unique_key' => $uk,
                    'clave4' => $clave4,
                    'id' => !empty($claseData['id']) ? (int) $claseData['id'] : null,
                ];
            }

            $hasTenantIdColumn = \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('clases_no_realizadas', 'tenant_id');
            $tenantIdActual = $hasTenantIdColumn ? (\App\Models\Tenant::current()?->id ?? null) : null;

            $cambiosCache = [];
            $procesadas = 0;
            $auditoriaService = app(AuditoriaService::class);
            AuditoriaService::iniciarLote($cfg['accion']);

            try {
                DB::connection('tenant')->transaction(function () use (
                    &$itemsMeta,
                    &$idsParaBuscar,
                    &$fechasParaBuscar,
                    &$asigsParaBuscar,
                    &$cambiosCache,
                    &$procesadas,
                    $estadoObjetivo,
                    $cfg,
                    $motivo,
                    $observaciones,
                    $notaAuditoria,
                    $sobrescribir,
                    $tenantIdActual,
                    $auditoriaService
                ) {
                    $modelosPorId = collect();
                    if (!empty($idsParaBuscar)) {
                        $modelosPorId = ClaseNoRealizada::whereIn('id', array_values(array_unique($idsParaBuscar)))
                            ->get()
                            ->keyBy('id');
                    }

                    foreach ($itemsMeta as $meta) {
                        if ($meta['id'] !== null && !$modelosPorId->has($meta['id'])) {
                            $fechasParaBuscar[] = $meta['datos']['fecha_clase'];
                            $asigsParaBuscar[] = $meta['datos']['id_asignatura'];
                        }
                    }

                    $modelosPorClave4 = [];
                    $fechasUnicas = array_values(array_unique($fechasParaBuscar));
                    $asigsUnicas = array_values(array_unique($asigsParaBuscar));

                    if (!empty($fechasUnicas) && !empty($asigsUnicas)) {
                        $existentesNat = ClaseNoRealizada::whereIn('fecha_clase', $fechasUnicas)
                            ->whereIn('id_asignatura', $asigsUnicas)
                            ->get();

                        foreach ($existentesNat as $reg) {
                            $fechaReg = $reg->fecha_clase instanceof Carbon
                                ? $reg->fecha_clase->format('Y-m-d')
                                : Carbon::parse($reg->fecha_clase)->format('Y-m-d');
                            $c4 = $this->claveNatural4((string) $reg->id_asignatura, (string) $reg->id_espacio, (string) $reg->id_modulo, $fechaReg);
                            $modelosPorClave4[$c4] = $reg;
                        }
                    }

                    $now = Carbon::now();
                    $nowStr = $now->format('Y-m-d H:i:s');
                    $rowsToInsertByClave4 = [];
                    $updatesPorObsHash = [];
                    $idsExistentesProcesados = [];
                    $fechasInsertadas = [];
                    $asigsInsertadas = [];

                    foreach ($itemsMeta as &$meta) {
                        $modelo = null;
                        if ($meta['id'] !== null && $modelosPorId->has($meta['id'])) {
                            $modelo = $modelosPorId->get($meta['id']);
                        } elseif (isset($modelosPorClave4[$meta['clave4']])) {
                            $modelo = $modelosPorClave4[$meta['clave4']];
                        }

                        if ($modelo !== null) {
                            $meta['es_nuevo'] = false;
                            $meta['valores_anteriores'] = $modelo->only(['estado', 'motivo', 'observaciones', 'id_espacio', 'id_modulo', 'fecha_clase']);
                            $meta['estado_anterior'] = $modelo->estado ?? 'no_realizada';
                            $nuevaObs = $notaAuditoria;
                            if (!$sobrescribir && !empty($modelo->observaciones)) {
                                $nuevaObs = $modelo->observaciones . "\n\n" . $notaAuditoria;
                            }
                            $meta['nueva_obs'] = $nuevaObs;
                            $meta['modelo'] = $modelo;

                            if (!isset($idsExistentesProcesados[$modelo->id])) {
                                $idsExistentesProcesados[$modelo->id] = true;
                                $obsHash = md5($nuevaObs);
                                if (!isset($updatesPorObsHash[$obsHash])) {
                                    $updatesPorObsHash[$obsHash] = [
                                        'observaciones' => $nuevaObs,
                                        'ids' => [],
                                    ];
                                }
                                $updatesPorObsHash[$obsHash]['ids'][] = $modelo->id;
                            }
                        } else {
                            $meta['es_nuevo'] = true;
                            $meta['valores_anteriores'] = [
                                'estado' => $meta['datos']['estado_inicial'],
                                'motivo' => $meta['datos']['motivo'],
                                'observaciones' => $meta['datos']['observaciones'],
                                'id_espacio' => $meta['datos']['id_espacio'],
                                'id_modulo' => $meta['datos']['id_modulo'],
                                'fecha_clase' => $meta['datos']['fecha_clase'],
                            ];
                            $meta['estado_anterior'] = $meta['datos']['estado_inicial'];
                            $meta['nueva_obs'] = $notaAuditoria;
                            $meta['modelo'] = null;

                            if (!isset($rowsToInsertByClave4[$meta['clave4']])) {
                                $row = [
                                    'id_asignatura' => $meta['datos']['id_asignatura'],
                                    'id_espacio' => $meta['datos']['id_espacio'],
                                    'id_modulo' => $meta['datos']['id_modulo'],
                                    'fecha_clase' => $meta['datos']['fecha_clase'],
                                    'run_profesor' => $meta['datos']['run_profesor'],
                                    'periodo' => $meta['datos']['periodo'],
                                    'estado' => $estadoObjetivo,
                                    'motivo' => $motivo,
                                    'observaciones' => $notaAuditoria,
                                    'hora_deteccion' => $nowStr,
                                    'created_at' => $nowStr,
                                    'updated_at' => $nowStr,
                                ];
                                if ($tenantIdActual !== null) {
                                    $row['tenant_id'] = $tenantIdActual;
                                }
                                $rowsToInsertByClave4[$meta['clave4']] = $row;
                                $fechasInsertadas[$meta['datos']['fecha_clase']] = true;
                                $asigsInsertadas[$meta['datos']['id_asignatura']] = true;
                            }
                        }
                    }
                    unset($meta);

                    if (!empty($rowsToInsertByClave4)) {
                        foreach (array_chunk(array_values($rowsToInsertByClave4), 500) as $chunk) {
                            DB::connection('tenant')->table('clases_no_realizadas')->insertOrIgnore($chunk);
                        }

                        $recienCreados = ClaseNoRealizada::whereIn('fecha_clase', array_keys($fechasInsertadas))
                            ->whereIn('id_asignatura', array_keys($asigsInsertadas))
                            ->get();

                        foreach ($recienCreados as $reg) {
                            $fechaReg = $reg->fecha_clase instanceof Carbon
                                ? $reg->fecha_clase->format('Y-m-d')
                                : Carbon::parse($reg->fecha_clase)->format('Y-m-d');
                            $c4 = $this->claveNatural4((string) $reg->id_asignatura, (string) $reg->id_espacio, (string) $reg->id_modulo, $fechaReg);
                            $modelosPorClave4[$c4] = $reg;
                        }
                    }

                    foreach ($updatesPorObsHash as $grupo) {
                        foreach (array_chunk($grupo['ids'], 500) as $idChunk) {
                            ClaseNoRealizada::whereIn('id', $idChunk)->update([
                                'estado' => $estadoObjetivo,
                                'motivo' => $motivo,
                                'observaciones' => $grupo['observaciones'],
                                'updated_at' => $nowStr,
                            ]);
                        }
                    }

                    $auditItems = [];
                    $idsTodosAfectados = [];
                    $estadoDisplay = $this->estadoToDisplay($estadoObjetivo);

                    foreach ($itemsMeta as $meta) {
                        $modelo = $meta['modelo'] ?? ($modelosPorClave4[$meta['clave4']] ?? null);
                        if (!$modelo) {
                            continue;
                        }

                        $idsTodosAfectados[$modelo->id] = true;
                        $valoresNuevos = [
                            'estado' => $estadoObjetivo,
                            'motivo' => $motivo,
                            'observaciones' => $meta['nueva_obs'],
                            'id_espacio' => $meta['datos']['id_espacio'],
                            'id_modulo' => $meta['datos']['id_modulo'],
                            'fecha_clase' => $meta['datos']['fecha_clase'],
                        ];

                        $auditItems[] = [
                            'entidad' => $modelo,
                            'accion' => $cfg['accion'],
                            'valores_anteriores' => $meta['valores_anteriores'],
                            'valores_nuevos' => $valoresNuevos,
                            'motivo' => $motivo,
                            'observaciones' => $observaciones,
                            'estado_anterior' => $meta['estado_anterior'],
                            'estado_nuevo' => $estadoObjetivo,
                            'origen' => 'web',
                        ];

                        $cambiosCache[$meta['unique_key']] = [
                            'id' => $modelo->id,
                            'estado' => $estadoDisplay,
                            'motivo' => $motivo,
                            'observaciones' => $meta['nueva_obs'],
                        ];

                        $procesadas++;
                    }

                    if ($estadoObjetivo === 'recuperada' && !empty($idsTodosAfectados)) {
                        foreach (array_chunk(array_keys($idsTodosAfectados), 500) as $idChunk) {
                            RecuperacionClase::whereIn('id_clase_no_realizada', $idChunk)
                                ->update(['estado' => 'realizada']);
                        }
                    }

                    if (!empty($auditItems)) {
                        $auditoriaService->registrarLote($auditItems);
                    }
                });
            } finally {
                AuditoriaService::cerrarLote();
            }

            $this->actualizarItemsEnCache($cambiosCache);
            $this->limpiarSeleccion();
            $this->ajustarPaginaSiVacia();

            $this->dispatch('show-success', [
                'message' => "Se {$cfg['verbo']} exitosamente {$procesadas} clases ({$cfg['etiqueta']})."
            ]);

            Log::info("Cambio masivo de estado completado exitosamente", [
                'usuario' => $usuarioActual,
                'estado_nuevo' => $estadoObjetivo,
                'clases_procesadas' => $procesadas,
                'motivo' => $motivo,
            ]);
        } catch (\Exception $e) {
            Log::error("Error en cambio masivo de estado: " . $e->getMessage(), [
                'estado_objetivo' => $estadoObjetivo,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('show-error', [
                'message' => 'Error al procesar el cambio masivo: ' . $e->getMessage()
            ]);
        }
    }

    public function ejecutarJustificacionMasiva($motivo, $observaciones, $sobrescribir = false)
    {
        $this->ejecutarCambioEstadoMasivo('justificado', $motivo, $observaciones, $sobrescribir);
    }

    public function ejecutarRegistroMasivo($motivo, $observaciones, $sobrescribir = false)
    {
        $this->ejecutarCambioEstadoMasivo('realizada', $motivo, $observaciones, $sobrescribir);
    }

    public function generarUniqueKey($clase): string
    {
        if (empty($clase['fecha'])) {
            $fecha = Carbon::today()->format('Y-m-d');
        } elseif (is_string($clase['fecha'])) {
            $fecha = Carbon::parse($clase['fecha'])->format('Y-m-d');
        } else {
            $fecha = $clase['fecha']->format('Y-m-d');
        }
        $idAsignatura = $clase['id_asignatura'] ?? '';
        $espacio = $clase['espacio'] ?? '';
        $modulo = $clase['modulo'] ?? '';
        $run = $clase['run_profesor'] ?? '';
        
        return "{$idAsignatura}_{$espacio}_{$modulo}_{$fecha}_{$run}";
    }

    protected function resolverDatosNaturalesClase(array $claseData): array
    {
        $fechaCarbon = empty($claseData['fecha'])
            ? Carbon::today()
            : (is_string($claseData['fecha']) ? Carbon::parse($claseData['fecha']) : Carbon::instance($claseData['fecha']));
        $fecha = $fechaCarbon->format('Y-m-d');

        $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        $prefijoDia = $prefijosDias[$fechaCarbon->dayOfWeek] ?? 'LU';

        if (!empty($claseData['id_modulo_raw'])) {
            $idModulo = (string) $claseData['id_modulo_raw'];
        } else {
            $modulo = (string) ($claseData['modulo'] ?? '');
            if (!str_contains($modulo, '.')) {
                $partesModulo = explode(',', $modulo);
                $modulosConPrefijo = array_map(function ($m) use ($prefijoDia) {
                    $m = trim($m);
                    return str_contains($m, '.') ? $m : $prefijoDia . '.' . $m;
                }, $partesModulo);
                $idModulo = implode(',', $modulosConPrefijo);
            } else {
                $idModulo = $modulo;
            }
        }

        $periodo = (!empty($claseData['periodo']) && $claseData['periodo'] !== 'N/A')
            ? $claseData['periodo']
            : ($this->periodo ?: SemesterHelper::getCurrentPeriod());

        $estadoInicial = match ($claseData['estado'] ?? '') {
            'No Registrada' => 'no_realizada',
            'Justificada', 'Feriado/Justificado' => 'justificado',
            'Pendiente de Recuperación' => 'pendiente',
            'Recuperada' => 'recuperada',
            default => 'realizada',
        };

        $idAsignatura = !empty($claseData['id_asignatura'])
            ? (string) $claseData['id_asignatura']
            : (!empty($claseData['codigo_asignatura']) && $claseData['codigo_asignatura'] !== 'N/A'
                ? (string) $claseData['codigo_asignatura']
                : 'RESERVA');

        return [
            'id_asignatura' => $idAsignatura,
            'id_espacio' => (string) ($claseData['espacio'] ?? ''),
            'id_modulo' => $idModulo,
            'fecha_clase' => $fecha,
            'run_profesor' => $claseData['run_profesor'] ?? null,
            'periodo' => $periodo,
            'estado_inicial' => $estadoInicial,
            'motivo' => $claseData['motivo'] ?? 'Generado para acción manual',
            'observaciones' => $claseData['observaciones'] ?? null,
        ];
    }

    protected function claveNatural4(string $idAsignatura, string $idEspacio, string $idModulo, string $fecha): string
    {
        return "{$idAsignatura}|{$idEspacio}|{$idModulo}|{$fecha}";
    }

    public function materializarClaseNoRealizada($claseData): ClaseNoRealizada
    {
        $id = $claseData['id'] ?? null;
        if ($id) {
            return ClaseNoRealizada::findOrFail($id);
        }

        $datos = $this->resolverDatosNaturalesClase((array) $claseData);

        $existente = ClaseNoRealizada::where('id_asignatura', $datos['id_asignatura'])
            ->where('id_espacio', $datos['id_espacio'])
            ->where('id_modulo', $datos['id_modulo'])
            ->whereDate('fecha_clase', $datos['fecha_clase'])
            ->first();

        if ($existente) {
            return $existente;
        }

        try {
            return ClaseNoRealizada::create([
                'id_asignatura' => $datos['id_asignatura'],
                'id_espacio' => $datos['id_espacio'],
                'id_modulo' => $datos['id_modulo'],
                'fecha_clase' => $datos['fecha_clase'],
                'run_profesor' => $datos['run_profesor'],
                'periodo' => $datos['periodo'],
                'estado' => $datos['estado_inicial'],
                'motivo' => $datos['motivo'],
                'observaciones' => $datos['observaciones'],
                'hora_deteccion' => Carbon::now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return ClaseNoRealizada::where('id_asignatura', $datos['id_asignatura'])
                ->where('id_espacio', $datos['id_espacio'])
                ->where('id_modulo', $datos['id_modulo'])
                ->whereDate('fecha_clase', $datos['fecha_clase'])
                ->firstOrFail();
        }
    }

    protected function getClasesBaseCollection()
    {
        if ($this->todasLasClasesBaseCache !== null) {
            return $this->todasLasClasesBaseCache;
        }

        $baseKey = $this->getBaseCacheKey();
        $cached = $this->safeCacheGet($baseKey);

        if ($cached instanceof \Illuminate\Support\Collection) {
            $this->todasLasClasesBaseCache = $cached;
            return $this->todasLasClasesBaseCache;
        }

        $servicio = new \App\Services\TodasClasesService();
        $clases = $servicio->obtenerTodasLasClases(
            $this->fecha_inicio,
            $this->fecha_fin,
            $this->periodo,
            null,
            null,
            null
        );

        $clases = $clases->map(function ($item) {
            if (empty($item['unique_key'])) {
                $item['unique_key'] = $this->generarUniqueKey($item);
            }
            return $item;
        });

        $this->todasLasClasesBaseCache = $clases;
        $this->safeCachePut($baseKey, $clases, 600);

        return $this->todasLasClasesBaseCache;
    }

    protected function getClasesFiltradasCollection(bool $aplicarEstado = true)
    {
        if ($aplicarEstado && $this->todasLasClasesFiltradasCache !== null) {
            return $this->todasLasClasesFiltradasCache;
        }

        $todasLasClases = clone $this->getClasesBaseCollection();

        // Aplicar filtro de UA en memoria si está establecido (por ID o por nombre de carrera)
        if (!empty($this->ua)) {
            $uaNorm = mb_strtolower(trim($this->ua), 'UTF-8');
            $todasLasClases = $todasLasClases->filter(function ($item) use ($uaNorm) {
                $carreraId = mb_strtolower(trim((string) ($item['ua'] ?? '')), 'UTF-8');
                $carreraNombre = mb_strtolower(trim((string) ($item['carrera'] ?? '')), 'UTF-8');
                return $carreraId === $uaNorm || str_contains($carreraNombre, $uaNorm);
            })->values();
        }

        // Aplicar filtro de Escuela en memoria si está establecido
        if (!empty($this->escuela)) {
            $escNorm = $this->normalizarTexto($this->escuela);
            $todasLasClases = $todasLasClases->filter(function ($item) use ($escNorm) {
                $itemEsc = $this->normalizarTexto((string) ($item['escuela'] ?? ''));
                $itemIdEsc = $this->normalizarTexto((string) ($item['id_escuela'] ?? ''));
                return $itemEsc === $escNorm || $itemIdEsc === $escNorm || str_contains($itemEsc, $escNorm) || str_contains($escNorm, $itemEsc);
            })->values();
        }

        // Aplicar filtro de estado en memoria
        if ($aplicarEstado && $this->estado) {
            if ($this->estado === 'impartidas') {
                $todasLasClases = $todasLasClases->whereIn('estado', ['Realizada', 'Registrada', 'Recuperada', 'Justificada', 'Feriado/Justificado']);
            } else {
                $estadoStr = match ($this->estado) {
                    'no_realizada' => 'No Registrada',
                    'realizada', 'registrada' => 'Realizada',
                    'recuperada' => 'Recuperada',
                    'justificado', 'justificada' => 'Justificada',
                    'pendiente', 'reagendada', 'programada', 'confirmada' => 'Pendiente de Recuperación',
                    default => null
                };
                if ($estadoStr) {
                    if ($estadoStr === 'Realizada') {
                        $todasLasClases = $todasLasClases->whereIn('estado', ['Realizada', 'Registrada']);
                    } elseif ($estadoStr === 'Justificada') {
                        $todasLasClases = $todasLasClases->whereIn('estado', ['Justificada', 'Feriado/Justificado']);
                    } else {
                        $todasLasClases = $todasLasClases->where('estado', $estadoStr);
                    }
                }
            }
        }

        // Aplicar filtro de búsqueda en memoria con soporte multi-palabra (orden independiente de nombre y apellido)
        if (!empty(trim($this->search ?? ''))) {
            $searchNorm = $this->normalizarTexto($this->search);
            $palabras = array_values(array_filter(explode(' ', $searchNorm)));
            $cleanRunSearch = preg_replace('/[^0-9kK]/', '', $this->search);

            $todasLasClases = $todasLasClases->filter(function ($item) use ($palabras, $cleanRunSearch) {
                // 1. Coincidencia por RUN si tiene al menos 3 caracteres
                if (!empty($cleanRunSearch) && strlen($cleanRunSearch) >= 3) {
                    $itemRun = preg_replace('/[^0-9kK]/', '', $item['run_profesor'] ?? '');
                    if (str_contains($itemRun, $cleanRunSearch)) {
                        return true;
                    }
                }

                if (empty($palabras)) {
                    return true;
                }

                // 2. Coincidencia en el nombre del profesor: TODAS las palabras deben coincidir (sin importar el orden)
                $profesorNorm = $this->normalizarTexto($item['profesor'] ?? '');
                $todasEnProfesor = true;
                foreach ($palabras as $p) {
                    if (!str_contains($profesorNorm, $p)) {
                        $todasEnProfesor = false;
                        break;
                    }
                }
                if ($todasEnProfesor) {
                    return true;
                }

                // 3. Coincidencia en la asignatura o código: TODAS las palabras deben coincidir
                $asigNorm = $this->normalizarTexto(($item['asignatura'] ?? '') . ' ' . ($item['codigo_asignatura'] ?? '') . ' ' . ($item['seccion'] ?? ''));
                $todasEnAsignatura = true;
                foreach ($palabras as $p) {
                    if (!str_contains($asigNorm, $p)) {
                        $todasEnAsignatura = false;
                        break;
                    }
                }
                if ($todasEnAsignatura) {
                    return true;
                }

                // 4. Coincidencia combinada en toda la información de la clase (espacio, ua, carrera, escuela, profesor, asignatura)
                $espacioRaw = $item['espacio'] ?? '';
                $textoFila = $this->normalizarTexto(
                    ($item['profesor'] ?? '') . ' ' .
                    ($item['asignatura'] ?? '') . ' ' .
                    ($item['codigo_asignatura'] ?? '') . ' ' .
                    ($item['seccion'] ?? '') . ' ' .
                    $espacioRaw . ' ' .
                    str_replace('-', '', $espacioRaw) . ' ' .
                    ($item['ua'] ?? '') . ' ' .
                    ($item['carrera'] ?? '') . ' ' .
                    ($item['escuela'] ?? '') . ' ' .
                    ($item['run_profesor'] ?? '')
                );
                foreach ($palabras as $p) {
                    if (!str_contains($textoFila, $p)) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Ordenamiento dinámico sobre la colección filtrada
        $sortField = $this->sortField;
        if (str_contains($sortField, '.')) {
            $sortField = explode('.', $sortField)[1];
        }
        if ($sortField === 'fecha_clase') {
            $sortField = 'fecha';
        }

        if ($this->sortDirection === 'asc') {
            $todasLasClases = $todasLasClases->sortBy($sortField)->values();
        } else {
            $todasLasClases = $todasLasClases->sortByDesc($sortField)->values();
        }

        if ($aplicarEstado) {
            $this->todasLasClasesFiltradasCache = $todasLasClases;
            return $this->todasLasClasesFiltradasCache;
        }

        return $todasLasClases;
    }

    public function getPuedeGestionarClasesProperty(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // Estudiante en Práctica es estrictamente de solo lectura (no puede modificar, agregar ni cambiar estados)
        if ($user->hasRole('Estudiante en Práctica')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasAnyRole(['Administrador', 'Supervisor']) || $user->can('gestionar recuperacion clases');
    }

    public function getHayFiltrosActivosProperty(): bool
    {
        $periodoActual = SemesterHelper::getPeriodoActual();
        $fechaInicioDefecto = $periodoActual && $periodoActual->fecha_inicio 
            ? Carbon::parse($periodoActual->fecha_inicio)->format('Y-m-d') 
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFinDefecto = Carbon::today()->format('Y-m-d');

        return !empty(trim($this->search ?? ''))
            || !empty($this->estado)
            || !empty($this->ua)
            || !empty($this->escuela)
            || ($this->fecha_inicio && $this->fecha_inicio !== $fechaInicioDefecto)
            || ($this->fecha_fin && $this->fecha_fin !== $fechaFinDefecto);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        $this->todasLasClasesFiltradasCache = null;
    }

    public function prepararAccion($accion, $claseData)
    {
        $id = $claseData['id'] ?? null;

        if ($accion === 'historial') {
            $this->verHistorialClase($id, $claseData);
            return;
        }

        if (!$this->puedeGestionarClases) {
            $this->dispatch('show-error', ['message' => 'No tienes permisos para modificar o cambiar estados de clases.']);
            return;
        }

        if (!$id) {
            $clase = $this->materializarClaseNoRealizada($claseData);
            $id = $clase->id;

            $uk = $claseData['unique_key'] ?? $this->generarUniqueKey($claseData);
            $this->actualizarItemsEnCache([
                $uk => ['id' => $id],
            ]);
        }

        if ($accion === 'reagendar') {
            $this->showReagendarModal($id);
        } elseif ($accion === 'editar') {
            $this->showEditModal($id);
        } elseif ($accion === 'eliminar') {
            $this->showDeleteModal($id);
        } elseif ($accion === 'recuperada') {
            $this->marcarComoRecuperada($id);
        }
    }

    public function showEditModal($id)
    {
        $clase = ClaseNoRealizada::with(['asignatura', 'profesor'])->findOrFail($id);
        
        $this->dispatch('show-edit-modal', [
            'id' => $id,
            'estado' => $clase->estado,
            'observaciones' => $clase->observaciones ?? '',
            'profesor' => $clase->profesor->name ?? 'N/A',
            'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
            'fecha' => $clase->fecha_clase->format('d/m/Y'),
            'espacio' => $clase->id_espacio,
        ]);
    }

    public function updateClase($id, $estado, $observaciones)
    {
        if (!$this->puedeGestionarClases) {
            $this->dispatch('show-error', ['message' => 'No tienes permisos para modificar o cambiar estados de clases.']);
            return;
        }
        // Validar los datos recibidos
        if (empty($estado) || !in_array($estado, ['no_realizada', 'justificado', 'pendiente', 'realizada', 'registrada', 'recuperada'])) {
            $this->dispatch('show-error', ['message' => 'El estado seleccionado no es válido']);
            return;
        }

        if (strlen((string) $observaciones) > 1000) {
            $this->dispatch('show-error', ['message' => 'Las observaciones no pueden exceder 1000 caracteres']);
            return;
        }

        $estadoNormalizado = ($estado === 'registrada') ? 'realizada' : $estado;

        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            $valoresAnteriores = $clase->only(['estado', 'motivo', 'observaciones', 'id_espacio', 'id_modulo', 'fecha_clase']);
            $estadoAnterior = $clase->estado;

            $accion = match ($estadoNormalizado) {
                'justificado' => 'justificar',
                'realizada' => 'registrar',
                'recuperada' => 'marcar_recuperada',
                default => 'cambiar_estado',
            };

            $nuevoMotivo = $clase->motivo;
            if ($estadoNormalizado === 'no_realizada') {
                $nuevoMotivo = 'Cambio manual a No Registrada';
            }

            $clase->update([
                'estado' => $estadoNormalizado,
                'motivo' => $nuevoMotivo,
                'observaciones' => $observaciones,
            ]);

            if ($estadoNormalizado === 'recuperada') {
                $modulosClase = array_values(array_filter(array_map('trim', explode(',', (string) $clase->id_modulo))));
                RecuperacionClase::where(function ($q) use ($clase, $modulosClase) {
                    $q->where('id_clase_no_realizada', $clase->id)
                      ->orWhere(function ($sub) use ($clase, $modulosClase) {
                          $sub->where('run_profesor', $clase->run_profesor)
                              ->where('id_asignatura', $clase->id_asignatura)
                              ->whereDate('fecha_reagendada', $clase->fecha_clase->format('Y-m-d'))
                              ->whereIn('id_modulo_reagendado', $modulosClase);
                      });
                })->update(['estado' => 'realizada']);
            }

            app(AuditoriaService::class)->registrar(
                $clase,
                $accion,
                $valoresAnteriores,
                $clase->only(['estado', 'motivo', 'observaciones', 'id_espacio', 'id_modulo', 'fecha_clase']),
                $nuevoMotivo,
                $observaciones,
                $estadoAnterior,
                $estadoNormalizado,
                'web'
            );

            $this->actualizarItemsEnCache([], [
                $clase->id => [
                    'id' => $clase->id,
                    'estado' => $this->estadoToDisplay($estadoNormalizado),
                    'motivo' => $nuevoMotivo,
                    'observaciones' => $observaciones,
                ],
            ]);
            $this->ajustarPaginaSiVacia();

            $this->dispatch('show-success', ['message' => 'Clase actualizada exitosamente']);
        } catch (\Exception $e) {
            $this->dispatch('show-error', ['message' => 'Error al actualizar la clase: ' . $e->getMessage()]);
        }
    }

    public function showReagendarModal($id)
    {
        $clase = ClaseNoRealizada::with(['asignatura', 'profesor'])->findOrFail($id);
        
        // Detectar automáticamente cuántos módulos corresponden a esta sesión no realizada
        // Si la clase tiene id_modulo separado por comas (ej: "MI.7,MI.8"), contar cuántos son.
        // Si es un solo módulo (ej: "MI.7"), corresponde a 1 módulo.
        $modulosArray = array_filter(array_map('trim', explode(',', (string)$clase->id_modulo)));
        $totalModulosProgramados = !empty($modulosArray) ? count($modulosArray) : 1;
        
        // Log para debugging
        Log::info("Clase no realizada modal reagendar: {$clase->id}, Asignatura: {$clase->id_asignatura}, Módulos de la sesión: {$totalModulosProgramados} ({$clase->id_modulo})");

        $this->dispatch('show-reagendar-modal', [
            'id' => $id,
            'profesor' => $clase->profesor->name ?? 'N/A',
            'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
            'fecha_original' => $clase->fecha_clase->format('d/m/Y'),
            'espacio_original' => $clase->id_espacio,
            'modulo_original' => $clase->id_modulo,
            'totalModulosProgramados' => $totalModulosProgramados,
        ]);
    }

    public function reagendarClase($id, $nuevaFecha, $nuevoEspacio, $nuevoModulo, $cantidadModulos = 1, $observaciones = '')
    {
        if (!$this->puedeGestionarClases) {
            $this->dispatch('show-error', ['message' => 'No tienes permisos para modificar o cambiar estados de clases.']);
            return;
        }
        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            
            // Convertir cantidadModulos a entero y validar
            $cantidadModulos = max(1, min(15, (int)$cantidadModulos));
            
            // Obtener el día de la nueva fecha
            $fechaParsed = Carbon::parse($nuevaFecha);
            $nombreDia = strtolower($fechaParsed->format('l'));
            
            // Mapear nombre del día en inglés a prefijo en BD (mayúsculas)
            $diasMap = [
                'monday' => 'LU',
                'tuesday' => 'MA',
                'wednesday' => 'MI',
                'thursday' => 'JU',
                'friday' => 'VI',
                'saturday' => 'SA',
                'sunday' => 'DO'
            ];
            $prefijoDia = $diasMap[$nombreDia] ?? 'LU';
            
            // Extraer el número del módulo si viene con el prefijo (ej: "LU.1" -> 1)
            $moduloBase = $nuevoModulo;
            if (is_string($nuevoModulo) && strpos($nuevoModulo, '.') !== false) {
                $moduloBase = (int) explode('.', $nuevoModulo)[1];
            } else {
                $moduloBase = (int) $nuevoModulo;
            }
            
            // Construir los módulos individuales (un registro por módulo para evitar duplicados MI.5,MI.6 + MI.5 + MI.6)
            $modulosNuevos = [];
            for ($i = 0; $i < max(1, (int) $cantidadModulos); $i++) {
                $numeroModulo = $moduloBase + $i;
                $modulosNuevos[] = "{$prefijoDia}.{$numeroModulo}";
            }
            $nuevoModuloId = implode(',', $modulosNuevos);
            $primerModuloNuevo = $modulosNuevos[0];
            
            // Crear observación completa indicando que debe recuperarse
            $observacionReagendamiento = "⚠️ CLASE PENDIENTE DE RECUPERACIÓN - ";
            $observacionReagendamiento .= "Reagendada desde {$clase->fecha_clase->format('d/m/Y')} ({$clase->id_espacio}, módulo {$clase->id_modulo}) ";
            $observacionReagendamiento .= "a {$nuevaFecha} ({$nuevoEspacio}, módulo {$nuevoModuloId}). ";
            $observacionReagendamiento .= "El profesor debe recuperar esta clase en la nueva fecha programada.";
            
            if ($observaciones) {
                $observacionReagendamiento .= " Motivo: {$observaciones}";
            }

            $fechaOriginal = $clase->fecha_clase->toDateString();
            $espacioOriginal = $clase->id_espacio;
            $moduloOriginal = trim(explode(',', (string) $clase->id_modulo)[0]);
            $prefijoOrig = strpos($moduloOriginal, '.') !== false ? explode('.', $moduloOriginal)[0] : $prefijoDia;
            $numOrigBase = strpos($moduloOriginal, '.') !== false ? (int) explode('.', $moduloOriginal)[1] : 1;
            $valoresAnteriores = $clase->only(['fecha_clase', 'id_espacio', 'id_modulo', 'estado', 'observaciones']);
            $estadoAnterior = $clase->estado;
            
            // Cambiar estado a PENDIENTE (esperando recuperación) creando/actualizando 1 fila por módulo
            $clase->getConnection()->transaction(function () use ($clase, $nuevaFecha, $nuevoEspacio, $modulosNuevos, $primerModuloNuevo, $observacionReagendamiento, $fechaOriginal, $espacioOriginal, $prefijoOrig, $numOrigBase) {
                $clase->update([
                    'fecha_clase' => $nuevaFecha,
                    'id_espacio' => $nuevoEspacio,
                    'id_modulo' => $primerModuloNuevo,
                    'estado' => 'pendiente',
                    'observaciones' => $observacionReagendamiento,
                ]);

                foreach ($modulosNuevos as $idx => $modDestino) {
                    $modOrigItem = "{$prefijoOrig}." . ($numOrigBase + $idx);
                    if ($idx === 0) {
                        $cnrItem = $clase;
                    } else {
                        $cnrItem = ClaseNoRealizada::firstOrNew([
                            'id_asignatura' => $clase->id_asignatura,
                            'id_espacio'    => $nuevoEspacio,
                            'id_modulo'     => $modDestino,
                            'run_profesor'  => $clase->run_profesor,
                            'fecha_clase'   => $nuevaFecha,
                        ]);
                        $cnrItem->periodo = $clase->periodo;
                        $cnrItem->motivo = $clase->motivo;
                        $cnrItem->estado = 'pendiente';
                        $cnrItem->observaciones = $observacionReagendamiento;
                        $cnrItem->hora_deteccion = $clase->hora_deteccion ?? now();
                        $cnrItem->save();
                    }

                    $recuperacion = RecuperacionClase::where('id_clase_no_realizada', $cnrItem->id)->first()
                        ?? RecuperacionClase::where('run_profesor', $clase->run_profesor)
                            ->where('id_asignatura', $clase->id_asignatura)
                            ->where('id_espacio', $espacioOriginal)
                            ->whereDate('fecha_clase_original', $fechaOriginal)
                            ->where('id_modulo_original', $modOrigItem)
                            ->first();

                    if (!$recuperacion) {
                        $recuperacion = new RecuperacionClase();
                    }

                    $recuperacion->fill([
                        'id_clase_no_realizada' => $cnrItem->id,
                        'run_profesor' => $clase->run_profesor,
                        'id_asignatura' => $clase->id_asignatura,
                        'id_espacio' => $espacioOriginal,
                        'fecha_clase_original' => $fechaOriginal,
                        'id_modulo_original' => $modOrigItem,
                        'fecha_reagendada' => $nuevaFecha,
                        'id_modulo_reagendado' => $modDestino,
                        'id_espacio_reagendado' => $nuevoEspacio,
                        'estado' => 'reagendada',
                        'notificado' => false,
                        'fecha_notificacion' => null,
                        'notas' => $observacionReagendamiento,
                        'gestionado_por' => auth()->user()?->run,
                    ]);
                    $recuperacion->save();
                }
            });

            app(AuditoriaService::class)->registrar(
                $clase,
                'reagendar',
                $valoresAnteriores,
                $clase->only(['fecha_clase', 'id_espacio', 'id_modulo', 'estado', 'observaciones']),
                $observaciones ?: 'Reagendamiento de clase',
                $observacionReagendamiento,
                $estadoAnterior,
                'pendiente',
                'web'
            );
            
            Log::info("Clase reagendada exitosamente", [
                'clase_id' => $id,
                'fecha_original' => $fechaOriginal,
                'fecha_nueva' => $nuevaFecha,
                'espacio_original' => $espacioOriginal,
                'espacio_nuevo' => $nuevoEspacio,
                'modulo_original' => $moduloOriginal,
                'modulo_nuevo' => $nuevoModuloId,
                'estado' => 'pendiente',
            ]);
            
            $this->dispatch('show-success', ['message' => 'Clase reagendada exitosamente. Quedará como PENDIENTE hasta que se confirme su realización.']);
            $this->refresh(); // Refrescar datos después de reagendar
        } catch (\Exception $e) {
            Log::error("Error al reagendar clase: " . $e->getMessage());
            $this->dispatch('show-error', ['message' => 'Error al reagendar la clase: ' . $e->getMessage()]);
        }
    }

    public function marcarComoRecuperada($id)
    {
        if (!$this->puedeGestionarClases) {
            $this->dispatch('show-error', ['message' => 'No tienes permisos para modificar o cambiar estados de clases.']);
            return;
        }
        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            
            // Verificar que no esté ya registrada o recuperada
            if (in_array($clase->estado, ['recuperada', 'realizada', 'registrada'])) {
                $this->dispatch('show-info', ['message' => 'Esta clase ya se encuentra registrada o recuperada.']);
                return;
            }

            $valoresAnteriores = $clase->only(['estado', 'observaciones']);
            $estadoAnterior = $clase->estado;
            
            // Actualizar observaciones agregando confirmación de recuperación
            $observacionAnterior = $clase->observaciones ?? '';
            $nuevaObservacion = $observacionAnterior 
                ? ($observacionAnterior . "\n\n✓ CLASE RECUPERADA - Confirmada el " . Carbon::now()->format('d/m/Y H:i'))
                : ("✓ CLASE RECUPERADA - Confirmada el " . Carbon::now()->format('d/m/Y H:i'));
            
            // Cambiar estado a recuperada y sincronizar RecuperacionClase
            $clase->update([
                'estado' => 'recuperada',
                'observaciones' => $nuevaObservacion,
            ]);

            $modulosClase = array_values(array_filter(array_map('trim', explode(',', (string) $clase->id_modulo))));
            RecuperacionClase::where(function ($q) use ($clase, $modulosClase) {
                $q->where('id_clase_no_realizada', $clase->id)
                  ->orWhere(function ($sub) use ($clase, $modulosClase) {
                      $sub->where('run_profesor', $clase->run_profesor)
                          ->where('id_asignatura', $clase->id_asignatura)
                          ->whereDate('fecha_reagendada', $clase->fecha_clase->format('Y-m-d'))
                          ->whereIn('id_modulo_reagendado', $modulosClase);
                  });
            })->update(['estado' => 'realizada']);

            app(AuditoriaService::class)->registrar(
                $clase,
                'marcar_recuperada',
                $valoresAnteriores,
                $clase->only(['estado', 'observaciones']),
                'Clase recuperada confirmada',
                $nuevaObservacion,
                $estadoAnterior,
                'recuperada',
                'web'
            );
            
            Log::info("Clase marcada como recuperada", [
                'clase_id' => $id,
                'fecha_clase' => $clase->fecha_clase->format('Y-m-d'),
                'profesor' => $clase->profesor->name ?? 'N/A',
            ]);
            
            $this->actualizarItemsEnCache([], [
                $clase->id => [
                    'id' => $clase->id,
                    'estado' => 'Recuperada',
                    'observaciones' => $nuevaObservacion,
                ],
            ]);
            $this->ajustarPaginaSiVacia();

            $this->dispatch('show-success', ['message' => 'Clase marcada como recuperada exitosamente']);
        } catch (\Exception $e) {
            Log::error("Error al marcar clase como recuperada: " . $e->getMessage());
            $this->dispatch('show-error', ['message' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }

    public function showDeleteModal($id)
    {
        $clase = ClaseNoRealizada::with(['asignatura', 'profesor'])->findOrFail($id);
        
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'profesor' => $clase->profesor->name ?? 'N/A',
            'asignatura' => $clase->asignatura->nombre_asignatura ?? 'N/A',
            'fecha' => $clase->fecha_clase->format('d/m/Y'),
        ]);
    }

    public function confirmDelete($id)
    {
        if (!$this->puedeGestionarClases) {
            $this->dispatch('show-error', ['message' => 'No tienes permisos para modificar o cambiar estados de clases.']);
            return;
        }
        try {
            $clase = ClaseNoRealizada::findOrFail($id);
            $valoresAnteriores = $clase->toArray();
            $estadoAnterior = $clase->estado;

            app(AuditoriaService::class)->registrar(
                $clase,
                'eliminar',
                $valoresAnteriores,
                null,
                'Registro eliminado de clases no realizadas',
                $clase->observaciones,
                $estadoAnterior,
                'eliminado',
                'web'
            );

            $clase->delete();

            $this->limpiarCacheFiltradas();
            $this->limpiarSeleccion();
            $this->ajustarPaginaSiVacia();

            $this->dispatch('show-success', ['message' => 'Registro eliminado exitosamente']);
        } catch (\Exception $e) {
            $this->dispatch('show-error', ['message' => 'Error al eliminar el registro: ' . $e->getMessage()]);
        }
    }

    public function verHistorialClase($id = null, $claseData = null)
    {
        $clase = $id ? ClaseNoRealizada::with(['profesor', 'asignatura'])->find($id) : null;

        $runProfesor = $clase?->run_profesor ?? ($claseData['run_profesor'] ?? null);
        $fechaYmd = $clase?->fecha_clase?->format('Y-m-d');
        if (!$fechaYmd && !empty($claseData['fecha'])) {
            $fechaYmd = Carbon::parse($claseData['fecha'])->format('Y-m-d');
        }

        $historial = app(AuditoriaService::class)->obtenerHistorialClase(
            $id,
            $runProfesor,
            $fechaYmd
        );

        $fechaFormateada = $clase?->fecha_clase?->format('d/m/Y')
            ?? (!empty($claseData['fecha']) ? Carbon::parse($claseData['fecha'])->format('d/m/Y') : 'N/A');

        $this->dispatch('abrir-modal-historial-auditoria', [
            'clase_id' => $id,
            'profesor' => $clase?->profesor?->name ?? ($claseData['profesor'] ?? 'Docente'),
            'asignatura' => $clase?->asignatura?->nombre_asignatura ?? ($claseData['asignatura'] ?? 'Asignatura'),
            'fecha' => $fechaFormateada,
            'historial' => $historial->map(function ($item) {
                return [
                    'id' => $item->id,
                    'fecha_hora' => $item->created_at->format('d/m/Y H:i:s'),
                    'usuario' => $item->user_nombre,
                    'rol' => $item->user_rol ?? 'Usuario',
                    'accion' => ucfirst(str_replace('_', ' ', $item->accion)),
                    'estado_anterior' => $item->estado_anterior ?? '—',
                    'estado_nuevo' => $item->estado_nuevo ?? '—',
                    'motivo' => $item->motivo,
                    'observaciones' => $item->observaciones,
                    'ip' => $item->ip,
                ];
            })->toArray()
        ]);
    }

    public function render()
    {
        $periodosDisponibles = SemesterHelper::getPeriodosDisponibles();

        $metaKey = $this->getMetadataCacheKey();
        $metaCached = $this->safeCacheGet($metaKey);

        if (is_array($metaCached) && isset($metaCached['escuelas'], $metaCached['uas'])) {
            $escuelasDisponibles = collect($metaCached['escuelas']);
            $unidadesAcademicas = collect($metaCached['uas']);
        } else {
            $escuelasDisponibles = collect();
            $unidadesAcademicas = collect();
            try {
                $tenant = \App\Models\Tenant::current();
                $carrerasSede = DB::connection('tenant')->table('carreras')
                    ->join('area_academicas as aa', 'carreras.id_area_academica', '=', 'aa.id_area_academica')
                    ->join('facultades as f', 'aa.id_facultad', '=', 'f.id_facultad')
                    ->when($tenant && $tenant->sede_id, fn ($query) => $query->where('f.id_sede', $tenant->sede_id))
                    ->select('carreras.id_carrera', 'carreras.nombre', 'aa.nombre_area_academica')
                    ->orderByRaw('CAST(carreras.id_carrera AS UNSIGNED) ASC')
                    ->orderBy('carreras.id_carrera', 'ASC')
                    ->get();

                $todasClasesServ = new \App\Services\TodasClasesService();
                foreach ($carrerasSede as $carr) {
                    $objCarrera = (object) [
                        'id_carrera' => $carr->id_carrera,
                        'nombre' => $carr->nombre,
                        'areaAcademica' => (object) ['nombre_area_academica' => $carr->nombre_area_academica, 'id_area_academica' => null]
                    ];
                    $escInfo = $todasClasesServ->resolverDatosEscuela((object) ['carrera' => $objCarrera, 'id_carrera' => $carr->id_carrera]);
                    if (!empty($escInfo['nombre'])) {
                        $escuelasDisponibles->push($escInfo['nombre']);
                    }
                }

                $unidadesAcademicas = $carrerasSede->map(fn ($carr) => (object) [
                    'id_carrera' => $carr->id_carrera,
                    'nombre' => $carr->nombre,
                ])->values();
            } catch (\Throwable $e) {}

            $escuelasDisponibles = $escuelasDisponibles->unique()->sort()->values();
            $this->safeCachePut($metaKey, [
                'escuelas' => $escuelasDisponibles->all(),
                'uas' => $unidadesAcademicas->all(),
            ], 600);
        }

        $periodoModel = null;
        if ($this->periodo) {
            $partes = explode('-', $this->periodo);
            if (count($partes) === 2) {
                $periodoModel = PeriodoAcademico::where('anio', (int) $partes[0])
                    ->where('semestre', (int) $partes[1])
                    ->first();
            }
        } else {
            $periodoModel = SemesterHelper::getPeriodoActual();
        }

        $periodoNoIniciado = $periodoModel && $periodoModel->noHaIniciado();

        if ($periodoNoIniciado) {
            return view('livewire.clases-no-realizadas-table', [
                'clasesNoRealizadas' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'estadisticas' => [
                    'total' => 0,
                    'no_realizadas' => 0,
                    'pendientes' => 0,
                    'justificados' => 0,
                    'realizadas' => 0,
                ],
                'periodoNoIniciado' => true,
                'nombrePeriodo' => $periodoModel->nombre_completo ?? 'Período',
                'periodosDisponibles' => $periodosDisponibles,
                'unidadesAcademicas' => $unidadesAcademicas,
                'escuelasDisponibles' => $escuelasDisponibles,
                'totalNoRealizadasFiltradas' => 0,
                'currentPageNoRealizadasKeys' => [],
                'totalFiltradasSeleccionables' => 0,
                'currentPageSelectableKeys' => [],
                'cantidadSeleccionada' => 0,
            ]);
        }

        $todasLasClases = $this->getClasesFiltradasCollection();
        if ($escuelasDisponibles->isEmpty()) {
            $todasLasClasesBase = $this->getClasesBaseCollection();
            if ($todasLasClasesBase->isNotEmpty()) {
                $escuelasDisponibles = $todasLasClasesBase->pluck('escuela')->filter()->unique()->sort()->values();
            }
        }

        if ($this->cachedEstadisticas === null) {
            $statsCacheKey = $this->getStatsCacheKey();
            $statsCached = $this->safeCacheGet($statsCacheKey);
            if (is_array($statsCached)) {
                $this->cachedEstadisticas = $statsCached;
            }
        }

        if ($this->cachedEstadisticas === null) {
            $clasesParaStats = empty($this->estado)
                ? $todasLasClases
                : $this->getClasesFiltradasCollection(false);

            // Agrupar por bloque de clase (fecha + espacio + run_profesor + asignatura) para
            // contar clases reales en lugar de módulos individuales, igual que hace el Dashboard.
            $clasesAgrupadas = $clasesParaStats->groupBy(function ($item) {
                $fecha = ($item['fecha'] instanceof \Carbon\Carbon)
                    ? $item['fecha']->format('Y-m-d')
                    : substr((string) $item['fecha'], 0, 10);
                return $fecha . '_' . ($item['espacio'] ?? '') . '_' . ($item['run_profesor'] ?? '') . '_' . ($item['id_asignatura'] ?? '');
            });

            // Para cada bloque, determinar el estado predominante siguiendo la misma prioridad
            // que usa el dashboard: no_realizada > pendiente > justificada > realizada/recuperada.
            $totalClases = 0;
            $totalNoRealizadas = 0;
            $totalPendientes = 0;
            $totalRealizadas = 0;

            foreach ($clasesAgrupadas as $bloqueItems) {
                $totalClases++;
                $estados = $bloqueItems->pluck('estado')->unique()->values()->toArray();

                if (in_array('Realizada', $estados) || in_array('Recuperada', $estados) || in_array('Justificada', $estados) || in_array('Feriado/Justificado', $estados)) {
                    $totalRealizadas++;
                } elseif (in_array('Pendiente de Recuperación', $estados)) {
                    $totalPendientes++;
                } elseif (in_array('No Registrada', $estados)) {
                    $totalNoRealizadas++;
                } else {
                    $totalRealizadas++;
                }
            }

            // Calcular estadísticas a partir de clases agrupadas (no módulos individuales)
            $this->cachedEstadisticas = [
                'total' => $totalClases,
                'no_realizadas' => $totalNoRealizadas,
                'pendientes' => $totalPendientes,
                'justificados' => $clasesParaStats->whereIn('estado', ['Justificada', 'Feriado/Justificado'])->count(), // para la tarjeta KPI individual
                'realizadas' => $totalRealizadas,
            ];
            $this->safeCachePut($statsCacheKey, $this->cachedEstadisticas, 600);
        }

        $estadisticas = $this->cachedEstadisticas;

        // Paginación manual
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $items = $todasLasClases->forPage($currentPage, $this->perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $todasLasClases->count(),
            $this->perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        $currentPageKeys = $items->pluck('unique_key')->filter()->values()->toArray();
        $totalFiltradasSeleccionables = $todasLasClases->count();

        // Sincronizar estado de selección de la página actual sin mantener miles de keys en el snapshot Livewire
        if ($this->selectAllFiltered) {
            $this->selectedClases = array_values(array_diff($currentPageKeys, $this->excludedClases));
            $this->selectAllPage = !empty($currentPageKeys) && count($this->selectedClases) === count($currentPageKeys);
        } elseif (!empty($currentPageKeys) && empty(array_diff($currentPageKeys, $this->selectedClases))) {
            $this->selectAllPage = true;
        } else {
            $this->selectAllPage = false;
        }

        $cantidadSeleccionada = $this->calcularCantidadSeleccionada();

        return view('livewire.clases-no-realizadas-table', [
            'clasesNoRealizadas' => $paginator,
            'estadisticas' => $estadisticas,
            'periodoNoIniciado' => false,
            'nombrePeriodo' => '',
            'periodosDisponibles' => $periodosDisponibles,
            'unidadesAcademicas' => $unidadesAcademicas,
            'escuelasDisponibles' => $escuelasDisponibles,
            'totalNoRealizadasFiltradas' => $totalFiltradasSeleccionables,
            'currentPageNoRealizadasKeys' => $currentPageKeys,
            'totalFiltradasSeleccionables' => $totalFiltradasSeleccionables,
            'currentPageSelectableKeys' => $currentPageKeys,
            'cantidadSeleccionada' => $cantidadSeleccionada,
        ]);
    }

    /**
     * Normalizar texto para búsquedas (minúsculas, sin acentos/tildes y sin puntuación)
     */
    protected function normalizarTexto(?string $texto): string
    {
        if (empty($texto)) {
            return '';
        }

        $texto = mb_strtolower(trim($texto), 'UTF-8');

        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'à', 'è', 'ì', 'ò', 'ù'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'a', 'e', 'i', 'o', 'u'];
        $texto = str_replace($buscar, $reemplazar, $texto);

        // Remover caracteres que no sean alfanuméricos ni espacios
        $texto = preg_replace('/[^a-z0-9\s]/', ' ', $texto);

        return preg_replace('/\s+/', ' ', trim($texto));
    }
}

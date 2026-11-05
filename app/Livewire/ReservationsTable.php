<?php

namespace App\Livewire;

use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ReservationsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $tipoEspacio = '';
    public $tipoSolicitante = ''; // '' = Docentes + Externos, 'externo' = Solo Externos, 'docente' = Solo Docentes, 'todos' = Todos (incl. Salas de Estudio)
    public $fechaInicio = '';
    public $fechaFin = '';
    public $estado = '';
    public $sortField = 'fecha_reserva';
    public $sortDirection = 'desc';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'tipoEspacio' => ['except' => ''],
        'tipoSolicitante' => ['except' => ''],
        'fechaInicio' => ['except' => ''],
        'fechaFin' => ['except' => ''],
        'estado' => ['except' => ''],
        'sortField' => ['except' => 'fecha_reserva'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTipoEspacio()
    {
        $this->resetPage();
    }

    public function updatedTipoSolicitante()
    {
        $this->resetPage();
    }

    public function updatedFechaInicio()
    {
        $this->resetPage();
    }

    public function updatedFechaFin()
    {
        $this->resetPage();
    }

    public function updatedEstado()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'tipoEspacio', 'tipoSolicitante', 'fechaInicio', 'fechaFin', 'estado']);
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Construye la consulta base de reservas excluyendo marcajes QR de clases regulares programadas,
     * pero incluyendo todas las reservas manuales/administrativas (de salas de clases, auditorio,
     * laboratorios, talleres, etc.) tanto de docentes como de solicitantes externos.
     */
    public static function construirQuery(
        ?string $tipoEspacio = '',
        ?string $tipoSolicitante = '',
        ?string $fechaInicio = '',
        ?string $fechaFin = '',
        ?string $estado = '',
        ?string $search = '',
        bool $aplicarFiltroTipoEspacio = true
    ): Builder {
        $searchTerm = trim((string) $search);

        $query = Reserva::query()
            ->where(function ($q) {
                $q->where('id_reserva', 'like', 'RES-%')
                  ->orWhereNotNull('creado_por')
                  ->orWhere('observaciones', 'like', '%RESERVA CREADA MANUALMENTE%')
                  ->orWhereNotNull('run_solicitante')
                  ->orWhereNotNull('nombre_actividad')
                  ->orWhereNotNull('descripcion_actividad')
                  ->orWhereIn('tipo_reserva', ['directa', 'recurrente', 'espontanea'])
                  ->orWhereHas('espacio', function ($eq) {
                      $eq->whereIn('tipo_espacio', ['Auditorio', 'Sala de Estudio'])
                         ->orWhere('tipo_espacio', 'like', 'Laboratorio%');
                  });
            });

        // Filtro por Tipo de Solicitante
        if ($tipoSolicitante === 'externo') {
            $query->whereNull('run_profesor')
                  ->where(function ($q) {
                      // Excluir auto-escaneos estudiantiles en Sala de Estudio sin actividad ni creador manual
                      $q->whereNotNull('nombre_actividad')
                        ->orWhereNotNull('descripcion_actividad')
                        ->orWhereNotNull('creado_por')
                        ->orWhere('id_reserva', 'like', 'RES-%')
                        ->orWhereHas('espacio', fn($eq) => $eq->where('tipo_espacio', '!=', 'Sala de Estudio'));
                  });
        } elseif ($tipoSolicitante === 'docente') {
            $query->whereNotNull('run_profesor');
        } elseif ($tipoSolicitante === '' && $tipoEspacio !== 'Sala de Estudio') {
            // Por defecto ("Docentes + Externos"): mostrar todas las reservas de docentes y externos,
            // excluyendo los auto-registros QR de alumnos en Salas de Estudio (sin creador ni actividad externa)
            $query->where(function ($q) {
                $q->whereNotNull('run_profesor')
                  ->orWhereNotNull('nombre_actividad')
                  ->orWhereNotNull('descripcion_actividad')
                  ->orWhereNotNull('creado_por')
                  ->orWhere('id_reserva', 'like', 'RES-%')
                  ->orWhereHas('espacio', fn($eq) => $eq->where('tipo_espacio', '!=', 'Sala de Estudio'));
            });
        }

        // Filtro por Tipo de Espacio
        if ($aplicarFiltroTipoEspacio && !empty($tipoEspacio)) {
            if ($tipoEspacio === 'Laboratorios') {
                $query->whereHas('espacio', function ($eq) {
                    $eq->where('tipo_espacio', 'like', 'Laboratorio%');
                });
            } else {
                $query->whereHas('espacio', function ($eq) use ($tipoEspacio) {
                    $eq->where('tipo_espacio', $tipoEspacio);
                });
            }
        }

        // Filtros de Fecha y Estado
        if (!empty($fechaInicio)) {
            $query->whereDate('fecha_reserva', '>=', $fechaInicio);
        }
        if (!empty($fechaFin)) {
            $query->whereDate('fecha_reserva', '<=', $fechaFin);
        }
        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        // Búsqueda integral (incluye actividad, institución, descripción, observaciones, creador, RUN, sala)
        if ($searchTerm !== '') {
            $cleanRun = preg_replace('/[^0-9Kk]/', '', $searchTerm);
            $termSinTilde = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
                $searchTerm
            );
            $terms = array_unique(array_filter([$searchTerm, $termSinTilde, mb_strtoupper($searchTerm), mb_strtolower($searchTerm)]));

            $query->where(function ($q) use ($terms, $searchTerm, $cleanRun) {
                $q->where('id_reserva', 'like', '%' . $searchTerm . '%')
                  ->orWhere('id_espacio', 'like', '%' . $searchTerm . '%')
                  ->orWhere('fecha_reserva', 'like', '%' . $searchTerm . '%');

                if (!empty($cleanRun) && strlen($cleanRun) >= 3) {
                    $q->orWhere('run_profesor', 'like', '%' . $cleanRun . '%')
                      ->orWhere('run_solicitante', 'like', '%' . $cleanRun . '%');
                }

                foreach ($terms as $t) {
                    $q->orWhere('estado', 'like', '%' . $t . '%')
                      ->orWhere('nombre_actividad', 'like', '%' . $t . '%')
                      ->orWhere('descripcion_actividad', 'like', '%' . $t . '%')
                      ->orWhere('observaciones', 'like', '%' . $t . '%')
                      ->orWhere('creado_por', 'like', '%' . $t . '%')
                      ->orWhereHas('profesor', function ($pq) use ($t) {
                          $pq->where('name', 'like', '%' . $t . '%');
                      })
                      ->orWhereHas('solicitante', function ($sq) use ($t) {
                          $sq->where('nombre', 'like', '%' . $t . '%');
                      })
                      ->orWhereHas('espacio', function ($eq) use ($t) {
                          $eq->where('nombre_espacio', 'like', '%' . $t . '%')
                             ->orWhere('tipo_espacio', 'like', '%' . $t . '%');
                      })
                      ->orWhereHas('asignatura', function ($aq) use ($t) {
                          $aq->where('nombre_asignatura', 'like', '%' . $t . '%');
                      });
                }
            });
        }

        return $query;
    }

    /**
     * Calcula las horas cronológicas de duración de una reserva.
     */
    public static function calcularHorasReserva($reserva): float
    {
        $modulos = max(1, (int) ($reserva->modulos ?? 1));

        if (!empty($reserva->hora) && !empty($reserva->hora_salida)) {
            try {
                $inicio = Carbon::parse($reserva->hora);
                $fin = Carbon::parse($reserva->hora_salida);
                if ($fin->greaterThan($inicio)) {
                    $minutos = $inicio->diffInMinutes($fin);
                    if ($minutos >= 15 && $minutos <= 900) {
                        return round($minutos / 60, 2);
                    }
                }
            } catch (\Exception $e) {
                // Fallback a cálculo por módulos
            }
        }

        // Cada módulo equivale a ~50 min + 10 min receso (~1 hora pedagógica/bloque)
        return round((($modulos * 60) - 10) / 60, 2);
    }

    /**
     * Extrae la categoría (Docente / Externo), la institución/entidad solicitante y el detalle de actividad.
     */
    public static function extraerInstitucionYActividad($reserva): array
    {
        $esDocente = !empty($reserva->run_profesor);
        $tipoEspacio = $reserva->espacio->tipo_espacio ?? '';
        $esEstudianteSalaEstudio = !$esDocente
            && $tipoEspacio === 'Sala de Estudio'
            && empty($reserva->nombre_actividad)
            && empty($reserva->creado_por);

        $categoria = $esDocente
            ? 'Docente'
            : ($esEstudianteSalaEstudio ? 'Estudiante' : 'Externo');

        $descripcionRaw = trim((string) ($reserva->descripcion_actividad ?? ''));
        $nombreActRaw = trim((string) ($reserva->nombre_actividad ?? ''));
        $obsRaw = trim((string) ($reserva->observaciones ?? ''));

        $institucion = null;
        $descripcionLimpia = $descripcionRaw;

        // 1. Buscar etiqueta explícita [Institución: ...] en descripción u observaciones
        if (preg_match('/\[Instituci[óo]n:\s*([^\]]+)\]/iu', $descripcionRaw, $m)) {
            $institucion = trim($m[1]);
            $descripcionLimpia = trim(str_replace($m[0], '', $descripcionRaw));
        } elseif (preg_match('/\[Instituci[óo]n:\s*([^\]]+)\]/iu', $obsRaw, $m)) {
            $institucion = trim($m[1]);
        }

        // 2. Si no tiene etiqueta explícita, detectar entidades comunes mencionadas en actividad/descripción/observaciones
        if (empty($institucion) && !$esDocente) {
            $textoCompleto = $nombreActRaw . ' ' . $descripcionRaw . ' ' . $obsRaw;
            $entidadesConocidas = [
                'Servicio de Salud' => '/servicio\s+de\s+salud/iu',
                'Municipalidad' => '/municipalidad|ilustre\s+municipalidad|muni\b/iu',
                'Hospital' => '/\bhospital\b/iu',
                'CESFAM' => '/\bcesfam\b/iu',
                'SEREMI' => '/\bseremi\b/iu',
                'DAEM / DEM' => '/\bdaem\b|\bdem\b/iu',
                'Gobierno Regional' => '/gobierno\s+regional|\bgore\b/iu',
                'MINEDUC' => '/\bmineduc\b|ministerio\s+de\s+educaci[óo]n/iu',
                'MINSAL' => '/\bminsal\b|ministerio\s+de\s+salud/iu',
                'Carabineros / PDI' => '/carabineros|\bpdi\b/iu',
                'Bomberos' => '/\bbomberos\b/iu',
                'Colegio / Liceo' => '/\bcolegio\b|\bliceo\b/iu',
            ];

            foreach ($entidadesConocidas as $nombreEntidad => $patron) {
                if (preg_match($patron, $textoCompleto)) {
                    $institucion = $nombreEntidad;
                    break;
                }
            }
        }

        // 3. Asignar valor por defecto según categoría
        if (empty($institucion)) {
            if ($esDocente) {
                $institucion = 'Docente Interno';
            } elseif ($esEstudianteSalaEstudio) {
                $institucion = 'Estudiante Interno';
            } else {
                $institucion = $nombreActRaw !== '' ? $nombreActRaw : 'Externo / Particular';
            }
        }

        $actividad = $nombreActRaw !== ''
            ? $nombreActRaw
            : ($reserva->asignatura->nombre_asignatura ?? ($descripcionLimpia !== '' ? $descripcionLimpia : ($esDocente ? 'Reserva Docente' : 'Reserva Externa')));

        return [
            'categoria' => $categoria,
            'es_docente' => $esDocente,
            'institucion' => $institucion,
            'actividad' => $actividad,
            'descripcion' => $descripcionLimpia,
        ];
    }

    /**
     * Calcula las métricas completas de ocupación de reservas (Total, Docentes, Externos, por Institución y por Sala).
     */
    public static function calcularMetricasOcupacion(
        Collection $reservas,
        ?string $tipoEspacio = '',
        ?string $fechaInicio = '',
        ?string $fechaFin = '',
        ?string $tipoSolicitante = ''
    ): array {
        // Filtrar reservas no canceladas para el cálculo de ocupación efectiva
        $reservasValidas = $reservas->filter(fn($r) => strtolower($r->estado ?? '') !== 'cancelada');

        $totalReservas = $reservas->count();
        $reservasDocentes = 0;
        $reservasExternos = 0;
        $horasTotales = 0.0;
        $horasDocentes = 0.0;
        $horasExternos = 0.0;
        $modulosTotales = 0;
        $modulosDocentes = 0;
        $modulosExternos = 0;

        $porInstitucion = [];
        $porTipoEspacio = [];
        $espaciosUsadosIds = [];

        foreach ($reservasValidas as $res) {
            $info = self::extraerInstitucionYActividad($res);
            $horas = self::calcularHorasReserva($res);
            $modulos = max(1, (int) ($res->modulos ?? 1));
            $tEsp = $res->espacio->tipo_espacio ?? 'Sin clasificar';

            if (!empty($res->id_espacio)) {
                $espaciosUsadosIds[$res->id_espacio] = true;
            }

            $horasTotales += $horas;
            $modulosTotales += $modulos;

            if ($info['es_docente']) {
                $reservasDocentes++;
                $horasDocentes += $horas;
                $modulosDocentes += $modulos;
            } else {
                $reservasExternos++;
                $horasExternos += $horas;
                $modulosExternos += $modulos;
            }

            // Agrupar por Institución / Entidad
            $claveInst = $info['institucion'];
            if (!isset($porInstitucion[$claveInst])) {
                $porInstitucion[$claveInst] = [
                    'institucion' => $claveInst,
                    'categoria' => $info['categoria'],
                    'total_reservas' => 0,
                    'horas' => 0.0,
                    'modulos' => 0,
                ];
            }
            $porInstitucion[$claveInst]['total_reservas']++;
            $porInstitucion[$claveInst]['horas'] += $horas;
            $porInstitucion[$claveInst]['modulos'] += $modulos;

            // Agrupar por Tipo de Espacio
            if (!isset($porTipoEspacio[$tEsp])) {
                $porTipoEspacio[$tEsp] = [
                    'tipo_espacio' => $tEsp,
                    'total_reservas' => 0,
                    'reservas_docentes' => 0,
                    'reservas_externos' => 0,
                    'horas_totales' => 0.0,
                    'horas_docentes' => 0.0,
                    'horas_externos' => 0.0,
                    'espacios_usados' => [],
                ];
            }
            $porTipoEspacio[$tEsp]['total_reservas']++;
            $porTipoEspacio[$tEsp]['horas_totales'] += $horas;
            if ($info['es_docente']) {
                $porTipoEspacio[$tEsp]['reservas_docentes']++;
                $porTipoEspacio[$tEsp]['horas_docentes'] += $horas;
            } else {
                $porTipoEspacio[$tEsp]['reservas_externos']++;
                $porTipoEspacio[$tEsp]['horas_externos'] += $horas;
            }
            if (!empty($res->id_espacio)) {
                $porTipoEspacio[$tEsp]['espacios_usados'][$res->id_espacio] = true;
            }
        }

        // Determinar rango de fechas efectivo para calcular las horas disponibles
        $inicioCarbon = !empty($fechaInicio) ? Carbon::parse($fechaInicio)->startOfDay() : null;
        $finCarbon = !empty($fechaFin) ? Carbon::parse($fechaFin)->startOfDay() : null;

        if (!$inicioCarbon || !$finCarbon) {
            $fechas = $reservasValidas->pluck('fecha_reserva')->filter()->map(fn($f) => Carbon::parse($f)->startOfDay());
            if ($fechas->isNotEmpty()) {
                $inicioCarbon = $inicioCarbon ?: $fechas->min();
                $finCarbon = $finCarbon ?: $fechas->max();
            } else {
                $inicioCarbon = Carbon::now()->startOfMonth();
                $finCarbon = Carbon::now()->endOfMonth();
            }
        }

        if ($finCarbon->lessThan($inicioCarbon)) {
            $finCarbon = $inicioCarbon->copy();
        }

        // Calcular horas disponibles por sala en el rango de fechas (Lun-Vie = 15h, Sáb = 5h, Dom = 0h)
        $horasDisponiblesPorSala = 0.0;
        $diasHabiles = 0;
        $cursor = $inicioCarbon->copy();
        // Limitar iteración a máximo 366 días para evitar ciclos largos
        $maxDias = 0;
        while ($cursor->lte($finCarbon) && $maxDias < 366) {
            if ($cursor->isWeekday()) {
                $horasDisponiblesPorSala += 15;
                $diasHabiles++;
            } elseif ($cursor->isSaturday()) {
                $horasDisponiblesPorSala += 5;
                $diasHabiles++;
            }
            $cursor->addDay();
            $maxDias++;
        }
        $horasDisponiblesPorSala = max(15.0, $horasDisponiblesPorSala);

        // Contar espacios disponibles según el filtro
        $espaciosQuery = Espacio::query();
        if (!empty($tipoEspacio)) {
            if ($tipoEspacio === 'Laboratorios') {
                $espaciosQuery->where('tipo_espacio', 'like', 'Laboratorio%');
            } else {
                $espaciosQuery->where('tipo_espacio', $tipoEspacio);
            }
        } elseif ($tipoSolicitante === '' || $tipoSolicitante === 'externo' || $tipoSolicitante === 'docente') {
            $espaciosQuery->where('tipo_espacio', '!=', 'Sala de Estudio');
        }
        $totalEspaciosCatalogo = max(1, $espaciosQuery->count());
        $totalEspaciosUsados = max(1, count($espaciosUsadosIds));

        // Horas disponibles en las salas que tuvieron reservas y en todo el catálogo
        $horasDisponiblesSalasUsadas = round($horasDisponiblesPorSala * $totalEspaciosUsados, 1);
        $horasDisponiblesCatalogo = round($horasDisponiblesPorSala * $totalEspaciosCatalogo, 1);

        // Porcentajes de ocupación sobre salas reservadas en el período
        $pctOcupacionSalasUsadas = $horasDisponiblesSalasUsadas > 0
            ? min(100, round(($horasTotales / $horasDisponiblesSalasUsadas) * 100, 1))
            : 0.0;
        $pctOcupacionDocentesSalas = $horasDisponiblesSalasUsadas > 0
            ? min(100, round(($horasDocentes / $horasDisponiblesSalasUsadas) * 100, 1))
            : 0.0;
        $pctOcupacionExternosSalas = $horasDisponiblesSalasUsadas > 0
            ? min(100, round(($horasExternos / $horasDisponiblesSalasUsadas) * 100, 1))
            : 0.0;

        // Porcentajes sobre el total del parque de salas (catálogo)
        $pctOcupacionCatalogo = $horasDisponiblesCatalogo > 0
            ? min(100, round(($horasTotales / $horasDisponiblesCatalogo) * 100, 2))
            : 0.0;

        // Distribución porcentual dentro del total de horas reservadas (Docentes vs Externos)
        $pctShareDocentes = $horasTotales > 0 ? round(($horasDocentes / $horasTotales) * 100, 1) : 0.0;
        $pctShareExternos = $horasTotales > 0 ? round(($horasExternos / $horasTotales) * 100, 1) : 0.0;

        // Enriquecer tabla por institución
        foreach ($porInstitucion as &$item) {
            $item['horas'] = round($item['horas'], 2);
            $item['pct_del_total_reservas'] = $horasTotales > 0 ? round(($item['horas'] / $horasTotales) * 100, 1) : 0.0;
            $item['pct_ocupacion_salas'] = $horasDisponiblesSalasUsadas > 0 ? round(($item['horas'] / $horasDisponiblesSalasUsadas) * 100, 2) : 0.0;
        }
        unset($item);
        uasort($porInstitucion, fn($a, $b) => $b['horas'] <=> $a['horas']);

        // Enriquecer tabla por tipo de espacio
        foreach ($porTipoEspacio as $k => &$espItem) {
            $numSalasUsadasTipo = max(1, count($espItem['espacios_usados']));
            $horasDispTipo = round($horasDisponiblesPorSala * $numSalasUsadasTipo, 1);
            $espItem['num_salas_usadas'] = count($espItem['espacios_usados']);
            $espItem['horas_disponibles'] = $horasDispTipo;
            $espItem['horas_totales'] = round($espItem['horas_totales'], 2);
            $espItem['horas_docentes'] = round($espItem['horas_docentes'], 2);
            $espItem['horas_externos'] = round($espItem['horas_externos'], 2);
            $espItem['pct_ocupacion'] = $horasDispTipo > 0 ? min(100, round(($espItem['horas_totales'] / $horasDispTipo) * 100, 1)) : 0.0;
            unset($espItem['espacios_usados']);
        }
        unset($espItem);

        return [
            'total_reservas' => $totalReservas,
            'reservas_activas_validas' => $reservasValidas->count(),
            'reservas_docentes' => $reservasDocentes,
            'reservas_externos' => $reservasExternos,
            'horas_totales' => round($horasTotales, 1),
            'horas_docentes' => round($horasDocentes, 1),
            'horas_externos' => round($horasExternos, 1),
            'modulos_totales' => $modulosTotales,
            'modulos_docentes' => $modulosDocentes,
            'modulos_externos' => $modulosExternos,
            'espacios_usados_count' => count($espaciosUsadosIds),
            'espacios_catalogo_count' => $totalEspaciosCatalogo,
            'dias_habiles' => max(1, $diasHabiles),
            'horas_disponibles_por_sala' => round($horasDisponiblesPorSala, 1),
            'horas_disponibles_salas_usadas' => $horasDisponiblesSalasUsadas,
            'horas_disponibles_catalogo' => $horasDisponiblesCatalogo,
            'pct_ocupacion_salas_usadas' => $pctOcupacionSalasUsadas,
            'pct_ocupacion_docentes_salas' => $pctOcupacionDocentesSalas,
            'pct_ocupacion_externos_salas' => $pctOcupacionExternosSalas,
            'pct_ocupacion_catalogo' => $pctOcupacionCatalogo,
            'pct_share_docentes' => $pctShareDocentes,
            'pct_share_externos' => $pctShareExternos,
            'fecha_inicio_calculo' => $inicioCarbon->format('d/m/Y'),
            'fecha_fin_calculo' => $finCarbon->format('d/m/Y'),
            'por_institucion' => array_values($porInstitucion),
            'por_tipo_espacio' => array_values($porTipoEspacio),
        ];
    }

    public function render()
    {
        // Consulta base sin filtro de tipoEspacio para conteos rápidos por tipo de sala
        $baseQuerySinEspacio = self::construirQuery(
            '',
            $this->tipoSolicitante,
            $this->fechaInicio,
            $this->fechaFin,
            $this->estado,
            $this->search,
            false
        );

        // Consulta completa aplicando todos los filtros seleccionados
        $reservasQuery = self::construirQuery(
            $this->tipoEspacio,
            $this->tipoSolicitante,
            $this->fechaInicio,
            $this->fechaFin,
            $this->estado,
            $this->search,
            true
        )->with(['profesor', 'solicitante', 'espacio.piso.facultad', 'asignatura']);

        // Colección para el cálculo exacto de KPIs y % de ocupación
        $todasFiltradas = (clone $reservasQuery)->get();
        $metricas = self::calcularMetricasOcupacion(
            $todasFiltradas,
            $this->tipoEspacio,
            $this->fechaInicio,
            $this->fechaFin,
            $this->tipoSolicitante
        );

        $kpis = array_merge($metricas, [
            'total' => $todasFiltradas->count(),
            'salas_clases' => (clone $baseQuerySinEspacio)->whereHas('espacio', fn($q) => $q->where('tipo_espacio', 'Sala de Clases'))->count(),
            'auditorio' => (clone $baseQuerySinEspacio)->whereHas('espacio', fn($q) => $q->where('tipo_espacio', 'Auditorio'))->count(),
            'laboratorios' => (clone $baseQuerySinEspacio)->whereHas('espacio', fn($q) => $q->where('tipo_espacio', 'like', 'Laboratorio%'))->count(),
            'salas_estudio' => (clone $baseQuerySinEspacio)->whereHas('espacio', fn($q) => $q->where('tipo_espacio', 'Sala de Estudio'))->count(),
        ]);

        $reservas = $reservasQuery
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('hora', 'asc')
            ->paginate($this->perPage);

        return view('livewire.reservations-table', compact('reservas', 'kpis'));
    }
}

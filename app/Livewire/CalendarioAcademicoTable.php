<?php

namespace App\Livewire;

use App\Models\DiaFeriado;
use App\Models\PeriodoAcademico;
use App\Models\CursoVerano;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CalendarioAcademicoTable extends Component
{
    use WithPagination;

    // Vista activa: 'tabla' o 'calendario'
    public $vistaActiva = 'tabla';
    
    // Tab activo: 'feriados' o 'periodos'
    public $tabActivo = 'feriados';

    // Mes y año para vista calendario
    public $mesCalendario;
    public $anioCalendario;

    // Búsqueda y filtros para feriados
    public $search = '';
    public $tipo = '';
    public $sortField = 'fecha_inicio';
    public $sortDirection = 'desc';

    // Modal feriados
    public $showModal = false;
    public $editMode = false;
    public $feriadoId;
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $nombre = '';
    public $descripcion = '';
    public $tipo_feriado = 'feriado';
    public $activo = true;

    // Modal períodos académicos
    public $showModalPeriodo = false;
    public $editModePeriodo = false;
    public $periodoId;
    public $periodo_anio;
    // Semestre 1
    public $periodo_1_fecha_inicio = '';
    public $periodo_1_fecha_fin = '';
    // Semestre 2
    public $periodo_2_fecha_inicio = '';
    public $periodo_2_fecha_fin = '';
    public $periodo_activo = true;

    // Modal cursos de verano
    public $showModalVerano = false;
    public $showModalSeleccionarVerano = false;
    public $editModeVerano = false;
    public $cursoVeranoId;
    public $verano_anio;
    public $verano_inicio = '';
    public $verano_fin = '';
    public $verano_activo = true;

    protected $queryString = ['search', 'tipo', 'vistaActiva', 'tabActivo'];

    protected function rules()
    {
        return [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_feriado' => 'required|in:feriado,semana_reajuste,suspension_actividades',
            'activo' => 'boolean',
        ];
    }

    protected function rulesForPeriodo()
    {
        return [
            'periodo_anio' => 'required|integer|min:2020|max:2100',
            'periodo_1_fecha_inicio' => 'required|date',
            'periodo_1_fecha_fin' => 'required|date|after:periodo_1_fecha_inicio',
            'periodo_2_fecha_inicio' => 'required|date',
            'periodo_2_fecha_fin' => 'required|date|after:periodo_2_fecha_inicio',
            'periodo_activo' => 'boolean',
        ];
    }

    protected function rulesForVerano()
    {
        return [
            'verano_anio' => 'required|integer|min:2020|max:2100',
            'verano_semestre' => 'required|in:1,2',
            'verano_inicio' => 'required|date',
            'verano_fin' => 'required|date|after_or_equal:verano_inicio',
        ];
    }

    public function mount()
    {
        $this->mesCalendario = Carbon::now()->month;
        $this->anioCalendario = Carbon::now()->year;
        $this->periodo_anio = Carbon::now()->year;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipo()
    {
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

    public function cambiarVista($vista)
    {
        $this->vistaActiva = $vista;
    }

    public function cambiarTab($tab)
    {
        $this->tabActivo = $tab;
    }

    public function mesAnterior()
    {
        $fecha = Carbon::create($this->anioCalendario, $this->mesCalendario, 1)->subMonth();
        $this->mesCalendario = $fecha->month;
        $this->anioCalendario = $fecha->year;
    }

    public function mesSiguiente()
    {
        $fecha = Carbon::create($this->anioCalendario, $this->mesCalendario, 1)->addMonth();
        $this->mesCalendario = $fecha->month;
        $this->anioCalendario = $fecha->year;
    }

    public function irAHoy()
    {
        $this->mesCalendario = Carbon::now()->month;
        $this->anioCalendario = Carbon::now()->year;
    }

    // ==================== MÉTODOS PARA FERIADOS ====================

    public function openCreateModal()
    {
        $this->reset(['fecha_inicio', 'fecha_fin', 'nombre', 'descripcion', 'tipo_feriado', 'activo', 'editMode', 'feriadoId']);
        $this->tipo_feriado = 'feriado';
        $this->activo = true;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $feriado = DiaFeriado::findOrFail($id);
        $this->feriadoId = $id;
        $this->fecha_inicio = $feriado->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $feriado->fecha_fin->format('Y-m-d');
        $this->nombre = $feriado->nombre;
        $this->descripcion = $feriado->descripcion;
        $this->tipo_feriado = $feriado->tipo;
        $this->activo = $feriado->activo;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['fecha_inicio', 'fecha_fin', 'nombre', 'descripcion', 'tipo_feriado', 'activo', 'editMode', 'feriadoId']);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo_feriado,
            'activo' => $this->activo,
            'created_by' => Auth::user()->run ?? null,
        ];

        if ($this->editMode) {
            $feriado = DiaFeriado::findOrFail($this->feriadoId);
            $feriado->update($data);
            session()->flash('message', 'Día feriado actualizado exitosamente.');
        } else {
            DiaFeriado::create($data);
            session()->flash('message', 'Día feriado creado exitosamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $feriado = DiaFeriado::findOrFail($id);
        $feriado->delete();
        session()->flash('message', 'Día feriado eliminado exitosamente.');
    }

    public function toggleActivo($id)
    {
        $feriado = DiaFeriado::findOrFail($id);
        $feriado->update(['activo' => !$feriado->activo]);
        session()->flash('message', 'Estado actualizado exitosamente.');
    }

    // ==================== MÉTODOS PARA PERÍODOS ACADÉMICOS ====================

    public function openCreateModalPeriodo()
    {
        $this->reset([
            'periodo_anio', 'periodo_1_fecha_inicio', 'periodo_1_fecha_fin',
            'periodo_2_fecha_inicio', 'periodo_2_fecha_fin',
            'periodo_activo', 'editModePeriodo', 'periodoId'
        ]);
        $this->periodo_anio = Carbon::now()->year;
        $this->periodo_activo = true;
        $this->showModalPeriodo = true;
    }

    public function openEditModalPeriodo($id)
    {
        $periodo = PeriodoAcademico::findOrFail($id);
        $this->periodoId = $id;
        $this->periodo_anio = $periodo->anio;
        
        if ($periodo->semestre === 1) {
            $this->periodo_1_fecha_inicio = $periodo->fecha_inicio->format('Y-m-d');
            $this->periodo_1_fecha_fin = $periodo->fecha_fin->format('Y-m-d');
            // Obtener semestre 2 si existe
            $periodo2 = PeriodoAcademico::where('anio', $periodo->anio)
                ->where('semestre', 2)
                ->first();
            if ($periodo2) {
                $this->periodo_2_fecha_inicio = $periodo2->fecha_inicio->format('Y-m-d');
                $this->periodo_2_fecha_fin = $periodo2->fecha_fin->format('Y-m-d');
            }
        } else {
            $this->periodo_2_fecha_inicio = $periodo->fecha_inicio->format('Y-m-d');
            $this->periodo_2_fecha_fin = $periodo->fecha_fin->format('Y-m-d');
            // Obtener semestre 1 si existe
            $periodo1 = PeriodoAcademico::where('anio', $periodo->anio)
                ->where('semestre', 1)
                ->first();
            if ($periodo1) {
                $this->periodo_1_fecha_inicio = $periodo1->fecha_inicio->format('Y-m-d');
                $this->periodo_1_fecha_fin = $periodo1->fecha_fin->format('Y-m-d');
            }
        }
        
        $this->periodo_activo = $periodo->activo;
        $this->editModePeriodo = true;
        $this->showModalPeriodo = true;
    }

    public function closeModalPeriodo()
    {
        $this->showModalPeriodo = false;
        $this->reset([
            'periodo_anio', 'periodo_1_fecha_inicio', 'periodo_1_fecha_fin',
            'periodo_2_fecha_inicio', 'periodo_2_fecha_fin',
            'periodo_activo', 'editModePeriodo', 'periodoId'
        ]);
    }

    public function savePeriodo()
    {
        $this->validate($this->rulesForPeriodo());

        if ($this->editModePeriodo) {
            // Editar: actualizar ambos semestres existentes
            $periodo = PeriodoAcademico::findOrFail($this->periodoId);
            $semestre = $periodo->semestre;
            
            // Actualizar el período actual
            if ($semestre === 1) {
                $periodo->update([
                    'fecha_inicio' => $this->periodo_1_fecha_inicio,
                    'fecha_fin' => $this->periodo_1_fecha_fin,
                    'activo' => $this->periodo_activo,
                ]);
                // Actualizar semestre 2 si existe
                $periodo2 = PeriodoAcademico::where('anio', $this->periodo_anio)
                    ->where('semestre', 2)
                    ->first();
                if ($periodo2) {
                    $periodo2->update([
                        'fecha_inicio' => $this->periodo_2_fecha_inicio,
                        'fecha_fin' => $this->periodo_2_fecha_fin,
                        'activo' => $this->periodo_activo,
                    ]);
                }
            } else {
                $periodo->update([
                    'fecha_inicio' => $this->periodo_2_fecha_inicio,
                    'fecha_fin' => $this->periodo_2_fecha_fin,
                    'activo' => $this->periodo_activo,
                ]);
                // Actualizar semestre 1 si existe
                $periodo1 = PeriodoAcademico::where('anio', $this->periodo_anio)
                    ->where('semestre', 1)
                    ->first();
                if ($periodo1) {
                    $periodo1->update([
                        'fecha_inicio' => $this->periodo_1_fecha_inicio,
                        'fecha_fin' => $this->periodo_1_fecha_fin,
                        'activo' => $this->periodo_activo,
                    ]);
                }
            }
            session()->flash('message', 'Períodos académicos actualizados exitosamente.');
        } else {
            // Crear: crear ambos semestres
            // Verificar que no existan ya
            $existe1 = PeriodoAcademico::where('anio', $this->periodo_anio)
                ->where('semestre', 1)
                ->exists();
            $existe2 = PeriodoAcademico::where('anio', $this->periodo_anio)
                ->where('semestre', 2)
                ->exists();
            
            if ($existe1 || $existe2) {
                session()->flash('error', 'Ya existen períodos académicos para ese año. Edite los existentes.');
                return;
            }

            // Crear semestre 1
            PeriodoAcademico::create([
                'anio' => $this->periodo_anio,
                'semestre' => 1,
                'fecha_inicio' => $this->periodo_1_fecha_inicio,
                'fecha_fin' => $this->periodo_1_fecha_fin,
                'activo' => $this->periodo_activo,
                'created_by' => Auth::user()->run ?? null,
            ]);

            // Crear semestre 2
            PeriodoAcademico::create([
                'anio' => $this->periodo_anio,
                'semestre' => 2,
                'fecha_inicio' => $this->periodo_2_fecha_inicio,
                'fecha_fin' => $this->periodo_2_fecha_fin,
                'activo' => $this->periodo_activo,
                'created_by' => Auth::user()->run ?? null,
            ]);

            session()->flash('message', 'Períodos académicos creados exitosamente (1° y 2° semestre).');
        }

        $this->closeModalPeriodo();
    }

    public function deletePeriodo($id)
    {
        $periodo = PeriodoAcademico::findOrFail($id);
        $periodo->delete();
        session()->flash('message', 'Período académico eliminado exitosamente.');
    }

    public function toggleActivoPeriodo($id)
    {
        $periodo = PeriodoAcademico::findOrFail($id);
        $periodo->update(['activo' => !$periodo->activo]);
        session()->flash('message', 'Estado del período actualizado exitosamente.');
    }

    // ==================== MÉTODOS PARA CURSOS DE VERANO ====================

    public function openModalAgregarVerano()
    {
        $this->resetModalVerano();
        $this->editModeVerano = false;
        $this->showModalVerano = true;
        $this->verano_anio = now()->year;
    }

    public function openModalEditarVerano($id)
    {
        $curso = CursoVerano::findOrFail($id);
        $this->cursoVeranoId = $curso->id_curso_verano;
        $this->verano_anio = $curso->anio;
        $this->verano_inicio = $curso->fecha_inicio->format('Y-m-d');
        $this->verano_fin = $curso->fecha_fin->format('Y-m-d');
        $this->verano_activo = $curso->activo;
        $this->editModeVerano = true;
        $this->showModalVerano = true;
    }

    public function saveVerano()
    {
        $this->validate([
            'verano_anio' => 'required|integer|min:2000|max:' . (now()->year + 10),
            'verano_inicio' => 'required|date|date_format:Y-m-d',
            'verano_fin' => 'required|date|date_format:Y-m-d|after_or_equal:verano_inicio',
        ]);

        try {
            if ($this->editModeVerano) {
                $curso = CursoVerano::findOrFail($this->cursoVeranoId);
                $curso->update([
                    'anio' => $this->verano_anio,
                    'fecha_inicio' => $this->verano_inicio,
                    'fecha_fin' => $this->verano_fin,
                    'activo' => $this->verano_activo,
                ]);
                session()->flash('success', 'Curso de verano actualizado correctamente');
            } else {
                CursoVerano::create([
                    'anio' => $this->verano_anio,
                    'fecha_inicio' => $this->verano_inicio,
                    'fecha_fin' => $this->verano_fin,
                    'activo' => $this->verano_activo,
                    'created_by' => auth()->id(),
                ]);
                session()->flash('success', 'Curso de verano creado correctamente');
            }
            $this->resetModalVerano();
            $this->closeModalVerano();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function deleteVerano($id)
    {
        try {
            CursoVerano::destroy($id);
            session()->flash('success', 'Curso de verano eliminado correctamente');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function toggleActivoVerano($id)
    {
        try {
            $curso = CursoVerano::findOrFail($id);
            $curso->update(['activo' => !$curso->activo]);
            session()->flash('success', 'Estado actualizado correctamente');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function closeModalVerano()
    {
        $this->showModalVerano = false;
    }

    private function resetModalVerano()
    {
        $this->cursoVeranoId = null;
        $this->verano_anio = now()->year;
        $this->verano_inicio = '';
        $this->verano_fin = '';
        $this->verano_activo = true;
        $this->editModeVerano = false;
    }

    // ==================== MÉTODOS PARA CALENDARIO ====================

    public function getDiasCalendario()
    {
        $primerDia = Carbon::create($this->anioCalendario, $this->mesCalendario, 1);
        $ultimoDia = $primerDia->copy()->endOfMonth();
        
        // Obtener el día de la semana del primer día (0 = Domingo, 1 = Lunes, etc.)
        $primerDiaSemana = $primerDia->dayOfWeek;
        
        // Ajustar para que la semana empiece en lunes
        $primerDiaSemana = $primerDiaSemana === 0 ? 6 : $primerDiaSemana - 1;
        
        $dias = [];
        
        // Días del mes anterior para completar la primera semana
        $diaAnterior = $primerDia->copy()->subDays($primerDiaSemana);
        for ($i = 0; $i < $primerDiaSemana; $i++) {
            $dias[] = [
                'fecha' => $diaAnterior->copy(),
                'esMesActual' => false,
                'eventos' => $this->obtenerEventosDia($diaAnterior),
            ];
            $diaAnterior->addDay();
        }
        
        // Días del mes actual
        $diaActual = $primerDia->copy();
        while ($diaActual->lte($ultimoDia)) {
            $dias[] = [
                'fecha' => $diaActual->copy(),
                'esMesActual' => true,
                'eventos' => $this->obtenerEventosDia($diaActual),
            ];
            $diaActual->addDay();
        }
        
        // Días del mes siguiente para completar la última semana
        $diasRestantes = 42 - count($dias); // 6 semanas * 7 días = 42
        for ($i = 0; $i < $diasRestantes; $i++) {
            $dias[] = [
                'fecha' => $diaActual->copy(),
                'esMesActual' => false,
                'eventos' => $this->obtenerEventosDia($diaActual),
            ];
            $diaActual->addDay();
        }
        
        return $dias;
    }

    private function obtenerEventosDia($fecha)
    {
        $eventos = [];
        
        // Buscar feriados
        $feriados = DiaFeriado::where('activo', true)
            ->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->get();
        
        foreach ($feriados as $feriado) {
            $eventos[] = [
                'tipo' => $feriado->tipo,
                'nombre' => $feriado->nombre,
                'color' => $this->getColorEvento($feriado->tipo),
            ];
        }
        
        // Buscar si es inicio/fin de período académico
        $periodos = PeriodoAcademico::where('activo', true)->get();
        foreach ($periodos as $periodo) {
            if ($periodo->fecha_inicio->isSameDay($fecha)) {
                $eventos[] = [
                    'tipo' => 'inicio_semestre',
                    'nombre' => "Inicio {$periodo->nombre_corto}",
                    'color' => 'bg-green-500',
                ];
            }
            if ($periodo->fecha_fin->isSameDay($fecha)) {
                $eventos[] = [
                    'tipo' => 'fin_semestre',
                    'nombre' => "Fin {$periodo->nombre_corto}",
                    'color' => 'bg-red-500',
                ];
            }
            if ($periodo->inicio_verano && $periodo->inicio_verano->isSameDay($fecha)) {
                $eventos[] = [
                    'tipo' => 'inicio_verano',
                    'nombre' => 'Inicio Cursos Verano',
                    'color' => 'bg-orange-500',
                ];
            }
            if ($periodo->fin_verano && $periodo->fin_verano->isSameDay($fecha)) {
                $eventos[] = [
                    'tipo' => 'fin_verano',
                    'nombre' => 'Fin Cursos Verano',
                    'color' => 'bg-orange-500',
                ];
            }
        }
        
        return $eventos;
    }

    private function getColorEvento($tipo)
    {
        return match ($tipo) {
            'feriado' => 'bg-blue-500',
            'semana_reajuste' => 'bg-yellow-500',
            'suspension_actividades' => 'bg-red-500',
            default => 'bg-gray-500',
        };
    }

    public function getNombreMes()
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$this->mesCalendario];
    }

    public function render()
    {
        // Feriados para la tabla
        $query = DiaFeriado::query()->with('creador');

        if ($this->search) {
            $searchTerm = trim($this->search);
            if (strlen($searchTerm) > 0) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                      ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
                });
            }
        }

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        $feriados = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        // Períodos académicos
        $periodos = PeriodoAcademico::orderBy('anio', 'desc')
            ->orderBy('semestre', 'desc')
            ->paginate(10);

        // Cursos de verano
        $cursosVerano = CursoVerano::orderBy('anio', 'desc')
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        // Período actual
        $periodoActual = PeriodoAcademico::obtenerPeriodoActual();

        // Días para el calendario
        $diasCalendario = $this->vistaActiva === 'calendario' ? $this->getDiasCalendario() : [];

        return view('livewire.calendario-academico-table', [
            'feriados' => $feriados,
            'periodos' => $periodos,
            'cursosVerano' => $cursosVerano,
            'periodoActual' => $periodoActual,
            'diasCalendario' => $diasCalendario,
            'nombreMes' => $this->getNombreMes(),
        ]);
    }
}

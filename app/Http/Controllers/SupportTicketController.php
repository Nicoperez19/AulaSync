<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Sede;
use App\Models\User;
use App\Exports\SupportTicketsReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SupportTicketController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function isStaff(User $user): bool
    {
        return $user->is_superuser
            || (string)$user->run === '19716146'
            || $this->hasAnyRole($user, ['Técnico', 'Soporte'])
            || $user->can('gestionar tickets de soporte')
            || $this->hasAnyRole($user, ['Administrador', 'Super Admin', 'Supervisor']);
    }

    private function hasAnyRole(User $user, array $roles): bool
    {
        return $user->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Aplica scope de sede al query.
     * - Superusuario / Admin Máster: ve todos los tickets del sistema.
     * - Resto: sólo los de su sede.
     */
    private function scopeBySede($query, User $user): void
    {
        $isGlobalAdmin = $user->is_superuser || (string)$user->run === '19716146' || $this->hasAnyRole($user, ['Super Admin', 'Administrador', 'Soporte']);
        if (!$isGlobalAdmin && $user->id_sede) {
            $query->where('support_tickets.id_sede', $user->id_sede);
        }
    }

    /** Comprueba que el personal pueda operar tickets dentro de su alcance. */
    private function canManageTicket(User $user, SupportTicket $ticket): bool
    {
        if (!$this->isStaff($user)) {
            return false;
        }

        $isGlobalAdmin = $user->is_superuser
            || (string)$user->run === '19716146'
            || $this->hasAnyRole($user, ['Super Admin', 'Administrador', 'Soporte']);

        return $isGlobalAdmin || !$user->id_sede || (string)$user->id_sede === (string)$ticket->id_sede;
    }

    private function canFilterReportSede(User $user): bool
    {
        return $user->is_superuser
            || (string)$user->run === '19716146'
            || $this->hasAnyRole($user, ['Soporte', 'Super Admin', 'Administrador']);
    }

    /** Prepara filtros y consultas compartidas por la vista y las descargas. */
    private function supportReportData(User $user, Request $request): array
    {
        $canFilterSede = $this->canFilterReportSede($user);
        $schoolQuery = DB::connection('mysql')->table('area_academicas as school_area')
            ->join('facultades as school_faculty', 'school_faculty.id_facultad', '=', 'school_area.id_facultad')
            ->where('school_area.tipo_area_academica', 'escuela');
        if (!$canFilterSede && $user->id_sede) {
            $schoolQuery->where('school_faculty.id_sede', $user->id_sede);
        }
        $schools = $schoolQuery
            ->orderBy('nombre_area_academica')
            ->get(['school_area.id_area_academica', 'school_area.nombre_area_academica']);
        $validator = Validator::make($request->only('fecha_inicio', 'fecha_fin', 'id_sede', 'estado', 'prioridad', 'id_escuela', 'tipo_problema'), [
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'id_sede' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', Rule::in(['open', 'in_progress', 'closed'])],
            'prioridad' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'id_escuela' => ['nullable', 'string', Rule::in($schools->pluck('id_area_academica')->all())],
            'tipo_problema' => ['nullable', Rule::in(array_keys(SupportTicket::CATEGORIES))],
        ]);
        $periodError = $validator->fails();
        $filters = $periodError ? [] : $validator->validated();
        $fechaInicio = Carbon::parse($filters['fecha_inicio'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $fechaFin = Carbon::parse($filters['fecha_fin'] ?? now()->toDateString())->endOfDay();
        $selectedSedeId = $canFilterSede ? ($filters['id_sede'] ?? null) : null;
        $selectedStatus = $filters['estado'] ?? null;
        $selectedPriority = $filters['prioridad'] ?? null;
        $selectedSchoolId = $filters['id_escuela'] ?? null;
        $selectedCategory = $filters['tipo_problema'] ?? null;

        $isStaff = $this->isStaff($user);
        $query = SupportTicket::query();
        if ($isStaff) {
            $this->scopeBySede($query, $user);
        } else {
            $query->where('support_tickets.user_id', $user->run);
        }
        if ($selectedSedeId) {
            $query->where('support_tickets.id_sede', $selectedSedeId);
        }
        $query->whereBetween('support_tickets.created_at', [$fechaInicio, $fechaFin]);
        if ($selectedStatus) {
            $query->where('support_tickets.status', $selectedStatus);
        }
        if ($selectedPriority) {
            $query->where('support_tickets.priority', $selectedPriority);
        }
        if ($selectedCategory) {
            $query->where('support_tickets.category', $selectedCategory);
        }
        if ($selectedSchoolId) {
            $schoolIdExpression = "COALESCE(CASE WHEN direct_school.tipo_area_academica = 'escuela' THEN direct_school.id_area_academica END, CASE WHEN career_school.tipo_area_academica = 'escuela' THEN career_school.id_area_academica END)";
            $query->whereExists(function ($subquery) use ($selectedSchoolId, $schoolIdExpression) {
                $subquery->selectRaw('1')
                    ->from('users as report_user')
                    ->leftJoin('area_academicas as direct_school', 'direct_school.id_area_academica', '=', 'report_user.id_area_academica')
                    ->leftJoin('carreras as report_career', 'report_career.id_carrera', '=', 'report_user.id_carrera')
                    ->leftJoin('area_academicas as career_school', 'career_school.id_area_academica', '=', 'report_career.id_area_academica')
                    ->whereColumn('report_user.run', 'support_tickets.user_id')
                    ->whereRaw("{$schoolIdExpression} = ?", [$selectedSchoolId]);
            });
        }

        $reportStats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'sedes' => (clone $query)->whereNotNull('id_sede')->distinct()->count('id_sede'),
        ];
        $reportStats['average_resolution_minutes'] = (clone $query)
            ->where('status', 'closed')
            ->whereNotNull('closed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, closed_at)) as average_minutes')
            ->value('average_minutes');

        $sedes = $isStaff
            ? Sede::orderBy('nombre_sede')->get()
            : Sede::where('id_sede', $user->id_sede)->orderBy('nombre_sede')->get();
        $reportBySede = (clone $query)
            ->select('id_sede')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(CASE WHEN status = 'open' THEN 1 END) as open")
            ->selectRaw("COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress")
            ->selectRaw("COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed")
            ->selectRaw("COUNT(CASE WHEN priority = 'low' THEN 1 END) as low")
            ->selectRaw("COUNT(CASE WHEN priority = 'medium' THEN 1 END) as medium")
            ->selectRaw("COUNT(CASE WHEN priority = 'high' THEN 1 END) as high")
            ->selectRaw("COUNT(CASE WHEN category = 'access' THEN 1 END) as access")
            ->selectRaw("COUNT(CASE WHEN category = 'connectivity' THEN 1 END) as connectivity")
            ->selectRaw("COUNT(CASE WHEN category = 'hardware' THEN 1 END) as hardware")
            ->selectRaw("COUNT(CASE WHEN category = 'software' THEN 1 END) as software")
            ->selectRaw("COUNT(CASE WHEN category = 'reservations' THEN 1 END) as reservations")
            ->selectRaw("COUNT(CASE WHEN category = 'other' THEN 1 END) as other")
            ->groupBy('id_sede')
            ->orderByDesc('total')
            ->get();
        $sedeNames = $sedes->keyBy('id_sede');
        $reportBySede->transform(function ($row) use ($sedeNames) {
            $row->sede_name = $sedeNames->get($row->id_sede)?->nombre_sede ?? 'Sin sede';
            $topCategory = collect(SupportTicket::CATEGORIES)->keys()
                ->sortByDesc(fn ($category) => (int) $row->{$category})
                ->first();
            $row->top_category = SupportTicket::CATEGORIES[$topCategory] ?? 'Sin datos';
            $row->top_category_total = (int) ($row->{$topCategory} ?? 0);
            return $row;
        });

        $schoolIdExpression = "COALESCE(CASE WHEN direct_school.tipo_area_academica = 'escuela' THEN direct_school.id_area_academica END, CASE WHEN career_school.tipo_area_academica = 'escuela' THEN career_school.id_area_academica END)";
        $schoolNameExpression = "COALESCE(CASE WHEN direct_school.tipo_area_academica = 'escuela' THEN direct_school.nombre_area_academica END, CASE WHEN career_school.tipo_area_academica = 'escuela' THEN career_school.nombre_area_academica END, 'Sin escuela')";
        $schoolJoins = function ($builder) {
            return $builder
                ->leftJoin('users as report_user', 'report_user.run', '=', 'support_tickets.user_id')
                ->leftJoin('area_academicas as direct_school', 'direct_school.id_area_academica', '=', 'report_user.id_area_academica')
                ->leftJoin('carreras as report_career', 'report_career.id_carrera', '=', 'report_user.id_carrera')
                ->leftJoin('area_academicas as career_school', 'career_school.id_area_academica', '=', 'report_career.id_area_academica');
        };
        $reportBySchool = $schoolJoins(clone $query)
            ->selectRaw("{$schoolIdExpression} as school_id")
            ->selectRaw("{$schoolNameExpression} as school_name")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(CASE WHEN support_tickets.status = 'open' THEN 1 END) as open")
            ->selectRaw("COUNT(CASE WHEN support_tickets.status = 'in_progress' THEN 1 END) as in_progress")
            ->selectRaw("COUNT(CASE WHEN support_tickets.status = 'closed' THEN 1 END) as closed")
            ->selectRaw("COUNT(CASE WHEN support_tickets.priority = 'low' THEN 1 END) as low")
            ->selectRaw("COUNT(CASE WHEN support_tickets.priority = 'medium' THEN 1 END) as medium")
            ->selectRaw("COUNT(CASE WHEN support_tickets.priority = 'high' THEN 1 END) as high")
            ->selectRaw("COUNT(CASE WHEN support_tickets.category = 'access' THEN 1 END) as access")
            ->selectRaw("COUNT(CASE WHEN support_tickets.category = 'connectivity' THEN 1 END) as connectivity")
            ->selectRaw("COUNT(CASE WHEN support_tickets.category = 'hardware' THEN 1 END) as hardware")
            ->selectRaw("COUNT(CASE WHEN support_tickets.category = 'software' THEN 1 END) as software")
            ->selectRaw("COUNT(CASE WHEN support_tickets.category = 'reservations' THEN 1 END) as reservations")
            ->selectRaw("COUNT(CASE WHEN support_tickets.category = 'other' THEN 1 END) as other")
            ->groupByRaw("{$schoolIdExpression}, {$schoolNameExpression}")
            ->orderByDesc('total')
            ->get();
        $reportBySchool->transform(function ($row) {
            $topCategory = collect(SupportTicket::CATEGORIES)->keys()
                ->sortByDesc(fn ($category) => (int) $row->{$category})
                ->first();
            $row->top_category = SupportTicket::CATEGORIES[$topCategory] ?? 'Sin datos';
            $row->top_category_total = (int) ($row->{$topCategory} ?? 0);
            return $row;
        });
        $reportStats['escuelas'] = $reportBySchool->whereNotNull('school_id')->count();
        $reportByPriority = (clone $query)
            ->select('priority')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('priority')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->get();
        foreach (['low', 'medium', 'high'] as $priorityLevel) {
            $reportStats['priority_' . $priorityLevel] = $reportByPriority->firstWhere('priority', $priorityLevel)?->total ?? 0;
        }
        $reportByCategory = (clone $query)
            ->select('category')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        $reportByCategory->transform(function ($row) {
            $row->label = SupportTicket::CATEGORIES[$row->category] ?? SupportTicket::CATEGORIES['other'];
            return $row;
        });
        $reportStats['top_category'] = $reportByCategory->first();

        $userIds = (clone $query)->distinct()->pluck('support_tickets.user_id');
        $schoolNameByUser = $userIds->isEmpty()
            ? collect()
            : DB::connection('mysql')->table('users as report_user')
                ->leftJoin('area_academicas as direct_school', 'direct_school.id_area_academica', '=', 'report_user.id_area_academica')
                ->leftJoin('carreras as report_career', 'report_career.id_carrera', '=', 'report_user.id_carrera')
                ->leftJoin('area_academicas as career_school', 'career_school.id_area_academica', '=', 'report_career.id_area_academica')
                ->whereIn('report_user.run', $userIds)
                ->select('report_user.run as user_id')
                ->selectRaw("{$schoolNameExpression} as school_name")
                ->get()
                ->keyBy('user_id');

        $reportTickets = (clone $query)
            ->with(['user', 'assignedTo'])
            ->withCount('replies')
            ->orderByDesc('support_tickets.created_at');

        return compact(
            'fechaInicio', 'fechaFin', 'periodError', 'canFilterSede', 'selectedSedeId',
            'selectedStatus', 'selectedPriority', 'selectedSchoolId', 'selectedCategory', 'schools',
            'reportStats', 'reportBySede', 'reportBySchool', 'reportByPriority', 'reportByCategory', 'reportTickets', 'sedes', 'schoolNameByUser'
        ) + [
            'selectedSedeName' => $selectedSedeId
                ? ($sedeNames->get($selectedSedeId)?->nombre_sede ?? 'Sede seleccionada')
                : 'Todas las sedes',
            'selectedSchoolName' => $selectedSchoolId
                ? ($schools->firstWhere('id_area_academica', $selectedSchoolId)?->nombre_area_academica ?? 'Escuela seleccionada')
                : 'Todas las escuelas',
            'selectedCategoryName' => $selectedCategory ? (SupportTicket::CATEGORIES[$selectedCategory] ?? 'Tipo seleccionado') : 'Todos los tipos',
        ];
    }

    private function averageResolutionLabel($minutes): string
    {
        if ($minutes === null) {
            return 'Sin datos';
        }

        $minutes = (int) round((float) $minutes);
        return intdiv($minutes, 60) . ' h ' . ($minutes % 60) . ' min';
    }

    private function excelSafeText(?string $value): string
    {
        $value = $value ?? '';
        return preg_match('/^\s*[=+@-]/u', $value) ? "'" . $value : $value;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VISTAS
    // ─────────────────────────────────────────────────────────────────────────

    /** Dashboard de gestión para el equipo de soporte. */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $isStaff = $this->isStaff($user);
        $reportData = $this->supportReportData($user, $request);
        $canFilterSede = $reportData['canFilterSede'];

        $baseQuery = SupportTicket::query();
        if ($isStaff) {
            $this->scopeBySede($baseQuery, $user);
        } else {
            // Vista previa visual para usuarios sin rol de soporte: sólo sus propios tickets.
            $baseQuery->where('support_tickets.user_id', $user->run);
        }
        if ($reportData['selectedSedeId']) {
            $baseQuery->where('support_tickets.id_sede', $reportData['selectedSedeId']);
        }

        $stats = [
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
            'total' => (clone $baseQuery)->count(),
        ];

        $tickets = (clone $baseQuery)->whereIn('status', ['open', 'in_progress'])->with(['user', 'assignedTo'])->withCount('replies')
            ->orderByRaw("FIELD(status, 'open', 'in_progress', 'closed')")
            ->orderByDesc('support_tickets.created_at')->limit(8)->get();

        return view('soporte.dashboard', array_merge($reportData, compact('stats', 'tickets', 'isStaff')));
    }

    /** Informe detallado de gestión de soporte. */
    public function reportes(Request $request)
    {
        $data = $this->supportReportData(Auth::user(), $request);
        $data['tickets'] = $data['reportTickets']->paginate(25)->withQueryString();
        unset($data['reportTickets']);

        return view('soporte.reportes', $data);
    }

    /** Descarga el informe de soporte en Excel o PDF. */
    public function exportReportes(Request $request, string $formato)
    {
        abort_unless(in_array($formato, ['excel', 'pdf'], true), 404);

        $data = $this->supportReportData(Auth::user(), $request);
        $fechaNombre = $data['fechaInicio']->format('Ymd') . '_' . $data['fechaFin']->format('Ymd');
        $filename = 'reporte_soporte_' . $fechaNombre;

        if ($formato === 'excel') {
            $ticketRows = $data['reportTickets']->get()->map(fn ($ticket) => [
                $ticket->id,
                $this->excelSafeText($ticket->title),
                $this->excelSafeText($ticket->description),
                $this->excelSafeText($ticket->user?->name ?? 'Sin solicitante'),
                $this->excelSafeText($data['sedes']->firstWhere('id_sede', $ticket->id_sede)?->nombre_sede ?? 'Sin sede'),
                $this->excelSafeText($data['schoolNameByUser']->get($ticket->user_id)?->school_name ?? 'Sin escuela'),
                $ticket->categoryLabel(),
                $ticket->priorityLabel(),
                $ticket->statusLabel(),
                $this->excelSafeText($ticket->assignedTo?->name ?? 'Sin asignar'),
                $ticket->replies_count,
                $ticket->created_at?->format('d/m/Y H:i'),
                $ticket->closed_at?->format('d/m/Y H:i') ?? '—',
            ])->all();

            return Excel::download(new SupportTicketsReportExport(
                $data['fechaInicio']->format('d/m/Y') . ' al ' . $data['fechaFin']->format('d/m/Y'),
                $data['selectedSedeName'],
                $data['selectedSchoolName'],
                match ($data['selectedStatus']) { 'open' => 'Pendiente', 'in_progress' => 'En proceso', 'closed' => 'Cerrado', default => 'Todos' },
                match ($data['selectedPriority']) { 'low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', default => 'Todas' },
                $data['selectedCategoryName'],
                $data['reportStats'],
                $this->averageResolutionLabel($data['reportStats']['average_resolution_minutes']),
                $data['reportBySede']->map(fn ($row) => [$row->sede_name, $row->total, $row->open, $row->in_progress, $row->closed, $row->low, $row->medium, $row->high, $row->access, $row->connectivity, $row->hardware, $row->software, $row->reservations, $row->other])->all(),
                $data['reportBySchool']->map(fn ($row) => [$row->school_name, $row->total, $row->open, $row->in_progress, $row->closed, $row->low, $row->medium, $row->high, $row->access, $row->connectivity, $row->hardware, $row->software, $row->reservations, $row->other])->all(),
                $data['reportByPriority']->map(fn ($row) => [[ 'low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta' ][$row->priority], $row->total])->all(),
                $data['reportByCategory']->map(fn ($row) => [$row->label, $row->total])->all(),
                $ticketRows,
                Auth::user()->name ?? 'Equipo de Soporte',
                now()->format('d/m/Y H:i')
            ), $filename . '.xlsx');
        }

        $tickets = $data['reportTickets']->get();
        unset($data['reportTickets']);
        $pdf = Pdf::loadView('soporte.reportes-pdf', [
            ...$data,
            'tickets' => $tickets,
            'periodo' => $data['fechaInicio']->format('d/m/Y') . ' al ' . $data['fechaFin']->format('d/m/Y'),
            'tiempoPromedio' => $this->averageResolutionLabel($data['reportStats']['average_resolution_minutes']),
            'estadoSeleccionado' => match ($data['selectedStatus']) { 'open' => 'Pendiente', 'in_progress' => 'En proceso', 'closed' => 'Cerrado', default => 'Todos' },
            'prioridadSeleccionada' => match ($data['selectedPriority']) { 'low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', default => 'Todas' },
            'tipoSeleccionado' => $data['selectedCategoryName'],
            'generadoPor' => Auth::user()->name ?? 'Equipo de Soporte',
            'generadoEn' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Listado de tickets.
     * - Staff (Técnico/Admin/Supervisor): ve todos los tickets de su sede.
     * - Superuser: ve todos los tickets del sistema.
     * - Usuario normal: ve solo sus propios tickets.
     */
    public function index(Request $request)
    {
        $user      = Auth::user();
        $isTecnico = $this->isStaff($user);
        $isGlobalAdmin = $user->is_superuser
            || (string)$user->run === '19716146'
            || $this->hasAnyRole($user, ['Super Admin', 'Administrador']);

        $query = SupportTicket::with(['user', 'assignedTo', 'replies.user'])
            ->orderByRaw("FIELD(status, 'open', 'in_progress', 'closed')")
            ->orderBy('created_at', 'desc');

        if ($isTecnico) {
            $this->scopeBySede($query, $user);
        } else {
            $query->where('user_id', $user->run);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($isGlobalAdmin && $request->filled('id_sede')) {
            $query->where('id_sede', $request->input('id_sede'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $tickets = $query->paginate(15)->withQueryString();

        $stats = null;
        if ($isTecnico) {
            $baseStats = SupportTicket::query();
            $this->scopeBySede($baseStats, $user);
            if ($isGlobalAdmin && $request->filled('id_sede')) {
                $baseStats->where('id_sede', $request->input('id_sede'));
            }
            $stats = [
                'open'        => (clone $baseStats)->where('status', 'open')->count(),
                'in_progress' => (clone $baseStats)->where('status', 'in_progress')->count(),
                'closed'      => (clone $baseStats)->where('status', 'closed')->count(),
            ];
        } else {
            $baseStats = SupportTicket::where('user_id', $user->run);
            $stats = [
                'open'        => (clone $baseStats)->where('status', 'open')->count(),
                'in_progress' => (clone $baseStats)->where('status', 'in_progress')->count(),
                'closed'      => (clone $baseStats)->where('status', 'closed')->count(),
            ];
        }

        $sedes = $isGlobalAdmin
            ? Sede::orderBy('nombre_sede')->get()
            : ($isTecnico && $user->id_sede
                ? Sede::where('id_sede', $user->id_sede)->get()
                : collect());

        return view('soporte.index', compact('tickets', 'isTecnico', 'isGlobalAdmin', 'stats', 'sedes'));
    }

    /**
     * Formulario para crear nuevo ticket.
     */
    public function create()
    {
        return view('soporte.create');
    }

    /**
     * Guardar nuevo ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'required|string|max:5000',
            'category'    => ['required', Rule::in(array_keys(SupportTicket::CATEGORIES))],
            'priority'    => 'required|in:low,medium,high',
        ], [
            'title.required'       => 'El título es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'category.required'    => 'Selecciona el tipo de problema.',
            'priority.required'    => 'Selecciona una prioridad.',
        ]);

        $user   = Auth::user();
        $ticket = SupportTicket::create([
            'user_id'     => $user->run,
            'id_sede'     => $user->id_sede,
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'category'    => $validated['category'],
            'priority'    => $validated['priority'],
            'status'      => 'open',
        ]);

        return redirect()
            ->route('soporte.show', $ticket)
            ->with('success', 'Ticket creado correctamente. Un técnico lo atenderá a la brevedad.');
    }

    /**
     * Ver detalle de un ticket con sus respuestas.
     */
    public function show(SupportTicket $ticket)
    {
        $user      = Auth::user();
        $isTecnico = $this->isStaff($user);

        $canView = $ticket->user_id === $user->run
            || $this->canManageTicket($user, $ticket);

        if (!$canView) {
            abort(403, 'No tienes permiso para ver este ticket.');
        }

        $ticket->load(['user', 'assignedTo', 'replies.user']);

        $tecnicos = collect();
        if ($isTecnico) {
            $tecnicoQuery = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Técnico', 'Soporte', 'Administrador']);
            })->orderBy('name');
            if (!$user->is_superuser && !$this->hasAnyRole($user, ['Soporte']) && !$user->can('gestionar tickets de soporte') && $user->id_sede) {
                $tecnicoQuery->where('id_sede', $user->id_sede);
            }
            $tecnicos = $tecnicoQuery->get();
        }

        return view('soporte.show', compact('ticket', 'isTecnico', 'tecnicos'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACCIONES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Agregar respuesta a un ticket.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $user      = Auth::user();
        $isTecnico = $this->isStaff($user);
        $isOwner   = $ticket->user_id === $user->run;

        if (!$isOwner && !$this->canManageTicket($user, $ticket)) {
            abort(403);
        }

        if ($ticket->isClosed()) {
            return back()->with('error', 'No se puede responder a un ticket cerrado.');
        }

        $request->validate([
            'message' => 'required|string|max:5000',
        ], [
            'message.required' => 'El mensaje no puede estar vacío.',
        ]);

        DB::transaction(function () use ($request, $ticket, $user, $isTecnico) {
            SupportTicketReply::create([
                'ticket_id'      => $ticket->id,
                'user_id'        => $user->run,
                'message'        => $request->message,
                'is_staff_reply' => $isTecnico,
            ]);

            // Si el técnico responde, pasa a "en proceso" automáticamente
            if ($isTecnico && $ticket->isOpen()) {
                $ticket->update([
                    'status'      => 'in_progress',
                    'assigned_to' => $ticket->assigned_to ?? $user->run,
                ]);
            }
        });

        return back()->with('success', 'Respuesta enviada correctamente.');
    }

    /**
     * Cambiar el estado del ticket (solo staff).
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        if (!$this->canManageTicket(Auth::user(), $ticket)) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:open,in_progress,closed']);

        $data = ['status' => $request->status];
        if ($request->status === 'closed') {
            $data['closed_at'] = now();
        } elseif ($ticket->isClosed()) {
            $data['closed_at'] = null;
        }

        $ticket->update($data);

        $label = match ($request->status) {
            'open'        => 'marcado como abierto',
            'in_progress' => 'marcado como en proceso',
            'closed'      => 'cerrado',
            default       => 'actualizado',
        };

        return back()->with('success', "Ticket {$label} correctamente.");
    }

    /**
     * Asignar técnico a un ticket.
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        if (!$this->canManageTicket(Auth::user(), $ticket)) {
            abort(403);
        }

        $request->validate(['assigned_to' => 'nullable|exists:users,run']);

        $ticket->update([
            'assigned_to' => $request->assigned_to ?: null,
            'status'      => $request->assigned_to ? 'in_progress' : $ticket->status,
        ]);

        return back()->with('success', 'Asignación actualizada.');
    }

    /**
     * Cerrar ticket (dueño o staff).
     */
    public function close(SupportTicket $ticket)
    {
        $user = Auth::user();
        if ($ticket->user_id !== $user->run && !$this->canManageTicket($user, $ticket)) {
            abort(403);
        }

        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        return back()->with('success', 'Ticket cerrado correctamente.');
    }

    /**
     * Reabrir ticket cerrado.
     */
    public function reopen(SupportTicket $ticket)
    {
        $user = Auth::user();
        if ($ticket->user_id !== $user->run && !$this->canManageTicket($user, $ticket)) {
            abort(403);
        }

        $ticket->update(['status' => 'open', 'closed_at' => null]);

        return back()->with('success', 'Ticket reabierto.');
    }
}

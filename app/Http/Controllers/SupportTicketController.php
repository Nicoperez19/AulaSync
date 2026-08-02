<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupportTicketController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function isStaff(User $user): bool
    {
        return $user->is_superuser
            || (string)$user->run === '19716146'
            || $user->hasRole('Técnico')
            || $user->hasRole('Administrador')
            || $user->hasRole('Super Admin')
            || $user->hasRole('Supervisor');
    }

    /**
     * Aplica scope de sede al query.
     * - Superusuario / Admin Máster: ve todos los tickets del sistema.
     * - Resto: sólo los de su sede.
     */
    private function scopeBySede($query, User $user): void
    {
        $isGlobalAdmin = $user->is_superuser || (string)$user->run === '19716146' || $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        if (!$isGlobalAdmin && $user->id_sede) {
            $query->where('id_sede', $user->id_sede);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VISTAS
    // ─────────────────────────────────────────────────────────────────────────

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

        $query = SupportTicket::with(['user', 'assignedTo', 'replies'])
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
            $stats = [
                'open'        => (clone $baseStats)->where('status', 'open')->count(),
                'in_progress' => (clone $baseStats)->where('status', 'in_progress')->count(),
                'closed'      => (clone $baseStats)->where('status', 'closed')->count(),
            ];
        }

        return view('soporte.index', compact('tickets', 'isTecnico', 'stats'));
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
            'priority'    => 'required|in:low,medium,high',
        ], [
            'title.required'       => 'El título es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'priority.required'    => 'Selecciona una prioridad.',
        ]);

        $user   = Auth::user();
        $ticket = SupportTicket::create([
            'user_id'     => $user->run,
            'id_sede'     => $user->id_sede,
            'title'       => $validated['title'],
            'description' => $validated['description'],
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
            || $user->is_superuser
            || ($isTecnico && ($user->id_sede === $ticket->id_sede || !$user->id_sede));

        if (!$canView) {
            abort(403, 'No tienes permiso para ver este ticket.');
        }

        $ticket->load(['user', 'assignedTo', 'replies.user']);

        $tecnicos = collect();
        if ($isTecnico) {
            $tecnicoQuery = User::role(['Técnico', 'Administrador'])->orderBy('name');
            if (!$user->is_superuser && $user->id_sede) {
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

        if (!$isTecnico && !$isOwner) {
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
        if (!$this->isStaff(Auth::user())) {
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
        if (!$this->isStaff(Auth::user())) {
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
        if ($ticket->user_id !== $user->run && !$this->isStaff($user)) {
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
        if ($ticket->user_id !== $user->run && !$this->isStaff($user)) {
            abort(403);
        }

        $ticket->update(['status' => 'open', 'closed_at' => null]);

        return back()->with('success', 'Ticket reabierto.');
    }
}

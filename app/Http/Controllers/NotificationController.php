<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Obtener notificaciones del usuario
        $notifications = Notification::where('user_run', $user->run)
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        // Contadores
        $unreadCount = Notification::where('user_run', $user->run)
            ->unread()
            ->active()
            ->count();
            
        $urgentCount = Notification::where('user_run', $user->run)
            ->unread()
            ->active()
            ->byPriority(Notification::PRIORITY_URGENT)
            ->count();
            
        // Estadísticas por tipo
        $statsByType = Notification::where('user_run', $user->run)
            ->active()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
            
        return view('notifications.index', compact('notifications', 'unreadCount', 'urgentCount', 'statsByType'));
    }

    public function markAsRead(Request $request)
    {
        $notification = Notification::findOrFail($request->notification_id);
        
        // Verificar que la notificación pertenece al usuario
        if ($notification->user_run !== Auth::user()->run) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('user_run', $user->run)
            ->unread()
            ->active()
            ->update(['read_at' => Carbon::now()]);
            
        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $notification = Notification::findOrFail($request->notification_id);
        
        // Verificar que la notificación pertenece al usuario
        if ($notification->user_run !== Auth::user()->run) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        $notification->delete();
        
        return response()->json(['success' => true]);
    }

    public function clearAll()
    {
        $user = Auth::user();
        
        Notification::where('user_run', $user->run)->delete();
        
        return response()->json(['success' => true]);
    }

    public function getUnreadCount(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('dashboard');
        }
        $user = Auth::user();
        $unreadCount = Notification::where('user_run', $user->run)
            ->unread()
            ->active()
            ->count();
        $urgentCount = Notification::where('user_run', $user->run)
            ->unread()
            ->active()
            ->byPriority(Notification::PRIORITY_URGENT)
            ->count();
        return response()->json([
            'unread_count' => $unreadCount,
            'urgent_count' => $urgentCount
        ]);
    }

    public function getRecentNotifications(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('dashboard');
        }
        $user = Auth::user();
        $notifications = Notification::where('user_run', $user->run)
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        return response()->json($notifications);
    }

    public function filter(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('dashboard');
        }
        $user = Auth::user();
        $query = Notification::where('user_run', $user->run)->active();
        // Filtros
        if ($request->has('type') && $request->type !== 'all') {
            $query->byType($request->type);
        }
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->byPriority($request->priority);
        }
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $query->unread();
            } elseif ($request->status === 'read') {
                $query->read();
            }
        }
        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($notifications);
    }

    // Método para crear notificaciones programáticamente
    public static function createNotification($data)
    {
        return Notification::create([
            'type' => $data['type'] ?? Notification::TYPE_INFO,
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => $data['data'] ?? null,
            'user_run' => $data['user_run'] ?? null,
            'priority' => $data['priority'] ?? Notification::PRIORITY_MEDIUM,
            'expires_at' => isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
            'action_url' => $data['action_url'] ?? null,
            'action_text' => $data['action_text'] ?? null,
        ]);
    }

    // Método para crear notificación de devolución de llaves
    public static function createKeyReturnNotification($profesor, $espacio, $horaTermino, $userRun)
    {
        return self::createNotification([
            'type' => Notification::TYPE_KEY_RETURN,
            'title' => 'Devolución de llaves pendiente',
            'message' => "El profesor {$profesor} debe devolver las llaves de la sala {$espacio}. Su clase finaliza a las {$horaTermino}.",
            'priority' => Notification::PRIORITY_HIGH,
            'expires_at' => Carbon::now()->addHours(2), // Expira en 2 horas
            'action_url' => '/dashboard',
            'action_text' => 'Ver Dashboard',
            'user_run' => $userRun,
            'data' => [
                'profesor' => $profesor,
                'espacio' => $espacio,
                'hora_termino' => $horaTermino
            ]
        ]);
    }

    // Método para crear notificación de reserva
    public static function createReservationNotification($user, $espacio, $fecha, $hora)
    {
        return self::createNotification([
            'type' => Notification::TYPE_RESERVATION,
            'title' => 'Reserva confirmada',
            'message' => "Su reserva para el espacio {$espacio} ha sido confirmada para el {$fecha} a las {$hora}.",
            'user_run' => $user->run,
            'priority' => Notification::PRIORITY_MEDIUM,
            'expires_at' => Carbon::now()->addDays(1),
            'action_url' => '/reservations',
            'action_text' => 'Ver Reservas',
            'data' => [
                'espacio' => $espacio,
                'fecha' => $fecha,
                'hora' => $hora
            ]
        ]);
    }

    // Método para crear notificación del sistema
    public static function createSystemNotification($title, $message, $priority = Notification::PRIORITY_MEDIUM)
    {
        return self::createNotification([
            'type' => Notification::TYPE_SYSTEM,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'expires_at' => Carbon::now()->addDays(7),
        ]);
    }
}

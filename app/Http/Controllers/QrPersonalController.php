<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Espacio;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QrPersonalController extends Controller
{
    /**
     * Generar imagen QR usando la API de endroid/qr-code v6.x
     */
    private function generarImagenQr(string $data, int $size = 300, int $margin = 15): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        );

        $result = $builder->build();
        return $result->getString();
    }

    /**
     * Generar un nuevo token QR personal para un usuario.
     * El token se cifra con Argon2 antes de almacenarse.
     */
    public function generar(Request $request, $run)
    {
        try {
            // Verificar permiso
            if (!auth()->user()->can('generar qr personal')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para generar QR personales.'
                ], 403);
            }

            $user = User::where('run', $run)->firstOrFail();

            // Generar token único
            $tokenPlano = Str::random(32) . '_' . $user->run . '_' . time();
            
            // Cifrar con Argon2 (usando password_hash de PHP)
            $tokenCifrado = password_hash($tokenPlano, PASSWORD_ARGON2ID);

            // Guardar en la base de datos
            $user->qr_personal_token = $tokenCifrado;
            $user->qr_personal_created_at = Carbon::now();
            $user->save();

            Log::info('QR personal generado', [
                'run' => $run,
                'generado_por' => auth()->user()->run
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR personal generado exitosamente.',
                'tiene_qr' => true,
                'fecha_creacion' => $user->qr_personal_created_at->format('d/m/Y H:i')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al generar QR personal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar el QR como imagen PNG.
     */
    public function descargar(Request $request, $run)
    {
        try {
            // Verificar permiso
            if (!auth()->user()->can('generar qr personal')) {
                abort(403, 'No tienes permiso para descargar QR personales.');
            }

            $user = User::where('run', $run)->firstOrFail();

            if (!$user->tieneQrPersonal()) {
                abort(404, 'Este usuario no tiene un QR personal generado.');
            }

            // Datos del QR
            $qrData = json_encode([
                'type' => 'qr_personal_aulasync',
                'run' => $user->run,
                'name' => $user->name,
                'token_hash' => substr($user->qr_personal_token, 0, 20) . '...',
                'created_at' => $user->qr_personal_created_at?->format('Y-m-d H:i:s'),
            ]);

            // Generar QR con endroid/qr-code v6.x
            $imageContent = $this->generarImagenQr($qrData, 400, 20);

            $filename = 'qr_personal_' . $user->run . '.png';

            return response($imageContent)
                ->header('Content-Type', 'image/png')
                ->header('Content-Length', strlen($imageContent))
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Usuario no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al descargar QR personal: ' . $e->getMessage());
            abort(500, 'Error al generar la imagen del QR.');
        }
    }

    /**
     * Obtener el QR como imagen para preview (base64).
     */
    public function preview(Request $request, $run)
    {
        try {
            // Verificar permiso
            if (!auth()->user()->can('generar qr personal')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver QR personales.'
                ], 403);
            }

            $user = User::where('run', $run)->firstOrFail();

            if (!$user->tieneQrPersonal()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este usuario no tiene un QR personal generado.'
                ], 404);
            }

            // Datos del QR
            $qrData = json_encode([
                'type' => 'qr_personal_aulasync',
                'run' => $user->run,
                'name' => $user->name,
                'token_hash' => substr($user->qr_personal_token, 0, 20) . '...',
                'created_at' => $user->qr_personal_created_at?->format('Y-m-d H:i:s'),
            ]);

            // Generar QR con endroid/qr-code v6.x
            $imageContent = $this->generarImagenQr($qrData, 300, 15);

            return response()->json([
                'success' => true,
                'qr_base64' => base64_encode($imageContent),
                'usuario' => [
                    'run' => $user->run,
                    'name' => $user->name,
                    'fecha_creacion' => $user->qr_personal_created_at?->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al previsualizar QR personal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Anular/eliminar el token QR personal de un usuario.
     */
    public function anular(Request $request, $run)
    {
        try {
            // Verificar permiso
            if (!auth()->user()->can('generar qr personal')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para anular QR personales.'
                ], 403);
            }

            $user = User::where('run', $run)->firstOrFail();

            if (!$user->tieneQrPersonal()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este usuario no tiene un QR personal para anular.'
                ], 404);
            }

            // Eliminar token
            $user->qr_personal_token = null;
            $user->qr_personal_created_at = null;
            $user->save();

            Log::info('QR personal anulado', [
                'run' => $run,
                'anulado_por' => auth()->user()->run
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR personal anulado exitosamente.',
                'tiene_qr' => false
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al anular QR personal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al anular el QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si un usuario tiene QR personal.
     */
    public function verificar(Request $request, $run)
    {
        try {
            if (!auth()->user()->can('generar qr personal')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para esta acción.'
                ], 403);
            }

            $user = User::where('run', $run)->firstOrFail();

            return response()->json([
                'success' => true,
                'tiene_qr' => $user->tieneQrPersonal(),
                'fecha_creacion' => $user->qr_personal_created_at?->format('d/m/Y H:i')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
    }

    /**
     * Verificar QR personal escaneado (API pública para el plano digital).
     * Verifica que el QR personal sea válido y el usuario tenga permiso de liberar salas.
     */
    public function verificarQrPersonalEscaneado(Request $request)
    {
        try {
            $request->validate([
                'qr_data' => 'required|string'
            ]);

            // Intentar decodificar el QR
            $qrData = json_decode($request->qr_data, true);

            if (!$qrData || !isset($qrData['type']) || $qrData['type'] !== 'qr_personal_aulasync') {
                return response()->json([
                    'success' => false,
                    'es_qr_personal' => false,
                    'message' => 'No es un QR personal válido.'
                ]);
            }

            $run = $qrData['run'] ?? null;
            if (!$run) {
                return response()->json([
                    'success' => false,
                    'es_qr_personal' => true,
                    'message' => 'QR personal inválido: sin RUN.'
                ]);
            }

            // Buscar usuario
            $user = User::where('run', $run)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'es_qr_personal' => true,
                    'message' => 'Usuario del QR no encontrado.'
                ]);
            }

            // Verificar que tenga QR personal activo
            if (!$user->tieneQrPersonal()) {
                return response()->json([
                    'success' => false,
                    'es_qr_personal' => true,
                    'message' => 'El QR personal de este usuario ha sido anulado.'
                ]);
            }

            // Verificar que tenga el permiso de liberar salas forzadamente
            if (!$user->can('liberar salas forzadamente')) {
                return response()->json([
                    'success' => false,
                    'es_qr_personal' => true,
                    'message' => 'Este usuario no tiene permiso para liberar salas.'
                ]);
            }

            Log::info('QR personal verificado para liberación de salas', [
                'run' => $run,
                'nombre' => $user->name
            ]);

            return response()->json([
                'success' => true,
                'es_qr_personal' => true,
                'puede_liberar' => true,
                'usuario' => [
                    'run' => $user->run,
                    'nombre' => $user->name
                ],
                'message' => 'QR personal válido. Puede liberar salas.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al verificar QR personal escaneado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'es_qr_personal' => false,
                'message' => 'Error al verificar el QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liberar una sala forzadamente usando QR personal.
     * Requiere que el usuario del QR tenga el permiso 'liberar salas forzadamente'.
     */
    public function liberarSalaForzadamente(Request $request)
    {
        try {
            $request->validate([
                'run_administrador' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runAdmin = $request->run_administrador;
            $idEspacio = $request->id_espacio;

            // Verificar que el administrador tenga el permiso
            $admin = User::where('run', $runAdmin)->first();
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Administrador no encontrado.'
                ], 404);
            }

            if (!$admin->can('liberar salas forzadamente')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para liberar salas forzadamente.'
                ], 403);
            }

            // Verificar que el espacio exista
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado.'
                ], 404);
            }

            DB::beginTransaction();

            $reservasFinalizadas = [];

            // Finalizar todas las reservas activas en este espacio
            $reservasActivas = Reserva::where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->get();

            foreach ($reservasActivas as $reserva) {
                $reserva->estado = 'finalizada';
                $reserva->hora_salida = Carbon::now()->format('H:i:s');
                $reserva->observaciones = ($reserva->observaciones ? $reserva->observaciones . ' | ' : '') . 
                    'Liberada forzadamente por ' . $admin->name . ' (' . $admin->run . ') el ' . Carbon::now()->format('d/m/Y H:i:s');
                $reserva->save();
                $reservasFinalizadas[] = $reserva->id_reserva;
            }

            // Cambiar estado del espacio a Disponible
            $estadoAnterior = $espacio->estado ?? 'Ocupado';
            $espacio->estado = 'Disponible';
            $espacio->save();

            DB::commit();

            Log::info('Sala liberada forzadamente', [
                'id_espacio' => $idEspacio,
                'liberada_por' => $admin->run,
                'nombre_admin' => $admin->name,
                'reservas_finalizadas' => $reservasFinalizadas,
                'estado_anterior' => $estadoAnterior
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sala liberada forzadamente por ' . $admin->name,
                'espacio' => [
                    'id' => $idEspacio,
                    'nombre' => $espacio->nombre_espacio,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => 'Disponible'
                ],
                'reservas_finalizadas' => $reservasFinalizadas,
                'liberado_por' => [
                    'run' => $admin->run,
                    'nombre' => $admin->name
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al liberar sala forzadamente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al liberar la sala: ' . $e->getMessage()
            ], 500);
        }
    }
}

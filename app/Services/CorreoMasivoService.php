<?php

namespace App\Services;

use App\Models\TipoCorreoMasivo;
use App\Models\DestinatarioCorreo;
use App\Models\AsistenteAcademico;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para enviar correos masivos usando el sistema de administración
 *
 * Ejemplo de uso:
 *
 * $servicio = new CorreoMasivoService();
 * $servicio->enviarInformeClasesNoRealizadas($datosInforme);
 */
class CorreoMasivoService
{
    /**
     * Obtiene el ID del área académica si el usuario autenticado es asistente académico
     * 
     * @return string|null
     */
    private function obtenerAreaAcademicaAsistente(): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        $userEmail = auth()->user()->email;
        $asistente = AsistenteAcademico::where('email', $userEmail)->first();
        
        return $asistente ? $asistente->id_area_academica : null;
    }

    /**
     * Envía un correo masivo a los destinatarios habilitados para un tipo específico
     *
     * @param string $codigoTipoCorreo Código del tipo de correo
     * @param \Illuminate\Mail\Mailable $mailable Clase Mailable a enviar
     * @param array $destinatariosExcluidos IDs de destinatarios a excluir (opcional)
     * @return array Resultados del envío
     */
    public function enviarCorreoMasivo(
        string $codigoTipoCorreo,
        $mailable,
        array $destinatariosExcluidos = []
    ): array {
        // Obtener el tipo de correo
        $tipoCorreo = TipoCorreoMasivo::where('codigo', $codigoTipoCorreo)
            ->activos()
            ->first();

        if (!$tipoCorreo) {
            Log::error("Tipo de correo no encontrado: {$codigoTipoCorreo}");
            return [
                'success' => false,
                'message' => 'Tipo de correo no encontrado o inactivo',
                'enviados' => 0,
                'errores' => 0
            ];
        }

        // Obtener destinatarios habilitados
        $destinatarios = $tipoCorreo->destinatariosHabilitados()
            ->with('user')
            ->get()
            ->filter(function($destinatario) use ($destinatariosExcluidos) {
                // Filtrar destinatarios excluidos y sin email
                return !in_array($destinatario->id, $destinatariosExcluidos)
                    && $destinatario->user
                    && $destinatario->user->email;
            });

        if ($destinatarios->isEmpty()) {
            Log::warning("No hay destinatarios habilitados para: {$codigoTipoCorreo}");
            return [
                'success' => false,
                'message' => 'No hay destinatarios habilitados',
                'enviados' => 0,
                'errores' => 0
            ];
        }

        // Enviar correos
        $enviados = 0;
        $errores = 0;
        $erroresDetalle = [];

        foreach ($destinatarios as $destinatario) {
            try {
                Mail::to($destinatario->user->email)->send($mailable);
                $enviados++;

                Log::info("Correo enviado a: {$destinatario->user->email} ({$destinatario->user->name})");
            } catch (\Exception $e) {
                $errores++;
                $erroresDetalle[] = [
                    'destinatario' => $destinatario->user->name,
                    'email' => $destinatario->user->email,
                    'error' => $e->getMessage()
                ];

                Log::error("Error enviando correo a {$destinatario->user->email}: " . $e->getMessage());
            }
        }

        return [
            'success' => $enviados > 0,
            'message' => "Correos enviados: {$enviados}, Errores: {$errores}",
            'enviados' => $enviados,
            'errores' => $errores,
            'errores_detalle' => $erroresDetalle,
            'tipo_correo' => $tipoCorreo->nombre
        ];
    }

    /**
     * Envía informe semanal de clases no realizadas
     *
     * @param array $datos Datos para el informe
     * @return array
     */
    public function enviarInformeClasesNoRealizadas(array $datos): array
    {
        // Aquí deberías crear tu clase Mailable
        // $mailable = new \App\Mail\InformeClasesNoRealizadas($datos);

        // Por ahora, ejemplo de cómo se usaría:
        /*
        return $this->enviarCorreoMasivo(
            'informe_semanal_clases_no_realizadas',
            $mailable
        );
        */

        return [
            'success' => false,
            'message' => 'Implementar la clase Mailable correspondiente'
        ];
    }

    /**
     * Obtiene la lista de destinatarios para un tipo de correo
     *
     * @param string $codigoTipoCorreo
     * @return \Illuminate\Support\Collection
     */
    public function obtenerDestinatarios(string $codigoTipoCorreo)
    {
        $tipoCorreo = TipoCorreoMasivo::where('codigo', $codigoTipoCorreo)
            ->activos()
            ->first();

        if (!$tipoCorreo) {
            return collect([]);
        }

        return $tipoCorreo->destinatariosHabilitados()
            ->with('user')
            ->get()
            ->filter(function($destinatario) {
                return $destinatario->user && $destinatario->user->email;
            })
            ->map(function($destinatario) {
                return [
                    'id' => $destinatario->id,
                    'nombre' => $destinatario->user->name,
                    'email' => $destinatario->user->email,
                    'rol' => $destinatario->rol,
                    'cargo' => $destinatario->cargo,
                ];
            });
    }

    /**
     * Verifica si un usuario está habilitado para recibir un tipo de correo
     *
     * @param string $codigoTipoCorreo
     * @param int $userId RUN del usuario
     * @return bool
     */
    public function usuarioEstaHabilitado(string $codigoTipoCorreo, int $userId): bool
    {
        $tipoCorreo = TipoCorreoMasivo::where('codigo', $codigoTipoCorreo)
            ->activos()
            ->first();

        if (!$tipoCorreo) {
            return false;
        }

        $destinatario = DestinatarioCorreo::where('user_id', $userId)
            ->activos()
            ->first();

        if (!$destinatario) {
            return false;
        }

        // Verificar si está en la relación y habilitado
        return $tipoCorreo->destinatariosHabilitados()
            ->where('destinatario_correo_id', $destinatario->id)
            ->exists();
    }

    /**
     * Obtiene la configuración de un tipo de correo
     *
     * @param string $codigoTipoCorreo
     * @return array|null
     */
    public function obtenerConfiguracion(string $codigoTipoCorreo): ?array
    {
        $tipoCorreo = TipoCorreoMasivo::where('codigo', $codigoTipoCorreo)->first();

        return $tipoCorreo ? $tipoCorreo->configuracion : null;
    }
}

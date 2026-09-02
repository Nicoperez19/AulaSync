<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Reserva;
use App\Models\Sede;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComprobanteReservaService
{
    /**
     * Obtener los datos formateados de la reserva para el comprobante
     */
    public function obtenerDatosComprobante(Reserva $reserva): array
    {
        // Cargar relaciones si el modelo existe en BD
        try {
            if ($reserva->exists) {
                $reserva->loadMissing(['profesor', 'solicitante', 'espacio.piso.facultad.universidad', 'asignatura']);
            }
        } catch (\Throwable $e) {
            // Continuar con los datos disponibles si no se pueden cargar relaciones
        }

        $espacio = null;
        $profesor = null;
        $solicitante = null;
        $asignatura = null;

        try {
            $espacio = $reserva->relationLoaded('espacio') ? $reserva->espacio : ($reserva->exists ? $reserva->espacio : null);
        } catch (\Throwable $e) {}

        try {
            $profesor = $reserva->relationLoaded('profesor') ? $reserva->profesor : ($reserva->exists ? $reserva->profesor : null);
        } catch (\Throwable $e) {}

        try {
            $solicitante = $reserva->relationLoaded('solicitante') ? $reserva->solicitante : ($reserva->exists ? $reserva->solicitante : null);
        } catch (\Throwable $e) {}

        try {
            $asignatura = $reserva->relationLoaded('asignatura') ? $reserva->asignatura : ($reserva->exists ? $reserva->asignatura : null);
        } catch (\Throwable $e) {}

        // Nombre y RUN del responsable
        $nombreResponsable = 'Usuario';
        try {
            $nombreResponsable = $reserva->nombre_usuario ?: 'Usuario';
        } catch (\Throwable $e) {
            $nombreResponsable = $reserva->run_profesor ?: $reserva->run_solicitante ?: 'Usuario';
        }
        $runResponsable = $reserva->run_profesor ?: $reserva->run_solicitante ?: 'No especificado';
        $emailResponsable = $profesor->email ?? $solicitante->correo ?? $solicitante->email ?? 'No especificado';
        $telefonoResponsable = $profesor->telefono ?? $solicitante->telefono ?? 'No registrado';
        $tipoResponsable = $reserva->run_profesor ? 'Profesor / Académico' : 'Solicitante Externo';

        // Fechas y horas
        $fechaCarbon = $reserva->fecha_reserva instanceof Carbon
            ? $reserva->fecha_reserva
            : Carbon::parse($reserva->fecha_reserva);
        $fechaFormateada = $fechaCarbon->locale('es')->isoFormat('dddd D [de] MMMM, YYYY');
        $fechaCorta = $fechaCarbon->format('d/m/Y');

        $horaInicio = substr($reserva->hora, 0, 5);
        $horaFin = $reserva->hora_salida ? substr($reserva->hora_salida, 0, 5) : 'No definida';

        // Módulos
        $modulosTexto = 'No especificado';
        if ($reserva->modulo_inicio && $reserva->modulo_fin) {
            $modulosTexto = $reserva->modulo_inicio == $reserva->modulo_fin
                ? "Módulo {$reserva->modulo_inicio}"
                : "Módulos {$reserva->modulo_inicio} al {$reserva->modulo_fin}";
            if ($reserva->modulos) {
                $modulosTexto .= " ({$reserva->modulos} " . ($reserva->modulos == 1 ? 'módulo' : 'módulos') . ")";
            }
        } elseif ($reserva->modulos) {
            $modulosTexto = "{$reserva->modulos} " . ($reserva->modulos == 1 ? 'módulo' : 'módulos');
        }

        // Ubicación
        $piso = null;
        $facultad = null;
        $universidad = null;
        try {
            $piso = $espacio?->piso;
            $facultad = $piso?->facultad;
            $universidad = $facultad?->universidad;
        } catch (\Throwable $e) {}

        $nombreEspacio = $espacio ? ($espacio->nombre_espacio ?? $reserva->id_espacio) : $reserva->id_espacio;
        $tipoEspacio = $espacio->tipo_espacio ?? 'Espacio';
        $capacidad = $espacio->capacidad_maxima ?? 'N/D';
        $pisoTexto = $piso ? "Piso {$piso->numero_piso}" : 'No especificado';
        $facultadTexto = $facultad->nombre_facultad ?? 'Instituto Tecnológico';
        $institucionTexto = $universidad->nombre_universidad ?? (function_exists('tenant') && tenant() ? tenant()->nombre ?? 'Instituto Tecnológico' : 'Instituto Tecnológico');

        // Asignatura / Actividad
        $actividadTexto = null;
        if ($asignatura) {
            $actividadTexto = "{$asignatura->codigo_asignatura} - {$asignatura->nombre_asignatura}";
        } elseif ($reserva->nombre_actividad) {
            $actividadTexto = $reserva->nombre_actividad;
        } else {
            $actividadTexto = 'Uso general de espacio';
        }

        // Generar QR en Base64 con marca SIA
        $qrBase64 = $this->generarQrBase64($reserva);

        // Obtener Logo Institucional en Base64
        $logoBase64 = $this->obtenerLogoBase64();

        return [
            'reserva' => $reserva,
            'id_reserva' => $reserva->id_reserva,
            'fecha_emision' => now()->format('d/m/Y H:i:s'),
            'nombre_responsable' => $nombreResponsable,
            'run_responsable' => $runResponsable,
            'email_responsable' => $emailResponsable,
            'telefono_responsable' => $telefonoResponsable,
            'tipo_responsable' => $tipoResponsable,
            'id_espacio' => $reserva->id_espacio,
            'nombre_espacio' => $nombreEspacio,
            'tipo_espacio' => $tipoEspacio,
            'capacidad' => $capacidad,
            'piso' => $pisoTexto,
            'facultad' => $facultadTexto,
            'institucion' => $institucionTexto,
            'fecha' => $fechaFormateada,
            'fecha_corta' => $fechaCorta,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'modulos' => $modulosTexto,
            'actividad' => $actividadTexto,
            'descripcion_actividad' => $reserva->descripcion_actividad,
            'tipo_reserva' => ucfirst($reserva->tipo_reserva ?? 'Directa'),
            'estado' => ucfirst($reserva->estado ?? 'Activa'),
            'creado_por' => $reserva->creado_por ?? 'Sistema',
            'observaciones' => $reserva->observaciones,
            'qr_base64' => $qrBase64,
            'logo_base64' => $logoBase64,
        ];
    }

    /**
     * Obtener el logo institucional en formato Base64 para DomPDF
     */
    public function obtenerLogoBase64(): string
    {
        try {
            $logoFilePath = null;

            // 1. Verificar si hay un tenant/sede activo
            $tenant = class_exists(Tenant::class) ? Tenant::current() : null;
            $sedeActual = $tenant?->sede;
            $idSede = $sedeActual?->id_sede ?? 'TH';

            if ($sedeActual && $sedeActual->logo) {
                $path = 'sedes/logos/' . $sedeActual->logo;
                if (Storage::disk('public')->exists($path)) {
                    $logoFilePath = Storage::disk('public')->path($path);
                }
            }

            // 2. Verificar configuración institucional por sede
            if (!$logoFilePath && class_exists(Configuracion::class)) {
                try {
                    $configLogo = Configuracion::where('clave', "logo_institucional_{$idSede}")->first();
                    if ($configLogo && $configLogo->valor) {
                        $path = 'images/logo/' . $configLogo->valor;
                        if (Storage::disk('public')->exists($path)) {
                            $logoFilePath = Storage::disk('public')->path($path);
                        }
                    }
                } catch (\Throwable $e) {}
            }

            // 3. Buscar logos en storage/app/public/sedes/logos
            if (!$logoFilePath) {
                $storageLogos = glob(storage_path('app/public/sedes/logos/*.png'));
                if (!empty($storageLogos)) {
                    $logoFilePath = $storageLogos[0];
                }
            }

            // 4. Fallback a los logos en public/images/
            if (!$logoFilePath || !file_exists($logoFilePath)) {
                $candidatos = [
                    public_path('images/logo_instituto_tecnologico-01.png'),
                    public_path('images/logo_IT_talcahuano.png'),
                    public_path('images/logo_IT_talcahuano02.png'),
                    public_path('images/Logo-UCSC-Color-Horizontal.png'),
                    public_path('images/logo_correo.png'),
                ];

                foreach ($candidatos as $candidato) {
                    if (file_exists($candidato)) {
                        $logoFilePath = $candidato;
                        break;
                    }
                }
            }

            if ($logoFilePath && file_exists($logoFilePath)) {
                $fileContent = file_get_contents($logoFilePath);
                $ext = pathinfo($logoFilePath, PATHINFO_EXTENSION) ?: 'png';
                return 'data:image/' . ($ext === 'svg' ? 'svg+xml' : $ext) . ';base64,' . base64_encode($fileContent);
            }
        } catch (\Throwable $e) {
            Log::warning('⚠️ Error al obtener logo institucional para comprobante: ' . $e->getMessage());
        }

        return '';
    }

    /**
     * Generar código QR para el comprobante en formato data URI (base64)
     */
    public function generarQrBase64(Reserva $reserva): string
    {
        try {
            $payload = json_encode([
                'app' => 'SIA',
                'tipo' => 'comprobante_reserva',
                'id_reserva' => $reserva->id_reserva,
                'espacio' => $reserva->id_espacio,
                'fecha' => $reserva->fecha_reserva instanceof Carbon ? $reserva->fecha_reserva->format('Y-m-d') : (string) $reserva->fecha_reserva,
                'hora' => substr($reserva->hora, 0, 5),
                'run' => $reserva->run_profesor ?: $reserva->run_solicitante,
                'hash' => substr(hash('sha256', $reserva->id_reserva . config('app.key')), 0, 16),
            ]);

            $qrCode = new QrCode($payload);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            return 'data:image/png;base64,' . base64_encode($result->getString());
        } catch (\Throwable $e) {
            Log::warning('⚠️ Error al generar código QR para comprobante: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Generar la instancia DomPDF para la reserva
     */
    public function generarPdf(Reserva $reserva)
    {
        $data = $this->obtenerDatosComprobante($reserva);
        $pdf = Pdf::loadView('pdf.comprobante-reserva', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf;
    }

    /**
     * Descargar o previsualizar el PDF de comprobante
     */
    public function responderPdf(Reserva $reserva, bool $descargar = false): Response
    {
        $pdf = $this->generarPdf($reserva);
        $filename = 'comprobante-reserva-' . $reserva->id_reserva . '.pdf';

        if ($descargar) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}

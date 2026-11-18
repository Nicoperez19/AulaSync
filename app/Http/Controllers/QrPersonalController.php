<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class QrPersonalController extends Controller
{
    private const QR_PREFIX = 'qr_personal_aulasync';

    public function generar(Request $request, string $run)
    {
        $payload = $this->buildPayload($run);
        $png = $this->qrPng($payload);

        return response()->json([
            'success' => true,
            'run' => $run,
            'payload' => $payload,
            'png_base64' => base64_encode($png),
        ]);
    }

    public function preview(string $run)
    {
        $payload = $this->buildPayload($run);
        $png = $this->qrPng($payload);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="qr-personal-' . $run . '.png"',
        ]);
    }

    public function descargar(string $run)
    {
        $payload = $this->buildPayload($run);
        $png = $this->qrPng($payload);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-personal-' . $run . '.png"',
        ]);
    }

    public function anular(string $run)
    {
        // No se implementa revocación persistente por ahora.
        return response()->json([
            'success' => false,
            'message' => 'Revocación de QR personal no implementada.',
        ], 501);
    }

    public function verificar(string $run)
    {
        $payload = $this->buildPayload($run);

        return response()->json([
            'success' => true,
            'run' => $run,
            'payload' => $payload,
        ]);
    }

    private function buildPayload(string $run): string
    {
        $runLimpio = preg_replace('/[^0-9]/', '', $run) ?? '';
        $sig = $this->signatureForRun($runLimpio);

        // Formato simple: coincide con el "includes('qr_personal_aulasync')" del frontend.
        return self::QR_PREFIX . '?run=' . $runLimpio . '&sig=' . $sig;
    }

    private function qrPng(string $text): string
    {
        $qrCode = new QrCode($text);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getString();
    }

    private function signatureForRun(string $run): string
    {
        return hash_hmac('sha256', $run, (string) config('app.key'));
    }

    private function isValidSignature(string $run, string $sig): bool
    {
        $expected = $this->signatureForRun($run);
        return hash_equals($expected, $sig);
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function extractRunAndSig(string $qrData): array
    {
        // JSON: {"type":"qr_personal_aulasync","run":"...","sig":"..."}
        $decoded = json_decode($qrData, true);
        if (is_array($decoded)) {
            $run = $decoded['run'] ?? null;
            $sig = $decoded['sig'] ?? null;
            if (is_string($run) && is_string($sig)) {
                $runLimpio = preg_replace('/[^0-9]/', '', $run);
                return [$runLimpio ?: null, $sig];
            }
        }

        // Query-string style: qr_personal_aulasync?run=...&sig=...
        if (str_contains($qrData, '?')) {
            $query = explode('?', $qrData, 2)[1] ?? '';
            parse_str($query, $params);
            $run = isset($params['run']) ? (string) $params['run'] : null;
            $sig = isset($params['sig']) ? (string) $params['sig'] : null;
            if ($run && $sig) {
                $runLimpio = preg_replace('/[^0-9]/', '', $run);
                return [$runLimpio ?: null, $sig];
            }
        }

        // Fallback: extraer RUN (7-8 dígitos) + sig (64 hex)
        $run = null;
        if (preg_match('/RUN[^0-9]*(\d{7,8})/i', $qrData, $m)) {
            $run = $m[1];
        } elseif (preg_match('/(\d{7,8})/', $qrData, $m)) {
            $run = $m[1];
        }

        $sig = null;
        if (preg_match('/sig=([a-f0-9]{64})/i', $qrData, $m)) {
            $sig = $m[1];
        } elseif (preg_match('/([a-f0-9]{64})/i', $qrData, $m)) {
            $sig = $m[1];
        }

        return [$run, $sig];
    }

    private function findUserByRun(string $run): ?User
    {
        $runLimpio = preg_replace('/[^0-9]/', '', $run);
        return User::where('run', $run)
            ->orWhere('run', $runLimpio)
            ->first();
    }
}

<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Illuminate\Support\Facades\Storage;

class QRService
{
    /**
     * Genera un código QR para un usuario y lo guarda en el almacenamiento
     *
     * @param string $run El RUN del usuario
     * @return string La ruta del archivo QR generado
     */
    public function generateQRForUser($run)
    {
        try {
            // Verificar configuración de GD
            if (!extension_loaded('gd')) {
                throw new \Exception('La extensión GD no está habilitada en PHP');
            }

            // Verificar funciones de GD
            if (!function_exists('imagecreatetruecolor')) {
                throw new \Exception('La función imagecreatetruecolor no está disponible');
            }

            // Crear el directorio si no existe
            $directory = 'public/qr_usuarios';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Generar el nombre del archivo
            $fileName = "qr_{$run}.png";
            $filePath = "{$directory}/{$fileName}";

            // Crear el código QR
            $qrCode = QrCode::create($run)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
                ->setSize(300)
                ->setMargin(10)
                ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255));

            // Crear el writer
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Guardar el código QR
            Storage::put($filePath, $result->getString());

            return $filePath;
        } catch (\Exception $e) {
            \Log::error('Error al generar QR: ' . $e->getMessage(), [
                'run' => $run,
                'php_version' => PHP_VERSION,
                'gd_enabled' => extension_loaded('gd'),
                'gd_functions' => [
                    'imagecreatetruecolor' => function_exists('imagecreatetruecolor'),
                    'imagepng' => function_exists('imagepng'),
                    'imagecolorallocate' => function_exists('imagecolorallocate')
                ],
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ]);
            throw $e;
        }
    }
} 
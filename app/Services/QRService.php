<?php

namespace App\Services;

use App\Models\Space;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QRService
{
    public function generateSpaceQR(Space $space, int $size = 300): string
    {
        // Generamos una URL única para el espacio
        $url = route('spaces.show', ['space' => $space->id]);
        
        $qrCode = new QrCode($url);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Guardamos el código QR en el almacenamiento
        $path = 'qrcodes/qr_' . $space->id . '.png';
        Storage::put('public/' . $path, $result->getString());

        return $path;
    }

    public function generateQR(string $data, int $size = 300): string
    {
        $qrCode = new QrCode($data);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Guardamos el código QR en el almacenamiento
        $path = 'qrcodes/qr_' . $data . '.png';
        Storage::put('public/' . $path, $result->getString());

        return $path;
    }

    public function generateQRForEspacio(string $espacioId): string
    {
        // Generamos una URL única para el espacio
        $url = route('espacios.show', ['espacio' => $espacioId]);
        
        return $this->generateQR($espacioId);
    }
} 
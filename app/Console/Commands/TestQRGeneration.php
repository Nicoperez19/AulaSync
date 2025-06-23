<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QRService;

class TestQRGeneration extends Command
{
    protected $signature = 'test:qr {run}';
    protected $description = 'Prueba la generación de códigos QR';

    protected $qrService;

    public function __construct(QRService $qrService)
    {
        parent::__construct();
        $this->qrService = $qrService;
    }

    public function handle()
    {
        $run = $this->argument('run');
        
        try {
            $this->info("Generando código QR para el RUN: {$run}");
            $qrPath = $this->qrService->generateQRForUser($run);
            $this->info("Código QR generado exitosamente en: {$qrPath}");
            
            // Verificar que el archivo existe
            if (file_exists(storage_path('app/' . $qrPath))) {
                $this->info("El archivo QR existe en el sistema de archivos");
                $this->info("Tamaño del archivo: " . filesize(storage_path('app/' . $qrPath)) . " bytes");
            } else {
                $this->error("El archivo QR no existe en el sistema de archivos");
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error al generar el código QR: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 
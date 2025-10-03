<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClasesNoRealizadasReportService;
use App\Mail\ReporteSemanalClasesNoRealizadas;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnviarReporteSemanalClasesNoRealizadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reportes:clases-no-realizadas-semanal {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar reporte semanal de clases no realizadas por correo';

    protected $reportService;

    public function __construct(ClasesNoRealizadasReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generando reporte semanal de clases no realizadas...');

        try {
            // Determinar el período (semana pasada, de lunes a domingo)
            $fechaInicio = Carbon::now()->subWeek()->startOfWeek();
            $fechaFin = Carbon::now()->subWeek()->endOfWeek();

            $this->info("Período: {$fechaInicio->format('d/m/Y')} - {$fechaFin->format('d/m/Y')}");

            // Generar datos del reporte
            $datos = $this->reportService->generarReporteSemanal($fechaInicio, $fechaFin);

            // Generar PDF
            $pdf = $this->reportService->generarPDFSemanal($fechaInicio, $fechaFin);
            
            // Obtener destinatarios
            $destinatarios = $this->option('email') 
                ? explode(',', $this->option('email'))
                : explode(',', config('mail.report_recipients', 'admin@example.com'));

            // Enviar correo a cada destinatario
            foreach ($destinatarios as $email) {
                $email = trim($email);
                
                $this->info("Enviando reporte a: {$email}");
                
                Mail::to($email)->send(new ReporteSemanalClasesNoRealizadas($datos, $pdf));
            }

            $this->info('✓ Reporte semanal enviado exitosamente');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error al generar o enviar el reporte: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}

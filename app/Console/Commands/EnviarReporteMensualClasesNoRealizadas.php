<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClasesNoRealizadasReportService;
use App\Mail\ReporteMensualClasesNoRealizadas;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnviarReporteMensualClasesNoRealizadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reportes:clases-no-realizadas-mensual {--email=} {--mes=} {--anio=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar reporte mensual de clases no realizadas por correo';

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
        $this->info('Generando reporte mensual de clases no realizadas...');

        try {
            // Determinar el período (mes anterior por defecto)
            $mes = $this->option('mes') ?? Carbon::now()->subMonth()->month;
            $anio = $this->option('anio') ?? Carbon::now()->subMonth()->year;

            $fechaTexto = Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM YYYY');
            $this->info("Período: {$fechaTexto}");

            // Generar datos del reporte
            $datos = $this->reportService->generarReporteMensual($mes, $anio);

            // Generar PDF
            $pdf = $this->reportService->generarPDFMensual($mes, $anio);
            
            // Obtener destinatarios
            $destinatarios = $this->option('email') 
                ? explode(',', $this->option('email'))
                : explode(',', config('mail.report_recipients', 'admin@example.com'));

            // Enviar correo a cada destinatario
            foreach ($destinatarios as $email) {
                $email = trim($email);
                
                $this->info("Enviando reporte a: {$email}");
                
                Mail::to($email)->send(new ReporteMensualClasesNoRealizadas($datos, $pdf));
            }

            $this->info('✓ Reporte mensual enviado exitosamente');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error al generar o enviar el reporte: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}

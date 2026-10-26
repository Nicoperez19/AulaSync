<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\OcupacionSemanalService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerarSnapshotOcupacionSemanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocupacion:snapshot-semanal 
                            {--semana= : Fecha de la semana a procesar (YYYY-MM-DD), por defecto semana anterior} 
                            {--tipo= : Tipo específico (todos, laboratorios, salas_clases, salas_estudio)} 
                            {--forzar : Sobrescribir si ya existe un snapshot} 
                            {--tenant= : Dominio de un tenant específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcula y almacena el snapshot semanal de ocupación para histórico y exportación';

    protected OcupacionSemanalService $service;

    public function __construct(OcupacionSemanalService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $semanaOpt = $this->option('semana');
        $ahora = Carbon::now();
        $lunesDate = $semanaOpt
            ? Carbon::parse($semanaOpt)->startOfWeek()
            : ($ahora->dayOfWeekIso >= 6 ? $ahora->copy()->startOfWeek() : $ahora->copy()->subWeek()->startOfWeek());
        $tipoOpt = $this->option('tipo');
        $forzar = (bool) $this->option('forzar');
        $tenantDomain = $this->option('tenant');

        $this->info('Iniciando generación de snapshots de ocupación...');
        $this->info("Semana objetivo: {$lunesDate->format('d/m/Y')} al {$lunesDate->copy()->addDays(5)->format('d/m/Y')}");

        $query = Tenant::query();
        if ($tenantDomain) {
            $query->where('domain', $tenantDomain);
        }
        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants. Procesando en contexto actual...');
            $this->procesarSemana($lunesDate, $tipoOpt, $forzar);

            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->line("Procesando sede / tenant: {$tenant->name} ({$tenant->domain})...");
            try {
                $tenant->makeCurrent();
                $this->procesarSemana($lunesDate, $tipoOpt, $forzar);
            } catch (\Throwable $e) {
                $this->error("Error procesando tenant {$tenant->domain}: ".$e->getMessage());
            }
        }

        $this->info('Proceso de snapshots de ocupación finalizado con éxito.');

        return 0;
    }

    protected function procesarSemana(Carbon $lunesDate, ?string $tipoOpt, bool $forzar): void
    {
        $tipos = $tipoOpt ? [strtolower($tipoOpt)] : OcupacionSemanalService::TIPOS_FILTRO;

        foreach ($tipos as $tipo) {
            $snapshot = $this->service->guardarSnapshot($lunesDate, $tipo, $forzar, null, 'automatico');
            $prom = $snapshot->resumen['promedio_general'] ?? 0;
            $pico = $snapshot->resumen['pico_maximo'] ?? 0;
            $this->info("  [OK] Tipo '{$tipo}': Promedio {$prom}%, Pico {$pico}% (Total espacios: {$snapshot->total_espacios})");
        }
    }
}

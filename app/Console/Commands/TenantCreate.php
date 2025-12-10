<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Sede;

class TenantCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create 
                            {domain : The subdomain for the tenant}
                            {--name= : The name of the tenant}
                            {--sede= : The sede ID to associate with the tenant}
                            {--prefix= : The space prefix for the tenant}
                            {--database= : The database name (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        $name = $this->option('name') ?? ucfirst($domain);
        $sedeId = $this->option('sede');
        $prefix = $this->option('prefix');
        $database = $this->option('database');

        // Validar que el dominio no exista
        if (Tenant::where('domain', $domain)->exists()) {
            $this->error("Tenant with domain '{$domain}' already exists!");
            return 1;
        }

        // Validar sede si se proporciona
        if ($sedeId) {
            $sede = Sede::find($sedeId);
            if (!$sede) {
                $this->error("Sede with ID '{$sedeId}' not found!");
                return 1;
            }

            // Si no se proporciona nombre, usar el de la sede
            if (!$this->option('name')) {
                $name = $sede->nombre_sede;
            }

            // Si no se proporciona prefijo, usar el de la sede
            if (!$prefix) {
                $prefix = $sede->prefijo_sala;
            }
        }

        try {
            $tenant = Tenant::create([
                'name' => $name,
                'domain' => $domain,
                'database' => $database,
                'prefijo_espacios' => $prefix,
                'sede_id' => $sedeId,
                'is_active' => true,
            ]);

            $this->info("Tenant '{$name}' created successfully!");
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $tenant->id],
                    ['Name', $tenant->name],
                    ['Domain', $tenant->domain],
                    ['Prefix', $tenant->prefijo_espacios ?? 'N/A'],
                    ['Sede ID', $tenant->sede_id ?? 'N/A'],
                    ['Database', $tenant->database ?? 'Shared'],
                    ['Active', $tenant->is_active ? 'Yes' : 'No'],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to create tenant: {$e->getMessage()}");
            return 1;
        }
    }
}

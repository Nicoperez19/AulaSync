<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Sede;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {sede_id : The ID of the sede}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant for a sede with its own database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sedeId = $this->argument('sede_id');
        
        // Find the sede
        $sede = Sede::find($sedeId);
        
        if (!$sede) {
            $this->error("Sede with ID {$sedeId} not found!");
            return 1;
        }
        
        // Check if tenant already exists for this sede
        if (Tenant::where('sede_id', $sedeId)->exists()) {
            $this->error("A tenant already exists for this sede!");
            return 1;
        }
        
        // Ask for domain (subdomain)
        $domain = $this->ask('Enter the subdomain for this tenant (e.g., "maipu" for maipu.aulasync.com)');
        
        if (!$domain) {
            $this->error('Domain is required!');
            return 1;
        }
        
        // Create database name from sede ID (sanitize)
        $databaseName = 'tenant_' . Str::slug($sedeId, '_');
        
        $this->info("Creating tenant for sede: {$sede->nombre_sede}");
        $this->info("Subdomain: {$domain}");
        $this->info("Database: {$databaseName}");
        
        if (!$this->confirm('Do you want to continue?')) {
            $this->info('Tenant creation cancelled.');
            return 0;
        }
        
        try {
            // Create database name from sede ID (sanitize)
            $databaseName = 'tenant_' . Str::slug($sedeId, '_');
            
            // Validate database name to prevent SQL injection
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $databaseName)) {
                $this->error('Invalid database name format!');
                return 1;
            }
            
            // Create the database
            $escapedDbName = DB::connection()->getPdo()->quote($databaseName);
            DB::statement("CREATE DATABASE IF NOT EXISTS {$escapedDbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database {$databaseName} created successfully.");
            
            // Create the tenant record
            $tenant = Tenant::create([
                'name' => $sede->nombre_sede,
                'domain' => $domain,
                'database' => $databaseName,
                'sede_id' => $sedeId,
            ]);
            
            $this->info("Tenant created with ID: {$tenant->id}");
            
            // Run migrations for this tenant
            $this->info("Running migrations for tenant database...");
            $this->call('tenants:artisan', [
                'artisanCommand' => 'migrate --path=database/migrations/tenant',
                '--tenant' => $tenant->id
            ]);
            
            $this->info("Tenant created successfully!");
            
        } catch (\Exception $e) {
            $this->error("Error creating tenant: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateCentral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:central
                            {--fresh : Drop all tables and re-run all migrations}
                            {--seed : Seed the database after running migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecutar migraciones de la base de datos central (gestoraulasit)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏢 Migrando base de datos CENTRAL (gestoraulasit)...');
        $this->newLine();

        $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';

        $args = [
            '--database' => 'mysql',
            '--path' => 'database/migrations/central',
            '--force' => true,
        ];

        $exitCode = Artisan::call($command, $args);

        if ($exitCode === 0) {
            $this->info('✅ Migraciones CENTRAL completadas exitosamente');

            if ($this->option('seed')) {
                $this->info('Ejecutando seeders...');
                Artisan::call('db:seed', [
                    '--class' => 'CentralDatabaseSeeder',
                    '--force' => true,
                ]);
                $this->info('✅ Seeders completados');
            }
        } else {
            $this->error('❌ Error en migraciones CENTRAL');
            $this->line(Artisan::output());
            return 1;
        }

        return 0;
    }
}

<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenant = App\Models\Tenant::where('sede_id', 'CT')->first();
$tenant->makeCurrent();

Artisan::call('db:seed', [
    '--class' => 'TenantDatabaseSeeder',
    '--database' => 'tenant',
    '--force' => true
]);

echo Artisan::output();

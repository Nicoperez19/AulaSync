<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Asumimos tenant 5 por el log anterior
$tenant = App\Models\Tenant::find(5);
if ($tenant) {
    config(['database.connections.tenant.database' => $tenant->database]);
    \DB::purge('tenant');
    \DB::setDefaultConnection('tenant');
}

$reservas = App\Models\Reserva::on('tenant')->orderBy('created_at', 'desc')->take(3)->get();
foreach ($reservas as $r) {
    echo "ID: " . $r->id_reserva . "\n";
    echo "Estado: " . $r->estado . "\n";
    echo "Modulo: " . $r->modulo_inicio . "-" . $r->modulo_fin . "\n";
    echo "Fecha Reserva: " . $r->fecha_reserva . "\n";
    echo "Observaciones: " . $r->observaciones . "\n";
    echo "Created At: " . $r->created_at . "\n";
    echo "--------------------------\n";
}

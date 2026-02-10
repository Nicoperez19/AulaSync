<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Espacio;

// 1. List raw spaces from DB to see all of them regardless of tenant
echo "--- RAW DB SPACES (First 50) ---\n";
$spaces = DB::table('espacios')->select('id_espacio', 'nombre_espacio', 'tenant_id')->limit(50)->get();

foreach ($spaces as $space) {
    echo "ID: [{$space->id_espacio}] | Name: [{$space->nombre_espacio}] | Tenant: [{$space->tenant_id}]\n";
}

// 2. Check specific spaces mentioned in errors
$targets = ['TH-L06', '07-15'];
echo "\n--- TARGET CHECK ---\n";
foreach ($targets as $t) {
    $found = DB::table('espacios')->where('id_espacio', $t)->orWhere('nombre_espacio', $t)->first();
    if ($found) {
        echo "Found '$t': ID=[{$found->id_espacio}] Name=[{$found->nombre_espacio}] Tenant=[{$found->tenant_id}]\n";
    } else {
        echo "Not found in RAW DB: '$t'\n";
    }
}

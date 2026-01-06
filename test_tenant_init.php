<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Sede;
use App\Models\Tenant;

$sede = Sede::where('codigo', 'TH')->with('tenant')->first();

echo "Sede: " . $sede->nombre . PHP_EOL;
echo "Tenant ID: " . $sede->tenant->id . PHP_EOL;
echo "Tenant domain: " . $sede->tenant->domain . PHP_EOL;
echo "Is initialized: " . ($sede->tenant->is_initialized ? 'YES' : 'NO') . PHP_EOL;
echo "Needs initialization: " . ($sede->tenant->needsInitialization() ? 'YES' : 'NO') . PHP_EOL;

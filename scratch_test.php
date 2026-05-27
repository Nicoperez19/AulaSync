<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;

$request = Request::create('/api/verificar-estado-espacio-reserva', 'POST', [
    'run' => '',
    'id_espacio' => ''
]);

// Set host domain so the middleware resolves the tenant
$request->headers->set('HOST', 'th.localhost:8000');

$response = $kernel->handle($request);

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content: " . $response->getContent() . "\n";



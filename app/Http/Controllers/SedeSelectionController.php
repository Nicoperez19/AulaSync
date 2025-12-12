<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SedeSelectionController extends Controller
{
    /**
     * Mostrar la página de selección de sedes
     */
    public function index()
    {
        // Obtener todas las sedes con tenant activo
        $sedes = Sede::whereHas('tenant', function ($query) {
            $query->where('is_active', true);
        })
        ->with(['tenant', 'universidad', 'comuna'])
        ->get();

        return view('sedes.selection', compact('sedes'));
    }

    /**
     * Redirigir al subdominio de la sede seleccionada
     */
    public function redirect(Request $request, $sedeId)
    {
        $sede = Sede::with('tenant')->findOrFail($sedeId);
        
        if (!$sede->tenant || !$sede->tenant->is_active) {
            return back()->with('error', 'Esta sede no está disponible actualmente.');
        }

        $subdomain = $sede->tenant->domain;
        $appUrl = config('app.url');
        $parsedUrl = parse_url($appUrl);
        $host = $parsedUrl['host'] ?? 'localhost';
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

        // Construir la URL con el subdominio
        $redirectUrl = "{$scheme}://{$subdomain}.{$host}{$port}";

        return redirect()->away($redirectUrl);
    }
}

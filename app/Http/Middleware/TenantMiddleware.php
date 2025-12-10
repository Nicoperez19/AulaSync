<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Extraer el subdominio
        $subdomain = $this->getSubdomain($host);
        
        if ($subdomain && $subdomain !== 'www') {
            // Buscar el tenant por el subdominio
            $tenant = Tenant::where('domain', $subdomain)
                ->where('is_active', true)
                ->first();
            
            if ($tenant) {
                // Establecer el tenant actual
                $tenant->makeCurrent();
            } else {
                // Si no se encuentra el tenant, retornar error o redirigir
                abort(404, 'Tenant no encontrado');
            }
        } else {
            // Si no hay subdominio, usar el tenant por defecto o retornar error
            $defaultTenant = Tenant::where('is_active', true)
                ->orderBy('id')
                ->first();
            
            if ($defaultTenant) {
                $defaultTenant->makeCurrent();
            }
        }
        
        return $next($request);
    }

    /**
     * Extraer el subdominio del host
     */
    protected function getSubdomain(string $host): ?string
    {
        // Obtener el dominio base de la configuración
        $appUrl = config('app.url');
        $appDomain = parse_url($appUrl, PHP_URL_HOST);
        
        // Si estamos en localhost, buscar subdominios en el formato: subdomain.localhost
        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            $parts = explode('.', $host);
            if (count($parts) > 1) {
                return $parts[0];
            }
            return null;
        }
        
        // Para dominios reales
        if ($appDomain && str_contains($host, $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
            if ($subdomain !== $appDomain) {
                return $subdomain;
            }
        }
        
        return null;
    }
}

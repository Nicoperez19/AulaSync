<?php

namespace App\Multitenancy;

use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class DomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();
        
        // Extract subdomain from the host
        // Assuming format: subdomain.domain.tld
        $parts = explode('.', $host);
        
        // If we have at least 3 parts, the first one is the subdomain
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            
            // Find tenant by domain (subdomain)
            return Tenant::where('domain', $subdomain)->first();
        }
        
        // For local development, check if host matches tenant domain exactly
        return Tenant::where('domain', $host)->first();
    }
}

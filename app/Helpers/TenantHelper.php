<?php

if (!function_exists('tenant')) {
    /**
     * Get the current tenant instance
     *
     * @return \App\Models\Tenant|null
     */
    function tenant()
    {
        return app('tenant');
    }
}

if (!function_exists('tenant_id')) {
    /**
     * Get the current tenant ID
     *
     * @return int|null
     */
    function tenant_id()
    {
        $tenant = tenant();
        return $tenant ? $tenant->id : null;
    }
}

if (!function_exists('tenant_domain')) {
    /**
     * Get the current tenant domain
     *
     * @return string|null
     */
    function tenant_domain()
    {
        $tenant = tenant();
        return $tenant ? $tenant->domain : null;
    }
}

if (!function_exists('tenant_prefijo')) {
    /**
     * Get the current tenant space prefix
     *
     * @return string|null
     */
    function tenant_prefijo()
    {
        $tenant = tenant();
        return $tenant ? $tenant->prefijo_espacios : null;
    }
}

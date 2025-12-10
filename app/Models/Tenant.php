<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $fillable = [
        'name',
        'domain',
        'database',
        'sede_id',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id', 'id_sede');
    }
}

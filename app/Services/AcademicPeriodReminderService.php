<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\PeriodoAcademico;
use App\Models\Tenant;
use Carbon\Carbon;

class AcademicPeriodReminderService
{
    public function shouldNotify(): bool
    {
        $tenant = Tenant::current();

        if (!$tenant || !$tenant->sede_id || PeriodoAcademico::query()->exists()) {
            return false;
        }

        $lastNotification = Configuracion::get(
            "periodos_ultima_notificacion_{$tenant->sede_id}"
        );

        return !$lastNotification
            || Carbon::parse($lastNotification)->diffInDays(now()) >= 15;
    }
}

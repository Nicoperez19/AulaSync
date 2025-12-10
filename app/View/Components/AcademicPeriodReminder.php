<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Tenant;
use App\Models\Configuracion;
use Carbon\Carbon;

class AcademicPeriodReminder extends Component
{
    public bool $showReminder = false;
    public string $reminderMessage = '';

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->checkAcademicPeriodStatus();
    }

    /**
     * Check if academic periods need to be configured
     */
    protected function checkAcademicPeriodStatus(): void
    {
        $tenant = Tenant::current();
        
        if (!$tenant || !$tenant->sede_id) {
            return;
        }

        // Check if periods are defined
        $periodsDefinedConfig = Configuracion::where('clave', "periodos_academicos_definidos_{$tenant->sede_id}")->first();
        
        if (!$periodsDefinedConfig || $periodsDefinedConfig->valor === 'false') {
            // Check last notification date
            $lastNotificationConfig = Configuracion::where('clave', "periodos_ultima_notificacion_{$tenant->sede_id}")->first();
            
            $shouldShowReminder = true;
            
            if ($lastNotificationConfig && $lastNotificationConfig->valor) {
                $lastNotificationDate = Carbon::parse($lastNotificationConfig->valor);
                $daysSinceLastNotification = $lastNotificationDate->diffInDays(now());
                
                // Show reminder every 15 days
                $shouldShowReminder = $daysSinceLastNotification >= 15;
            }
            
            if ($shouldShowReminder) {
                $this->showReminder = true;
                $this->reminderMessage = 'Los períodos académicos no están configurados. Configúrelos para un mejor seguimiento de horarios.';
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.academic-period-reminder');
    }
}

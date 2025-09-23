<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearDashboardCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:clear-cache {--all : Clear all dashboard cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear dashboard cache to improve performance and fix timeout issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing dashboard cache...');

        if ($this->option('all')) {
            // Clear all cache
            Cache::flush();
            $this->info('All cache cleared successfully!');
        } else {
            // Clear only dashboard specific cache
            $patterns = [
                'dashboard_basicos_*',
                'dashboard_pesados_*',
                'tipos_espacio'
            ];

            foreach ($patterns as $pattern) {
                // Clear cache keys that match the pattern
                $keys = Cache::getMemcached() ? 
                    $this->getMemcachedKeys($pattern) : 
                    $this->getFileKeys($pattern);
                    
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
            
            $this->info('Dashboard cache cleared successfully!');
        }

        // Clear compiled views that might be cached
        $this->call('view:clear');
        
        // Clear config cache if exists
        $this->call('config:clear');

        $this->info('Dashboard optimization complete!');
        
        return 0;
    }

    private function getFileKeys($pattern)
    {
        // For file cache, we'll clear based on common keys
        $keys = [
            'dashboard_basicos_IT_TH_',
            'dashboard_basicos_IT_TH_1',
            'dashboard_basicos_IT_TH_2',
            'dashboard_basicos_IT_TH_3',
            'dashboard_pesados_IT_TH_',
            'dashboard_pesados_IT_TH_1', 
            'dashboard_pesados_IT_TH_2',
            'dashboard_pesados_IT_TH_3',
            'tipos_espacio'
        ];
        
        return $keys;
    }

    private function getMemcachedKeys($pattern)
    {
        // For memcached, return common keys
        return $this->getFileKeys($pattern);
    }
}

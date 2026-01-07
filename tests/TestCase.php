<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Run central migrations after refreshing the default database.
     */
    protected function afterRefreshingDatabase()
    {
        Artisan::call('migrate', [
            '--database' => config('database.default'),
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);
    }
}

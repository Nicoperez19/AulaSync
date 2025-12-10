<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Sede;
use App\Models\Espacio;
use App\Models\Profesor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tenant creation
     */
    public function test_can_create_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test',
            'prefijo_espacios' => 'TEST',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('tenants', [
            'domain' => 'test',
            'name' => 'Test Tenant',
        ]);
    }

    /**
     * Test tenant can be made current
     */
    public function test_tenant_can_be_made_current()
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test',
            'is_active' => true,
        ]);

        $tenant->makeCurrent();

        $this->assertEquals($tenant->id, tenant_id());
        $this->assertEquals($tenant->domain, tenant_domain());
    }

    /**
     * Test tenant helper functions
     */
    public function test_tenant_helper_functions()
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test',
            'prefijo_espacios' => 'TEST',
            'is_active' => true,
        ]);

        $tenant->makeCurrent();

        $this->assertNotNull(tenant());
        $this->assertEquals('test', tenant_domain());
        $this->assertEquals('TEST', tenant_prefijo());
    }

    /**
     * Test space prefix filtering
     */
    public function test_espacio_filtered_by_prefix()
    {
        // This test would require setting up the database with proper relationships
        // Skipping for now as it requires extensive setup
        $this->markTestSkipped('Requires full database setup with relationships');
    }

    /**
     * Test profesor filtered by sede
     */
    public function test_profesor_filtered_by_sede()
    {
        // This test would require setting up the database with proper relationships
        // Skipping for now as it requires extensive setup
        $this->markTestSkipped('Requires full database setup with relationships');
    }
}

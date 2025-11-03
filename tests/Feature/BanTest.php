<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Ban;
use App\Models\Solicitante;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a solicitante can be banned.
     */
    public function test_can_create_ban(): void
    {
        // Create a solicitante
        $solicitante = Solicitante::factory()->create([
            'run_solicitante' => '12345678-9',
            'nombre' => 'Test User',
            'activo' => true,
        ]);

        // Create a ban
        $ban = Ban::create([
            'run_solicitante' => $solicitante->run_solicitante,
            'razon' => 'Test reason for banning',
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => Carbon::now()->addDays(7),
            'activo' => true,
        ]);

        $this->assertDatabaseHas('bans', [
            'run_solicitante' => $solicitante->run_solicitante,
            'razon' => 'Test reason for banning',
        ]);

        $this->assertTrue($ban->estaVigente());
    }

    /**
     * Test that a banned solicitante is detected correctly.
     */
    public function test_banned_solicitante_is_detected(): void
    {
        // Create a solicitante
        $solicitante = Solicitante::factory()->create([
            'run_solicitante' => '12345678-9',
            'nombre' => 'Test User',
            'activo' => true,
        ]);

        // Create a ban
        Ban::create([
            'run_solicitante' => $solicitante->run_solicitante,
            'razon' => 'Test reason',
            'fecha_inicio' => Carbon::now()->subDays(1),
            'fecha_fin' => Carbon::now()->addDays(7),
            'activo' => true,
        ]);

        // Check if banned
        $this->assertTrue(Ban::estaBaneado($solicitante->run_solicitante));
    }

    /**
     * Test that an expired ban is not active.
     */
    public function test_expired_ban_is_not_active(): void
    {
        // Create a solicitante
        $solicitante = Solicitante::factory()->create([
            'run_solicitante' => '12345678-9',
            'nombre' => 'Test User',
            'activo' => true,
        ]);

        // Create an expired ban
        $ban = Ban::create([
            'run_solicitante' => $solicitante->run_solicitante,
            'razon' => 'Test reason',
            'fecha_inicio' => Carbon::now()->subDays(10),
            'fecha_fin' => Carbon::now()->subDays(3),
            'activo' => true,
        ]);

        // Check if ban is not vigente
        $this->assertFalse($ban->estaVigente());
        $this->assertFalse(Ban::estaBaneado($solicitante->run_solicitante));
    }

    /**
     * Test that getting remaining days works correctly.
     */
    public function test_dias_restantes_calculation(): void
    {
        // Create a solicitante
        $solicitante = Solicitante::factory()->create([
            'run_solicitante' => '12345678-9',
            'nombre' => 'Test User',
            'activo' => true,
        ]);

        // Create a ban that ends in 5 days
        $ban = Ban::create([
            'run_solicitante' => $solicitante->run_solicitante,
            'razon' => 'Test reason',
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => Carbon::now()->addDays(5),
            'activo' => true,
        ]);

        // Check days remaining (should be around 5, allowing for test execution time)
        $diasRestantes = $ban->diasRestantes();
        $this->assertTrue($diasRestantes >= 4 && $diasRestantes <= 5);
    }
}

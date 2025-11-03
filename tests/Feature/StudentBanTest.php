<?php

namespace Tests\Feature;

use App\Models\StudentBan;
use App\Models\User;
use App\Models\Profesor;
use App\Models\Solicitante;
use App\Models\Espacio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class StudentBanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a student ban can be created
     */
    public function test_student_ban_can_be_created(): void
    {
        $ban = StudentBan::create([
            'run' => '12345678-9',
            'reason' => 'Violación de normas de reserva',
            'banned_until' => now()->addDays(7),
        ]);

        $this->assertDatabaseHas('student_bans', [
            'run' => '12345678-9',
            'reason' => 'Violación de normas de reserva',
        ]);

        $this->assertTrue($ban->isActive());
    }

    /**
     * Test that an expired ban is not active
     */
    public function test_expired_ban_is_not_active(): void
    {
        $ban = StudentBan::create([
            'run' => '12345678-9',
            'reason' => 'Violación de normas de reserva',
            'banned_until' => now()->subDays(1), // Expired yesterday
        ]);

        $this->assertFalse($ban->isActive());
    }

    /**
     * Test that isUserBanned returns true for banned user
     */
    public function test_is_user_banned_returns_true_for_banned_user(): void
    {
        StudentBan::create([
            'run' => '12345678-9',
            'reason' => 'Test ban',
            'banned_until' => now()->addDays(7),
        ]);

        $this->assertTrue(StudentBan::isUserBanned('12345678-9'));
    }

    /**
     * Test that isUserBanned returns false for non-banned user
     */
    public function test_is_user_banned_returns_false_for_non_banned_user(): void
    {
        $this->assertFalse(StudentBan::isUserBanned('99999999-9'));
    }

    /**
     * Test that isUserBanned returns false for expired ban
     */
    public function test_is_user_banned_returns_false_for_expired_ban(): void
    {
        StudentBan::create([
            'run' => '12345678-9',
            'reason' => 'Test ban',
            'banned_until' => now()->subDays(1),
        ]);

        $this->assertFalse(StudentBan::isUserBanned('12345678-9'));
    }

    /**
     * Test that getActiveBan returns the active ban
     */
    public function test_get_active_ban_returns_active_ban(): void
    {
        $ban = StudentBan::create([
            'run' => '12345678-9',
            'reason' => 'Test ban',
            'banned_until' => now()->addDays(7),
        ]);

        $activeBan = StudentBan::getActiveBan('12345678-9');

        $this->assertNotNull($activeBan);
        $this->assertEquals('12345678-9', $activeBan->run);
        $this->assertEquals('Test ban', $activeBan->reason);
    }

    /**
     * Test that getActiveBan returns null for non-banned user
     */
    public function test_get_active_ban_returns_null_for_non_banned_user(): void
    {
        $activeBan = StudentBan::getActiveBan('99999999-9');

        $this->assertNull($activeBan);
    }

    /**
     * Test that cleanExpiredBans removes expired bans
     */
    public function test_clean_expired_bans_removes_expired_bans(): void
    {
        // Create an expired ban
        StudentBan::create([
            'run' => '12345678-9',
            'reason' => 'Expired ban',
            'banned_until' => now()->subDays(1),
        ]);

        // Create an active ban
        StudentBan::create([
            'run' => '98765432-1',
            'reason' => 'Active ban',
            'banned_until' => now()->addDays(7),
        ]);

        $this->assertEquals(2, StudentBan::count());

        StudentBan::cleanExpiredBans();

        $this->assertEquals(1, StudentBan::count());
        $this->assertDatabaseMissing('student_bans', [
            'run' => '12345678-9',
        ]);
        $this->assertDatabaseHas('student_bans', [
            'run' => '98765432-1',
        ]);
    }

    /**
     * Test remaining time attribute for active ban
     */
    public function test_remaining_time_attribute_for_active_ban(): void
    {
        $ban = StudentBan::create([
            'run' => '12345678-9',
            'reason' => 'Test ban',
            'banned_until' => now()->addDays(2),
        ]);

        $remainingTime = $ban->remaining_time;

        $this->assertStringContainsString('día', $remainingTime);
    }

    /**
     * Test remaining time attribute for expired ban
     */
    public function test_remaining_time_attribute_for_expired_ban(): void
    {
        $ban = StudentBan::create([
            'run' => '12345678-9',
            'reason' => 'Test ban',
            'banned_until' => now()->subDays(1),
        ]);

        $remainingTime = $ban->remaining_time;

        $this->assertEquals('Baneo expirado', $remainingTime);
    }
}

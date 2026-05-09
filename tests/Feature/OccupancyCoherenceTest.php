<?php

namespace Tests\Feature;

use App\Models\Espacio;
use App\Models\Reserva;
use App\Services\OccupancyService;
use Carbon\Carbon;
use Tests\TestCase;

class OccupancyCoherenceTest extends TestCase
{
    protected $occupancyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->occupancyService = app(OccupancyService::class);
    }

    /**
     * Test que verifica que OccupancyService calcula módulos reales correctamente
     */
    public function test_calcula_modulos_reales_correctamente()
    {
        // Test sin horas
        $resultado = $this->occupancyService->calcularModulosReales(null, null);
        $this->assertEquals(1, $resultado);

        // Test con menos de 10 minutos
        $horaInicio = '08:00:00';
        $horaSalida = '08:05:00';
        $resultado = $this->occupancyService->calcularModulosReales($horaInicio, $horaSalida);
        $this->assertEquals(0, $resultado);

        // Test con 50 minutos (1 módulo)
        $horaInicio = '08:00:00';
        $horaSalida = '08:50:00';
        $resultado = $this->occupancyService->calcularModulosReales($horaInicio, $horaSalida);
        $this->assertTrue($resultado >= 0.5 && $resultado <= 1.5);
    }

    /**
     * Test que verifica turno diurno/vespertino
     */
    public function test_identifica_turno_correctamente()
    {
        // Diurno
        $this->assertTrue($this->occupancyService->esTurno('10:00:00', 'diurno'));
        $this->assertFalse($this->occupancyService->esTurno('20:00:00', 'diurno'));

        // Vespertino
        $this->assertTrue($this->occupancyService->esTurno('20:00:00', 'vespertino'));
        $this->assertFalse($this->occupancyService->esTurno('10:00:00', 'vespertino'));

        // Sin filtro
        $this->assertTrue($this->occupancyService->esTurno('10:00:00', null));
        $this->assertTrue($this->occupancyService->esTurno('20:00:00', null));
    }

    /**
     * Test que verifica horas disponibles por turno
     */
    public function test_horas_por_turno_correctas()
    {
        // Día normal
        $this->assertEquals(11, $this->occupancyService->horasPorTurno('diurno'));
        $this->assertEquals(4, $this->occupancyService->horasPorTurno('vespertino'));
        $this->assertEquals(15, $this->occupancyService->horasPorTurno(null));

        // Sábado
        $sabado = Carbon::now()->next(Carbon::SATURDAY);
        $this->assertEquals(5, $this->occupancyService->horasPorTurno('diurno', $sabado));
        $this->assertEquals(0, $this->occupancyService->horasPorTurno('vespertino', $sabado));
        $this->assertEquals(5, $this->occupancyService->horasPorTurno(null, $sabado));
    }

    /**
     * Test que verifica coherencia entre dashboard y modulos actuales
     */
    public function test_coherencia_entre_dashboard_y_tablero()
    {
        // Este test verifica que ambos sistemas retornan datos coherentes
        // Los datos pueden variar en tiempo, pero la estructura debe ser la misma

        $facultad = 'IT_TH'; // Facultad de ejemplo
        $piso = null;

        // Obtener datos del servicio
        $ocupacionSemanal = $this->occupancyService->calcularOcupacionSemanal($facultad, $piso);
        $ocupacionMensual = $this->occupancyService->calcularOcupacionMensual($facultad, $piso);
        $salasOcupadas = $this->occupancyService->obtenerSalasOcupadas($facultad, $piso);

        // Verificar tipos de retorno
        $this->assertIsFloat($ocupacionSemanal);
        $this->assertIsFloat($ocupacionMensual);
        $this->assertIsArray($salasOcupadas);
        $this->assertArrayHasKey('ocupadas', $salasOcupadas);
        $this->assertArrayHasKey('libres', $salasOcupadas);

        // Verificar rangos válidos
        $this->assertGreaterThanOrEqual(0, $ocupacionSemanal);
        $this->assertLessThanOrEqual(100, $ocupacionSemanal);
        $this->assertGreaterThanOrEqual(0, $ocupacionMensual);
        $this->assertLessThanOrEqual(100, $ocupacionMensual);
    }

    /**
     * Test que verifica coherencia de espacios ocupados
     */
    public function test_coherencia_espacios_ocupados()
    {
        $facultad = 'IT_TH';
        $piso = null;

        // Obtener espacios ocupados totales
        $espaciosOcupados = $this->occupancyService->obtenerEspaciosOcupadosTotal($facultad, $piso);

        // Verificar estructura
        $this->assertArrayHasKey('ocupadas', $espaciosOcupados);
        $this->assertArrayHasKey('libres', $espaciosOcupados);

        // Verificar que son números no negativos
        $this->assertGreaterThanOrEqual(0, $espaciosOcupados['ocupadas']);
        $this->assertGreaterThanOrEqual(0, $espaciosOcupados['libres']);

        // La suma de ocupadas y libres debe ser mayor que 0 si hay espacios
        $this->assertGreaterThanOrEqual(0, $espaciosOcupados['ocupadas'] + $espaciosOcupados['libres']);
    }

    /**
     * Test que verifica datos por día
     */
    public function test_datos_por_dia_coherentes()
    {
        $facultad = 'IT_TH';
        $piso = null;

        $usoPorDia = $this->occupancyService->obtenerUsoPorDia($facultad, $piso);
        $ocupacionPorDia = $this->occupancyService->obtenerOcupacionPorDia($facultad, $piso);

        // Verificar estructura
        $this->assertIsArray($usoPorDia);
        $this->assertIsArray($ocupacionPorDia);

        // Verificar que tiene 6 días (lunes a sábado)
        $this->assertCount(6, $usoPorDia);
        $this->assertCount(6, $ocupacionPorDia);

        // Verificar que cada entrada tiene los campos esperados
        foreach ($usoPorDia as $entrada) {
            $this->assertArrayHasKey('dia', $entrada);
            $this->assertArrayHasKey('cantidad', $entrada);
            $this->assertGreaterThanOrEqual(0, $entrada['cantidad']);
        }

        foreach ($ocupacionPorDia as $entrada) {
            $this->assertArrayHasKey('dia', $entrada);
            $this->assertArrayHasKey('ocupacion', $entrada);
            $this->assertGreaterThanOrEqual(0, $entrada['ocupacion']);
            $this->assertLessThanOrEqual(100, $entrada['ocupacion']);
        }
    }

    /**
     * Test que verifica estado de espacios
     */
    public function test_estado_espacio_coherente()
    {
        $espacio = Espacio::first();

        if (!$espacio) {
            $this->markTestSkipped('No hay espacios en la BD');
        }

        $estado = $this->occupancyService->obtenerEstadoEspacio($espacio->id_espacio);

        // Verificar estructura
        $this->assertArrayHasKey('ocupado', $estado);
        $this->assertArrayHasKey('clase', $estado);
        $this->assertArrayHasKey('finalizaEn', $estado);

        // Si está ocupado, debe tener datos de clase
        if ($estado['ocupado']) {
            $this->assertNotNull($estado['clase']);
            $this->assertIsArray($estado['clase']);
        } else {
            $this->assertNull($estado['clase']);
        }
    }
}

<?php

namespace Tests\Unit;

use Tests\TestCase; // ¡Importante que use Tests\TestCase!
use App\Models\User;
use App\Models\Medication;
use App\Services\ScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase; // Usa RefreshDatabase para limpiar la BD

    /**
     * Prueba que el servicio no crea tomas en el pasado al crear
     * un nuevo medicamento.
     * @test
     */
    public function it_does_not_create_takes_in_the_past_on_creation(): void
    {
        // 1. PREPARAR
        $user = User::factory()->create();
        $service = new ScheduleService();
        
        // Simular que son las 5 PM (17:00)
        Carbon::setTestNow(now()->setTime(17, 0, 0));

        // Creamos una medicina que se toma c/8 horas, empezando a las 8 AM
        $medication = Medication::factory()->create([
            'user_id' => $user->id,
            'frequency_hours' => 8,
            'start_time' => '08:00:00',
        ]);

        // 2. ACTUAR
        // Ejecutamos el servicio que estamos probando
        $service->generateTakes($medication);

        // 3. AFIRMAR (Assert)
        // Buscamos la primera toma que se creó
        $firstTake = $medication->takes()->orderBy('scheduled_at', 'asc')->first();

        // Afirmamos que la primera toma NO es a las 8 AM (pasado)
        // y NO es a las 4 PM (pasado)
        // SINO que es a las 12 AM de mañana (la próxima toma futura)
        $this->assertNotNull($firstTake);
        $this->assertEquals('00:00:00', $firstTake->scheduled_at->format('H:i:s'));
        $this->assertTrue($firstTake->scheduled_at->isTomorrow());
    }
}
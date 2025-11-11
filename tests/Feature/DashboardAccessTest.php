<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    /** * Prueba que los invitados (no logueados) son redirigidos.
     * @test 
     */
    public function guests_are_redirected_from_dashboard_to_login(): void
    {
        $response = $this->get('/dashboard');
        
        $response->assertStatus(302); // 302 es Redirección
        $response->assertRedirect('/login');
    }

    /** * Prueba que los usuarios autenticados sí pueden ver el panel.
     * @test 
     */
    public function authenticated_users_can_see_the_dashboard(): void
    {
        // 1. PREPARAR: Creamos un usuario
        $user = User::factory()->create();

        // 2. ACTUAR: Actuamos "como" ese usuario y vamos al dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        
        // 3. AFIRMAR: Afirmamos que la página cargó (200 OK)
        // y que vemos el texto "Panel"
        $response->assertStatus(200);
        $response->assertSeeText('Panel');
    }
}
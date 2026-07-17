<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Pruebas de sistema — atributo: SEGURIDAD.
 *
 * Verifican controles de seguridad de la aplicación de extremo a extremo:
 * autenticación obligatoria en rutas protegidas, configuración segura de
 * sesión y almacenamiento seguro de credenciales.
 *
 * Se ejecutan dentro de la suite Integration del pipeline CI/CD.
 */
class SecurityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PS-SEC-001: Las rutas protegidas exigen autenticación.
     * Un visitante no autenticado debe ser redirigido (302) y
     * nunca recibir contenido de la aplicación (200).
     */
    public function test_rutas_protegidas_requieren_autenticacion()
    {
        $this->get('/people')->assertRedirect();
        $this->get('/journal')->assertRedirect();
        $this->get('/settings')->assertRedirect();
    }

    /**
     * PS-SEC-002: Un visitante no autenticado no puede crear datos.
     * El intento de POST es redirigido y no persiste ningún registro.
     */
    public function test_visitante_no_puede_crear_actividades()
    {
        $response = $this->post('/activities', ['summary' => 'intrusion-test']);

        $response->assertRedirect();
        $this->assertDatabaseMissing('activities', ['summary' => 'intrusion-test']);
    }

    /**
     * PS-SEC-003: Configuración segura de la sesión.
     * La cookie de sesión es HttpOnly (no accesible desde JavaScript,
     * mitiga XSS) y usa SameSite=lax (mitiga CSRF).
     */
    public function test_configuracion_segura_de_sesion()
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
    }

    /**
     * PS-SEC-004: Las contraseñas se almacenan con hash bcrypt,
     * nunca en texto plano.
     */
    public function test_passwords_almacenados_con_hash_bcrypt()
    {
        $user = factory(User::class)->create();

        $this->assertMatchesRegularExpression('/^\$2y\$/', $user->password);
        $this->assertNotEquals('secret', $user->password);
    }
}

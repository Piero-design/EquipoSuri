<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactIntegrationTest extends TestCase
{
    // Utilizar RefreshDatabase para reiniciar la BD MySQL real en cada prueba
    use RefreshDatabase;

    /**
     * Prueba 1: Crear contacto exitosamente.
     */
    public function test_user_can_create_a_contact_successfully(): void
    {
        // $this->withoutExceptionHandling();

        // Desactivamos específicamente la protección CSRF para esta prueba
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = factory(User::class)->create();

        $contactData = [
            'first_name' => 'Taylor',
            'last_name'  => 'Otwell',
        ];

        $response = $this->actingAs($user)->post('/people', $contactData);

        $response->assertStatus(302);

        $this->assertDatabaseHas('contacts', [
            'account_id' => $user->account_id,
            'first_name' => 'Taylor',
            'last_name'  => 'Otwell',
        ]);
    }
}
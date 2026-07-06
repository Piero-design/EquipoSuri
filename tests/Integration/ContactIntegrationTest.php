<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Instance\IdHasher;

class ContactIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Método auxiliar para encriptar los IDs de los contactos tal como lo hace la aplicación.
     */
    private function getHashId($id)
    {
        return app(IdHasher::class)->encodeId($id);
    }

    /**
     * Prueba 1: Crear contacto exitosamente.
     */
    public function test_user_can_create_a_contact_successfully(): void
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = factory(User::class)->create();

        $contactData = [
            'first_name'  => 'Taylor',
            'last_name'   => 'Otwell',
            'nickname'    => 'Tay',
        ];

        $response = $this->actingAs($user)->post('/people', $contactData);

        $response->assertStatus(302);

        $this->assertDatabaseHas('contacts', [
            'account_id'  => $user->account_id,
            'first_name'  => 'Taylor',
            'last_name'   => 'Otwell',
            'nickname'    => 'Tay',
            'description' => null,
            'is_starred'  => 0,
        ]);
    }

    /**
     * Prueba 2: Marcar contacto como favorito usando el endpoint específico.
     */
    public function test_user_can_mark_contact_as_favorite(): void
    {
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
            'is_starred' => false,
        ]);

        $this->actingAs($user);

        $idHasher = app(\App\Services\Instance\IdHasher::class);
        $hashId = $idHasher->encodeId($contact->id);

        // El controlador lee 'toggle', no 'is_starred'
        $payload = [
            'toggle' => 'true',
        ];

        $response = $this->post("/people/{$hashId}/favorite", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', [
            'id'         => $contact->id,
            'is_starred' => 1,
        ]);
    }
}

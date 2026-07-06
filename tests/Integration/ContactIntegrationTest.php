<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Instance\IdHasher;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\CheckCompliance;

class ContactIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Estos tests validan el módulo de Contactos, no el de Compliance
        // ni CSRF; ambos se excluyen para todas las pruebas de esta clase.
        $this->withoutMiddleware([
            VerifyCsrfToken::class,
            CheckCompliance::class,
        ]);
    }

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

        $user = factory(User::class)->create();

        $contactData = [
            'first_name' => 'Taylor',
            'last_name'  => 'Otwell',
            'nickname'   => 'Tay',
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
     * Prueba 2: Marcar contacto como favorito.
     */
    public function test_user_can_mark_contact_as_favorite(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
            'is_starred' => false,
        ]);

        $this->actingAs($user);

        $hashId = $this->getHashId($contact->id);

        $response = $this->post("/people/{$hashId}/favorite", [
            'toggle' => 'true',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', [
            'id'         => $contact->id,
            'is_starred' => 1,
        ]);
    }

    /**
     * Prueba 3: Eliminar contacto (soft delete).
     */
    public function test_user_can_delete_a_contact(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $hashId = $this->getHashId($contact->id);

        $response = $this->actingAs($user)->delete("/people/{$hashId}");

        $response->assertRedirect(route('people.index'));

        $this->assertSoftDeleted('contacts', [
            'id' => $contact->id,
        ]);
    }

    /**
     * Prueba 4: Validación de campos obligatorios al crear un contacto.
     */
    public function test_contact_creation_fails_without_first_name(): void
    {
        $user = factory(User::class)->create();

        $contactData = [
            'last_name' => 'Otwell',
            'nickname'  => 'Tay',
        ];

        $response = $this->actingAs($user)->post('/people', $contactData);

        $response->assertSessionHasErrors('first_name');

        $this->assertDatabaseMissing('contacts', [
            'last_name' => 'Otwell',
            'nickname'  => 'Tay',
        ]);
    }
}

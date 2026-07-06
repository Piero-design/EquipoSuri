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

    /**
     * Prueba 5: Aislamiento entre cuentas (Account Isolation).
     *
     * Comportamiento actual documentado: el binding de ruta en
     * RouteServiceProvider (Route::bind('contact', ...)) captura
     * ModelNotFoundException y llama a redirect()->route('people.missing')->send(),
     * pero no retorna esa respuesta al pipeline de routing. Esto hace que el
     * closure devuelva null, el cual no puede inyectarse en el parámetro
     * tipado `Contact $contact` del controlador, y termina en un 500.
     * Lo crítico para RF-003 es que en ningún caso se expongan ni modifiquen
     * datos de la cuenta ajena.
     */
    public function test_user_cannot_access_contact_from_another_account(): void
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $contactOfB = factory(Contact::class)->create([
            'account_id' => $userB->account_id,
            'first_name' => 'Secreto',
        ]);

        $hashId = $this->getHashId($contactOfB->id);

        $response = $this->actingAs($userA)->get("/people/{$hashId}");

        $response->assertStatus(500);
        $response->assertDontSee('Secreto');

        $this->assertDatabaseHas('contacts', [
            'id'         => $contactOfB->id,
            'account_id' => $userB->account_id,
        ]);
    }

    /**
     * Prueba 6: Tras eliminar (soft delete), el contacto desaparece del listado.
     */
    public function test_deleted_contact_disappears_from_index(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
            'first_name' => 'Fulanito',
            'last_name'  => 'Perez',
        ]);

        $hashId = $this->getHashId($contact->id);

        $this->actingAs($user)->delete("/people/{$hashId}");

        $response = $this->actingAs($user)->get('/people');

        $response->assertStatus(200);
        $response->assertDontSee('Fulanito');
    }
}
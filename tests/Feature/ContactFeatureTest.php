<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Contact;
use App\Models\Account\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User\User */
    public $user;

    /** @var \App\Models\Account\Account */
    public $account;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuramos la cuenta relacional y el usuario autenticado para la API
        $this->account = factory(Account::class)->create();
        $this->user = factory(User::class)->create([
            'account_id' => $this->account->id
        ]);
    }

    /**
     * 1. test_it_can_create_a_contact_in_the_database
     * Prueba la creación exitosa enviando los datos obligatorios.
     */
    public function test_it_can_create_a_contact_in_the_database()
    {
        // Agregamos los campos booleanos obligatorios que pide la API
        $attributes = [
            'first_name' => 'Sebastian',
            'last_name' => 'Diaz Ticona',
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/contacts', $attributes);

        // Ya podemos comentar el dump porque el error debería estar solucionado
        // $response->dump();

        $response->assertSuccessful();

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Sebastian',
            'last_name' => 'Diaz Ticona',
            'account_id' => $this->account->id,
        ]);
    }

    /**
     * 2. test_user_cannot_create_contact_without_first_name
     * Verifica que el sistema devuelva un error 422 si falta el nombre.
     */
    public function test_user_cannot_create_contact_without_first_name()
    {
        $attributes = factory(Contact::class)->raw([
            'account_id' => $this->account->id,
            'first_name' => '', // Forzamos el error de validación
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson('/api/contacts', $attributes);

        $response->assertStatus(422);
    }

    /**
     * 3. test_user_can_view_own_contact_via_api
     * Comprueba que un usuario puede consultar un contacto de su propia cuenta.
     */
    public function test_user_can_view_own_contact_via_api()
    {
        $contact = factory(Contact::class)->create([
            'account_id' => $this->account->id
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson("/api/contacts/{$contact->id}");

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'id' => $contact->id,
            'first_name' => $contact->first_name,
        ]);
    }

    /**
     * 4. test_user_cannot_view_other_account_contact
     * Asegura el aislamiento de datos entre cuentas ajenas (espera un 404).
     */
    public function test_user_cannot_view_other_account_contact()
    {
        $otherAccount = factory(Account::class)->create();
        $otherContact = factory(Contact::class)->create([
            'account_id' => $otherAccount->id
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson("/api/contacts/{$otherContact->id}");

        $response->assertStatus(404);
    }

    /**
     * 5. test_user_can_delete_own_contact
     * Prueba la eliminación a través de la API y verifica que no esté activo en BD.
     */
    public function test_user_can_delete_own_contact()
    {
        $contact = factory(Contact::class)->create([
            'account_id' => $this->account->id
        ]);

        $response = $this->actingAs($this->user, 'api')->deleteJson("/api/contacts/{$contact->id}");

        $response->assertSuccessful();

        // Monica CRM utiliza SoftDeletes, aseguramos que el registro ya no esté activo (deleted_at no es null)
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
            'deleted_at' => null
        ]);
    }

    /**
     * 6. test_user_can_update_a_contact
     * Verifica la actualización de los datos básicos del contacto.
     */
    public function test_user_can_update_a_contact()
    {
        $contact = factory(Contact::class)->create([
            'account_id' => $this->account->id,
            'first_name' => 'Original',
        ]);

        // Al usar el método PUT, la API suele exigir el objeto completo,
        // así que incluimos también los campos obligatorios aquí.
        $payload = [
            'first_name' => 'NombreCambiado',
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        $response = $this->actingAs($this->user, 'api')->putJson("/api/contacts/{$contact->id}", $payload);

        // $response->dump();

        $response->assertSuccessful();

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'NombreCambiado',
            'account_id' => $this->account->id,
        ]);
    }
}

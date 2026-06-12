<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Contact;
use App\Models\Account\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactAdvancedFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User\User */
    public $user;

    /** @var \App\Models\Account\Account */
    public $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();
        $this->user = factory(User::class)->create([
            'account_id' => $this->account->id
        ]);
    }

    /**
     * 1. test_user_cannot_update_other_account_contact
     * Valida que un usuario no pueda modificar la información de un contacto de otra cuenta.
     */
    public function test_user_cannot_update_other_account_contact()
    {
        $otherAccount = factory(Account::class)->create();
        $otherContact = factory(Contact::class)->create([
            'account_id' => $otherAccount->id,
            'first_name' => 'Contacto Protegido'
        ]);

        $payload = [
            'first_name' => 'AtaqueMalicioso',
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/contacts/{$otherContact->id}", $payload);

        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));

        $this->assertDatabaseHas('contacts', [
            'id' => $otherContact->id,
            'first_name' => 'Contacto Protegido'
        ]);
    }

    /**
     * 2. test_user_cannot_delete_other_account_contact
     * Asegura que las eliminaciones accidentales o maliciosas entre cuentas estén bloqueadas.
     */
    public function test_user_cannot_delete_other_account_contact()
    {
        $otherAccount = factory(Account::class)->create();
        $otherContact = factory(Contact::class)->create([
            'account_id' => $otherAccount->id
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/contacts/{$otherContact->id}");

        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));

        $this->assertDatabaseHas('contacts', [
            'id' => $otherContact->id,
            'deleted_at' => null
        ]);
    }

    /**
     * 3. test_authenticated_user_can_retrieve_paginated_contacts_list
     * Valida que la respuesta del índice sea limpia y estructurada.
     */
    public function test_authenticated_user_can_retrieve_paginated_contacts_list()
    {
        // Creamos un contacto propio para garantizar que haya elementos en la lista
        factory(Contact::class)->create([
            'account_id' => $this->account->id
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/contacts');

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'first_name', 'last_name']
            ]
        ]);
    }

    /**
     * 4. test_contact_creation_validates_required_boolean_payloads
     * Valida que la API proteja el ciclo de vida exigiendo los tres campos que descubrimos previamente.
     */
    public function test_contact_creation_validates_required_boolean_payloads()
    {
        // Enviamos datos sin los parámetros booleanos obligatorios descubiertos con dump()
        $invalidAttributes = [
            'first_name' => 'Error',
            'last_name' => 'Validacion'
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/contacts', $invalidAttributes);

        $response->assertStatus(422);
    }

    /**
     * 5. test_contact_not_found_returns_404
     * Verifica que buscar un ID de contacto que no existe devuelva el error HTTP correcto de forma controlada.
     */
    public function test_contact_not_found_returns_404()
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/contacts/99999999');

        $response->assertStatus(404);
    }

    /**
     * 6. test_deleted_contact_does_not_appear_in_list
     * Verifica que los contactos borrados lógicamente (soft deletes) sean excluidos del índice principal.
     */
    public function test_deleted_contact_does_not_appear_in_list()
    {
        $deletedContact = factory(Contact::class)->create([
            'account_id' => $this->account->id,
            'first_name' => 'Contacto Fantasma',
            'deleted_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/contacts');

        $response->assertSuccessful();

        $response->assertJsonMissing([
            'id' => $deletedContact->id,
            'first_name' => 'Contacto Fantasma'
        ]);
    }
}

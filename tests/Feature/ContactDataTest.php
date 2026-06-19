<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact\Contact;
use App\Models\Account\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_contact_without_first_name_if_allowed()
    {
        $account = factory(Account::class)->create();

        // Creamos el contacto sin nombre (ponemos first_name como null o vacío)
        $contact = factory(Contact::class)->create([
            'first_name' => null,
            'account_id' => $account->id
        ]);

        // Verificamos que el sistema sí lo permitió
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => null
        ]);
    }

    /** @test */
    public function it_requires_a_first_name_to_be_valid()
    {
        $account = factory(Account::class)->create();

        // Intentamos crear un contacto sin nombre
        $this->expectException(\Illuminate\Database\QueryException::class);

        factory(Contact::class)->create([
            'first_name' => null,
            'account_id' => $account->id
        ]);
    }
}
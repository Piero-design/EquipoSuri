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

        $this->account = factory(Account::class)->create();

        $this->user = factory(User::class)->create([
            'account_id' => $this->account->id
        ]);
    }

    public function test_user_cannot_create_contact_without_first_name()
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/api/contacts', [
            'last_name' => 'Pérez',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_view_own_contact()
    {
        $contact = factory(Contact::class)->create([
            'account_id' => $this->account->id
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson("/api/contacts/{$contact->id}");

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'first_name' => $contact->first_name,
        ]);
    }

    public function test_user_cannot_view_other_account_contact()
    {
        $otherAccount = factory(Account::class)->create();
        $otherContact = factory(Contact::class)->create([
            'account_id' => $otherAccount->id
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson("/api/contacts/{$otherContact->id}");

        $response->assertStatus(404);
    }
}

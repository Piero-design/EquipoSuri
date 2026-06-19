<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactFieldType;
use App\Models\Contact\Conversation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConversationsControllerExtraTest extends FeatureTestCase
{
    use DatabaseTransactions;

    protected function fetchUserAndContact()
    {
        $user = $this->signIn();

        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $contactFieldType = factory(ContactFieldType::class)->create([
            'account_id' => $user->account_id,
        ]);

        return [$user, $contact, $contactFieldType];
    }

    public function test_index_conversations()
    {
        [$user, $contact, $fieldType] = $this->fetchUserAndContact();

        $conversation = factory(Conversation::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'contact_field_type_id' => $fieldType->id,
        ]);

        $response = $this->get('/people/' . $contact->hashID() . '/conversations');
        $response->assertStatus(200);
    }

    public function test_create_conversation()
    {
        [$user, $contact, $fieldType] = $this->fetchUserAndContact();

        $this->withoutExceptionHandling();
        $response = $this->get('/people/' . $contact->hashID() . '/conversations/create');
        $response->assertStatus(200);
    }

    public function test_store_conversation()
    {
        [$user, $contact, $fieldType] = $this->fetchUserAndContact();

        $data = [
            'conversationDateRadio' => 'today',
            'messages' => '1',
            'contactFieldTypeId' => $fieldType->id,
            'who_wrote_1' => 'me',
            'content_1' => 'Hello there',
        ];

        $response = $this->post('/people/' . $contact->hashID() . '/conversations', $data);
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('conversations', [
            'contact_id' => $contact->id,
            'contact_field_type_id' => $fieldType->id,
        ]);
        
        $this->assertDatabaseHas('messages', [
            'content' => 'Hello there',
            'written_by_me' => 1,
        ]);
    }

    public function test_edit_conversation()
    {
        [$user, $contact, $fieldType] = $this->fetchUserAndContact();

        $conversation = factory(Conversation::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'contact_field_type_id' => $fieldType->id,
        ]);

        $this->withoutExceptionHandling();
        $response = $this->get('/people/' . $contact->hashID() . '/conversations/' . $conversation->hashID() . '/edit');
        $response->assertStatus(200);
    }

    public function test_update_conversation()
    {
        [$user, $contact, $fieldType] = $this->fetchUserAndContact();

        $conversation = factory(Conversation::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'contact_field_type_id' => $fieldType->id,
        ]);

        $data = [
            'conversationDateRadio' => 'yesterday',
            'messages' => '2',
            'contactFieldTypeId' => $fieldType->id,
            'who_wrote_2' => 'other',
            'content_2' => 'Updated message',
        ];

        $this->withoutExceptionHandling();
        $response = $this->put('/people/' . $contact->hashID() . '/conversations/' . $conversation->hashID(), $data);
        $response->assertStatus(302);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Updated message',
            'written_by_me' => 0,
        ]);
    }

    public function test_destroy_conversation()
    {
        [$user, $contact, $fieldType] = $this->fetchUserAndContact();

        $conversation = factory(Conversation::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'contact_field_type_id' => $fieldType->id,
        ]);

        $this->withoutExceptionHandling();
        $response = $this->delete('/people/' . $contact->hashID() . '/conversations/' . $conversation->hashID());
        $response->assertStatus(302);

        $this->assertDatabaseMissing('conversations', [
            'id' => $conversation->id,
        ]);
    }
}

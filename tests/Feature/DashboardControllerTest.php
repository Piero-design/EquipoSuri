<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\Contact\Call;
use App\Models\Contact\Debt;
use App\Models\Contact\Note;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DashboardControllerTest extends FeatureTestCase
{
    use DatabaseTransactions;

    public function test_it_displays_the_dashboard_blank_state()
    {
        $user = $this->signIn();

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.blank');
    }

    public function test_it_displays_the_dashboard_with_contacts()
    {
        $user = $this->signIn();
        
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
        $response->assertViewHas('number_of_contacts', 1);
    }

    public function test_it_gets_calls_for_dashboard()
    {
        $user = $this->signIn();
        
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $call = factory(Call::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
        ]);

        $response = $this->get('/dashboard/calls');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $call->id,
        ]);
    }

    public function test_it_gets_notes_for_dashboard()
    {
        $user = $this->signIn();
        
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $note = factory(Note::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'is_favorited' => true,
        ]);

        $response = $this->get('/dashboard/notes');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $note->id,
        ]);
    }

    public function test_it_gets_debts_for_dashboard()
    {
        $user = $this->signIn();
        
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $debt = factory(Debt::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
        ]);

        $response = $this->get('/dashboard/debts');

        $response->assertStatus(200);
    }

    public function test_it_sets_the_dashboard_tab()
    {
        $user = $this->signIn();

        $response = $this->post('/dashboard/setTab', [
            'tab' => 'recent_activity',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'dashboard_active_tab' => 'recent_activity',
        ]);
    }
}

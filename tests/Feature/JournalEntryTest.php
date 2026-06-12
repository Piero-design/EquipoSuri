<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\Journal\Day;
use App\Models\Journal\Entry;
use App\Models\Journal\JournalEntry;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class JournalEntryTest extends FeatureTestCase
{
    use DatabaseTransactions;

    public function test_user_can_view_journal_index()
    {
        $user = $this->signIn();
        $response = $this->get('/journal');
        $response->assertStatus(200);
        $response->assertViewIs('journal.index');
    }

    public function test_user_can_list_journal_entries()
    {
        $user = $this->signIn();

        $entry = factory(Entry::class)->create([
            'account_id' => $user->account_id,
            'title' => 'This is the title',
            'post' => 'this is a post',
        ]);
        $entry->date = '2017-01-01';
        $journalEntry = JournalEntry::add($entry);

        $response = $this->get('/journal/entries');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'total' => 1,
            'id' => $journalEntry->id,
            'journalable_id' => $entry->id,
        ]);
    }

    public function test_user_can_get_a_journal_entry()
    {
        $user = $this->signIn();

        $entry = factory(Entry::class)->create([
            'account_id' => $user->account_id,
            'title' => 'This is the title',
            'post' => 'this is a post',
        ]);
        $entry->date = '2017-01-01';
        $journalEntry = JournalEntry::add($entry);

        $response = $this->get('/journal/entries/'.$journalEntry->id);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $entry->id,
            'type' => 'entry',
        ]);
    }

    public function test_user_can_store_a_day_entry()
    {
        $user = $this->signIn();

        $params = [
            'rate' => 3,
            'comment' => 'Today was okay',
        ];

        $response = $this->post('/journal/day', $params);
        $response->assertStatus(200);

        $this->assertDatabaseHas('days', [
            'account_id' => $user->account_id,
            'rate' => 3,
            'comment' => 'Today was okay',
        ]);

        $day = Day::where('account_id', $user->account_id)->first();
        $this->assertDatabaseHas('journal_entries', [
            'account_id' => $user->account_id,
            'journalable_type' => Day::class,
            'journalable_id' => $day->id,
        ]);
    }

    public function test_user_can_delete_a_day_entry()
    {
        $user = $this->signIn();

        $day = factory(Day::class)->create([
            'account_id' => $user->account_id,
            'date' => now(),
            'rate' => 5,
            'comment' => 'Great day',
        ]);
        $journalEntry = JournalEntry::add($day);

        $response = $this->delete('/journal/day/'.$day->id);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('days', [
            'id' => $day->id,
        ]);
        $this->assertDatabaseMissing('journal_entries', [
            'id' => $journalEntry->id,
        ]);
    }

    public function test_user_can_check_if_has_rated_today()
    {
        $user = $this->signIn();

        $response = $this->get('/journal/hasRated');
        $response->assertStatus(200);
        $this->assertEquals('notYet', $response->getContent());

        $day = factory(Day::class)->create([
            'account_id' => $user->account_id,
            'date' => now(),
            'rate' => 4,
            'comment' => 'Good day',
        ]);
        JournalEntry::add($day);

        $response2 = $this->get('/journal/hasRated');
        $response2->assertStatus(200);
        $this->assertEquals('true', $response2->getContent());
    }

    public function test_user_can_view_create_journal_entry_screen()
    {
        $user = $this->signIn();
        $response = $this->get('/journal/add');
        $response->assertStatus(200);
        $response->assertViewIs('journal.add');
    }

    public function test_user_can_view_edit_journal_entry_screen()
    {
        $user = $this->signIn();

        $entry = factory(Entry::class)->create([
            'account_id' => $user->account_id,
            'title' => 'Title',
            'post' => 'Post',
        ]);
        $entry->date = '2017-01-01';
        JournalEntry::add($entry);

        $response = $this->get('/journal/entries/'.$entry->id.'/edit');
        $response->assertStatus(200);
        $response->assertViewIs('journal.edit');
    }

    public function test_user_can_add_a_journal_entry()
    {
        $user = $this->signIn();

        $params = [
            'entry' => 'Good day',
            'date' => '2018-01-01',
        ];

        $response = $this->post('/journal/create', $params);

        $response->assertStatus(302);

        $this->assertDatabaseHas('journal_entries', [
            'account_id' => $user->account_id,
            'date' => '2018-01-01 00:00:00',
            'journalable_type' => 'App\Models\Journal\Entry',
        ]);
        $this->assertDatabaseHas('entries', [
            'account_id' => $user->account_id,
            'post' => 'Good day',
        ]);
    }

    public function test_user_can_edit_a_journal_entry()
    {
        $user = $this->signIn();

        $entry = factory(Entry::class)->create([
            'account_id' => $user->account_id,
            'title' => 'This is the title',
            'post' => 'this is a post',
        ]);
        $entry->date = '2017-01-01';
        $journalEntry = JournalEntry::add($entry);

        $params = [
            'entry' => 'Good day',
            'date' => '2018-01-01',
        ];

        $response = $this->put('/journal/entries/'.$entry->id, $params);

        $response->assertStatus(302);

        $this->assertDatabaseHas('journal_entries', [
            'account_id' => $user->account_id,
            'date' => '2018-01-01 00:00:00',
            'journalable_id' => $entry->id,
            'journalable_type' => 'App\Models\Journal\Entry',
        ]);
        $this->assertDatabaseHas('entries', [
            'account_id' => $user->account_id,
            'post' => 'Good day',
        ]);
    }

    public function test_user_can_delete_a_journal_entry()
    {
        $user = $this->signIn();

        $entry = factory(Entry::class)->create([
            'account_id' => $user->account_id,
            'title' => 'This is the title',
            'post' => 'this is a post',
        ]);
        $entry->date = '2017-01-01';
        $journalEntry = JournalEntry::add($entry);

        $response = $this->delete('/journal/'.$entry->id);
        $response->assertSuccessful();

        $this->assertDatabaseMissing('entries', [
            'id' => $entry->id,
        ]);
        $this->assertDatabaseMissing('journal_entries', [
            'id' => $journalEntry->id,
        ]);
    }
}

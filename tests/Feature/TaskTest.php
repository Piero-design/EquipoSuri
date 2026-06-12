<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TaskTest extends FeatureTestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     * Returns an array containing a user object along with
     * a contact for that user.
     *
     * @return array
     */
    private function fetchUser()
    {
        $user = $this->signIn();

        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        return [$user, $contact];
    }

    public function test_user_can_add_a_task()
    {
        [$user, $contact] = $this->fetchUser();

        $taskTitle = $this->faker->realText();
        $taskDescription = $this->faker->realText();

        $params = [
            'title' => $taskTitle,
            'description' => $taskDescription,
            'completed' => 0,
            'contact_id' => $contact->id,
        ];

        $response = $this->post('/tasks', $params);

        // Assert the note has been added for the correct user.
        $params['account_id'] = $user->account_id;
        $params['contact_id'] = $contact->id;
        $params['title'] = $taskTitle;
        $params['description'] = $taskDescription;

        $this->assertDatabaseHas('tasks', $params);
    }

    public function test_user_can_get_tasks()
    {
        [$user, $contact] = $this->fetchUser();

        $task = factory(\App\Models\Contact\Task::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
        ]);

        $response = $this->get('/tasks');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $task->id,
        ]);
    }

    public function test_user_can_update_a_task()
    {
        [$user, $contact] = $this->fetchUser();

        $task = factory(\App\Models\Contact\Task::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
        ]);

        $params = [
            'title' => 'updated title',
            'description' => 'updated desc',
            'completed' => 1,
            'contact_id' => $contact->id,
        ];

        $response = $this->put('/tasks/' . $task->id, $params);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'updated title',
            'completed' => 1,
        ]);
    }

    public function test_user_can_destroy_a_task()
    {
        [$user, $contact] = $this->fetchUser();

        $task = factory(\App\Models\Contact\Task::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
        ]);

        $response = $this->delete('/tasks/' . $task->id);

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}

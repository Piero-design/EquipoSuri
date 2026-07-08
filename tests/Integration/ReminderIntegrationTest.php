<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Contact;
use App\Models\Contact\Reminder;
use App\Services\Instance\IdHasher;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\CheckCompliance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ReminderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
     * PI-REM-001: Crear recordatorio con frecuencia mensual
     */
    public function test_crear_recordatorio_frecuencia_mensual()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $hashId = $this->getHashId($contact->id);

        $payload = [
            'title' => 'Llamada mensual de seguimiento',
            'description' => 'Llamar para saber cómo le va',
            'frequency_type' => 'month',
            'frequency_number' => 1,
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
             ->post("/people/{$hashId}/reminders", $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('reminders', [
            'contact_id' => $contact->id,
            'title' => 'Llamada mensual de seguimiento',
            'frequency_type' => 'month',
            'frequency_number' => 1,
        ]);
    }

    /**
     * PI-REM-002: Crear recordatorio con frecuencia anual
     */
    public function test_crear_recordatorio_frecuencia_anual()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $hashId = $this->getHashId($contact->id);

        $payload = [
            'title' => 'Cumpleaños',
            'frequency_type' => 'year',
            'frequency_number' => 1,
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
             ->post("/people/{$hashId}/reminders", $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('reminders', [
            'contact_id' => $contact->id,
            'title' => 'Cumpleaños',
            'frequency_type' => 'year',
        ]);
    }

    /**
     * PI-REM-003: Crear recordatorio de una sola vez
     */
    public function test_crear_recordatorio_una_sola_vez()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $hashId = $this->getHashId($contact->id);

        $payload = [
            'title' => 'Devolver libro',
            'frequency_type' => 'one_time',
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
             ->post("/people/{$hashId}/reminders", $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('reminders', [
            'contact_id' => $contact->id,
            'title' => 'Devolver libro',
            'frequency_type' => 'one_time',
        ]);
    }

    /**
     * PI-REM-004: Rechazar recordatorio sin titulo
     */
    public function test_rechazar_recordatorio_sin_titulo()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $hashId = $this->getHashId($contact->id);

        $payload = [
            'frequency_type' => 'one_time',
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
             ->post("/people/{$hashId}/reminders", $payload);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseMissing('reminders', [
            'contact_id' => $contact->id,
        ]);
    }

    /**
     * PI-REM-005: Editar frecuencia de recordatorio existente
     */
    public function test_editar_frecuencia_recordatorio()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $reminder = factory(Reminder::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'frequency_type' => 'month',
            'title' => 'Llamada',
        ]);

        $contactHashId = $this->getHashId($contact->id);
        $reminderHashId = $this->getHashId($reminder->id);

        $payload = [
            'title' => 'Llamada Anual',
            'frequency_type' => 'year',
            'frequency_number' => 1,
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
             ->put("/people/{$contactHashId}/reminders/{$reminderHashId}", $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'Llamada Anual',
            'frequency_type' => 'year',
        ]);
    }

    /**
     * PI-REM-006: Eliminar recordatorio via HTTP
     */
    public function test_eliminar_recordatorio()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $reminder = factory(Reminder::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'title' => 'Para eliminar',
        ]);

        $contactHashId = $this->getHashId($contact->id);
        $reminderHashId = $this->getHashId($reminder->id);

        $response = $this->actingAs($user)
             ->delete("/people/{$contactHashId}/reminders/{$reminderHashId}");

        $response->assertStatus(302);

        $this->assertDatabaseMissing('reminders', [
            'id' => $reminder->id,
        ]);
    }

    /**
     * PI-REM-007: Verificar que recordatorio pertenezca al contacto correcto
     */
    public function test_verificar_pertenencia_a_contacto_correcto()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $contactA = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $contactB = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        $hashIdB = $this->getHashId($contactB->id);

        $payload = [
            'title' => 'Recordatorio del otro contacto',
            'frequency_type' => 'one_time',
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $this->actingAs($user)
             ->post("/people/{$hashIdB}/reminders", $payload);

        $this->assertDatabaseHas('reminders', [
            'contact_id' => $contactB->id,
            'title' => 'Recordatorio del otro contacto',
        ]);

        $this->assertDatabaseMissing('reminders', [
            'contact_id' => $contactA->id,
            'title' => 'Recordatorio del otro contacto',
        ]);
    }
}

<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Models\Contact\Contact;
use App\Models\Contact\Reminder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class ReminderIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $contact;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = factory(User::class)->create();
        $account = factory(Account::class)->create();
        $this->user->account_id = $account->id;
        $this->user->save();

        // Creamos un contacto para asociarle los recordatorios
        $this->contact = factory(Contact::class)->create([
            'account_id' => $account->id,
        ]);
    }

    /**
     * PI-REM-001: Crear recordatorio con frecuencia mensual
     */
    public function test_crear_recordatorio_frecuencia_mensual()
    {
        $payload = [
            'title' => 'Llamada mensual de seguimiento',
            'description' => 'Llamar para saber cómo le va',
            'frequency_type' => 'month',
            'frequency_number' => 1,
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
             ->post("/contacts/{$this->contact->id}/reminders", $payload);

        $response->assertStatus(302); // Redirección al crear
        
        $this->assertDatabaseHas('reminders', [
            'contact_id' => $this->contact->id,
            'title' => 'Llamada mensual de seguimiento',
            'frequency_type' => 'month',
            'frequency_number' => 1
        ]);
    }

    /**
     * PI-REM-002: Crear recordatorio con frecuencia anual
     */
    public function test_crear_recordatorio_frecuencia_anual()
    {
        $payload = [
            'title' => 'Cumpleaños',
            'frequency_type' => 'year',
            'frequency_number' => 1,
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
             ->post("/contacts/{$this->contact->id}/reminders", $payload);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('reminders', [
            'contact_id' => $this->contact->id,
            'title' => 'Cumpleaños',
            'frequency_type' => 'year'
        ]);
    }

    /**
     * PI-REM-003: Crear recordatorio de una sola vez
     */
    public function test_crear_recordatorio_una_sola_vez()
    {
        $payload = [
            'title' => 'Devolver libro',
            'frequency_type' => 'one_time',
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
             ->post("/contacts/{$this->contact->id}/reminders", $payload);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('reminders', [
            'contact_id' => $this->contact->id,
            'title' => 'Devolver libro',
            'frequency_type' => 'one_time'
        ]);
    }

    /**
     * PI-REM-004: Rechazar recordatorio sin titulo
     */
    public function test_rechazar_recordatorio_sin_titulo()
    {
        $payload = [
            // Falta el título
            'frequency_type' => 'one_time',
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
             ->post("/contacts/{$this->contact->id}/reminders", $payload);

        // Debería fallar la validación y redirigir con errores de sesión
        $response->assertSessionHasErrors('title');
        
        $this->assertDatabaseMissing('reminders', [
            'contact_id' => $this->contact->id,
        ]);
    }

    /**
     * PI-REM-005: Editar frecuencia de recordatorio existente
     */
    public function test_editar_frecuencia_recordatorio()
    {
        // Setup: Crear un recordatorio inicial
        $reminder = factory(Reminder::class)->create([
            'account_id' => $this->user->account_id,
            'contact_id' => $this->contact->id,
            'frequency_type' => 'month',
            'title' => 'Llamada'
        ]);

        $payload = [
            'title' => 'Llamada Anual',
            'frequency_type' => 'year',
            'frequency_number' => 1,
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        // Acción: Editar (Actualizar)
        $response = $this->actingAs($this->user)
             ->put("/contacts/{$this->contact->id}/reminders/{$reminder->id}", $payload);

        $response->assertStatus(302);

        // Verificación
        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'Llamada Anual',
            'frequency_type' => 'year'
        ]);
    }

    /**
     * PI-REM-006: Eliminar recordatorio via HTTP
     */
    public function test_eliminar_recordatorio()
    {
        $reminder = factory(Reminder::class)->create([
            'account_id' => $this->user->account_id,
            'contact_id' => $this->contact->id,
            'title' => 'Para eliminar'
        ]);

        $response = $this->actingAs($this->user)
             ->delete("/contacts/{$this->contact->id}/reminders/{$reminder->id}");

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
        // Creamos otro contacto diferente
        $otherContact = factory(Contact::class)->create([
            'account_id' => $this->user->account_id,
        ]);

        $payload = [
            'title' => 'Recordatorio del otro contacto',
            'frequency_type' => 'one_time',
            'initial_date' => Carbon::now()->format('Y-m-d'),
        ];

        // Creamos el recordatorio para el OTRO contacto
        $this->actingAs($this->user)
             ->post("/contacts/{$otherContact->id}/reminders", $payload);

        // Aseguramos que el recordatorio tiene el contact_id del $otherContact y NO del original
        $this->assertDatabaseHas('reminders', [
            'contact_id' => $otherContact->id,
            'title' => 'Recordatorio del otro contacto',
        ]);

        $this->assertDatabaseMissing('reminders', [
            'contact_id' => $this->contact->id,
            'title' => 'Recordatorio del otro contacto',
        ]);
    }
}

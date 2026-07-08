<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Models\Contact\Contact;
use App\Models\Contact\Activity;
use App\Models\Account\ActivityType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class ActivityIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $contact;
    protected $activityType;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = factory(User::class)->create();
        $account = factory(Account::class)->create();
        $this->user->account_id = $account->id;
        $this->user->save();

        $this->contact = factory(Contact::class)->create([
            'account_id' => $account->id,
        ]);

        $this->activityType = factory(ActivityType::class)->create([
            'account_id' => $account->id,
        ]);
    }

    /**
     * PI-ACT-001: Registrar actividad con categoria valida
     */
    public function test_registrar_actividad_categoria_valida()
    {
        $payload = [
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Resumen de prueba',
            'date_it_happened' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
             ->post("/contacts/{$this->contact->id}/activities", $payload);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('activities', [
            'account_id' => $this->user->account_id,
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Resumen de prueba'
        ]);
        
        $this->assertDatabaseHas('activity_contact', [
            'contact_id' => $this->contact->id,
        ]);
    }

    /**
     * PI-ACT-002: Registrar actividad con descripcion y fecha
     */
    public function test_registrar_actividad_descripcion_fecha()
    {
        $payload = [
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Almuerzo de negocios',
            'description' => 'Fuimos al restaurante para hablar del proyecto',
            'date_it_happened' => '2023-10-15',
        ];

        $response = $this->actingAs($this->user)
             ->post("/contacts/{$this->contact->id}/activities", $payload);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('activities', [
            'summary' => 'Almuerzo de negocios',
            'description' => 'Fuimos al restaurante para hablar del proyecto',
            'date_it_happened' => '2023-10-15 00:00:00'
        ]);
    }

    /**
     * PI-ACT-003: Rechazar actividad sin categoria seleccionada
     */
    public function test_rechazar_actividad_sin_categoria()
    {
        $payload = [
            // Falta activity_type_id
            'summary' => 'Falta categoría',
            'date_it_happened' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
             ->post("/contacts/{$this->contact->id}/activities", $payload);

        $response->assertSessionHasErrors('activity_type_id');
        
        $this->assertDatabaseMissing('activities', [
            'summary' => 'Falta categoría',
        ]);
    }

    /**
     * PI-ACT-004: Editar actividad preservando fecha original
     */
    public function test_editar_actividad_preservando_fecha()
    {
        // 1. Crear la actividad
        $activity = factory(Activity::class)->create([
            'account_id' => $this->user->account_id,
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Actividad original',
            'date_it_happened' => '2022-01-01 00:00:00'
        ]);
        
        $activity->contacts()->sync([$this->contact->id => ['account_id' => $this->user->account_id]]);

        // 2. Payload para editar
        $payload = [
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Actividad modificada',
            'date_it_happened' => '2022-01-01', // La misma fecha
        ];

        $response = $this->actingAs($this->user)
             ->put("/contacts/{$this->contact->id}/activities/{$activity->id}", $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'summary' => 'Actividad modificada',
            'date_it_happened' => '2022-01-01 00:00:00'
        ]);
    }

    /**
     * PI-ACT-005: Eliminar actividad y verificar timeline
     */
    public function test_eliminar_actividad()
    {
        $activity = factory(Activity::class)->create([
            'account_id' => $this->user->account_id,
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Para eliminar'
        ]);
        
        $activity->contacts()->sync([$this->contact->id => ['account_id' => $this->user->account_id]]);

        $response = $this->actingAs($this->user)
             ->delete("/contacts/{$this->contact->id}/activities/{$activity->id}");

        $response->assertStatus(302);

        $this->assertDatabaseMissing('activities', [
            'id' => $activity->id,
        ]);
        
        // Verifica que se borre de la tabla pivote
        $this->assertDatabaseMissing('activity_contact', [
            'activity_id' => $activity->id,
        ]);
    }

    /**
     * PI-ACT-006: Asociar actividad a multiples contactos
     */
    public function test_asociar_actividad_a_multiples_contactos()
    {
        // Creamos un segundo contacto
        $secondContact = factory(Contact::class)->create([
            'account_id' => $this->user->account_id,
        ]);

        $payload = [
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Reunión grupal',
            'date_it_happened' => Carbon::now()->format('Y-m-d'),
            // En Monica, si enviamos un arreglo de contacts, el CreateActivityController o similar debería manejarlo.
            // Para la prueba de integración, lo pasaremos como parámetros HTTP y simularemos la selección
            'contacts' => [
                $this->contact->id,
                $secondContact->id
            ]
        ];

        $response = $this->actingAs($this->user)
             ->post("/contacts/{$this->contact->id}/activities", $payload);

        $response->assertStatus(302);
        
        $activity = Activity::where('summary', 'Reunión grupal')->first();

        // Debe haber registros para AMBOS contactos en la tabla pivote
        $this->assertDatabaseHas('activity_contact', [
            'activity_id' => $activity->id,
            'contact_id' => $this->contact->id,
        ]);

        $this->assertDatabaseHas('activity_contact', [
            'activity_id' => $activity->id,
            'contact_id' => $secondContact->id,
        ]);
    }
}

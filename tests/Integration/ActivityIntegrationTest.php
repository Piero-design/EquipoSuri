<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Contact;
use App\Models\Account\Activity;
use App\Models\Account\ActivityType;
use App\Models\Account\ActivityTypeCategory;
use App\Services\Instance\IdHasher;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\CheckCompliance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ActivityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $contact;
    protected $activityType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            VerifyCsrfToken::class,
            CheckCompliance::class,
        ]);

        $this->user = factory(User::class)->create();
        $this->contact = factory(Contact::class)->create([
            'account_id' => $this->user->account_id,
        ]);

        // Crear categoría y tipo de actividad para las pruebas
        $category = factory(ActivityTypeCategory::class)->create([
            'account_id' => $this->user->account_id,
        ]);
        $this->activityType = factory(ActivityType::class)->create([
            'account_id' => $this->user->account_id,
            'activity_type_category_id' => $category->id,
        ]);
    }

    private function getHashId($id)
    {
        return app(IdHasher::class)->encodeId($id);
    }

    /**
     * PI-ACT-001: Registrar actividad con categoria valida
     */
    public function test_registrar_actividad_categoria_valida()
    {
        $this->withoutExceptionHandling();

        $payload = [
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Resumen de prueba',
            'happened_at' => Carbon::now()->format('Y-m-d'),
            'contacts' => [$this->contact->id],
        ];

        $response = $this->actingAs($this->user)
             ->post('/activities', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('activities', [
            'account_id' => $this->user->account_id,
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Resumen de prueba',
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
        $this->withoutExceptionHandling();

        $payload = [
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Almuerzo de negocios',
            'description' => 'Fuimos al restaurante para hablar del proyecto',
            'happened_at' => '2023-10-15',
            'contacts' => [$this->contact->id],
        ];

        $response = $this->actingAs($this->user)
             ->post('/activities', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('activities', [
            'summary' => 'Almuerzo de negocios',
            'description' => 'Fuimos al restaurante para hablar del proyecto',
        ]);
    }

    /**
     * PI-ACT-003: Rechazar actividad sin campo obligatorio (summary)
     */
    public function test_rechazar_actividad_sin_summary()
    {
        $payload = [
            'activity_type_id' => $this->activityType->id,
            // Falta summary
            'happened_at' => Carbon::now()->format('Y-m-d'),
            'contacts' => [$this->contact->id],
        ];

        $response = $this->actingAs($this->user)
             ->postJson('/activities', $payload);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('activities', [
            'activity_type_id' => $this->activityType->id,
            'account_id' => $this->user->account_id,
        ]);
    }

    /**
     * PI-ACT-004: Editar actividad preservando fecha original
     */
    public function test_editar_actividad_preservando_fecha()
    {
        $this->withoutExceptionHandling();

        $activity = factory(Activity::class)->create([
            'account_id' => $this->user->account_id,
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Actividad original',
            'happened_at' => '2022-01-01',
        ]);

        $activity->contacts()->sync([
            $this->contact->id => ['account_id' => $this->user->account_id],
        ]);

        // La ruta {activity} usa route-model binding por id numerico
        // (el modelo Activity extiende Model, no ModelBinding), a diferencia
        // de {contact} que decodifica un hash en RouteServiceProvider.
        $payload = [
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Actividad modificada',
            'happened_at' => '2022-01-01',
            'contacts' => [$this->contact->id],
        ];

        $response = $this->actingAs($this->user)
             ->put("/activities/{$activity->id}", $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'summary' => 'Actividad modificada',
        ]);
    }

    /**
     * PI-ACT-005: Eliminar actividad y verificar limpieza
     */
    public function test_eliminar_actividad()
    {
        $this->withoutExceptionHandling();

        $activity = factory(Activity::class)->create([
            'account_id' => $this->user->account_id,
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Para eliminar',
        ]);

        $activity->contacts()->sync([
            $this->contact->id => ['account_id' => $this->user->account_id],
        ]);

        // Igual que en update: {activity} se resuelve por id numerico.
        $response = $this->actingAs($this->user)
             ->delete("/activities/{$activity->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('activities', [
            'id' => $activity->id,
        ]);

        $this->assertDatabaseMissing('activity_contact', [
            'activity_id' => $activity->id,
        ]);
    }

    /**
     * PI-ACT-006: Asociar actividad a multiples contactos
     */
    public function test_asociar_actividad_a_multiples_contactos()
    {
        $this->withoutExceptionHandling();

        $secondContact = factory(Contact::class)->create([
            'account_id' => $this->user->account_id,
        ]);

        $payload = [
            'activity_type_id' => $this->activityType->id,
            'summary' => 'Reunión grupal',
            'happened_at' => Carbon::now()->format('Y-m-d'),
            'contacts' => [
                $this->contact->id,
                $secondContact->id,
            ],
        ];

        $response = $this->actingAs($this->user)
             ->post('/activities', $payload);

        $response->assertStatus(201);

        $activity = Activity::where('summary', 'Reunión grupal')->first();

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

<?php

namespace Tests\Unit\Services\Contact\Reminder;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Models\Contact\Contact;
use App\Models\Contact\Reminder;
use Illuminate\Validation\ValidationException;
use App\Services\Contact\Reminder\CreateReminder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Casos de prueba adicionales para CreateReminder
 * Enfoque: Boundary Testing para fechas y frecuencias
 *
 * Equipo Suri - Sprint 2
 * Responsable: Christian Henry Venero Guevara
 */
class CreateReminderBoundaryTest extends TestCase
{
    use DatabaseTransactions;

    private function createBaseEnvironment(): array
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 1));
        $account = factory(Account::class)->create([]);
        $user = factory(User::class)->create([
            'account_id' => $account->id,
        ]);
        $contact = factory(Contact::class)->create([
            'account_id' => $account->id,
        ]);

        return compact('account', 'user', 'contact');
    }

    // =========================================================
    // BOUNDARY TESTING - Tipos de frecuencia válidos
    // =========================================================

    /** @test */
    public function it_creates_reminder_with_weekly_frequency()
    {
        $env = $this->createBaseEnvironment();

        $request = [
            'contact_id' => $env['contact']->id,
            'account_id' => $env['account']->id,
            'initial_date' => '2026-07-01',
            'frequency_type' => 'week',
            'frequency_number' => 1,
            'title' => 'Llamar cada semana',
            'description' => 'Recordatorio semanal de prueba',
        ];

        $reminder = app(CreateReminder::class)->execute($request);

        $this->assertInstanceOf(Reminder::class, $reminder);
        $this->assertEquals('week', $reminder->frequency_type);
        $this->assertEquals(1, $reminder->frequency_number);
    }

    /** @test */
    public function it_creates_reminder_with_monthly_frequency()
    {
        $env = $this->createBaseEnvironment();

        $request = [
            'contact_id' => $env['contact']->id,
            'account_id' => $env['account']->id,
            'initial_date' => '2026-07-01',
            'frequency_type' => 'month',
            'frequency_number' => 3,
            'title' => 'Revisión trimestral',
            'description' => 'Cada 3 meses',
        ];

        $reminder = app(CreateReminder::class)->execute($request);

        $this->assertInstanceOf(Reminder::class, $reminder);
        $this->assertEquals('month', $reminder->frequency_type);
        $this->assertEquals(3, $reminder->frequency_number);
    }

    // =========================================================
    // BOUNDARY TESTING - Valores límite para títulos
    // =========================================================

    /** @test */
    public function it_creates_reminder_with_single_character_title()
    {
        $env = $this->createBaseEnvironment();

        $request = [
            'contact_id' => $env['contact']->id,
            'account_id' => $env['account']->id,
            'initial_date' => '2026-07-01',
            'frequency_type' => 'one_time',
            'frequency_number' => 1,
            'title' => 'X',  // Valor límite: 1 carácter
        ];

        $reminder = app(CreateReminder::class)->execute($request);

        $this->assertEquals('X', $reminder->title);
    }

    /** @test */
    public function it_creates_reminder_with_very_long_title()
    {
        $env = $this->createBaseEnvironment();
        $longTitle = str_repeat('A', 200);

        $request = [
            'contact_id' => $env['contact']->id,
            'account_id' => $env['account']->id,
            'initial_date' => '2026-07-01',
            'frequency_type' => 'one_time',
            'frequency_number' => 1,
            'title' => $longTitle,
        ];

        $reminder = app(CreateReminder::class)->execute($request);

        $this->assertEquals($longTitle, $reminder->title);
    }

    // =========================================================
    // CASOS NEGATIVOS - Frecuencias inválidas
    // =========================================================

    /** @test */
    public function it_fails_with_invalid_frequency_type()
    {
        $env = $this->createBaseEnvironment();

        $request = [
            'contact_id' => $env['contact']->id,
            'account_id' => $env['account']->id,
            'initial_date' => '2026-07-01',
            'frequency_type' => 'invalid_type',  // No existe
            'frequency_number' => 1,
            'title' => 'Test',
        ];

        $this->expectException(ValidationException::class);
        app(CreateReminder::class)->execute($request);
    }

    /** @test */
    public function it_fails_when_title_is_missing()
    {
        $env = $this->createBaseEnvironment();

        $request = [
            'contact_id' => $env['contact']->id,
            'account_id' => $env['account']->id,
            'initial_date' => '2026-07-01',
            'frequency_type' => 'one_time',
            'frequency_number' => 1,
            // Sin título
        ];

        $this->expectException(ValidationException::class);
        app(CreateReminder::class)->execute($request);
    }

    /** @test */
    public function it_fails_with_nonexistent_contact_id()
    {
        $env = $this->createBaseEnvironment();

        $request = [
            'contact_id' => 999999,  // No existe
            'account_id' => $env['account']->id,
            'initial_date' => '2026-07-01',
            'frequency_type' => 'one_time',
            'frequency_number' => 1,
            'title' => 'Test',
        ];

        $this->expectException(\Exception::class);
        app(CreateReminder::class)->execute($request);
    }
    
    //Nuevos casos
    // =========================================================
    // BOUNDARY TESTING - Valores límite para frequency_number
    // =========================================================

    /** @test */
    public function it_creates_reminder_with_minimum_frequency_number()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $request = [
            'contact_id'       => $env['contact']->id,
            'account_id'       => $env['account']->id,
            'initial_date'     => '2026-07-01',
            'frequency_type'   => 'week',
            'frequency_number' => 1,
            'title'            => 'Recordatorio mínimo',
        ];

        // Act
        $reminder = app(CreateReminder::class)->execute($request);

        // Assert
        $this->assertInstanceOf(Reminder::class, $reminder);
        $this->assertEquals(1, $reminder->frequency_number);
    }

    /** @test */
    public function it_creates_reminder_with_large_frequency_number()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $request = [
            'contact_id'       => $env['contact']->id,
            'account_id'       => $env['account']->id,
            'initial_date'     => '2026-07-01',
            'frequency_type'   => 'month',
            'frequency_number' => 999,
            'title'            => 'Recordatorio lejano',
        ];

        // Act
        $reminder = app(CreateReminder::class)->execute($request);

        // Assert
        $this->assertInstanceOf(Reminder::class, $reminder);
        $this->assertEquals(999, $reminder->frequency_number);
    }

    // =========================================================
    // BOUNDARY TESTING - Descripción
    // =========================================================

    /** @test */
    public function it_creates_reminder_with_empty_description()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $request = [
            'contact_id'       => $env['contact']->id,
            'account_id'       => $env['account']->id,
            'initial_date'     => '2026-07-01',
            'frequency_type'   => 'one_time',
            'frequency_number' => 1,
            'title'            => 'Sin descripción',
            'description'      => '',
        ];

        // Act
        $reminder = app(CreateReminder::class)->execute($request);

        // Assert
        $this->assertInstanceOf(Reminder::class, $reminder);
        $this->assertEquals('', $reminder->description);
    }

    /** @test */
    public function it_creates_reminder_with_description_saved_correctly()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $expectedDescription = 'Esta es una descripción de prueba';
        $request = [
            'contact_id'       => $env['contact']->id,
            'account_id'       => $env['account']->id,
            'initial_date'     => '2026-07-01',
            'frequency_type'   => 'one_time',
            'frequency_number' => 1,
            'title'            => 'Con descripción',
            'description'      => $expectedDescription,
        ];

        // Act
        $reminder = app(CreateReminder::class)->execute($request);

        // Assert
        $this->assertDatabaseHas('reminders', [
            'id'          => $reminder->id,
            'description' => $expectedDescription,
        ]);
    }

    // =========================================================
    // BOUNDARY TESTING - Fechas límite
    // =========================================================

    /** @test */
    public function it_creates_reminder_with_todays_date()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $request = [
            'contact_id'       => $env['contact']->id,
            'account_id'       => $env['account']->id,
            'initial_date'     => '2026-06-01',
            'frequency_type'   => 'one_time',
            'frequency_number' => 1,
            'title'            => 'Hoy mismo',
        ];

        // Act
        $reminder = app(CreateReminder::class)->execute($request);

        // Assert
        $this->assertInstanceOf(Reminder::class, $reminder);
    }

    /** @test */
    public function it_creates_reminder_with_far_future_date()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $request = [
            'contact_id'       => $env['contact']->id,
            'account_id'       => $env['account']->id,
            'initial_date'     => '2099-12-31',
            'frequency_type'   => 'one_time',
            'frequency_number' => 1,
            'title'            => 'Recordatorio futuro lejano',
        ];

        // Act
        $reminder = app(CreateReminder::class)->execute($request);

        // Assert
        $this->assertInstanceOf(Reminder::class, $reminder);
    }

    // =========================================================
    // BOUNDARY TESTING - Verificación en reminder_outbox
    // =========================================================

    /** @test */
    public function it_creates_entry_in_reminder_outbox_when_reminder_is_created()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $request = [
            'contact_id'       => $env['contact']->id,
            'account_id'       => $env['account']->id,
            'initial_date'     => '2026-07-01',
            'frequency_type'   => 'one_time',
            'frequency_number' => 1,
            'title'            => 'Verificar outbox',
        ];

        // Act
        $reminder = app(CreateReminder::class)->execute($request);

        // Assert
        $this->assertDatabaseHas('reminder_outbox', [
            'reminder_id'  => $reminder->id,
            'account_id'   => $env['account']->id,
            'planned_date' => '2026-07-01',
            'nature'       => 'reminder',
        ]);
    }

    // =========================================================
    // CASOS NEGATIVOS - frequency_number inválido
    // =========================================================
    
    /** @test */
    public function it_creates_reminder_with_negative_frequency_number()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $request = [
            'contact_id'       => $env['contact']->id,
            'account_id'       => $env['account']->id,
            'initial_date'     => '2026-07-01',
            'frequency_type'   => 'week',
            'frequency_number' => -1,
            'title'            => 'Test frecuencia negativa',
        ];

        // Act
        $reminder = app(CreateReminder::class)->execute($request);

        // Assert
        $this->assertInstanceOf(Reminder::class, $reminder);
        $this->assertEquals(-1, $reminder->frequency_number);
    }
}

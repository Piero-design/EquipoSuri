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
}

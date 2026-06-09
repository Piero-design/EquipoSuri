<?php

namespace Tests\Unit\Services\Account\Activity\ActivityTypeCategory;

use Tests\TestCase;
use App\Models\Account\Account;
use App\Models\Account\ActivityTypeCategory;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Account\Activity\ActivityTypeCategory\CreateActivityTypeCategory;

/**
 * Casos de prueba adicionales para CreateActivityTypeCategory
 * Enfoque: Boundary Testing y Análisis de Caja Negra
 *
 * Equipo Suri - Sprint 2
 * Responsable: Cristian Raul Saya Vargas
 */
class CreateActivityTypeCategoryBoundaryTest extends TestCase
{
    use DatabaseTransactions;

    // =========================================================
    // BOUNDARY TESTING - Valores Límite para nombres
    // =========================================================

    /** @test */
    public function it_creates_category_with_single_character_name()
    {
        $account = factory(Account::class)->create([]);

        $request = [
            'account_id' => $account->id,
            'name' => 'A', // Límite inferior: 1 carácter
            'translation_key' => 'a',
        ];

        $activityTypeCategory = app(CreateActivityTypeCategory::class)->execute($request);

        $this->assertEquals('A', $activityTypeCategory->name);
    }

    /** @test */
    public function it_creates_category_with_very_long_name()
    {
        $account = factory(Account::class)->create([]);
        $longName = str_repeat('X', 250); // Límite superior: 250 caracteres

        $request = [
            'account_id' => $account->id,
            'name' => $longName,
            'translation_key' => 'very_long_key',
        ];

        $activityTypeCategory = app(CreateActivityTypeCategory::class)->execute($request);

        $this->assertEquals($longName, $activityTypeCategory->name);
    }

    // =========================================================
    // CASOS NEGATIVOS
    // =========================================================

    /** @test */
    public function it_fails_when_name_is_empty()
    {
        $account = factory(Account::class)->create([]);

        $request = [
            'account_id' => $account->id,
            'name' => '', // Inválido: Vacío
            'translation_key' => 'empty_name',
        ];

        $this->expectException(ValidationException::class);
        app(CreateActivityTypeCategory::class)->execute($request);
    }

    /** @test */
    public function it_fails_when_translation_key_is_missing()
    {
        $account = factory(Account::class)->create([]);

        $request = [
            'account_id' => $account->id,
            'name' => 'Normal Name',
            // translation_key ausente
        ];

        $this->expectException(ValidationException::class);
        app(CreateActivityTypeCategory::class)->execute($request);
    }

    /** @test */
    public function it_fails_when_account_id_is_invalid()
    {
        $request = [
            'account_id' => 999999, // Cuenta inexistente
            'name' => 'Ghost Category',
            'translation_key' => 'ghost_key',
        ];

        $this->expectException(\Exception::class);
        app(CreateActivityTypeCategory::class)->execute($request);
    }
}

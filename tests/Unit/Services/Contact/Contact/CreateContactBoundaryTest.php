<?php

namespace Tests\Unit\Services\Contact\Contact;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Gender;
use App\Models\Account\Account;
use App\Models\Contact\Contact;
use Illuminate\Validation\ValidationException;
use App\Services\Contact\Contact\CreateContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Casos de prueba adicionales para CreateContact
 * Enfoque: Boundary Testing y Validación de Excepciones
 *
 * Equipo Suri - Sprint 2
 * Responsable: Sebastian Diaz Ticona
 */
class CreateContactBoundaryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper para crear el entorno base de pruebas (Arrange)
     */
    private function createBaseEnvironment(): array
    {
        $account = factory(Account::class)->create([]);
        $user = factory(User::class)->create([
            'account_id' => $account->id,
        ]);
        $gender = factory(Gender::class)->create([
            'account_id' => $account->id,
        ]);

        return compact('account', 'user', 'gender');
    }

    // =========================================================
    // BOUNDARY TESTING - Valores Límite para first_name
    // =========================================================

    /** @test */
    public function it_creates_contact_with_single_character_name()
    {
        // Arrange
        $env = $this->createBaseEnvironment();

        $request = [
            'account_id' => $env['account']->id,
            'author_id' => $env['user']->id,
            'first_name' => 'A',  // Valor límite inferior válido: 1 carácter
            'last_name' => 'Test',
            'gender_id' => $env['gender']->id,
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act
        $contact = app(CreateContact::class)->execute($request);

        // Assert
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'A',
        ]);
        $this->assertInstanceOf(Contact::class, $contact);
    }

    /** @test */
    public function it_creates_contact_with_very_long_name()
    {
        // Arrange
        $env = $this->createBaseEnvironment();
        $longName = str_repeat('A', 250); // Nombre de 250 caracteres

        $request = [
            'account_id' => $env['account']->id,
            'author_id' => $env['user']->id,
            'first_name' => $longName,
            'last_name' => 'Test',
            'gender_id' => $env['gender']->id,
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act
        $contact = app(CreateContact::class)->execute($request);

        // Assert
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
        ]);
        $this->assertEquals($longName, $contact->first_name);
    }

    /** @test */
    public function it_fails_when_first_name_is_empty_string()
    {
        // Arrange
        $env = $this->createBaseEnvironment();

        $request = [
            'account_id' => $env['account']->id,
            'author_id' => $env['user']->id,
            'first_name' => '',  // Valor límite inferior inválido: vacío
            'last_name' => 'Doe',
            'gender_id' => $env['gender']->id,
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        app(CreateContact::class)->execute($request);
    }

    /** @test */
    public function it_fails_when_first_name_is_null()
    {
        // Arrange
        $env = $this->createBaseEnvironment();

        $request = [
            'account_id' => $env['account']->id,
            'author_id' => $env['user']->id,
            'first_name' => null,  // Null explícito
            'last_name' => 'Doe',
            'gender_id' => $env['gender']->id,
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        app(CreateContact::class)->execute($request);
    }

    // =========================================================
    // CASOS NEGATIVOS - Datos Inválidos
    // =========================================================

    /** @test */
    public function it_fails_when_account_id_is_zero()
    {
        // Arrange
        $env = $this->createBaseEnvironment();

        $request = [
            'account_id' => 0,
            'author_id' => $env['user']->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        app(CreateContact::class)->execute($request);
    }

    /** @test */
    public function it_fails_when_account_id_is_negative()
    {
        // Arrange
        $request = [
            'account_id' => -1,
            'author_id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        app(CreateContact::class)->execute($request);
    }

    // =========================================================
    // CASOS ESPECIALES - Caracteres Unicode y Especiales
    // =========================================================

    /** @test */
    public function it_creates_contact_with_unicode_characters()
    {
        // Arrange
        $env = $this->createBaseEnvironment();

        $request = [
            'account_id' => $env['account']->id,
            'author_id' => $env['user']->id,
            'first_name' => 'José María',  // Caracteres con tilde
            'last_name' => 'García Ñoño',  // Ñ y acentos
            'gender_id' => $env['gender']->id,
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act
        $contact = app(CreateContact::class)->execute($request);

        // Assert
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'José María',
            'last_name' => 'García Ñoño',
        ]);
    }

    /** @test */
    public function it_creates_contact_with_only_first_name()
    {
        // Arrange
        $env = $this->createBaseEnvironment();

        $request = [
            'account_id' => $env['account']->id,
            'author_id' => $env['user']->id,
            'first_name' => 'Madonna',  // Solo nombre, sin apellido
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act
        $contact = app(CreateContact::class)->execute($request);

        // Assert
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'Madonna',
            'last_name' => null,
        ]);
    }

    /** @test */
    public function it_creates_contact_with_all_optional_fields_null()
    {
        // Arrange
        $env = $this->createBaseEnvironment();

        $request = [
            'account_id' => $env['account']->id,
            'author_id' => $env['user']->id,
            'first_name' => 'Minimal',
            'middle_name' => null,
            'last_name' => null,
            'description' => null,
            'is_partial' => false,
            'is_birthdate_known' => false,
            'is_deceased' => false,
            'is_deceased_date_known' => false,
        ];

        // Act
        $contact = app(CreateContact::class)->execute($request);

        // Assert
        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertEquals('Minimal', $contact->first_name);
        $this->assertNull($contact->middle_name);
    }
}

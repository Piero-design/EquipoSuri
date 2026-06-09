<?php

namespace Tests\Unit\Helpers;

use Carbon\Carbon;
use Tests\FeatureTestCase;
use App\Helpers\DateHelper;
use App\Helpers\TimezoneHelper;
use App\Models\Instance\SpecialDate;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DateHelperTest extends FeatureTestCase
{
    use DatabaseTransactions;

    public function testGetShortDateWithEnglishLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('en');

        $this->assertEquals(
            'Jan 22, 2017',
            DateHelper::getShortDate($date)
        );
    }

    public function testGetShortDateWithFrenchLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('fr');

        $this->assertEquals(
            '22 janv. 2017',
            DateHelper::getShortDate($date)
        );
    }

    public function testGetShortDateWithUnknownLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('jp');

        $this->assertEquals(
            'Jan 22, 2017',
            DateHelper::getShortDate($date)
        );
    }

    public function testGetFullDateWithEnglishLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('en');

        $this->assertEquals(
            'January 22, 2017',
            DateHelper::getFullDate($date)
        );
    }

    public function testGetFullDateWithFrenchLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('fr');

        $this->assertEquals(
            '22 janvier 2017',
            DateHelper::getFullDate($date)
        );
    }

    public function testGetFullDateWithUnknownLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('jp');

        $this->assertEquals(
            'January 22, 2017',
            DateHelper::getFullDate($date)
        );
    }

    public function testGetShortDateWithTimeWithEnglishLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('en');

        $this->assertEquals(
            'Jan 22, 2017 17:56',
            DateHelper::getShortDateWithTime($date)
        );
    }

    public function testGetShortDateWithTimeWithFrenchLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('fr');

        $this->assertEquals(
            '22 janv. 2017 17:56',
            DateHelper::getShortDateWithTime($date)
        );
    }

    public function testGetShortDateWithTimeWithUnknownLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('jp');

        $this->assertEquals(
            'Jan 22, 2017 17:56',
            DateHelper::getShortDateWithTime($date)
        );
    }

    public function test_get_short_date_without_year_returns_a_date()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('en');

        $this->assertEquals(
            'Jan 22',
            DateHelper::getShortDateWithoutYear($date)
        );

        App::setLocale('fr');

        $this->assertEquals(
            '22 janv.',
            DateHelper::getShortDateWithoutYear($date)
        );
    }

    public function test_it_returns_the_default_short_date()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale(null);

        $this->assertEquals(
            'Jan 22',
            DateHelper::getShortDateWithoutYear($date)
        );
    }

    public function test_add_time_according_to_frequency_type_returns_the_right_value()
    {
        $date = '2017-01-22 17:56:03';

        $testDate = DateHelper::parseDateTime($date);
        $this->assertEquals(
            '2017-01-29',
            DateHelper::addTimeAccordingToFrequencyType($testDate, 'week', 1)->toDateString()
        );

        $testDate = DateHelper::parseDateTime($date);
        $this->assertEquals(
            '2017-02-22',
            DateHelper::addTimeAccordingToFrequencyType($testDate, 'month', 1)->toDateString()
        );

        $testDate = DateHelper::parseDateTime($date);
        $this->assertEquals(
            '2018-01-22',
            DateHelper::addTimeAccordingToFrequencyType($testDate, 'year', 1)->toDateString()
        );
    }

    public function test_parse_dateTime()
    {
        $testDate = DateHelper::parseDateTime(null);

        $this->assertNull($testDate);

        $date = '2017-01-22 17:56:03';

        $testDate = DateHelper::parseDateTime($date);

        $this->assertInstanceOf(Carbon::class, $testDate);
    }

    public function test_parse_dateTime_bad()
    {
        $date = 'xF 2017';

        $testDate = DateHelper::parseDateTime($date);

        $this->assertNull($testDate);
    }

    public function test_parse_parseDate_bad()
    {
        $date = 'xF 2017';

        $testDate = DateHelper::parseDate($date);

        $this->assertNull($testDate);
    }

    public function test_parse_dateTime_format()
    {
        $date = '20190120T232144Z';

        $testDate = DateHelper::parseDateTime($date);

        $this->assertEquals(2019, $testDate->year);
        $this->assertEquals(1, $testDate->month);
        $this->assertEquals(20, $testDate->day);
        $this->assertEquals(23, $testDate->hour);
        $this->assertEquals(21, $testDate->minute);
        $this->assertEquals(44, $testDate->second);
        $this->assertEquals('UTC', $testDate->timezone->getName());

        $this->assertEquals(
            '2019-01-20',
            $testDate->toDateString()
        );
        $this->assertEquals(
            '2019-01-20T23:21:44Z',
            DateHelper::getTimestamp($testDate)
        );
    }

    public function test_parse_dateTime_utc()
    {
        $date = '2017-01-22 17:56:03';

        $testDate = DateHelper::parseDateTime($date);

        $this->assertEquals(2017, $testDate->year);
        $this->assertEquals(1, $testDate->month);
        $this->assertEquals(22, $testDate->day);
        $this->assertEquals(17, $testDate->hour);
        $this->assertEquals(56, $testDate->minute);
        $this->assertEquals(03, $testDate->second);
        $this->assertEquals('UTC', $testDate->timezone->getName());

        $this->assertEquals(
            '2017-01-22',
            $testDate->toDateString()
        );
        $this->assertEquals(
            '2017-01-22T17:56:03Z',
            DateHelper::getTimestamp($testDate)
        );
    }

    public function test_parse_dateTime_new_york()
    {
        $date = '2017-01-22 17:56:03';
        $timezone = 'America/New_York';

        $testDate = DateHelper::parseDateTime($date, $timezone);

        $this->assertEquals(2017, $testDate->year);
        $this->assertEquals(1, $testDate->month);
        $this->assertEquals(22, $testDate->day);
        $this->assertEquals(22, $testDate->hour);
        $this->assertEquals(56, $testDate->minute);
        $this->assertEquals(03, $testDate->second);
        $this->assertEquals('UTC', $testDate->timezone->getName());

        $this->assertEquals(
            '2017-01-22',
            $testDate->toDateString()
        );
        $this->assertEquals(
            '2017-01-22T22:56:03Z',
            DateHelper::getTimestamp($testDate)
        );
    }

    public function test_parse_dateTime_paris()
    {
        $date = '2019-01-01 00:56:03';
        $timezone = 'Europe/Paris';

        $testDate = DateHelper::parseDateTime($date, $timezone);

        $this->assertEquals(2018, $testDate->year);
        $this->assertEquals(12, $testDate->month);
        $this->assertEquals(31, $testDate->day);
        $this->assertEquals(23, $testDate->hour);
        $this->assertEquals(56, $testDate->minute);
        $this->assertEquals(03, $testDate->second);
        $this->assertEquals('UTC', $testDate->timezone->getName());

        $this->assertEquals(
            '2018-12-31',
            $testDate->toDateString()
        );
        $this->assertEquals(
            '2018-12-31T23:56:03Z',
            DateHelper::getTimestamp($testDate)
        );
    }

    public function test_parse_dateTime_carbon()
    {
        $date = new Carbon('2019-01-01 00:56:03', 'Europe/Paris');

        $testDate = DateHelper::parseDateTime($date);

        $this->assertEquals(2018, $testDate->year);
        $this->assertEquals(12, $testDate->month);
        $this->assertEquals(31, $testDate->day);
        $this->assertEquals(23, $testDate->hour);
        $this->assertEquals(56, $testDate->minute);
        $this->assertEquals(03, $testDate->second);
        $this->assertEquals('UTC', $testDate->timezone->getName());

        $this->assertEquals(
            '2018-12-31',
            $testDate->toDateString()
        );
        $this->assertEquals(
            '2018-12-31T23:56:03Z',
            DateHelper::getTimestamp($testDate)
        );
    }

    public function test_parse_dateTime_dateTimeObject()
    {
        $date = new \DateTime('2019-01-01 00:56:03', new \DateTimeZone('Europe/Paris'));

        $testDate = DateHelper::parseDateTime($date);

        $this->assertEquals(2018, $testDate->year);
        $this->assertEquals(12, $testDate->month);
        $this->assertEquals(31, $testDate->day);
        $this->assertEquals(23, $testDate->hour);
        $this->assertEquals(56, $testDate->minute);
        $this->assertEquals(03, $testDate->second);
        $this->assertEquals('UTC', $testDate->timezone->getName());

        $this->assertEquals(
            '2018-12-31',
            $testDate->toDateString()
        );
        $this->assertEquals(
            '2018-12-31T23:56:03Z',
            DateHelper::getTimestamp($testDate)
        );
    }

    public function testGetShortMonthWithEnglishLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('en');

        $this->assertEquals(
            'Jan',
            DateHelper::getShortMonth($date)
        );
    }

    public function testGetShortMonthWithFrenchLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('fr');

        $this->assertEquals(
            'janv.',
            DateHelper::getShortMonth($date)
        );
    }

    public function testGetShortMonthWithUnknownLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('jp');

        $this->assertEquals(
            'Jan',
            DateHelper::getShortMonth($date)
        );
    }

    public function testGetFullMonthAndDateWithEnglishLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('en');

        $this->assertEquals(
            'January 2017',
            DateHelper::getFullMonthAndDate($date)
        );
    }

    public function testGetFullMonthAndDateWithFrenchLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('fr');

        $this->assertEquals(
            'janvier 2017',
            DateHelper::getFullMonthAndDate($date)
        );
    }

    public function testGetFullMonthAndDateWithUnknownLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('jp');

        $this->assertEquals(
            'January 2017',
            DateHelper::getFullMonthAndDate($date)
        );
    }

    public function testGetShortDayWithEnglishLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('en');

        $this->assertEquals(
            'Sun',
            DateHelper::getShortDay($date)
        );
    }

    public function testGetShortDayWithFrenchLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('fr');

        $this->assertEquals(
            'dim.',
            DateHelper::getShortDay($date)
        );
    }

    public function testGetShortDayWithUnknownLocale()
    {
        $date = Carbon::parse('2017-01-22 17:56:03');
        App::setLocale('jp');

        $this->assertEquals(
            'Sun',
            DateHelper::getShortDay($date)
        );
    }

    public function test_get_month_and_year()
    {
        Carbon::setTestNow(Carbon::create(2017, 1, 1));

        $this->assertEquals(
            'Jul 2017',
            DateHelper::getMonthAndYear(6)
        );
    }

    public function test_it_gets_date_one_month_from_now()
    {
        Carbon::setTestNow(Carbon::create(2017, 1, 1));

        $this->assertEquals(
            '2017-02-01',
            DateHelper::getNextTheoriticalBillingDate('monthly')->toDateString()
        );
    }

    public function test_it_gets_date_one_year_from_now()
    {
        Carbon::setTestNow(Carbon::create(2017, 1, 1));

        $this->assertEquals(
            '2018-01-01',
            DateHelper::getNextTheoriticalBillingDate('yearly')->toDateString()
        );
    }

    public function test_it_returns_a_list_with_years()
    {
        $user = $this->signIn();
        $user->locale = 'en';
        $user->save();

        $this->assertCount(
            3,
            DateHelper::getListOfYears(2)
        );

        $this->assertEquals(
            now()->year,
            DateHelper::getListOfYears(2)->first()['name']
        );
        $this->assertEquals(
            now()->subYears(2)->year,
            DateHelper::getListOfYears(2)->last()['name']
        );
        $this->assertEquals(
            now()->subYears(-2)->year,
            DateHelper::getListOfYears(2, -2)->first()['name']
        );
        $this->assertEquals(
            now()->year,
            DateHelper::getListOfYears(2, -2)[2]['name']
        );
    }

    public function test_it_returns_a_list_with_twelve_months()
    {
        $user = $this->signIn();
        $user->locale = 'en';
        $user->save();

        $this->assertCount(
            12,
            DateHelper::getListOfMonths()
        );
    }

    public function test_it_returns_a_list_of_months_in_english()
    {
        $user = $this->signIn();
        $user->locale = 'en';
        $user->save();

        $months = DateHelper::getListOfMonths();

        $this->assertEquals(
            'January',
            $months[0]['name']
        );
    }

    public function test_it_returns_a_list_with_thirty_one_days()
    {
        $user = $this->signIn();
        $user->locale = 'en';
        $user->save();

        $this->assertCount(
            31,
            DateHelper::getListOfDays()
        );
    }

    public function test_it_returns_a_list_with_twenty_four_hours()
    {
        $this->assertCount(
            24,
            DateHelper::getListOfHours()
        );
    }

    public function test_it_returns_a_list_of_hours()
    {
        $hours = DateHelper::getListOfHours();

        $this->assertEquals(
            '01.00 AM',
            $hours[0]['name']
        );

        $this->assertEquals(
            '01:00',
            $hours[0]['id']
        );

        $this->assertEquals(
            '02.00 PM',
            $hours[13]['name']
        );

        $this->assertEquals(
            '14:00',
            $hours[13]['id']
        );
    }

    public function test_it_returns_a_list_of_hours_French()
    {
        App::setLocale('fr');
        $hours = DateHelper::getListOfHours();

        $this->assertEquals(
            '01:00',
            $hours[0]['name']
        );

        $this->assertEquals(
            '01:00',
            $hours[0]['id']
        );

        $this->assertEquals(
            '14:00',
            $hours[13]['name']
        );

        $this->assertEquals(
            '14:00',
            $hours[13]['id']
        );
    }

    // -------------------------------------------------------------------------
    // Nuevas pruebas unitarias
    // -------------------------------------------------------------------------

    /**
     * Prueba que parseDate acepta una cadena de fecha válida y devuelve
     * siempre un objeto Carbon normalizado a la zona horaria UTC, tanto
     * cuando no se especifica zona horaria como cuando se pasa 'America/Lima'.
     */
    public function test_parse_date_valid_string(): void
    {
        // Arrange: fecha en texto plano y zona horaria de Perú (UTC-5)
        $dateString = '2021-06-15';
        $timezone = 'America/Lima';

        // Act: analizar la fecha sin zona horaria y con zona horaria explícita
        $result = DateHelper::parseDate($dateString);
        $resultWithTimezone = DateHelper::parseDate($dateString, $timezone);

        // Assert: ambos resultados deben ser instancias de Carbon normalizadas a UTC
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2021-06-15', $result->toDateString());
        $this->assertEquals('UTC', $result->timezone->getName());

        $this->assertInstanceOf(Carbon::class, $resultWithTimezone);
        $this->assertEquals('UTC', $resultWithTimezone->timezone->getName());
    }

    /**
     * Prueba que parseDate acepta directamente una instancia de Carbon ya
     * construida, elimina la información de hora (la pone a 00:00:00) y
     * conserva únicamente la parte de fecha normalizada a UTC.
     */
    public function test_parse_date_carbon_instance(): void
    {
        // Arrange: instancia de Carbon con hora 12:30 — parseDate debe descartarla
        $carbon = Carbon::create(2021, 6, 15, 12, 30, 0, 'UTC');

        // Act: pasar la instancia de Carbon directamente al método
        $result = DateHelper::parseDate($carbon);

        // Assert: la fecha se mantiene, pero la hora queda en 00:00:00 y la zona en UTC
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2021-06-15', $result->toDateString());
        $this->assertEquals('UTC', $result->timezone->getName());
        $this->assertEquals(0, $result->hour);
        $this->assertEquals(0, $result->minute);
        $this->assertEquals(0, $result->second);
    }

    /**
     * Prueba que getDate devuelve null cuando recibe null como argumento,
     * evitando así errores al intentar formatear una fecha inexistente.
     */
    public function test_get_date_null(): void
    {
        // Arrange: valor nulo que representa una fecha no establecida
        $date = null;

        // Act: solicitar el formato de fecha sobre un valor nulo
        $result = DateHelper::getDate($date);

        // Assert: el método debe devolver null sin lanzar excepción
        $this->assertNull($result);
    }

    /**
     * Prueba que getDate formatea correctamente una cadena de fecha al
     * formato definido en la configuración 'api.date_timestamp_format' (Y-m-d).
     */
    public function test_get_date_string(): void
    {
        // Arrange: cadena de fecha en formato ISO
        $dateString = '2021-06-15';

        // Act: formatear la cadena usando getDate
        $result = DateHelper::getDate($dateString);

        // Assert: el resultado debe coincidir con el formato Y-m-d del config de la API
        $this->assertEquals('2021-06-15', $result);
    }

    /**
     * Prueba que getDate formatea correctamente una instancia de Carbon al
     * formato 'Y-m-d' definido en la configuración de la API.
     */
    public function test_get_date_carbon(): void
    {
        // Arrange: instancia de Carbon con fecha conocida
        $date = Carbon::create(2021, 6, 15, 0, 0, 0, 'UTC');

        // Act: formatear la instancia de Carbon usando getDate
        $result = DateHelper::getDate($date);

        // Assert: la cadena devuelta debe respetar el formato Y-m-d de la API
        $this->assertEquals('2021-06-15', $result);
    }

    /**
     * Prueba que getDate resuelve correctamente una instancia de SpecialDate:
     * extrae su propiedad 'date' (un Carbon interno) y la formatea como Y-m-d,
     * demostrando que el método sabe manejar este modelo especial sin base de datos.
     */
    public function test_get_date_special_date(): void
    {
        // Arrange: instancia de SpecialDate creada en memoria (sin persistir en DB)
        $specialDate = new SpecialDate();
        $specialDate->date = Carbon::create(2021, 6, 15, 0, 0, 0, 'UTC');

        // Act: pasar el SpecialDate a getDate para que lo resuelva y formatee
        $result = DateHelper::getDate($specialDate);

        // Assert: el resultado debe ser la fecha del modelo en formato Y-m-d
        $this->assertEquals('2021-06-15', $result);
    }

    /**
     * Prueba que getTimezone devuelve la zona horaria del usuario autenticado.
     * Se simula la fachada Auth para aislar la prueba de la base de datos,
     * verificando que el método lee correctamente el atributo 'timezone' del usuario.
     */
    public function test_get_timezone_authenticated(): void
    {
        // Arrange: simular un usuario autenticado con zona horaria de Perú
        $user = new \stdClass();
        $user->timezone = 'America/Lima';

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        // Act: obtener la zona horaria del contexto de autenticación actual
        $result = DateHelper::getTimezone();

        // Assert: debe devolver exactamente la zona horaria asignada al usuario
        $this->assertEquals('America/Lima', $result);
    }

    /**
     * Prueba que getTimezone devuelve null cuando no hay ningún usuario
     * autenticado en la sesión, garantizando que el método no falla ni
     * intenta acceder a propiedades de un usuario inexistente.
     */
    public function test_get_timezone_unauthenticated(): void
    {
        // Arrange: simular que Auth::check() indica que no hay sesión activa
        Auth::shouldReceive('check')->once()->andReturn(false);

        // Act: intentar obtener la zona horaria sin usuario autenticado
        $result = DateHelper::getTimezone();

        // Assert: sin sesión activa el método debe devolver null
        $this->assertNull($result);
    }

    /**
     * Prueba que addTimeAccordingToFrequencyType, al recibir una frecuencia
     * desconocida, cae en el bloque 'default' del switch y suma años a la fecha,
     * demostrando que el comportamiento por defecto es siempre avanzar en años.
     */
    public function test_add_time_frequency_default(): void
    {
        // Arrange: fecha base y tipo de frecuencia que no es 'week' ni 'month'
        $date = Carbon::create(2021, 6, 15, 0, 0, 0, 'UTC');
        $unknownFrequency = 'quarterly';

        // Act: aplicar la frecuencia desconocida con un incremento de 2
        $result = DateHelper::addTimeAccordingToFrequencyType($date, $unknownFrequency, 2);

        // Assert: al caer en default (años), 2021 + 2 = 2023 con el mismo día y mes
        $this->assertEquals('2023-06-15', $result->toDateString());
    }

    public function test_old_timezones_exists()
    {
        // These are all currently used timezone in monica
        $oldTimezones = [
            'US/Eastern',
            'US/Central',
            'America/Los_Angeles',
            'Pacific/Midway',
            'Pacific/Samoa',
            'Pacific/Honolulu',
            'US/Alaska',
            'America/Tijuana',
            'US/Arizona',
            'America/Chihuahua',
            'America/Chihuahua',
            'America/Mazatlan',
            'US/Mountain',
            'America/Managua',
            'US/Central',
            'America/Mexico_City',
            'America/Mexico_City',
            'America/Monterrey',
            'Canada/Saskatchewan',
            'America/Bogota',
            'US/Eastern',
            'US/East-Indiana',
            'America/Lima',
            'America/Bogota',
            'Canada/Atlantic',
            'America/Caracas',
            'America/La_Paz',
            'America/Santiago',
            'Canada/Newfoundland',
            'America/Sao_Paulo',
            'America/Argentina/Buenos_Aires',
            'America/Noronha',
            'Atlantic/Azores',
            'Atlantic/Cape_Verde',
            'Africa/Casablanca',
            'Europe/London',
            'Etc/Greenwich',
            'Europe/Lisbon',
            'Europe/London',
            'Africa/Monrovia',
            'UTC',
            'Europe/Amsterdam',
            'Europe/Belgrade',
            'Europe/Berlin',
            'Europe/Bratislava',
            'Europe/Brussels',
            'Europe/Budapest',
            'Europe/Copenhagen',
            'Europe/Ljubljana',
            'Europe/Madrid',
            'Europe/Paris',
            'Europe/Prague',
            'Europe/Rome',
            'Europe/Sarajevo',
            'Europe/Skopje',
            'Europe/Stockholm',
            'Europe/Vienna',
            'Europe/Warsaw',
            'Africa/Lagos',
            'Europe/Zagreb',
            'Europe/Zurich',
            'Europe/Athens',
            'Europe/Bucharest',
            'Africa/Cairo',
            'Africa/Harare',
            'Europe/Helsinki',
            'Europe/Istanbul',
            'Asia/Jerusalem',
            'Europe/Helsinki',
            'Africa/Johannesburg',
            'Europe/Riga',
            'Europe/Sofia',
            'Europe/Tallinn',
            'Europe/Vilnius',
            'Asia/Baghdad',
            'Asia/Kuwait',
            'Europe/Minsk',
            'Africa/Nairobi',
            'Asia/Riyadh',
            'Europe/Volgograd',
            'Asia/Tehran',
            'Asia/Muscat',
            'Asia/Baku',
            'Europe/Moscow',
            'Asia/Muscat',
            'Europe/Moscow',
            'Asia/Tbilisi',
            'Asia/Yerevan',
            'Asia/Kabul',
            'Asia/Karachi',
            'Asia/Karachi',
            'Asia/Tashkent',
            'Asia/Calcutta',
            'Asia/Kolkata',
            'Asia/Calcutta',
            'Asia/Calcutta',
            'Asia/Calcutta',
            'Asia/Katmandu',
            'Asia/Almaty',
            'Asia/Dhaka',
            'Asia/Dhaka',
            'Asia/Yekaterinburg',
            'Asia/Rangoon',
            'Asia/Bangkok',
            'Asia/Bangkok',
            'Asia/Jakarta',
            'Asia/Novosibirsk',
            'Asia/Hong_Kong',
            'Asia/Chongqing',
            'Asia/Hong_Kong',
            'Asia/Krasnoyarsk',
            'Asia/Kuala_Lumpur',
            'Australia/Perth',
            'Asia/Singapore',
            'Asia/Taipei',
            'Asia/Ulan_Bator',
            'Asia/Urumqi',
            'Asia/Irkutsk',
            'Asia/Tokyo',
            'Asia/Tokyo',
            'Asia/Seoul',
            'Asia/Tokyo',
            'Australia/Adelaide',
            'Australia/Darwin',
            'Australia/Brisbane',
            'Australia/Canberra',
            'Pacific/Guam',
            'Australia/Hobart',
            'Australia/Melbourne',
            'Pacific/Port_Moresby',
            'Australia/Sydney',
            'Asia/Yakutsk',
            'Asia/Vladivostok',
            'Pacific/Auckland',
            'Pacific/Fiji',
            'Pacific/Kwajalein',
            'Asia/Kamchatka',
            'Asia/Magadan',
            'Pacific/Fiji',
            'Asia/Magadan',
            'Asia/Magadan',
            'Pacific/Auckland',
            'Pacific/Tongatapu',
        ];

        $list = TimezoneHelper::getListOfTimezones();
        $list = collect($list);

        $missed = '';
        foreach ($oldTimezones as $timezone) {
            $timezone = TimezoneHelper::adjustEquivalentTimezone($timezone);
            if ($list->firstWhere('timezone', $timezone) == null) {
                $missed .= $timezone.',';
            }
        }

        $this->assertTrue(empty($missed), 'Missed timezones : '.$missed);
    }
}

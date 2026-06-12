<?php

namespace Tests\Unit\Exceptions;

use Tests\TestCase;
use RuntimeException;
use App\Exceptions\WrongIdException;
use App\Exceptions\WrongValueException;
use App\Exceptions\NoAccountException;
use App\Exceptions\NoCoordinatesException;
use App\Exceptions\StripeException;
use App\Exceptions\MissingEnvVariableException;
use App\Exceptions\RateLimitedSecondException;
use App\Exceptions\FileNotFoundException;
use Illuminate\Contracts\Filesystem\FileNotFoundException as FileNotFoundExceptionBase;

class ExceptionsTest extends TestCase
{
    /** @test */
    public function wrong_id_exception_is_instance_of_runtime_exception()
    {
        $exception = new WrongIdException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /** @test */
    public function wrong_id_exception_can_have_a_custom_message()
    {
        $exception = new WrongIdException('Invalid ID provided');
        $this->assertEquals('Invalid ID provided', $exception->getMessage());
    }

    /** @test */
    public function wrong_id_exception_default_code_is_zero()
    {
        $exception = new WrongIdException();
        $this->assertEquals(0, $exception->getCode());
    }

    /** @test */
    public function wrong_value_exception_is_instance_of_runtime_exception()
    {
        $exception = new WrongValueException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /** @test */
    public function wrong_value_exception_can_have_a_custom_message()
    {
        $exception = new WrongValueException('Wrong value given');
        $this->assertEquals('Wrong value given', $exception->getMessage());
    }

    /** @test */
    public function wrong_value_exception_default_code_is_zero()
    {
        $exception = new WrongValueException();
        $this->assertEquals(0, $exception->getCode());
    }

    /** @test */
    public function no_account_exception_is_instance_of_runtime_exception()
    {
        $exception = new NoAccountException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /** @test */
    public function no_account_exception_can_have_a_custom_message()
    {
        $exception = new NoAccountException('No account found');
        $this->assertEquals('No account found', $exception->getMessage());
    }

    /** @test */
    public function no_account_exception_default_code_is_zero()
    {
        $exception = new NoAccountException();
        $this->assertEquals(0, $exception->getCode());
    }

    /** @test */
    public function no_coordinates_exception_is_instance_of_runtime_exception()
    {
        $exception = new NoCoordinatesException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /** @test */
    public function no_coordinates_exception_can_have_a_custom_message()
    {
        $exception = new NoCoordinatesException('Coordinates missing');
        $this->assertEquals('Coordinates missing', $exception->getMessage());
    }

    /** @test */
    public function no_coordinates_exception_default_code_is_zero()
    {
        $exception = new NoCoordinatesException();
        $this->assertEquals(0, $exception->getCode());
    }

    /** @test */
    public function stripe_exception_is_instance_of_runtime_exception()
    {
        $exception = new StripeException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /** @test */
    public function stripe_exception_can_have_a_custom_message()
    {
        $exception = new StripeException('Stripe error occurred');
        $this->assertEquals('Stripe error occurred', $exception->getMessage());
    }

    /** @test */
    public function stripe_exception_default_code_is_zero()
    {
        $exception = new StripeException();
        $this->assertEquals(0, $exception->getCode());
    }

    /** @test */
    public function missing_env_variable_exception_is_instance_of_runtime_exception()
    {
        $exception = new MissingEnvVariableException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /** @test */
    public function missing_env_variable_exception_can_have_a_custom_message()
    {
        $exception = new MissingEnvVariableException('ENV variable missing');
        $this->assertEquals('ENV variable missing', $exception->getMessage());
    }

    /** @test */
    public function missing_env_variable_exception_default_code_is_zero()
    {
        $exception = new MissingEnvVariableException();
        $this->assertEquals(0, $exception->getCode());
    }

    /** @test */
    public function rate_limited_second_exception_is_instance_of_runtime_exception()
    {
        $previous = new \Exception('rate limited');
        $exception = new RateLimitedSecondException($previous);
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /** @test */
    public function rate_limited_second_exception_has_code_429()
    {
        $previous = new \Exception('rate limited');
        $exception = new RateLimitedSecondException($previous);
        $this->assertEquals(429, $exception->getCode());
    }

    /** @test */
    public function rate_limited_second_exception_has_empty_message()
    {
        $previous = new \Exception('rate limited');
        $exception = new RateLimitedSecondException($previous);
        $this->assertEquals('', $exception->getMessage());
    }

    /** @test */
    public function rate_limited_second_exception_stores_previous_exception()
    {
        $previous = new \Exception('rate limited');
        $exception = new RateLimitedSecondException($previous);
        $this->assertSame($previous, $exception->getPrevious());
    }

    /** @test */
    public function file_not_found_exception_is_instance_of_base_file_not_found_exception()
    {
        $exception = new FileNotFoundException('photo.jpg');
        $this->assertInstanceOf(FileNotFoundExceptionBase::class, $exception);
    }

    /** @test */
    public function file_not_found_exception_stores_file_name()
    {
        $exception = new FileNotFoundException('document.pdf');
        $this->assertEquals('document.pdf', $exception->fileName);
    }

    /** @test */
    public function file_not_found_exception_to_string_returns_formatted_message()
    {
        $exception = new FileNotFoundException('config.yml');
        $this->assertEquals('File not found: config.yml', (string) $exception);
    }

    /** @test */
    public function file_not_found_exception_to_string_works_with_path()
    {
        $exception = new FileNotFoundException('/var/uploads/image.png');
        $this->assertEquals('File not found: /var/uploads/image.png', $exception->__toString());
    }

    /** @test */
    public function file_not_found_exception_stores_empty_file_name()
    {
        $exception = new FileNotFoundException('');
        $this->assertEquals('', $exception->fileName);
        $this->assertEquals('File not found: ', (string) $exception);
    }
}

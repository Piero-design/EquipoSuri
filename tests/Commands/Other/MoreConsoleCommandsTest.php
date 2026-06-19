<?php

namespace Tests\Commands\Other;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use App\Models\Account\Account;

class MoreConsoleCommandsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_clean_command()
    {
        $exitCode = Artisan::call('monica:clean');
        $this->assertEquals(0, $exitCode);
    }

    public function test_export_all_command()
    {
        $exitCode = Artisan::call('export:all');
        $this->assertEquals(0, $exitCode);
    }

    public function test_update_gravatars_command()
    {
        $exitCode = Artisan::call('monica:updategravatars');
        $this->assertEquals(0, $exitCode);
    }

    public function test_send_reminders_command()
    {
        $exitCode = Artisan::call('send:reminders');
        $this->assertEquals(0, $exitCode);
    }

    public function test_send_stay_in_touch_command()
    {
        $exitCode = Artisan::call('send:stay_in_touch');
        $this->assertEquals(0, $exitCode);
    }

    public function test_create_account_command()
    {
        $this->artisan('account:create', [
            '--email' => 'command_user@example.com',
            '--password' => 'password123',
            '--firstname' => 'John',
            '--lastname' => 'Doe',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'command_user@example.com',
        ]);
    }
}

<?php

namespace Tests\Commands\Other;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Models\Instance\Statistic;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Avatars\UpdateAllGravatars;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConsoleCommandsTest extends TestCase
{
    use DatabaseTransactions;

    // ─── GetVersion ─────────────────────────────────────────────

    /** @test */
    public function get_version_outputs_the_current_app_version()
    {
        config(['monica.app_version' => '3.0.0']);

        $this->artisan('monica:getversion')
            ->expectsOutput('3.0.0')
            ->assertExitCode(0);
    }

    // ─── Inspire ────────────────────────────────────────────────

    /** @test */
    public function inspire_command_runs_successfully()
    {
        $this->artisan('inspire')
            ->assertExitCode(0);
    }


    // ─── SetUserAdmin ───────────────────────────────────────────

    /** @test */
    public function set_user_admin_promotes_a_regular_user()
    {
        $account = factory(Account::class)->create();
        $user = factory(User::class)->create([
            'account_id' => $account->id,
            'admin' => false,
        ]);

        $this->artisan('monica:admin', [
            '--email' => $user->email,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'admin' => true,
        ]);
    }

    /** @test */
    public function set_user_admin_demotes_an_admin_user()
    {
        $account = factory(Account::class)->create();
        $user = factory(User::class)->create([
            'account_id' => $account->id,
            'admin' => true,
        ]);

        $this->artisan('monica:admin', [
            '--email' => $user->email,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'admin' => false,
        ]);
    }

    /** @test */
    public function set_user_admin_shows_error_for_unknown_email()
    {
        $this->artisan('monica:admin', [
            '--email' => 'nonexistent@example.com',
            '--force' => true,
        ])->expectsOutput('No user with that email.')
          ->assertExitCode(0);
    }

    // ─── UpdateGravatars ────────────────────────────────────────

    /** @test */
    public function update_gravatars_dispatches_the_job()
    {
        Bus::fake([UpdateAllGravatars::class]);

        $this->artisan('monica:updategravatars')
            ->assertExitCode(0);

        Bus::assertDispatched(UpdateAllGravatars::class);
    }

    // ─── ExportAll ──────────────────────────────────────────────

    /** @test */
    public function export_all_creates_a_sql_file()
    {
        Storage::fake('local');

        $this->artisan('export:all')
            ->assertExitCode(0);

        // The command creates a file named export-all-{timestamp}.sql
        $files = Storage::disk('local')->files();
        $sqlFiles = array_filter($files, function ($file) {
            return strpos($file, 'export-all-') === 0 && substr($file, -4) === '.sql';
        });

        $this->assertNotEmpty($sqlFiles, 'An SQL export file should have been created');
    }

    // ─── CalculateStatistics ────────────────────────────────────

    /** @test */
    public function calculate_statistics_creates_a_statistic_record()
    {
        // Ensure at least one account exists so the counts are deterministic
        factory(Account::class)->create();

        $countBefore = Statistic::count();

        $this->artisan('monica:calculatestatistics')
            ->assertExitCode(0);

        $this->assertEquals($countBefore + 1, Statistic::count());
    }

    // ─── SendTestEmail ──────────────────────────────────────────

    /** @test */
    public function send_test_email_sends_an_email_to_given_address()
    {
        Mail::fake();

        $this->artisan('monica:test-email', [
            '--email' => 'test@example.com',
        ])->expectsOutput('Preparing and sending email to "test@example.com"')
          ->assertExitCode(0);
    }

    /** @test */
    public function send_test_email_fails_for_invalid_email()
    {
        Mail::fake();

        $this->artisan('monica:test-email', [
            '--email' => 'not-an-email',
        ])->expectsOutput('Invalid email address: "not-an-email".')
          ->assertExitCode(-1);

        Mail::assertNothingSent();
    }
}

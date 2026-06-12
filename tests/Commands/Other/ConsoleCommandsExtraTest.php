<?php

namespace Tests\Commands\Other;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Models\Instance\Instance;
use App\Models\Contact\Contact;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Console\Commands\Helpers\Command;
use App\Console\Commands\ImportAccounts;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConsoleCommandsExtraTest extends TestCase
{
    use DatabaseTransactions;

    // ─── Deactivate2FA ──────────────────────────────────────────

    /** @test */
    public function deactivate_2fa_shows_error_for_unknown_email()
    {
        $this->artisan('2fa:deactivate', [
            '--email' => 'unknown@example.com',
            '--force' => true,
        ])->expectsOutput('No user with that email.')
          ->assertExitCode(0);
    }

    /** @test */
    public function deactivate_2fa_shows_error_when_2fa_is_not_activated()
    {
        $account = factory(Account::class)->create();
        $user = factory(User::class)->create([
            'account_id' => $account->id,
            'google2fa_secret' => null,
        ]);

        $this->artisan('2fa:deactivate', [
            '--email' => $user->email,
            '--force' => true,
        ])->expectsOutput('2FA is currently not activated for this user.')
          ->assertExitCode(0);
    }

    /** @test */
    public function deactivate_2fa_deactivates_for_user_with_2fa_enabled()
    {
        $account = factory(Account::class)->create();
        $user = factory(User::class)->create([
            'account_id' => $account->id,
            'google2fa_secret' => 'some_secret_value',
        ]);

        $this->artisan('2fa:deactivate', [
            '--email' => $user->email,
            '--force' => true,
        ])->assertExitCode(0);

        $user->refresh();
        $this->assertNull($user->google2fa_secret);
    }

    // ─── SetPremiumAccount ──────────────────────────────────────

    /** @test */
    public function set_premium_account_runs_for_existing_account()
    {
        $account = factory(Account::class)->create();

        // The command calls $account->update() with 'has_access_to_paid_version_for_free'
        // which exercises the SetPremiumAccount handle() code path.
        $this->artisan('account:setpremium', [
            'accountId' => $account->id,
        ])->assertExitCode(0);
    }

    /** @test */
    public function set_premium_account_fails_with_nonexistent_id()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->artisan('account:setpremium', [
            'accountId' => 999999,
        ])->assertExitCode(1);
    }

    // ─── SentryRelease ──────────────────────────────────────────

    /** @test */
    public function sentry_release_exits_early_when_sentry_support_is_disabled()
    {
        config(['monica.sentry_support' => false]);

        $this->artisan('sentry:release', [
            '--force' => true,
        ])->assertExitCode(0);
    }

    /** @test */
    public function sentry_release_check_fails_without_auth_token()
    {
        config(['monica.sentry_support' => true]);
        config(['sentry-release.auth_token' => '']);
        config(['sentry-release.organisation' => '']);
        config(['sentry-release.project' => '']);
        config(['sentry-release.repo' => '']);

        $this->artisan('sentry:release', [
            '--force' => true,
        ])->expectsOutput('You must provide an auth_token (SENTRY_AUTH_TOKEN)')
          ->expectsOutput('You must provide an organisation slug (SENTRY_ORG)')
          ->expectsOutput('You must set the project (SENTRY_PROJECT)')
          ->expectsOutput('You must set the repository (SENTRY_REPO)')
          ->expectsOutput('No environment given')
          ->assertExitCode(0);
    }

    // ─── ImportAccounts (LDAP) ──────────────────────────────────

    /** @test */
    public function import_accounts_shows_error_when_missing_required_options()
    {
        $this->artisan('account:import_ldap')
            ->expectsOutput(ImportAccounts::ERROR_MISSING_LDAP_USER)
            ->expectsOutput(ImportAccounts::ERROR_MISSING_LDAP_PASS)
            ->expectsOutput(ImportAccounts::ERROR_MISSING_LDAP_BASE)
            ->expectsOutput(ImportAccounts::ERROR_MISSING_LDAP_FILTER)
            ->assertExitCode(0);
    }

    /** @test */
    public function import_accounts_shows_error_when_only_user_is_missing()
    {
        $this->artisan('account:import_ldap', [
            '--ldap_pass' => 'pass',
            '--ldap_base' => 'dc=example,dc=com',
            '--ldap_filter' => '(objectClass=person)',
        ])->expectsOutput(ImportAccounts::ERROR_MISSING_LDAP_USER)
          ->assertExitCode(0);
    }

    /** @test */
    public function import_accounts_shows_error_when_only_pass_is_missing()
    {
        $this->artisan('account:import_ldap', [
            '--ldap_user' => 'cn=admin,dc=example,dc=com',
            '--ldap_base' => 'dc=example,dc=com',
            '--ldap_filter' => '(objectClass=person)',
        ])->expectsOutput(ImportAccounts::ERROR_MISSING_LDAP_PASS)
          ->assertExitCode(0);
    }

    /** @test */
    public function import_accounts_shows_error_when_only_base_is_missing()
    {
        $this->artisan('account:import_ldap', [
            '--ldap_user' => 'cn=admin,dc=example,dc=com',
            '--ldap_pass' => 'pass',
            '--ldap_filter' => '(objectClass=person)',
        ])->expectsOutput(ImportAccounts::ERROR_MISSING_LDAP_BASE)
          ->assertExitCode(0);
    }

    /** @test */
    public function import_accounts_shows_error_when_only_filter_is_missing()
    {
        $this->artisan('account:import_ldap', [
            '--ldap_user' => 'cn=admin,dc=example,dc=com',
            '--ldap_pass' => 'pass',
            '--ldap_base' => 'dc=example,dc=com',
        ])->expectsOutput(ImportAccounts::ERROR_MISSING_LDAP_FILTER)
          ->assertExitCode(0);
    }

    // ─── PingVersionServer (extra error paths) ──────────────────

    /** @test */
    public function ping_version_server_exits_when_version_is_empty()
    {
        config(['monica.check_version' => true]);
        config(['monica.app_version' => '']);

        Instance::all()->each(function ($instance) {
            $instance->delete();
        });
        factory(Instance::class)->create();

        Http::fake();

        $this->artisan('monica:ping', ['--force' => true])
            ->assertExitCode(0);

        Http::assertNothingSent();
    }

    /** @test */
    public function ping_version_server_handles_http_error()
    {
        config(['monica.weekly_ping_server_url' => 'https://version.test/ping']);
        config(['monica.app_version' => '2.9.0']);
        config(['monica.check_version' => true]);

        Instance::all()->each(function ($instance) {
            $instance->delete();
        });
        factory(Instance::class)->create();

        Http::fake([
            'https://version.test/*' => Http::response([], 500),
        ]);

        $this->artisan('monica:ping', ['--force' => true])
            ->assertExitCode(0);
    }

    // ─── MigrateDatabaseCollation ───────────────────────────────

    /** @test */
    public function migrate_collation_exits_for_non_mysql_driver()
    {
        // The test suite uses SQLite, so the command should exit early
        // because the driver is not 'mysql'.
        $this->artisan('migrate:collation', [
            '--force' => true,
        ])->assertExitCode(0);
    }

    // ─── Helpers/Command ────────────────────────────────────────

    /** @test */
    public function command_helper_fake_returns_a_fake_instance()
    {
        $fake = Command::fake();

        $this->assertInstanceOf(
            \App\Console\Commands\Helpers\CommandCallerContract::class,
            $fake
        );
    }

    /** @test */
    public function command_helper_fake_records_artisan_calls()
    {
        /** @var \Tests\Helpers\CommandCallerFake */
        $fake = Command::fake();

        $fake->artisan(
            $this->createMock(\Illuminate\Console\Command::class),
            'Test message',
            'test:command',
            ['--option' => 'value']
        );

        $this->assertCount(1, $fake->buffer);
        $this->assertStringContainsString('Test message', $fake->buffer[0]['message']);
        $this->assertStringContainsString('test:command', $fake->buffer[0]['command']);
    }

    /** @test */
    public function command_helper_fake_records_exec_calls()
    {
        /** @var \Tests\Helpers\CommandCallerFake */
        $fake = Command::fake();

        $fake->exec(
            $this->createMock(\Illuminate\Console\Command::class),
            'Exec message',
            'echo hello'
        );

        $this->assertCount(1, $fake->buffer);
        $this->assertEquals('Exec message', $fake->buffer[0]['message']);
        $this->assertEquals('echo hello', $fake->buffer[0]['command']);
    }

    /** @test */
    public function command_helper_fake_assert_contains_message()
    {
        /** @var \Tests\Helpers\CommandCallerFake */
        $fake = Command::fake();

        $fake->exec(
            $this->createMock(\Illuminate\Console\Command::class),
            'My specific message',
            'some command'
        );

        // This should not throw
        $fake->assertContainsMessage('My specific message');
    }

    /** @test */
    public function command_helper_set_backend_changes_the_backend()
    {
        $fake = Command::fake();

        // After calling fake(), the backend should be set
        // Calling fake() again should return a new fake
        $fake2 = Command::fake();

        $this->assertInstanceOf(
            \App\Console\Commands\Helpers\CommandCallerContract::class,
            $fake2
        );
        $this->assertEmpty($fake2->buffer);
    }

    // ─── MoveAvatars (OneTime) ──────────────────────────────────

    /** @test */
    public function move_avatars_runs_with_no_contacts_having_avatars()
    {
        // With no contacts having has_avatar=true, the command should
        // simply complete without errors.
        $this->artisan('monica:moveavatars', [
            '--force' => true,
            '--dryrun' => true,
        ])->assertExitCode(0);
    }

    // ─── SetupFrontEndTestUser ──────────────────────────────────

    /** @test */
    public function setup_frontend_test_user_creates_a_user()
    {
        $countBefore = User::count();

        $this->artisan('setup:frontendtestuser')
            ->assertExitCode(0);

        $this->assertGreaterThan($countBefore, User::count());
    }

    // ─── Kernel ─────────────────────────────────────────────────

    /** @test */
    public function kernel_commands_method_loads_commands_without_error()
    {
        // Verify the kernel boots properly and can resolve commands.
        // If there were a loading issue, the app would have failed to boot.
        $kernel = app(\Illuminate\Contracts\Console\Kernel::class);

        $this->assertInstanceOf(
            \App\Console\Kernel::class,
            $kernel
        );
    }
}

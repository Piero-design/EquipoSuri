<?php

namespace Tests\Unit\Listeners;

use Tests\TestCase;
use App\Models\User\User;
use App\Listeners\LoginListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use LaravelWebauthn\Facades\Webauthn;
use LaravelWebauthn\Events\WebauthnLogin;
use PragmaRX\Google2FALaravel\Events\LoginSucceeded;
use App\Events\RecoveryLogin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Auth\Events\PasswordReset;
use App\Listeners\LogoutUserDevices;

class ListenersTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_listener_subscribe()
    {
        $listener = new LoginListener();
        $dispatcher = \Mockery::mock(\Illuminate\Events\Dispatcher::class);
        
        $dispatcher->shouldReceive('listen')->with(\Illuminate\Auth\Events\Login::class, '\App\Listeners\LoginListener@onLogin')->once();
        $dispatcher->shouldReceive('listen')->with(\PragmaRX\Google2FALaravel\Events\LoginSucceeded::class, '\App\Listeners\LoginListener@onGoogle2faLogin')->once();
        $dispatcher->shouldReceive('listen')->with(\LaravelWebauthn\Events\WebauthnLogin::class, '\App\Listeners\LoginListener@onWebauthnLogin')->once();
        $dispatcher->shouldReceive('listen')->with(\App\Events\RecoveryLogin::class, '\App\Listeners\LoginListener@onRecoveryLogin')->once();
        
        $listener->subscribe($dispatcher);
        $this->assertTrue(true);
    }

    public function test_on_login()
    {
        Auth::shouldReceive('viaRemember')->andReturn(true);
        $user = factory(User::class)->create(['google2fa_secret' => '']);
        config(['google2fa.enabled' => false]);
        Webauthn::shouldReceive('enabled')->with($user)->andReturn(false);

        $event = new Login('web', $user, true);
        $listener = new LoginListener();
        $listener->onLogin($event);

        $this->assertTrue(true);
    }

    public function test_on_google2fa_login()
    {
        $user = factory(User::class)->create();
        Webauthn::shouldReceive('enabled')->with($user)->andReturn(true);
        Webauthn::shouldReceive('forceAuthenticate')->once();

        $event = new LoginSucceeded($user);
        $listener = new LoginListener();
        $listener->onGoogle2faLogin($event);

        $this->assertTrue(true);
    }

    public function test_on_webauthn_login()
    {
        $user = factory(User::class)->create(['google2fa_secret' => '']);
        config(['google2fa.enabled' => false]);

        $event = new WebauthnLogin($user);
        $listener = new LoginListener();
        $listener->onWebauthnLogin($event);

        $this->assertTrue(true);
    }

    public function test_on_recovery_login()
    {
        $user = factory(User::class)->create(['google2fa_secret' => '']);
        config(['google2fa.enabled' => false]);
        Webauthn::shouldReceive('enabled')->with($user)->andReturn(true);
        Webauthn::shouldReceive('forceAuthenticate')->once();

        $event = new RecoveryLogin($user);
        $listener = new LoginListener();
        $listener->onRecoveryLogin($event);

        $this->assertTrue(true);
    }

    public function test_logout_user_devices_handle()
    {
        $user = factory(User::class)->create();
        $event = new PasswordReset($user);
        
        config(['session.driver' => 'file']); // Avoid DB session operations
        
        $listener = new LogoutUserDevices();
        $listener->handle($event);
        
        $this->assertTrue(true);
    }
}

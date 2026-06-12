<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\AuthServiceProvider;
use App\Providers\BroadcastServiceProvider;
use App\Providers\DAVServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\MacroServiceProvider;
use App\Providers\RouteServiceProvider;

class ProvidersTest extends TestCase
{
    public function test_auth_service_provider()
    {
        $provider = new AuthServiceProvider($this->app);
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register']);
        }
        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot']);
        }
        $this->assertTrue(true);
    }

    public function test_broadcast_service_provider()
    {
        $provider = new BroadcastServiceProvider($this->app);
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register']);
        }
        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot']);
        }
        $this->assertTrue(true);
    }

    public function test_dav_service_provider()
    {
        $provider = new DAVServiceProvider($this->app);
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register']);
        }
        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot']);
        }
        $this->assertTrue(true);
    }

    public function test_event_service_provider()
    {
        $provider = new EventServiceProvider($this->app);
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register']);
        }
        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot']);
        }
        $this->assertTrue(true);
    }

    public function test_macro_service_provider()
    {
        $provider = new MacroServiceProvider($this->app);
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register']);
        }
        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot']);
        }
        $this->assertTrue(true);
    }

    public function test_route_service_provider()
    {
        $provider = new RouteServiceProvider($this->app);
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register']);
        }
        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot']);
        }
        $this->assertTrue(true);
    }
}

<?php

namespace App\Providers;

use App\Guards\KeycloakGuard;
use App\Services\KeycloakService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class KeycloakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(KeycloakService::class);
    }

    public function boot(): void
    {
        Auth::extend('keycloak', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider'] ?? 'users');

            return new KeycloakGuard(
                $provider,
                $app->make(KeycloakService::class),
                $app->make('request'),
            );
        });
    }
}

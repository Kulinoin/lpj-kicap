<?php

namespace App\Providers;

use App\Auth\UsernameEmailUserProvider;
use App\Http\Responses\Auth\RoleLoginResponse;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, RoleLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('username_email_eloquent', function ($app, array $config): UsernameEmailUserProvider {
            return new UsernameEmailUserProvider($app['hash'], $config['model']);
        });

        //
    }
}

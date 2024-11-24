<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('recaptcha', function () {
            return '<input type="hidden" name="g-recaptcha-response">';
        });

        Blade::if('permission', function (string $permission): bool {
            if (!Auth::check()) {
                return false;
            }

            return Auth::user()->hasPermission($permission);
        });
    }
}

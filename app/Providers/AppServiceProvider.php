<?php

namespace App\Providers;

use Ramsey\Uuid\Uuid;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\PersonalAccessClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // ..
    }
}

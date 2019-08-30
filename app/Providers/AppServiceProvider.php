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
        // Laravel Passport using Client UUIDs
        // Source: https://mlo.io/blog/2018/08/17/laravel-passport-uuid/
        Client::creating(function (Client $client) {
            $client->incrementing = false;
            $client->id = Uuid::uuid4()->toString();
        });

        Client::retrieved(function (Client $client) {
            $client->incrementing = false;
        });

        PersonalAccessClient::creating(function (PersonalAccessClient $client) {
            $client->incrementing = false;
            $client->id = Uuid::uuid4()->toString();
        });

        PersonalAccessClient::retrieved(function (PersonalAccessClient $client) {
            $client->incrementing = false;
        });
    }
}

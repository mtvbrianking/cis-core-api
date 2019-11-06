<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Validator;
use Laravel\Passport\Passport;

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

        $this->app->bind(BaseConstraint::class, function () {
            return new Validator();
        });
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

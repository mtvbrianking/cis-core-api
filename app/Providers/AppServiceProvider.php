<?php

namespace App\Providers;

use JsonSchema\Validator;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use JsonSchema\Constraints\BaseConstraint;

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

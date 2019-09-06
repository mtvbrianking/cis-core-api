<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Laravel\Passport\Passport;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(get_class(Passport::client()), function (Faker $faker) {
    return [
        'user_id' => $faker->uuid,
        'name' => $faker->name,
        'secret' => Str::random(40),
        'redirect' => '',
        'personal_access_client' => false,
        'password_client' => false,
        'revoked' => false,
    ];
});

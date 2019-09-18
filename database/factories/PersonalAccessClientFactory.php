<?php

use App\Models\Client;
use Faker\Generator as Faker;
use App\Models\PersonalAccessClient;

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

$factory->define(PersonalAccessClient::class, function (Faker $faker) {
    return [
        'client_id' => function () {
            return factory(Client::class)->create([
                'personal_access_client' => true,
            ])->id;
        },
    ];
});

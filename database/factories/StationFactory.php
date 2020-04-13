<?php

use App\Models\Facility;
use App\Models\Station;
use Faker\Generator as Faker;

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

$factory->define(Station::class, function (Faker $faker) {
    return [
        'facility_id' => function () {
            return factory(Facility::class)->create()->id;
        },
        'code' => $faker->firstName,
        'name' => $faker->lastName,
    ];
});

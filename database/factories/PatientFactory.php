<?php

use App\Models\Facility;
use App\Models\Patient;
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

$factory->define(Patient::class, function (Faker $faker) {
    return [
        'facility_id' => function () {
            return factory(Facility::class)->create()->id;
        },
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'date_of_birth' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'gender' => $faker->randomElement(['male', 'female']),
    ];
});

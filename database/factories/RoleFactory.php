<?php

use App\Models\Facility;
use App\Models\Role;
use Faker\Generator as Faker;

/*
 * @var \Illuminate\Database\Eloquent\Factory
 */

$factory->define(Role::class, function (Faker $faker) {
    return [
        'facility_id' => function () {
            return factory(Facility::class)->create()->id;
        },
        'name' => $faker->name,
    ];
});

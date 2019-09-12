<?php

use App\Models\Role;
use Faker\Generator as Faker;

/*
 * @var \Illuminate\Database\Eloquent\Factory
 */
$factory->define(Role::class, function (Faker $faker) {
    return [
        'name' => $faker->jobTitle,
    ];
});

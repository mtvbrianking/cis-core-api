<?php

use App\Models\Module;
use Faker\Generator as Faker;

/*
 * @var \Illuminate\Database\Eloquent\Factory
 */
$factory->define(Module::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->sentence(3),
    ];
});

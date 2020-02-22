<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Facility;
use App\Models\Pharmacy\Catalog;
use Faker\Generator as Faker;

$factory->define(Catalog::class, function (Faker $faker) {
    return [
        'facility_id' => function () {
            return factory(Facility::class)->create()->id;
        },
        'name' => $faker->name,
        'brand' => $faker->name,
        'concentration' => $faker->sentence(2),
        'package' => $faker->randomElement(['tablet', 'syrup', 'pcs', 'bottles']),
        'description' => $faker->paragraph,
    ];
});

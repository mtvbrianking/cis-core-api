<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Facility;
use App\Models\Pharmacy\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
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

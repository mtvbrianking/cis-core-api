<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Pharmacy\Batch;
use App\Models\Pharmacy\Catalog;
use App\Models\Pharmacy\Store;
use Faker\Generator as Faker;

$factory->define(Batch::class, function (Faker $faker) {
    return [
        'store_id' => function () {
            return factory(Store::class)->create()->id;
        },
        'catalog_id' => function () {
            return factory(Catalog::class)->create()->id;
        },
        'quantity' => $faker->numberBetween(0, 100),
        'unit_price' => $faker->randomFloat(),
        'mfd_at' => $faker->dateTime($max = 'now'),
        'expires_at' => $faker->dateTimeBetween('now', '+10 days'),
    ];
});

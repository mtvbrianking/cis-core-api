<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Pharmacy\Inventory;
use App\Models\Pharmacy\Product;
use App\Models\Pharmacy\Store;
use Faker\Generator as Faker;

$factory->define(Inventory::class, function (Faker $faker) {
    return [
        'store_id' => function () {
            return factory(Store::class)->create()->id;
        },
        'product_id' => function () {
            return factory(Product::class)->create()->id;
        },
        'quantity' => $faker->numberBetween(0, 100),
        'unit_price' => $faker->randomFloat(),
    ];
});

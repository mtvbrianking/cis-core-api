<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Store;
use Faker\Generator as Faker;

$factory->define(Inventory::class, function (Faker $faker) {

    $batch = factory(Batch::class)->create();

    return [
        'store_id' => function () use ($batch) {
            return factory(Store::class)->create(['facility_id' => $batch->facility_id])->id;
        },
        'batch_id' => $batch->id,
        'quantity' => $faker->numberBetween(0, 1000)
    ];
});

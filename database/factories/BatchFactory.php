<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Batch;
use App\Models\Catalog;
use Faker\Generator as Faker;

$factory->define(Batch::class, function (Faker $faker) {
    $catalog = factory(Catalog::class)->create();

    return [
        'facility_id'   => $catalog->facility_id,
        'catalog_id'    => $catalog->id,
        'cost_price' => $faker->randomFloat(),
        'manufactured_date' => $faker->dateTime($max = 'now'),
        'expires_at' => $faker->dateTimeBetween('now', '+10 days')
    ];
});

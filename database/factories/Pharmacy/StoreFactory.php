<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Facility;
use App\Models\Pharmacy\Store;
use Faker\Generator as Faker;

$factory->define(Store::class, function (Faker $faker) {
    return [
        'facility_id' => function () {
            return factory(Facility::class)->create()->id;
        },
        'name' => $faker->name,
    ];
});

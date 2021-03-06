<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Facility;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\Store;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Sale::class, function (Faker $faker) {
    $facility = factory(Facility::class)->create();

    return [
        'store_id' => function () use ($facility) {
            return factory(Store::class)->create([
                'facility_id' => $facility->id,
            ])->id;
        },
        'user_id' => function () use ($facility) {
            return factory(User::class)->create([
                'facility_id' => $facility->id,
            ])->id;
        },
        'patient_id' => null,
        'tax_rate' => 0,
        'total' => 100,
    ];
});

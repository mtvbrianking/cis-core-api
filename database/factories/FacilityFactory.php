<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Facility;
use Faker\Generator as Faker;

$factory->define(Facility::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->sentence,
        'address' => $faker->streetAddress,
        'email' => $faker->safeEmail,
        'website' => $faker->safeEmailDomain,
        'phone' => $faker->phoneNumber,
    ];
});

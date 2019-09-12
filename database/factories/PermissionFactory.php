<?php

use \App\Models\Module;
use App\Models\Permission;
use Faker\Generator as Faker;

/*
 * @var \Illuminate\Database\Eloquent\Factory
 */
$factory->define(Permission::class, function (Faker $faker) {
    return [
        'module_name' => function () {
            return factory(Module::class)->create()->name;
        },
        'name' => $faker->word,
        'description' => $faker->sentence(4),
    ];
});

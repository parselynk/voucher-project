<?php

use Faker\Generator as Faker;

$factory->define(App\Offer::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'discount' => (float)(mt_rand(0, 100)),
    ];
});

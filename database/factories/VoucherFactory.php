<?php

use Faker\Generator as Faker;

$factory->define(App\Voucher::class, function (Faker $faker) {
    return [
        'code' => str_random(10),
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        },
        'used_at' => null
    ];
});

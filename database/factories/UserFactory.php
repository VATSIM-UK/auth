<?php

$factory->define(\App\User::class, function (Faker\Generator $faker) {
    return [
        'id' => rand(10000000, 99999999),
        'name_first' => $faker->firstName,
        'name_last' => $faker->lastName,
        'email' => $faker->email,
        'is_invisible' => 0,
    ];
});

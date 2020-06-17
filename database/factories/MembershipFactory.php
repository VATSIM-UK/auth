<?php

$factory->define(\App\Models\Membership::class, function (Faker\Generator $faker) {
    return [
        'identifier' => $faker->toUpper($faker->lexify('???')),
        'name' => $faker->words(3, true),
    ];
});

$factory->state(\App\Models\Membership::class, 'secondary', [
    'primary' => false,
]);

<?php

$factory->define(\App\Models\Ban::class, function (Faker\Generator $faker) {
    return [
        'user_id' => function () {
            return factory(\App\User::class)->create();
        },
        'banner_id' => function () {
            return factory(\App\User::class)->create();
        },
        'reason_id' => function () {
            return factory(\App\Models\Ban\Reason::class)->create();
        },
        'type' => \App\Constants\BanTypeConstants::getRandomValue(),
        'body' => $faker->paragraph,
        'starts_at' => \Carbon\Carbon::now(),
        'ends_at' => \Carbon\Carbon::now()->addDays($faker->randomDigitNot(0)),
    ];
});

$factory->define(\App\Models\Ban\Reason::class, function (Faker\Generator $faker) use ($factory) {
    return [
        'name' => $faker->name,
        'body' => $faker->paragraph,
        'period' => $faker->randomElement(['1D1M', 'T12H10S', '30DT12H']),
    ];
});

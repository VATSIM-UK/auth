<?php

$factory->define(\App\Models\Ban::class, function (Faker\Generator $faker) {
    return [
        'user_id' => factory(\App\User::class),
        'banner_id' => factory(\App\User::class),
        'reason_id' => factory(\App\Models\Ban\Reason::class),
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

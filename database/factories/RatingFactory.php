<?php

$factory->define(\App\Models\Rating::class, function (Faker\Generator $faker) {
    return [
        'code' => $faker->bothify('?##'),
        'code_long' => $faker->bothify('???##'),
        'name' => $faker->word,
        'name_long' => "$faker->word $faker->word",
        'vatsim_id' => $faker->randomDigit,
    ];
});

$factory->defineAs(\App\Models\Rating::class, 'atc', function (Faker\Generator $faker) use ($factory) {
    $atc = $factory->raw(\App\Models\Rating::class);
    return array_merge($atc, [
        'code' => $faker->bothify('C##'),
        'code_long' => $faker->bothify('STU##'),
        'type' => \App\Constants\RatingTypeConstants::ATC,
    ]);
});
$factory->defineAs(\App\Models\Rating::class, 'pilot', function (Faker\Generator $faker) use ($factory) {
    $atc = $factory->raw(\App\Models\Rating::class);
    return array_merge($atc, [
        'code' => $faker->bothify('P##'),
        'code_long' => $faker->bothify('P##'),
        'type' =>  \App\Constants\RatingTypeConstants::PILOT,
    ]);
});

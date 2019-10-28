<?php

$factory->define(\App\Models\Rating::class, function (Faker\Generator $faker) {
    return [
    'code' => $faker->bothify('?##'),
        'name_small' => $faker->word,
        'name_long' => $faker->word,
        'name_grp' => $faker->word,
        'vatsim' => $faker->randomDigit,
    ];
});

$factory->defineAs(\App\Models\Rating::class, 'atc', function (Faker\Generator $faker) use ($factory) {
    $atc = $factory->raw(\App\Models\Rating::class);
    return array_merge($atc, [
        'code' => $faker->bothify('?##'),
        'type' => 'atc',
    ]);
});
$factory->defineAs(\App\Models\Rating::class, 'pilot', function (Faker\Generator $faker) use ($factory) {
    $atc = $factory->raw(\App\Models\Rating::class);
    return array_merge($atc, [
        'code' => $faker->bothify('?##'),
        'type' => 'pilot',
    ]);
});

<?php

$factory->define(\App\Models\Role::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(3, true)
    ];
});

$factory->define(\App\Models\Permissions\Assignment::class, function (Faker\Generator $faker) {
    return [
        'related_type' => \App\Models\Role::class,
        'related_id' => function () {
            return factory(\App\Models\Role::class)->create()->id;
        },
        'permission' => '*'
    ];
});

$factory->defineAS(\App\Models\Permissions\Assignment::class, 'user', function (Faker\Generator $faker) {
    return [
        'related_type' => \App\User::class,
        'related_id' => function () {
            return factory(\App\User::class)->create()->id;
        },
        'permission' => '*'
    ];
});

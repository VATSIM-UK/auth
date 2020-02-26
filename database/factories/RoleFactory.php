<?php

$factory->define(\App\Models\Role::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(3, true),
    ];
});

$factory->define(\App\Models\Permissions\Assignment::class, function () {
    return [
        'related_type' => \App\Models\Role::class,
        'related_id' => factory(\App\Models\Role::class),
        'permission' => '*',
    ];
});

$factory->defineAS(\App\Models\Permissions\Assignment::class, 'user', function () {
    return [
        'related_type' => \App\User::class,
        'related_id' => factory(\App\User::class),
        'permission' => '*',
    ];
});

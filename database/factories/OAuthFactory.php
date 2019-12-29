<?php

use App\Passport\Client;

$factory->define(Client::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->sentence(2),
        'secret' => $faker->sha256,
        'redirect' => '',
        'personal_access_client' => true,
        'password_client' => true,
        'revoked' => false,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});

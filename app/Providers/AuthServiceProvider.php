<?php

namespace App\Providers;

use App\Passport\Client;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        // Register passport routes
        Passport::routes();

        // Override Default Client Model
        Passport::useClientModel(Client::class);
    }
}

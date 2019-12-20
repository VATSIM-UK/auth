<?php

namespace App\Providers;

use App\Passport\Client;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Schema;
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

        // Enable personal access client on development environments
        if (Schema::hasTable('oauth_clients') && $client = Client::where('personal_access_client', true)->first(['id'])) {
            Passport::personalAccessClientId($client->id);
        }
    }
}

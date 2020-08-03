<?php

namespace Tests\Unit\Commands;

use App\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenFactory;
use Laravel\Passport\PersonalAccessTokenResult;
use Tests\TestCase;

class GenerateAuthTokenCommandTest extends TestCase
{
    /** @test */
    public function itFailsSafelyWithNoClient()
    {
        if (Passport::$personalAccessClientId != null) {
            fwrite(STDOUT, "Personal Access Client Installed. Skipped test 'itFailsSafelyWithNoClient'.");
            $this->expectNotToPerformAssertions();

            return;
        }
        $this->artisan("token:generate {$this->user->id}")
            ->expectsOutput('No personal access client has been setup. Create one with "php artisan passport:client --personal"');
    }

    /** @test */
    public function itFailsSafelyWithInvalidUser()
    {
        $client = app()->make(ClientRepository::class)->create(null, 'Personal Access Client', '/', true);
        Passport::personalAccessClientId($client->id);

        $this->artisan('token:generate 1234')
            ->expectsOutput('A user was not found with the ID 1234');
    }

    /** @test */
    public function itGeneratesATokenWhenAble()
    {
        $id = factory(Client::class)->create([
            'personal_access_client' => true,
        ])->id;
        Passport::personalAccessClientId($id);

        $this->partialMock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldReceive('make')
                ->andReturn(new PersonalAccessTokenResult(
                    'eYMyJWTHere', 'eYMyJWTHere'
                ));
        });

        $this->artisan("token:generate {$this->user->id}")
            ->expectsOutput('Success! Token: eYMyJWTHere');
    }
}

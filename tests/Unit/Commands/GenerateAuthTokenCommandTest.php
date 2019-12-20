<?php


namespace Tests\Unit\Commands;


use App\Passport\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenFactory;
use Laravel\Passport\PersonalAccessTokenResult;
use Tests\TestCase;

class GenerateAuthTokenCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itFailsSafelyWithNoClient()
    {
        Artisan::call("token:generate {$this->user->id}");
        $this->assertEquals("No personal access client has been setup. Create one with \"php artisan passport:client --personal\"\n", Artisan::output());
    }

    /** @test */
    public function itFailsSafelyWithInvalidUser()
    {
        $client = app()->make(ClientRepository::class)->create(null, 'Personal Access Client', '/', true);
        Passport::personalAccessClientId($client->id);

        Artisan::call("token:generate 1234");
        $this->assertEquals("A user was not found with the ID 1234\n", Artisan::output());
    }

    /** @test */
    public function itGeneratesATokenWhenAble()
    {
        $id = factory(Client::class)->create([
            'personal_access_client' => true
        ])->id;
        Passport::personalAccessClientId($id);

        $this->partialMock(PersonalAccessTokenFactory::class, function ($mock) {
            $mock->shouldReceive("make")
                ->andReturn(new PersonalAccessTokenResult(
                    'eYMyJWTHere', 'eYMyJWTHere'
                ));
        });

        Artisan::call("token:generate {$this->user->id}");
        $this->assertEquals("Success! Token: eYMyJWTHere\n", Artisan::output());
    }
}
